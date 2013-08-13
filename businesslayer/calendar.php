<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
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

	private $autoPurgeMinimumInterval;
	private $timeFactory;

	public function __construct(CalendarMapper $calendarMapper,
								BackendBusinessLayer $backends,
	                            API $api,
	                            TimeFactory $timeFactory){
		$this->mapper = $calendarMapper;
		$this->timeFactory = $timeFactory;
		parent::__construct($api, $calendarMapper, $backends);
	}

	/**
	 * Find calendars of user $userId
	 * @param string $userId
	 * @return array containing all Calendar items
	 */
	public function findAll($userId) {
		return $this->mapper->findAll($userId);
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
	public function find($calendarId, $userId) {
		list($backend, $calendarURI) = $this->getBackendAndRealURIFromURI($calendarId);
		$this->checkBackendEnabled($backend);

		try {
			return $this->mapper->find($backend, $calendarURI, $userId);
		} catch (DoesNotExistException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch (MultipleObjectsReturnedException $ex) {
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
	public function create($calendar, $calendarId, $userId) {
		list($backend, $calendarURI) = $this->getBackendAndRealURIFromURI($calendarId);
		$this->checkBackendEnabled($backend);

		try {
			$this->allowNoCalendarURITwice($calendarId, $userId);
			$this->backends->checkEnabled($backend);

			$api = &$this->backends->find($backend)->api;
			$this->checkBackendSupports($backend, \OCA\Calendar\Backend\CREATE_CALENDAR);

			$calendar = $api->createCalendar($calendar, $calendarURI, $userId);
			$this->mapper->insert($calendar, $calendarURI, $userId);

			return $calendar;
		} catch(DoesNotImplementException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch(BackendException $ex) {
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
	public function update($calendar, $calendarId, $userId) {
		list($backend, $calendarURI) = $this->getBackendAndRealURIFromURI($calendarId);
		$this->checkBackendEnabled($backend);

		try {
			if($calendar->getBackend() !== $backend) {
				return $this->move($calendar, $calendarId, $userId);
			}

			if($calendar->getUri() !== $calendarURI) {
				
			}

			$api = &$this->backends->find($backend)->api;
			$this->checkBackendSupports($backend, \OCA\Calendar\Backend\UPDATE_CALENDAR);

			$calendar = $api->updateCalendar($calendar, $calendarURI, $userId);
			$this->mapper->update($calendar, $calendarURI, $userId);

			return $calendar;
		} catch(DoesNotImplementException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch(BackendException $ex) {
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
	public function move($calendar, $calendarId, $userId) {
		list($backend, $calendarURI) = $this->getBackendAndRealURIFromURI($calendarId);
		$this->checkBackendEnabled($backend);

		try {
			$oldBackend = $backend;
			$newBackend = $calendar->getBackend();
			if($oldBackend === $newBackend) {
				return $calendar;
			}

			$this->backends->checkEnabled($oldBackend);
			$this->backends->checkEnabled($newBackend);

			$this->checkBackendSupports($oldBackend, \OCA\Calendar\Backend\DELETE_CALENDAR);
			$this->checkBackendSupports($oldBackend, \OCA\Calendar\Backend\DELETE_OBJECT);
			$this->checkBackendSupports($newBackend, \OCA\Calendar\Backend\CREATE_CALENDAR);
			$this->checkBackendSupports($newBackend, \OCA\Calendar\Backend\CREATE_OBJECT);

			$oldBackendsAPI = &$this->backends->find($oldBackend)->api;
			$newBackendsAPI = &$this->backends->find($newBackend)->api;

			$allObjects = $oldBackendsAPI->findObjects($calendarURI, $userId);
			$calendar = $newBackendsAPI->createCalendar($calendar, $calendar->getUri(), $userId);

			foreach($allObjects as $object) {
				$newBackendsAPI->createObject($object, $calendar->getUri(), $uid, $userId);
				$oldBackendsAPI->deleteObject($calendar->getUri, $object->getUid(), $userId);
			}

			$oldBackendsAPI->deleteCalendar($calendarURI, $userId);
			return $calendar;
		} catch(DoesNotImplementException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch(BackendException $ex) {
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
	public function merge($calendar, $calendarId, $userId) {
		list($backend, $calendarURI) = $this->getBackendAndRealURIFromURI($calendarId);
		$this->checkBackendEnabled($backend);

		try {
			
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
	 * touch a calendar
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $userId
	 * @throws BusinessLayerException if backends do not exist
	 * @throws BusinessLayerException if backends are disabled
	 * @throws BusinessLayerException if backend does not implement updating a calendar
	 * @return Calendar $calendar - calendar object
	 */
	public function markDeleted($calendarURI, $userId) {
		$calendar = $this->find($calendarURI, $userId);
		$calendar->setDeletedAt($this->timeFactory->getTime());
		$this->mapper->update($calendar);
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
	public function unmarkDeleted($calendarURI, $userId) {
		$calendar = $this->find($calendarURI, $userId);
		$calendar->setDeletedAt(0);
		$this->mapper->update($calendar);
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
	public function purgeDeleted($userId=null, $useInterval=true) {
		$deleteOlderThan = null;

		if ($useInterval) {
			$now = $this->timeFactory->getTime();
			$deleteOlderThan = $now - $this->autoPurgeMinimumInterval;
		}

		$toDelete = $this->mapper->getToDelete($deleteOlderThan, $userId);

		foreach ($toDelete as $calendar) {
			try {
				$this->backends->find($calendar->getBackend())->api->deleteCalendar($calendar->getURI());
				$this->mapper->delete($calendar);
			} catch (Exception $ex) {}
		}
	}

	/**
	 * make sure that uri does not already exist when creating a new calendar
	 * @param string $backend
	 * @param string $calendarURI
	 * @param string $userId
	 * @throws BusinessLayerException if uri is already taken
	 */
	private function allowNoCalendarURITwice($backend, $calendarURI, $userId){
		$isAvailable = $this->isCalendarURIAvailable($backend, $calendarURI, $userId);
		if(!$isAvailable) {
			throw new BusinessLayerException('Can not add calendar: URI exists already');
		}
	}

	/**
	 * suggest available uri for backend
	 * if given uri is already available, the given uri will be returned
	 * @param string $backeend
	 * @param string $calendarURI
	 * @param string $userId
	 * @return string $calendarURI available uri
	 */
	private function suggestCalendarURI($backend, $calendarURI, $userId) {
		while(!$this->isCalendarURIAvailable($backend, $calendarURI, $userId)) {
			if(substr_count($calendarURI, '-') === 0) {
				$calendarURI . '-1';
			} else {
				$positionLastDash = strrpos($calendarURI, '-');
				$firstPart = substr($calendarURI, 0, strlen($calendarURI) - $positionLastDash);
				$lastPart = substr($calendarURI, $positionLastDash + 1);
				$pattern = "^\d$";
				if(preg_match($pattern, $lastPart)) {
					$lastPart = (int) $lastPart;
					$lastPart++;
					$calendarURI = $firstPart . '-' . $lastPart;
				} else {
					$calendarURI . '-1';
				}
			}
		}
		return $calendarURI;
	}

	/**
	 * checks if a uri is available
	 * @param string $backend
	 * @param string $calendarURI
	 * @param string $userId
	 * @return boolean
	 */
	private function isCalendarURIAvailable($backend, $calendarURI, $userId) {
		$existingCalendars = $this->mapper->find($backend, $calendarURI, $userId);
		if(count($existingCalendars) > 0) {
			return false;
		}

		$existingRemoteCalendars = $this->backends->find($backend)->api->findCalendar($calendarURI, $userId);
		if(count($existingRemoteCalendars) > 0) {
			return false;
		}

		return true;
	}
}