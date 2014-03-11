<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\BusinessLayer;

use \OCA\Calendar\AppFramework\Db\DoesNotExistException;
use \OCA\Calendar\AppFramework\Utility\TimeFactory;
use \OCA\Calendar\AppFramework\Core\API;

use \OCA\Calendar\Db\CalendarMapper;
use \OCA\Calendar\Db\Calendar;

class CalendarBusinessLayer extends BusinessLayer {

	public function __construct(CalendarMapper $calendarMapper,
								BackendBusinessLayer $backends,
	                            API $api){
		$this->mapper = $calendarMapper;
		parent::__construct($api, $calendarMapper, $backends);
	}

	/**
	 * Find calendars of user $userId
	 * @param string $userId
	 * @param int $limit
	 * @param int $offset
	 * @return array containing all Calendar items
	 */
	public function findAll($userId=null, $limit=null, $offset=null) {
		try {
			if($userId === null) {
				$userId = $this->api->getUserId();
			}

			if($limit !== null) {
				$limit = (int) $limit;
			}
	
			if($offset !== null || $limit !== null) {
				$offset = (int) $offset;
			}

			$calendars = $this->mapper->findAll($userId, $limit, $offset);

			return $calendars;
		} catch (DoesNotExistException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch(BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * Find calendar $calendarId of user $userId
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $uid UID of the object
	 * @param string $userId
	 * @throws BusinessLayerException if backend does not exist
	 * @throws BusinessLayerException if backend is disabled
	 * @return calendar item
	 */
	public function find($calendarId, $userId=null) {
		try {
			if($userId === null) {
				$userId = $this->api->getUserId();
			}

			list($backend, $calendarURI) = $this->splitCalendarURI($calendarId);

			$this->checkBackendEnabled($backend);

			$calendar = $this->mapper->find($backend, $calendarURI, $userId);

			return $calendar;
		} catch (DoesNotExistException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch (MultipleObjectsReturnedException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch (BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * create a new calendar
	 * @param Calendar $calendar
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $userId
	 * @throws BusinessLayerException if name exists already
	 * @throws BusinessLayerException if backend does not exist
	 * @throws BusinessLayerException if backend is disabled
	 * @throws BusinessLayerException if backend does not implement creating a calendar
	 * @return Calendar $calendar - calendar object
	 */
	public function create(Calendar $calendar, $userId=null) {
		try {
			if($userId === null) {
				$userId = $this->api->getUserId();
			}

			$backend = $calendar->getBackend();
			$calendarURI = $calendar->getUri();

			$this->checkBackendEnabled($backend);

			$this->allowNoCalendarURITwice($backend, $calendarURI, $userId);

			$api = &$this->backends->find($backend)->api;
			$this->checkBackendSupports($backend, \OCA\Calendar\Backend\CREATE_CALENDAR);

			if($calendar->isValid() === false) {
				$calendar->fix();
			}

			$calendar = $api->createCalendar($calendar);
			$this->mapper->insertEntity($calendar, $backend, $calendarURI, $userId);

			return $calendar;
		} catch (DoesNotImplementException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch (BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch (CalendarNotFixable $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * update a new calendar
	 * @param Calendar $calendar
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $userId
	 * @throws BusinessLayerException if backend does not exist
	 * @throws BusinessLayerException if backend is disabled
	 * @throws BusinessLayerException if backend does not implement updating a calendar
	 * @return Calendar $calendar - calendar object
	 */
	public function update(Calendar $calendar, $calendarId, $userId=null) {
		try {
			if($userId === null) {
				$userId = $this->api->getUserId();
			}

			list($backend, $calendarURI) = $this->splitCalendarURI($calendarId);

			$this->checkBackendEnabled($backend);

			/* Move calendar to another backend when:
			 *  - the backend changed
			 *  - uri is available on the other calendar
			 */
			if($calendar->getBackend() !== $backend &&
			   $this->isCalendarURIAvailable($calendar->getBackend(), $calendar->getUri(), $userId) === true) {
				return $this->move($calendar, $calendarId, $userId);
			}

			/* Merge calendar with another one when:
			 *  - the backend changed
			 *  - uri is not available on the other backend
			 */
			elseif($calendar->getBackend() !== $backend &&
			   $this->isCalendarURIAvailable($calendar->getBackend(), $calendar->getUri(), $userId) === false) {
				return $this->merge($calendar, $calendarId, $userId);
			}

			/* Merge calendar with another one when:
			 *  - uri changed
			 *  - uri is not available
			 */
			elseif($calendar->getUri() !== $calendarURI &&
			   $this->isCalendarURIAvailable($backend, $calendar->getUri(), $userId) === false) {
				return $this->merge($calendar, $calendarId, $userId);
			}

			/* Update calendar when:
			 *  - uri didn't change
			 *  - uri changed but new uri is available
			 */
			else {
				$api = &$this->backends->find($backend)->api;
				$this->checkBackendSupports($backend, \OCA\Calendar\Backend\UPDATE_CALENDAR);

				if($calendar->isValid() === false) {
					$calendar->fix();
				}

				$calendar = $api->updateCalendar($calendar, $calendarURI, $userId);
				$this->mapper->updateEntity($calendar, $backend, $calendarURI, $userId);

				return $calendar;
			}
		} catch(DoesNotImplementException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch(BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch (CalendarNotFixable $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * merge the defined by $calendarId and $userId into calendar defined by properties in $calendar
	 * @param Calendar $calendar
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $userId
	 * @throws BusinessLayerException if backends do not exist
	 * @throws BusinessLayerException if backends are disabled
	 * @throws BusinessLayerException if backend does not implement updating a calendar
	 * @return Calendar $calendar - calendar object
	 */
	public function merge(Calendar $calendar, $calendarId, $userId=null) {
		try {
			if($userId === null) {
				$userId = $this->api->getUserId();
			}

			list($oldBackend, $oldCalendarURI) = $this->splitCalendarURI($calendarId);

			$newBackend = $calendar->getBackend();
			$newCalendarURI = $calendar->getUri();

			$this->checkBackendEnabled($oldBackend);
			$this->checkBackendEnabled($newBackend);

			if($oldBackend === $newBackend && $oldCalendarURI === $newCalendarURI) {
				throw new BusinessLayerException('Can not merge calendar with itself.');
			}
	
			if($this->isCalendarURIAvailable($newBackend, $newCalendarURI, $userId) === true) {
				throw new BusinessLayerException('Can not merge calendar. Target-calendar does not exist!');
			}

			$this->checkBackendSupports($oldBackend, \OCA\Calendar\Backend\DELETE_CALENDAR);
			$this->checkBackendSupports($oldBackend, \OCA\Calendar\Backend\DELETE_OBJECT);
			$this->checkBackendSupports($newBackend, \OCA\Calendar\Backend\CREATE_OBJECT);

			$oldBackendsAPI = &$this->backends->find($oldBackend)->api;
			$newBackendsAPI = &$this->backends->find($newBackend)->api;

			/*$allObjects = $oldBackendsAPI->findObjects($oldCalendarURI, $userId);

			// use object businesslayer for this task

			$overallStatus = true;
			foreach($allObjects as $object) {
				$createStatus = $newBackendsAPI->createObject($object, $calendar->getUri(), $uid, $userId);
				if($createStatus === true) {
					$$oldBackendsAPI->deleteObject($calendar->getUri, $object->getUid(), $userId);
				} else {
					$overallStatus = false;
				}
			}

			//only delete old calendar if all objects were copied successfully 
			if($overallStatus === true) {
				$oldBackendsAPI->deleteCalendar($oldCalendarURI, $userId);
			}*/

			return $calendar;
		} catch(DoesNotImplementException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch(BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch (CalendarNotFixable $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * move a calendar to a different backend
	 * @param Calendar $calendar
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $userId
	 * @throws BusinessLayerException if backends do not exist
	 * @throws BusinessLayerException if backends are disabled
	 * @throws BusinessLayerException if old backend does not implement deleting a calendar
	 * @throws BusinessLayerException if old backend does not implement deleting an object
	 * @throws BusinessLayerException if new backend does not implement creating a calendar
	 * @throws BusinessLayerException if new backend does not implement creating an object
	 * @return Calendar $calendar - calendar object
	 */
	public function move(Calendar $calendar, $calendarId, $userId=null) {
		try {
			if($userId === null) {
				$userId = $this->api->getUserId();
			}

			list($oldBackend, $oldCalendarURI) = $this->splitCalendarURI($calendarId);

			$newBackend = $calendar->getBackend();
			$newCalendarURI = $calendar->getUri();

			$this->checkBackendEnabled($oldBackend);
			$this->checkBackendEnabled($newBackend);

			if($oldBackend === $newBackend && $oldCalendarURI === $$newCalendarURI) {
				throw new BusinessLayerException('Can not move calendar to another backend. Calendar is already stored in this backend.');
			}

			$this->checkBackendSupports($oldBackend, \OCA\Calendar\Backend\DELETE_CALENDAR);
			$this->checkBackendSupports($oldBackend, \OCA\Calendar\Backend\DELETE_OBJECT);
			$this->checkBackendSupports($newBackend, \OCA\Calendar\Backend\CREATE_CALENDAR);
			$this->checkBackendSupports($newBackend, \OCA\Calendar\Backend\CREATE_OBJECT);

			$oldBackendsAPI = &$this->backends->find($oldBackend)->api;
			$newBackendsAPI = &$this->backends->find($newBackend)->api;

			/*$allObjects = $oldBackendsAPI->findObjects($calendarURI, $userId);
			$calendar = $newBackendsAPI->createCalendar($calendar, $calendar->getUri(), $userId);

			$overallStatus = true;
			
			// use object businesslayer for this task
			
			foreach($allObjects as $object) {
				$createStatus = $newBackendsAPI->createObject($object, $calendar->getUri(), $uid, $userId);
				if($createStatus === true) {
					$$oldBackendsAPI->deleteObject($calendar->getUri, $object->getUid(), $userId);
				} else {
					$overallStatus = false;
				}
			}

			//TODO: UPDATE CACHE !!!!!1111ONEONEONEELEVEN

			//only delete old calendar if all objects were copied successfully 
			if($overallStatus === true) {
				$oldBackendsAPI->deleteCalendar($calendarURI, $userId);
			}*/

			return $calendar;
		} catch(DoesNotImplementException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch(BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch (CalendarNotFixable $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * delete a calendar
	 * @param Calendar $calendar
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $userId
	 * @throws BusinessLayerException if backend does not exist
	 * @throws BusinessLayerException if backend is disabled
	 * @throws BusinessLayerException if backend does not implement updating a calendar
	 * @return Calendar $calendar - calendar object
	 */
	public function delete($calendarId, $userId) {
		try {
			if($userId === null) {
				$userId = $this->api->getUserId();
			}

			list($backend, $calendarURI) = $this->splitCalendarURI($calendarId);

			$this->checkBackendEnabled($backend);

			$this->checkBackendSupports($backend, \OCA\Calendar\Backend\DELETE_CALENDAR);

			$api = &$this->backends->find($backend)->api;
			$api->deleteCalendar($calendarURI, $userId);
			$this->mapper->deleteEntity($backend, $calendarURI, $userId);

			return true;
		} catch(DoesNotImplementException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch(BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * touch a calendar
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $userId
	 * @throws BusinessLayerException if backends do not exist
	 * @throws BusinessLayerException if backends are disabled
	 * @throws BusinessLayerException if backend does not implement updating a calendar
	 * @return Calendar $calendar - calendar object
	 */
	public function touch($calendarId, $userId) {
		$calendar = $this->find($calendarId, $userId);

		$calendar->incrementCtag();
		$calendar = $this->update($calendar, $calendarId, $userId);

		return $calendar;
	}

	/**
	 * make sure that uri does not already exist when creating a new calendar
	 * @param string $backend
	 * @param string $calendarURI
	 * @param string $userId
	 * @return null
	 * @throws BusinessLayerException if uri is already taken
	 */
	private function allowNoCalendarURITwice($backend, $calendarURI, $userId){
		if($this->isCalendarURIAvailable($backend, $calendarURI, $userId, true) === false) {
			throw new BusinessLayerException('Can not add calendar: URI is already taken!');
		}
	}


	/**
	 * checks if a uri is available
	 * @param string $backend
	 * @param string $calendarURI
	 * @param string $userId
	 * @param boolean $checkRemote
	 * @return boolean
	 */
	private function isCalendarURIAvailable($backend, $calendarURI, $userId, $checkRemote=false) {
		$existingCalendars = (int) $this->mapper->countFind($backend, $calendarURI, $userId);

		if($existingCalendars !== 0) {
			return false;
		}

		try{
			if($checkRemote === true) {
				$api = &$this->backends->find($backend)->api;
				$existingRemoteCalendars = $api->findCalendar($calendarURI, $userId);
				if(count($existingRemoteCalendars) !== 0) {
					return false;
				}
			}
		} catch(DoesNotExistException $ex) {}

		return true;
	}

	/**
	 * slugify a calendarURI
	 * @param string $calendarURI
	 * @return string
	 */
	private function slugifyURI($calendarURI) {
		return CalendarUtility::slugify($calendarURI);
	}
}