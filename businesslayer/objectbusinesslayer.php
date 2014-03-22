<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\BusinessLayer;

use \OCA\Calendar\AppFramework\Core\API;
use \OCA\Calendar\AppFramework\Utility\TimeFactory;

use \OCA\Calendar\AppFramework\Db\DoesNotExistException;
use \OCA\Calendar\AppFramework\Db\MultipleObjectsReturnedException;

use \OCA\Calendar\Db\Object;
use \OCA\Calendar\Db\ObjectCollection;
use \OCA\Calendar\Db\ObjectMapper;

use \OCA\Calendar\Backend\BackendException;
use \OCA\Calendar\Backend\DoesNotImplementException;

class ObjectBusinessLayer extends BusinessLayer {

	private $cache;

	private $remoteObjectObjectCache=array();

	/**
	 * @param ObjectMapper $objectMapper: mapper for objects cache
	 * @param CalendarBusinessLayer $calendarBusinessLayer
	 * @param BackendBusinessLayer $backendBusinessLayer
	 * @param API $api: an api wrapper instance
	 */
	public function __construct(ObjectMapper $objectMapper,
								BackendBusinessLayer $backends,
	                            API $api){
		parent::__construct($api, $backends);
		$this->cache = $objectMapper;
	}


	/**
	 * Finds all objects of calendar $calendarId of user $userId
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $userId
	 * @param int limit
	 * @param int offset
	 * @throws BusinessLayerException
	 * @return ObjectCollection
	 */
	public function findAll($calendarId, $userId, $limit=null, $offset=null) {
		try {
			list($backend, $calendarURI) = $this->splitCalendarURI($calendarId);

			if($this->isBackendEnabled($backend) !== true) {
				$msg  = 'ObjectBusinessLayer::findAll(): User Error: ';
				$msg .= 'Backend found but not enabled!';
				throw new BusinessLayerException($msg);
			}

			$api = &$this->backends->find($backend)->api;

			$cacheObjects = $api->cacheObjects($calendarURI, $userId);
			if($cacheObjects) {
				$objects = $this->cache->findAll($backend, $calendarURI, $userId, $limit, $offset);
			} else {
				$objects = $api->findObjects($calendarURI, $userId, $limit, $offset);
			}

			if($objects === null || $objects === false) {
				//create empty calendar collection
				$objects = new ObjectCollection();
			}

			//check if $calendars is a CalendarCollection, if not throw an exception
			if(($objects instanceof ObjectCollection) === false) {
				$msg  = 'ObjectBusinessLayer::findAll(): Internal Error: ';
				$msg .= ($cacheObjects ? 'ObjectCache' : 'Backend') . ' returned unrecognised format!';
				throw new BusinessLayerException($msg);
			}

			return $objects;
		} catch (DoesNotExistException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch (BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * get the number how many calendars a user has
	 * @param string $userId
	 * @throws BusinessLayerException
	 * @return integer
	 */
	public function numberOfAllObjectsInCalendar($calendarId, $userId) {
		try {

		} catch (DoesNotExistException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * Find the object $objectURI of calendar $calendarId of user $userId
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $objectURI UID of the object
	 * @param string $userId
	 * @throws BusinessLayerException
	 * @return object
	 */
	public function find($calendarId, $objectURI, $userId) {
		try {
			list($backend, $calendarURI) = $this->splitCalendarURI($calendarId);

			if($this->isBackendEnabled($backend) !== true) {
				$msg  = 'ObjectBusinessLayer::find(): User Error: ';
				$msg .= 'Backend found but not enabled!';
				throw new BusinessLayerException($msg);
			}

			$api = &$this->backends->find($backend)->api;

			$cacheObjects = $api->cacheObjects($calendarURI, $userId);
			if($cacheObjects) {
				$object = $this->cache->find($backend, $calendarURI, $objectURI, $userId);
			} else {
				$object = $api->findObject($calendarURI, $objectURI, $userId);
			}

			if(($object instanceof Object) === false) {
				$msg  = 'ObjectBusinessLayer::find(): Internal Error: ';
				$msg .= ($cacheObjects ? 'ObjectCache' : 'Backend') . ' returned unrecognised format!';
				throw new BusinessLayerException($msg);
			}

			return $object;
		} catch (DoesNotExistException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch (MultipleObjectsReturnedException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch (BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * Find the object $objectURI of type $type of calendar $calendarId of user $userId
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $objectURI UID of the object
	 * @param string $type type of the searched objects, use OCA\Calendar\Db\ObjectType
	 * @param string $userId
	 * @throws BusinessLayerException
	 * @return object
	 */
	public function findByType($calendarId, $objectURI, $type, $userId) {
		$object = $this->find($calendarId, $objectURI, $userId);

		if($object->getType() !== $type) {
			$msg  = 'ObjectBusinessLayer::find(): User Error: ';
			$msg .= 'Requested object exists but is of different type!';
			throw new BusinessLayerException($msg);
		}

		return $object;
	}

	/**
	 * Finds all objects of type $type of calendar $calendarId of user $userId
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $type type of the searched objects, use OCA\Calendar\Db\ObjectType
	 * @param string $userId
	 * @param int limit
	 * @param int offset
	 * @throws BusinessLayerException
	 * @return array containing all items
	 */
	public function findAllByType($calendarId, $type, $userId, $limit=null, $offset=null) {
		try {
			list($backend, $calendarURI) = $this->splitCalendarURI($calendarId);

			if($this->isBackendEnabled($backend) !== true) {
				$msg  = 'ObjectBusinessLayer::findAllByType(): User Error: ';
				$msg .= 'Backend found but not enabled!';
				throw new BusinessLayerException($msg);
			}

			if($this->isObjectCacheEnabled($calendarURI, $userId) === true) {
				$objects = $this->cache->findAllByType($backend, $calendarURI, $type, $userId, $limit, $offset);

				//check if $objects is a ObjectCollection, if not throw an exception
				if(($objects instanceof ObjectCollection) === false) {
					$msg  = 'ObjectBusinessLayer::findAllByType(): Internal Error: ';
					$msg .= 'ObjectCache returned unrecognised format!';
					throw new BusinessLayerException($msg);
				}
			} else {
				$api = &$this->backends->find($backend)->api;

				$doesBackendSupport = $this->doesBackendSupport($backend, \OCA\Calendar\Backend\FIND_OBJECTS_BY_TYPE);
				if($doesBackendSupport === true) {
					$objects = $api->findObjectsByType($calendarURI, $type, $userId, $limit, $offset);
				} else {
					$objects = $api->findObjects($calendarURI, $userId);
				}

				//check if $objects is a ObjectCollection, if not throw an exception
				if(($objects instanceof ObjectCollection) === false) {
					$msg  = 'ObjectBusinessLayer::findAllByType(): Internal Error: ';
					$msg .= 'Backend returned unrecognised format!';
					throw new BusinessLayerException($msg);
				}

				if($doesBackendSupport === false) {
					$objects = $objects->byType($type)->subset($limit, $offset);
				}
			}

			return $objects;
		} catch (DoesNotExistException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch (BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * Finds all objects in timespan from $start to $end of calendar $calendarId of user $userId
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param DateTime $start start of timeframe
	 * @param DateTime $end end of timeframe
	 * @param string $userId
	 * @param int limit
	 * @param int offset
	 * @throws BusinessLayerException
	 * @return array containing all items
	 */
	public function findAllInPeriod($calendarId, $start, $end, $userId, $limit=null, $offset=null) {
		try {
			list($backend, $calendarURI) = $this->splitCalendarURI($calendarId);

			if($this->isBackendEnabled($backend) !== true) {
				$msg  = 'ObjectBusinessLayer::findAllInPeriod(): User Error: ';
				$msg .= 'Backend found but not enabled!';
				throw new BusinessLayerException($msg);
			}

			if($this->isObjectCacheEnabled($calendarURI, $userId) === true) {
				$objects = $this->cache->findAllInPeriod($backend, $calendarURI, $start, $end, $userId, $limit, $offset);

				//check if $objects is a ObjectCollection, if not throw an exception
				if(($objects instanceof ObjectCollection) === false) {
					$msg  = 'ObjectBusinessLayer::findAllInPeriod(): Internal Error: ';
					$msg .= 'ObjectCache returned unrecognised format!';
					throw new BusinessLayerException($msg);
				}
			} else {
				$api = &$this->backends->find($backend)->api;

				$doesBackendSupport = $this->doesBackendSupport($backend, \OCA\Calendar\Backend\FIND_IN_PERIOD);
				if($doesBackendSupport === true) {
					$objects = $api->findObjectsInPeriod($calendarURI, $type, $userId, $limit, $offset);
				} else {
					$objects = $api->findObjects($calendarURI, $userId);
				}

				//check if $objects is a ObjectCollection, if not throw an exception
				if(($objects instanceof ObjectCollection) === false) {
					$msg  = 'ObjectBusinessLayer::findAllByType(): Internal Error: ';
					$msg .= 'Backend returned unrecognised format!';
					throw new BusinessLayerException($msg);
				}

				if($doesBackendSupport === false) {
					$objects = $objects->inPeriod($start, $end)->subset($limit, $offset);
				}
			}

			return $objects;
		} catch (DoesNotExistException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch (BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * Finds all objects of type $type in timeframe from $start to $end of calendar $calendarId of user $userId
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $type type of the searched objects, use OCA\Calendar\Db\ObjectType
	 * @param DateTime $start start of the timeframe
	 * @param DateTime $end end of the timeframe
	 * @param string $userId
	 * @param boolean $expand expand if repeating event
	 * @param DateTime $expandStart don't return repeating events earlier than $expandStart
	 * @param DateTime $expandEnd  don't return repeating events later than $expandEnd
	 * @param int limit
	 * @param int offset
	 * @throws BusinessLayerException
	 * @return array containing all items
	 */
	public function findAllByTypeInPeriod($calendarId, $type, $start, $end, $userId, $limit=null, $offset=null) {
		try {
			list($backend, $calendarURI) = $this->splitCalendarURI($calendarId);

			if($this->isBackendEnabled($backend) !== true) {
				$msg  = 'ObjectBusinessLayer::findAllInPeriod(): User Error: ';
				$msg .= 'Backend found but not enabled!';
				throw new BusinessLayerException($msg);
			}

			if($this->isObjectCacheEnabled($calendarURI, $userId) === true) {
				$objects = $this->cache->findAllByTypeInPeriod($backend, $calendarURI, $start, $end, $type, $userId);

				//check if $objects is a ObjectCollection, if not throw an exception
				if(($objects instanceof ObjectCollection) === false) {
					$msg  = 'ObjectBusinessLayer::findAllByTypeInPeriod(): Internal Error: ';
					$msg .= 'ObjectCache returned unrecognised format!';
					throw new BusinessLayerException($msg);
				}
			} else {
				$api = &$this->backends->find($backend)->api;

				$doesBackendSupport = $this->doesBackendSupport($backend, \OCA\Calendar\Backend\FIND_IN_PERIOD_BY_TYPE);
				if($doesBackendSupport === true) {
					$objects = $api->findObjectsByTypeInPeriod($calendarURI, $start, $end, $type, $userId);
				} else {
					$objects = $api->findObjects($calendarURI, $userId);
				}

				//check if $objects is a ObjectCollection, if not throw an exception
				if(($objects instanceof ObjectCollection) === false) {
					$msg  = 'ObjectBusinessLayer::findAllByType(): Internal Error: ';
					$msg .= 'Backend returned unrecognised format!';
					throw new BusinessLayerException($msg);
				}

				if($doesBackendSupport === false) {
					$objects = $objects->byType($type)->inPeriod($start, $end)->subset($limit, $offset);
				}
			}

			return $objects;
		} catch (DoesNotExistException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch (BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * creates a new object
	 * @param Object $object
	 * @throws BusinessLayerException
	 * @return array containing all items
	 */
	public function create(Object $object) {
		try {
			$calendarId = $object->getCalendarId();
			$objectURI = $object->getObjectURI();
			list($backend, $calendarURI) = $this->splitCalendarURI($calendarId);
			if($this->isBackendEnabled($backend) !== true) {
				$msg  = 'ObjectBusinessLayer::create(): User Error: ';
				$msg .= 'Backend found but not enabled!';
				throw new BusinessLayerException($msg);
			}
			//validate that calendar exists
			if($this->doesObjectExist($calendarId, $objectURI, $userId) !== false) {
				$msg  = 'ObjectBusinessLayer::create(): User Error: ';
				$msg .= 'Object already exists!';
				throw new BusinessLayerException($msg);
			}
			if($this->doesBackendSupport($backend, \OCA\Calendar\Backend\CREATE_OBJECT) !== true) {
				$msg  = 'ObjectBusinessLayer::create(): User Error: ';
				$msg .= 'Backend does not support creating objects!';
				throw new BusinessLayerException($msg);
			}

			if($object->isValid() !== true) {
				//try to fix the object
				$object->fix();

				//check again
				if($object->isValid() !== true) {
					$msg  = 'ObjectBusinessLayer::create(): User Error: ';
					$msg .= 'Given object data is not valid and not fixable';
					throw new BusinessLayerException($msg);
				}
			}

			$api->createObject($object, $calendarURI, $objectURI, $userId);

			if($this->isObjectCacheEnabled($calendarURI, $userId) === true) {
				$this->cache->insert($object, $calendarURI, $objectURI, $userId);
			}

			$this->calendarBusinessLayer->touch($calendarId, $userId);

			return $object;
		} catch(DoesNotImplementException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch(BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * updates an object
	 * @param Object $object 
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $objectURI UID of the object
	 * @param string $userId
	 * @throws BusinessLayerException
	 * @return array containing all items
	 */
	public function update($object, $calendarId, $objectURI, $userId) {
		list($backend, $calendarURI) = $this->splitCalendarURI($calendarId);
		$this->checkBackendEnabled($backend);

		try {
			if($object->getBackend() !== $backend || $object->getUri() !== $calendarURI) {
				return $this->move($object, $calendarId, $objectURI, $userId);
			}

			$this->checkBackendSupports($backend, \OCA\Calendar\Backend\UPDATE_OBJECT);

			$api = &$this->backends->find($backend)->api;

			if($object->isValid() === false) {
				$object->fix();
			}

			$object = $api->updateObject($object, $calendarURI, $objectURI, $userId);
			$cacheObjects = $api->cacheObjects($calendarURI, $userId);
			if($cacheObjects) {
				$this->cache->update($object, $calendarURI, $objectURI, $userId);
			}

			$this->calendarBusinessLayer->touch($calendarId, $userId);

			return $object;
		} catch(DoesNotImplementException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch(BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * delete an object from a calendar
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $objectURI UID of the object
	 * @param string $userId
	 * @throws BusinessLayerException
	 * @return boolean
	 */
	public function delete($calendarId, $objectURI, $userId) {
		list($backend, $calendarURI) = $this->splitCalendarURI($calendarId);
		$this->checkBackendEnabled($backend);

		try {
			$this->checkBackendSupports($backend, \OCA\Calendar\Backend\DELETE_OBJECT);

			$api = &$this->backends->find($backend)->api;
			$api->deleteObject($calendarURI, $objectURI, $userId);

			if($api->cacheObjects($calendarURI, $userId)) {
				$this->cache->delete($calendar);
			}

			$this->calendarBusinessLayer->touch($calendarId, $userId);

			return true;
		} catch(DoesNotImplementException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch(BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * moves an object from one to another calendar
	 * @param Object $object
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $objectURI UID of the object
	 * @param string $userId
	 * @throws BusinessLayerException
	 * @return array containing all items
	 */
	public function move($object, $calendarId, $objectURI, $userId) {
		list($backend, $calendarURI) = $this->splitCalendarURI($calendarId);

		try {
			$oldBackend = $backend;
			$newBackend = $calendar->getBackend();

			$oldCalendarURI = $calendarURI;
			$newCalendarURI = $object->getCalendarURI();

			$oldObjectURI = $objectURI;
			$newObjectURI = $object->getURI();

			$this->checkBackendEnabled($oldBackend);
			$this->checkBackendEnabled($newBackend);

			$this->allowNoObjectURITwice($newBackend, $newCalendarURI, $newObjectURI, $userId);

			$oldBackendsAPI = &$this->backends->find($oldBackend)->api;
			$newBackendsAPI = &$this->backends->find($newBackend)->api;

			$doesBackendSupportMovingEvents = $oldBackendsAPI->implementsActions(\OCA\Calendar\Backend\MOVE_OBJECT);

			if($oldBackend == $newBackend && $doesBackendSupportMovingEvents === true) {
				$object = $newBackendsAPI->moveObject($object, $calendarURI, $objectURI, $userId);
			} else {
				$this->checkBackendSupports($oldBackend, \OCA\Calendar\Backend\DELETE_OBJECT);
				$this->checkBackendSupports($newBackend, \OCA\Calendar\Backend\CREATE_OBJECT);

				$status = $newBackendsAPI->createObject($object);
				if($status === true) {
					$object = $this->backends->find($object->getBackend())->api->createObject();
				} else {
					throw new BusinessLayerException('Could not move object to another calendar.');
				}
			}

			$cacheObjectsInOldBackend = $oldBackendsAPI->cacheObjects($calendarURI, $userId);
			if($cacheObjectsInOldBackend === true) {
				//dafuq
				$this->cache->delete($object, $calendarURI, $objectURI, $userId);
			}

			$cacheObjectsInNewBackend = $newBackendsAPI->cacheObjects($calendarURI, $userId);
			if($cacheObjectsInNewBackend === true) {
				//dafuq
				$this->cache->create($object, $object->getCalendarUri(), $object->getObjectUri(), $userId);
			}

			$this->calendarBusinessLayer->touch($calendarId, $userId);
			$this->calendarBusinessLayer->touch($object->getCalendarId(), $userId);

			return $object;
		} catch(DoesNotImplementException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch(BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * touch an object
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $objectURI UID of the object
	 * @param string $userId
	 * @throws BusinessLayerException
	 * @return array containing all items
	 */
	public function touch($calendarId, $objectURI, $userId) {
		list($backend, $calendarURI) = $this->splitCalendarURI($calendarId);

		try {
			$this->checkBackendEnabled($backend);

			$object = $this->find($calendarId, $objectURI, $userid);
			$object->touch();

			$this->update($object, $calendarId, $userId);
		} catch(DoesNotImplementException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch(BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * make sure that uri does not already exist when creating a new object
	 * @param string $backend
	 * @param string $calendarURI
	 * @param string $userId
	 * @return boolean
	 * @throws BusinessLayerException if uri is already taken
	 */
	private function allowNoObjectURITwice($backend, $calendarURI, $objectURI, $userId){
		if($this->isObjectURIAvailable($backend, $calendarURI, $objectURI, $userId, true) === false) {
			throw new BusinessLayerException('Can not add object: UID already exists');
		}
	}

	/**
	 * checks if a uri is available
	 * @param string $backend
	 * @param string $calendarURI
	 * @param string $objectURI
	 * @param string $userId
	 * @return boolean
	 */
	private function isObjectURIAvailable($backend, $calendarURI, $objectURI, $userId, $checkRemote=false) {
		$existingObjects = $this->cache->find($backend, $calendarURI, $objectURI, $userId);
		if(count($existingObjects) !== 0) {
			return false;
		}

		if($checkRemote === true) {
			$existingRemoteObjects = $this->backends->find($backend)->api->findObject($calendarURI, $objectURI, $userId);
			if(count($existingRemoteObjects) !== 0) {
				return false;
			}
		}

		return true;
	}	
}