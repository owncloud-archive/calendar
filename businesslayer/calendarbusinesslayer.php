<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\BusinessLayer;

use \OCP\AppFramework\IAppContainer;

use \OCA\Calendar\Db\Calendar;
use \OCA\Calendar\Db\CalendarCollection;
use \OCA\Calendar\Db\CalendarMapper;

use \OCA\Calendar\Db\DoesNotExistException;

use \OCA\Calendar\Utility\CalendarUtility;

class CalendarBusinessLayer extends BusinessLayer {

	private $cacheMapper;
	private $objectBusinessLayer;

	private $runtimeCalendarCache=array();
	private $remoteCalendarObjectCache=array();

	/**
	 * @param CalendarMapper $objectMapper: mapper for objects cache
	 * @param ObjectBusinessLayer $objectBusinessLayer
	 * @param BackendBusinessLayer $backendBusinessLayer
	 * @param API $api: an api wrapper instance
	 */
	public function __construct(IAppContainer $api,
								BackendBusinessLayer $backends,
								CalendarMapper $calendarMapper,
								ObjectBusinessLayer $objectBusinessLayer){

		parent::__construct($api, $backends);

		$this->cacheMapper = $calendarMapper;
		$this->objectBusinessLayer = $objectBusinessLayer;
	}

	/**
	 * Find calendars of user $userId
	 * @param string $userId
	 * @param int $limit
	 * @param int $offset
	 * @throws BusinessLayerException
	 * @return CalendarCollection
	 */
	public function findAll($userId, $limit=null, $offset=null) {
		try {
			$calendars = $this->cacheMapper->findAll($userId, $limit, $offset);

			//check if $calendars is a CalendarCollection, if not throw an exception
			if(($calendars instanceof CalendarCollection) === false) {
				$msg  = 'CalendarBusinessLayer::findAll(): Internal Error: ';
				$msg .= 'CalendarCache returned unrecognised format!';
				throw new BusinessLayerException($msg);
			}

			return $calendars;
		} catch (DoesNotExistException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch (BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * Find all calendars of user $userId on a backend
	 * @param string $backend
	 * @param string $userId
	 * @param int $limit
	 * @param int $offset
	 * @throws BusinessLayerException
	 * @return CalendarCollection
	 */
	public function findAllOnBackend($backend, $userId, $limit=null, $offset=null) {
		try {
			$calendars = $this->cacheMapper->findAllOnBackend($backend, $userId, $limit, $offset);

			//check if $calendars is a CalendarCollection, if not throw an exception
			if(($calendars instanceof CalendarCollection) === false) {
				$msg  = 'CalendarBusinessLayer::findAll(): Internal Error: ';
				$msg .= 'CalendarCache returned unrecognised format!';
				throw new BusinessLayerException($msg);
			}

			return $calendars;
		} catch (DoesNotExistException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch (BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * number of calendars
	 * @param string $userId
	 * @throws BusinessLayerException
	 * @return integer
	 */
	public function numberOfAllCalendars($userId) {
		try {
			$number = $this->cacheMapper->countAll($userId);

			//check if number is an integer, if not throw an exception
			if(gettype($number) !== 'integer') {
				$msg  = 'CalendarBusinessLayer::numberOfAllCalendars(): Internal Error: ';
				$msg .= 'CalendarCache returned unrecognised format!';
				throw new BusinessLayerException($msg);
			}

			return $number;
		} catch (DoesNotExistException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * number of calendars on a backend
	 * @param string $backend
	 * @param string $userId
	 * @throws BusinessLayerException
	 * @return integer
	 */
	public function numberOfAllCalendarsOnBackend($backend, $userId) {
		try {
			$number = $this->cacheMapper->countOnBackend($backend, $userId);

			//check if number is an integer, if not throw an exception
			if(gettype($number) !== 'integer') {
				$msg  = 'CalendarBusinessLayer::numberOfAllCalendarsOnBackend(): Internal Error: ';
				$msg .= 'CalendarCache returned unrecognised format!';
				throw new BusinessLayerException($msg);
			}

			return $number;
		} catch (DoesNotExistException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * Find calendar $calendarId of user $userId
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $userId
	 * @throws BusinessLayerException if backend does not exist
	 * @throws BusinessLayerException if backend is disabled
	 * @return calendar object
	 */
	public function find($calendarId, $userId) {
		try {
			$this->splitCalendarURI($calendarId, $backend, $calendarURI);

			if($this->isBackendEnabled($backend) !== true) {
				$msg  = 'CalendarBusinessLayer::find(): User Error: ';
				$msg .= 'Backend found but not enabled';
				throw new BusinessLayerException($msg);
			}

			$calendar = $this->cacheMapper->find($backend, $calendarURI, $userId);

			if(($calendar instanceof Calendar) === false) {
				$msg  = 'CalendarBusinessLayer::find(): Internal Error: ';
				$msg .= 'CalendarCache returned unrecognised format!';
				throw new BusinessLayerException($msg);
			}

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
	 * @throws BusinessLayerException if name exists already
	 * @throws BusinessLayerException if backend does not exist
	 * @throws BusinessLayerException if backend is disabled
	 * @throws BusinessLayerException if backend does not implement creating a calendar
	 * @return Calendar $calendar - calendar object
	 */
	public function create(Calendar $calendar) {
		try {
			$calendarId = $calendar->getCalendarId();
			$userId = $calendar->getUserId();

			$backend = $calendar->getBackend();
			$calendarURI = $calendar->getUri();

			if($this->isBackendEnabled($backend) !== true) {
				$msg  = 'CalendarBusinessLayer::create(): User Error: ';
				$msg .= 'Backend found but not enabled!';
				throw new BusinessLayerException($msg);
			}
			if($this->doesCalendarExist($calendarId, $userId) !== false) {
				$msg  = 'CalendarBusinessLayer::create(): User Error: ';
				$msg .= 'Calendar already exists!';
				throw new BusinessLayerException($msg);
			}
			if($this->doesBackendSupport($backend, \OCA\Calendar\Backend\CREATE_CALENDAR) !== true) {
				$msg  = 'CalendarBusinessLayer::create(): User Error: ';
				$msg .= 'Backend does not support creating calendars!';
				throw new BusinessLayerException($msg);
			}

			if($calendar->isValid() !== true) {
				//try to fix the calendar
				$calendar->fix();

				//check again
				if($calendar->isValid() !== true) {
					$msg  = 'CalendarBusinessLayer::create(): User Error: ';
					$msg .= 'Given calendar data is not valid and not fixable';
					throw new BusinessLayerException($msg);
				}
			}

			$this->backends->find($backend)->api->createCalendar($calendar);
			$this->mapper->insertEntity($calendar, $backend, $calendarURI, $userId);

			return $calendar;
		} catch (DoesNotImplementException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch (BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch (CacheOutDatedException $ex) {
			$this->updateCalendarFromRemote($calendarId, $userId);
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
	public function update(Calendar $calendar, $oldCalendarId, $oldUserId) {
		try {
			list($oldBackend, $oldCalendarURI) = $this->splitCalendarURI($oldCalendarId);

			if($calendar->doesContainNullValues() === true) {
				$oldCalendarObject = $this->find($oldCalendarId, $oldUserId);
				$calendar->merge($oldCalendarObject);
			}

			$newCalendarId = $calendar->getCalendarId();
			$newBackend = $calendar->getBackend();
			$newCalendarURI = $calendar->getUri();
			$newUserId = $calendar->getUserId();

			if($oldUserId !== $newUserId) {
				$msg  = 'CalendarBusinessLayer::update(): User Error: ';
				$msg .= 'Transferring a calendar to another user is not supported yet.';
				throw new BusinessLayerException($msg);
			}
			if($this->isBackendEnabled($oldBackend) !== true) {
				$msg  = 'CalendarBusinessLayer::update(): User Error: ';
				$msg .= 'Backend found but not enabled!';
				throw new BusinessLayerException($msg);
			}
			if($newBackend !== $oldBackend && $this->isBackendEnabled($newBackend) !== true) {
				$msg  = 'CalendarBusinessLayer::update(): User Error: ';
				$msg .= 'Backend found but not enabled!';
				throw new BusinessLayerException($msg);
			}

			$oldCalendarObject = $this->find($oldCalendarId, $oldUserId);
			//does ctag change when a calendar property like color or name changes?
			if($calendar->getCtag() < $oldCalendarObject->getCtag()) {
				$msg  = 'CalendarBusinessLayer::update(): User Error: ';
				$msg .= 'A newer version of the calendar is already saved!';
				throw new BusinessLayerException($msg);
			}

			$oldBackendsAPI = &$this->backends->find($oldBackend)->api;
			$newBackendsAPI = &$this->backends->find($newBackend)->api;

			if($calendar->isValid() !== true) {
				//try to fix the calendar
				$calendar->fix();

				//check again
				if($calendar->isValid() !== true) {
					$msg  = 'CalendarBusinessLayer::update(): User Error: ';
					$msg .= 'Given calendar data is not valid and not fixable!';
					throw new BusinessLayerException($msg);
				}
			}

			/* Move calendar to another backend when:
			 * - [x] the backend changed
			 * - [x] uri is available on the other calendar
			 */
			if($newBackend !== $oldBackend && $this->doesCalendarExist($newCalendarId, $userId) === false) {
				if($this->doesBackendSupport($oldBackend, \OCA\Calendar\Backend\DELETE_CALENDAR) !== true) {
					$msg  = 'CalendarBusinessLayer::update(): User Error: ';
					$msg .= 'Backend does not support deleting calendars!';
					throw new BusinessLayerException($msg);
				}
				if($this->doesBackendSupport($newBackend, \OCA\Calendar\Backend\CREATE_CALENDAR) !== true) {
					$msg  = 'CalendarBusinessLayer::update(): User Error: ';
					$msg .= 'Backend does not support creating calendars!';
					throw new BusinessLayerException($msg);
				}
				/*if($this->doesBackendSupport($oldBackend, \OCA\Calendar\Backend\DELETE_OBJECT) !== true) {
					$msg  = 'CalendarBusinessLayer::update(): User Error: ';
					$msg .= 'Backend does not support deleting objects!';
					throw new BusinessLayerException($msg);
				}
				if($this->doesBackendSupport($newBackend, \OCA\Calendar\Backend\CREATE_OBJECT) !== true) {
					$msg  = 'CalendarBusinessLayer::update(): User Error: ';
					$msg .= 'Backend does not support creating objects!';
					throw new BusinessLayerException($msg);
				}*/

				//create calendar on new backend
				$calendar = $newBackendsAPI->createCalendar($calendar);

				//move all objects
				$this->objectBusinessLayer->moveAll($calendar, $calendarId, $userId);

				//if no exception was thrown,
				//moving objects finished without any problem
				$oldBackendsAPI->deleteCalendar($oldCalendarURI, $userId);
				$this->cacheMapper->move($calendar, $calendarId, $userId);

				return $calendar;
			} else
			/* Merge calendar with another one when:
			 *  - [x] the backend changed
			 *  - [x] uri is not available on the other backend
			 * or:
			 *  - [x] backend didn't change
			 *  - [x] uri changed
			 *  - [x] uri is not available
			 */
			if(($newBackend !== $oldBackend || $newCalendarURI !== $oldCalendarURI) && 
				$this->doesCalendarExist($newCalendarId, $userId) === true) {
				if($newBackend === $oldBackend && $this->doesBackendSupport($oldBackend, \OCA\Calendar\Backend\MERGE_CALENDAR)) {
					$newBackendsAPI->mergeCalendar($calendar, $oldCalendarURI, $oldUserId);
				} else {
					if($this->doesBackendSupport($oldBackend, \OCA\Calendar\Backend\DELETE_CALENDAR) !== true) {
						$msg  = 'CalendarBusinessLayer::update(): User Error: ';
						$msg .= 'Backend does not support deleting calendars!';
						throw new BusinessLayerException($msg);
					}
	
					//move all objects
					$this->objectBusinessLayer->moveAll($calendar, $oldCalendarId, $oldUserId);
	
					//if no exception was thrown,
					//moving objects finished without any problem
					$this->cacheMapper->move($calendar, $oldCalendarId, $oldUserId);
					$oldBackendsAPI->deleteCalendar($oldCalendarURI, $oldUserId);
					$this->updateFromCache($oldCalendarId, $oldUserId);
				}

				return $calendar;
			}
			/* otherwise just update the calendar */
			if($this->doesBackendSupport($backend, \OCA\Calendar\Backend\UPDATE_CALENDAR) === true) {
				$this->backends->find($backend)->api->updateCalendar($calendar, $oldCalendarURI);
				$this->mapper->updateEntity($calendar, $oldBackend, $oldCalendarURI, $oldUserId);
				return $calendar;
			} else {
				if($oldBackend === $newBackend && $oldCalendarURI === $newCalendarURI) {
					$this->mapper->updateEntity($calendar, $oldBackend, $oldCalendarURI, $oldUserId);
				}
			}
			return $calendar;
		} catch(DoesNotImplementException $ex) {
			//write debug note to logfile
			throw new BusinessLayerException($ex->getMessage());
		} catch(BackendException $ex) {
			//write debug note to logfile
			throw new BusinessLayerException($ex->getMessage());
		} catch (CalendarNotFixable $ex) {
			//write error note to logfile
			throw new BusinessLayerException($ex->getMessage());
		} catch (CacheOutDatedException $ex) {
			//write debug note to logfile
			$this->updateCalendarFromRemote($oldCalendarId, $userId);
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * touch a calendar aka increment a calendars ctag
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $userId
	 * @throws BusinessLayerException if backends do not exist
	 * @throws BusinessLayerException if backends are disabled
	 * @throws BusinessLayerException if backend does not implement updating a calendar
	 * @return Calendar $calendar - calendar object
	 */
	public function touch($calendarId, $userId) {
		try {
			$calendar = $this->find($calendarId, $userId);
			$calendar->touch();
			$calendar = $this->update($calendar, $calendarId, $userId);

			return $calendar;
		} catch(BackendException $ex) {
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
	 */
	public function delete($calendarId, $userId) {
		try {
			list($backend, $calendarURI) = $this->splitCalendarURI($calendarId);

			if($this->isBackendEnabled($backend) !== true) {
				$msg  = 'CalendarBusinessLayer::delete(): User Error: ';
				$msg .= 'Backend found but not enabled!';
				throw new BusinessLayerException($msg);
			}
			if($this->doesBackendSupport($backend, \OCA\Calendar\Backend\DELETE_CALENDAR) !== true) {
				$msg  = 'CalendarBusinessLayer::delete(): User Error: ';
				$msg .= 'Backend does not support deleting calendars!';
				throw new BusinessLayerException($msg);
			}

			$this->backends->find($backend)->api->deleteCalendar($calendarURI, $userId);
			$this->cacheMapper->deleteEntity($backend, $calendarURI, $userId);
		} catch(DoesNotImplementException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch(BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * checks if a calendar exists
	 * @param string $backend
	 * @param string $calendarURI
	 * @param string $userId
	 * @param boolean $checkRemote
	 * @return boolean
	 */
	public function doesCalendarExist($calendarId, $userId, $checkRemote=false) {
		try {
			list($backend, $calendarURI) = $this->splitCalendarURI($calendarId);

			$cacheCalendars = $this->cacheMapper->countFind($backend, $calendarURI, $userId);

			if($cacheCalendars !== 0) {
				return false;
			}
	
			return true;
		} catch(BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch(DoesNotExistException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	public function doesAllow($cruds, $calendarId, $userId) {
		
	}

	/**
	 * checks if a calendar exists
	 * @param string $calendarId
	 * @param string $userId
	 * @return boolean
	 */
	public function isCalendarOutDated($calendarId, $userId=null) {
		try{
			list($backend, $calendarURI) = $this->splitCalendarURI($calendarId);

			$cachedCalendar = $this->find($calendarId, $userId);
			$remoteCalendar = $this->backends->find($backend)->api->findCalendar($calendarURI, $userId);

			$cachedCtag = $cachedCalendar->getCtag();
			$remoteCtag = $remoteCalendar->getCtag();
			if($cachedCtag === $remoteCtag) {
				return false;
			}
			if($cachedCtag < $remoteCtag) {
				$this->remoteCalendarObjectCache[$userId][$calendarId] = $remoteCalendar;
				return true;
			}
			if($cachedCtag > $remoteCalendar) {
				//TODO - how to handle this case appropriately?
				//could lead to endless updates if backend is sending broken ctag
			}
		} catch(BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch(DoesNotExistException $ex) {
			
		}
	}

	/**
	 * update all calendars of a user
	 * @param string $userId
	 * @return boolean
	 */
	public function updateCacheForAllFromRemote($userId) {
		try{
			$backends = $this->backends->findAll()->enabled();
			foreach($backends as &$backend) {
				try{
					$this->updateBackendFromRemote($backend->getBackend());
				} catch(/* */Exception $ex) {
					//log smth
					continue;
				}
			}
		} catch(BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * update all calendars of a user on a backend
	 * @param string $backend
	 * @param string $userId
	 * @return boolean
	 */
	public function updateCacheForBackendFromRemote($backend, $userId) {
		try{
			$api = &$this->backends->find($backend)->api;
			$numbersCached = $this->numberOfAllCalendarsOnBackend($backend, $userId);
			$numbersRemote = $api->countCalendars($userId);
			$offset = abs($numbersRemote - $numbersCached);

			$calendars = $this->findAllOnBackend($backend, $userId);
			


		} catch(BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * update a specific calendar
	 * @param string $userId
	 * @return boolean
	 */
	public function updateCacheForCalendarFromRemote($calendarId, $userId=null) {
		try{
			list($backend, $calendarURI) = $this->splitCalendarURI($calendarId);

			if($this->isCalendarOutDated($calendarId, $userId) === false) {
				return;
			}

			$this->objectBusinessLayer->updateCalendarFromRemote($calendarId, $userId);

			$remoteCalendar = $this->remoteCalendarObjectCache[$userId][$calendarId];
			$this->mapper->updateEntity($calendar, $backend, $calendarURI, $userId);
		} catch(BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}
}