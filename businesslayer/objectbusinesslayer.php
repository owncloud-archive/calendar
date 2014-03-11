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
use \OCA\Calendar\Db\ObjectMapper;

use \OCA\Calendar\Backend\BackendException;
use \OCA\Calendar\Backend\DoesNotImplementException;

class ObjectBusinessLayer extends BusinessLayer {

	private $calendarBusinessLayer;

	/**
	 * @param ObjectMapper $objectMapper: mapper for objects cache
	 * @param CalendarBusinessLayer $calendarBusinessLayer
	 * @param BackendBusinessLayer $backendBusinessLayer
	 * @param API $api: an api wrapper instance
	 */
	public function __construct(ObjectMapper $objectMapper,
		                        CalendarBusinessLayer $calendarBusinessLayer,
		                        BackendBusinessLayer $backendBusinessLayer,
	                            API $api){
		$this->calendarBusinessLayer = $calendarBusinessLayer;
		parent::__construct($api, $objectMapper, $backendBusinessLayer);
	}


	/**
	 * Finds all objects of calendar $calendarId of user $userId
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $userId
	 * @param int limit
	 * @param int offset
	 * @throws BusinessLayerException
	 * @return array containing all items
	 */
	public function findAll($calendarId, $userId, $limit=null, $offset=null) {
		list($backend, $calendarURI) = $this->splitCalendarURI($calendarId);
		$this->checkBackendEnabled($backend);

		if($limit !== null) {
			$limit = (int) $limit;
		}

		if($offset !== null || $limit !== null) {
			$offset = (int) $offset;
		}

		try {
			$api = &$this->backends->find($backend)->api;

			$cacheObjects = $api->cacheObjects($calendarURI, $userId);
			if($cacheObjects) {
				$objects = $this->mapper->findAll($backend, $calendarURI, $userId, $limit, $offset);
			} else { 
				$objects = $api->findObjects($calendarURI, $userId, $limit, $offset);
			}

			return $objects;
		} catch (DoesNotExistException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch (BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * Find the object $objectURI of calendar $calendarId of user $userId
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $objectURI UID of the object
	 * @param string $userId
	 * @throws BusinessLayerException
	 * @return array containing all items
	 */
	public function find($calendarId, $objectURI, $userId) {
		list($backend, $calendarURI) = $this->splitCalendarURI($calendarId);
		$this->checkBackendEnabled($backend);

		try {
			$api = &$this->backends->find($backend)->api;

			$cacheObjects = $api->cacheObjects($calendarURI, $userId);
			if($cacheObjects) {
				$object = $this->mapper->find($backend, $calendarURI, $objectURI, $userId);
			} else {
				$object = $api->findObject($calendarURI, $objectURI, $userId);
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
	 * Find the objects $objectURI of type $type of calendar $calendarId of user $userId
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $objectURI UID of the object
	 * @param string $type type of the searched objects, use OCA\Calendar\Db\ObjectType
	 * @param string $userId
	 * @throws BusinessLayerException
	 * @return array containing all items
	 */
	public function findByType($calendarId, $objectURI, $type, $userId) {
		$object = $this->find($calendarId, $objectURI, $userId);

		if($object->getType() !== $type) {
			throw new BusinessLayerException('Object exists, but is of different type.');
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
		list($backend, $calendarURI) = $this->splitCalendarURI($calendarId);
		$this->checkBackendEnabled($backend);

		if($limit !== null) {
			$limit = (int) $limit;
		}

		if($offset !== null || $limit !== null) {
			$offset = (int) $offset;
		}

		try {
			$api = &$this->backends->find($backend)->api;

			$cacheObjects = $api->cacheObjects($calendarURI, $userId);
			if($cacheObjects) {
				$objects = $this->mapper->findAllByType($backend, $calendarURI, $type, $userId, $limit, $offset);
			} else {
				$isSupported = $api->implementsActions(\OCA\Calendar\Backend\FIND_OBJECTS_BY_TYPE);
				if($isSupported) {
					$objects = $api->findObjectsByType($calendarURI, $type, $userId, $limit, $offset);
				} else {
					$allObjects = $api->findObjects($calendarURI, $userId);

					if($limit !== null) {
						$i = 0;						
					}

					foreach($allObjects as $objectToCheck) {
						if($objectToCheck->getType() === $type) {
							if($limit === null || ($i >= $offset)) {
								$objects[] = $objectToCheck;
							}
							if($limit !== null) {
								$i++;
								if($i > ($offset + $limit)) {
									break;
								}	
							}
						}
					}
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
		list($backend, $calendarURI) = $this->splitCalendarURI($calendarId);
		$this->checkBackendEnabled($backend);

		if($limit !== null) {
			$limit = (int) $limit;
		}

		if($offset !== null) {
			$offset = (int) $offset;
		}

		try {
			$api = &$this->backends->find($backend)->api;
			$cacheObjects = $api->cacheObjects($calendarURI, $userId);
			if($cacheObjects) {
				$objects = $this->mapper->findAllInPeriod($backend, $calendarURI, $start, $end, $userId, $limit, $offset);
			} else {
				$isSupported = $api->implementsActions(\OCA\Calendar\Backend\FIND_IN_PERIOD);
				if($isSupported) {
					$objects = $api->findObjectsInPeriod($calendarURI, $type, $userId, $limit, $offset);
				} else {
					$allObjects = $api->findObjects($calendarURI, $userId);

					if($limit !== null) {
						$i = 0;						
					}

					foreach($allObjects as $objectToCheck) {
						$startDate = $objectToCheck->getStartdate();
						$endDate = $objectToCheck->getEnddate();
						if( $objectToCheck->getRepeating() === true ||
							($startDate >= $start && $startDate <= $start) ||
							($endDate >= $end && $endDate <= $end) ||
							($startDate <= $start && $endDate >= $end)){
							if($limit === null || ($i >= $offset)) {
								$objects[] = $objectToCheck;
							}
							if($limit !== null) {
								$i++;
								if($i > ($offset + $limit)) {
									break;
								}	
							}
						}
					}
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
		list($backend, $calendarURI) = $this->splitCalendarURI($calendarId);
		$this->checkBackendEnabled($backend);

		if($limit !== null) {
			$limit = (int) $limit;
		}

		if($offset !== null) {
			$offset = (int) $offset;
		}

		try {
			$api = &$this->backends->find($backend)->api;
			$cacheObjects = $api->cacheObjects($calendarURI, $userId);
			if($cacheObjects) {
				$objects = $this->mapper->findAllByTypeInPeriod($backend, $calendarURI, $start, $end, $type, $userId);
			} else {
				$isSupported = $api->implementsActions(\OCA\Calendar\Backend\FIND_IN_PERIOD_BY_TYPE);
				if($isSupported) {
					$objects = $api->findObjectsByTypeInPeriod($calendarURI, $start, $end, $type, $userId);
				} else {
					$allObjects = $api->findObjects($calendarURI, $userId);

					if($limit !== null) {
						$i = 0;						
					}

					foreach($allObjects as $objectToCheck) {
						if($objectToCheck->getType() !== $type) {
							continue;
						}

						$startDate = $objectToCheck->getStartdate();
						$endDate = $objectToCheck->getEnddate();
						if( $objectToCheck->getRepeating() === true ||
							($startDate >= $start && $startDate <= $start) ||
							($endDate >= $end && $endDate <= $end) ||
							($startDate <= $start && $endDate >= $end)){
							if($limit === null || ($i >= $offset)) {
								$objects[] = $objectToCheck;
							}
							if($limit !== null) {
								$i++;
								if($i > ($offset + $limit)) {
									break;
								}	
							}
						}
					}
				}
			}

			return $objects;
		} catch (DoesNotExistException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * creates a new object
	 * @param Object $object
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $objectURI UID of the object
	 * @param string $userId
	 * @param int limit
	 * @param int offset
	 * @throws BusinessLayerException
	 * @return array containing all items
	 */
	public function create($object, $calendarId, $objectURI, $userId) {
		list($backend, $calendarURI) = $this->splitCalendarURI($calendarId);
		$this->allowNoObjectURITwice($backend, $calendarURI, $objectURI, $userId);
		$this->checkBackendEnabled($backend);

		try {
			$this->checkBackendSupports($backend, \OCA\Calendar\Backend\CREATE_OBJECT);
			$api = &$this->backends->find($backend)->api;

			if($object->isValid() === false) {
				$object->fix();
			}

			$object = $api->createObject($object, $calendarURI, $objectURI, $userId);

			$cacheObjects = $api->cacheObjects($calendarURI, $userId);
			if($cacheObjects) {
				$this->mapper->insert($object, $calendarURI, $objectURI, $userId);
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
				$this->mapper->update($object, $calendarURI, $objectURI, $userId);
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
				$this->mapper->delete($calendar);
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
				$this->mapper->delete($object, $calendarURI, $objectURI, $userId);
			}

			$cacheObjectsInNewBackend = $newBackendsAPI->cacheObjects($calendarURI, $userId);
			if($cacheObjectsInNewBackend === true) {
				//dafuq
				$this->mapper->create($object, $object->getCalendarUri(), $object->getObjectUri(), $userId);
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
		$existingObjects = $this->mapper->find($backend, $calendarURI, $objectURI, $userId);
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


	/**
	 * suggest available uri for backend
	 * if given uri is already available, the given uri will be returned
	 * @param string $backend
	 * @param string $calendarURI
	 * @param string $objectURI
	 * @param string $userId
	 * @return string $calendarURI available uri
	 */
	private function suggestObjectURI($backend, $calendarURI, $objectURI, $userId) {
		$objectURI = '';

		while(!$this->isObjectURIAvailable($backend, $calendarURI, $objectURI, $userId)) {
			$objectURI = substr(md5(rand().time().rand()),rand(0,11),20);
		}

		return $objectURI;
	}
}