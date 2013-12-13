<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
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
	 * Find the objects $objectURI of calendar $calendarId of user $userId
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $objectURI UID of the object
	 * @param string $userId
	 * @throws BusinessLayerException
	 * @return array containing all items
	 */
	public function find($calendarId, $objectURI, $userId) {
		list($backend, $calendarURI) = $this->getBackendAndRealURIFromURI($calendarId);

		try {
			$api = &$this->backends->find($backend)->api;
			$cacheObjects = $api->cacheObjects($calendarURI, $userId);
			if($cacheObjects) {
				$object = $this->mapper->find($backend, $calendarURI, $objectURI, $userId);
			} else {
				$object = $api->findObject($calendarURI, $objectURI, $userId);
			}
		} catch (DoesNotExistException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch (MultipleObjectsReturnedException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch (BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
		return $object;
	}

	/**
	 * Finds all objects of calendar $calendarId of user $userId
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $userId
	 * @throws BusinessLayerException
	 * @return array containing all items
	 */
	public function findAll($calendarId, $userId) {
		list($backend, $calendarURI) = $this->getBackendAndRealURIFromURI($calendarId);
		try {
			$api = &$this->backends->find($backend)->api;
			$cacheObjects = $api->cacheObjects($calendarURI, $userId);
			if($cacheObjects) {
				$objects = $this->mapper->findAll($backend, $calendarURI, $userId);
			} else { 
				$objects = $api->findObjects($calendarURI, $userId);
			}
			return $objects;
		} catch (DoesNotExistException $ex) {
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
	 * @throws BusinessLayerException
	 * @return array containing all items
	 */
	public function findAllByType($calendarId, $type, $userId) {
		list($backend, $calendarURI) = $this->getBackendAndRealURIFromURI($calendarId);
		try {
			$api = &$this->backends->find($backend)->api;
			$cacheObjects = $api->cacheObjects($calendarURI, $userId);
			if($cacheObjects) {
				$object = $this->mapper->findAllByType($backend, $calendarURI, $type, $userId);
			} else {
				$isSupported = $api->implementsActions(\OCA\Calendar\Backend\FIND_OBJECTS_BY_TYPE);
				if($isSupported) {
					$objects = $api->findObjectsByType($calendarURI, $type, $userId);
				} else {
					$allObjects = $api->findObjects($calendarURI, $userId);
					$objects = array();
					foreach($allObjects as $objectToCheck) {
						if($objectToCheck->getType() === $type) {
							$objects[] = $objectToCheck;
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
	 * @throws BusinessLayerException
	 * @return array containing all items
	 */
	public function findAllInPeriod($calendarId, $start, $end, $userId) {
		list($backend, $calendarURI) = $this->getBackendAndRealURIFromURI($calendarId);
		try {
			$api = &$this->backends->find($backend)->api;
			$cacheObjects = $api->cacheObjects($calendarURI, $userId);
			if($cacheObjects) {
				$objects = $this->mapper->findAllInPeriod($backend, $calendarURI, $start, $end, $userId);
			} else {
				$isSupported = $api->implementsActions(\OCA\Calendar\Backend\FIND_IN_PERIOD);
				if($isSupported) {
					$objects = $api->findObjectsInPeriod($calendarURI, $type, $userId);
				} else {
					$allObjects = $api->findObjects($calendarURI, $userId);
					$objects = array();
					foreach($allObjects as $objectToCheck) {
						$startDate = $objectToCheck->getStartdate();
						$endDate = $objectToCheck->getEnddate();
						if( $objectToCheck->getRepeating() === true ||
							($startDate >= $start && $startDate <= $start) ||
							($endDate >= $end && $endDate <= $end) ||
							($startDate <= $start && $endDate >= $end)){
							$objects[] = $objectToCheck;
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
	 * @throws BusinessLayerException
	 * @return array containing all items
	 */
	public function findAllByTypeInPeriod($calendarId, $type, $start, $end, $userId) {
		list($backend, $calendarURI) = $this->getBackendAndRealURIFromURI($calendarId);

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
					$objects = array();
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
							$objects[] = $objectToCheck;
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
	 * @throws BusinessLayerException
	 * @return array containing all items
	 */
	public function create($object, $calendarId, $objectURI, $userId) {
		$this->allowNoNameTwice($calendarId, $objectURI, $userId);

		list($backend, $calendarURI) = $this->getBackendAndRealURIFromURI($calendarId);

		try {
			$this->checkBackendSupports($backend, \OCA\Calendar\Backend\CREATE_OBJECT);
			$api = &$this->backends->find($backend)->api;

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
		list($backend, $calendarURI) = $this->getBackendAndRealURIFromURI($calendarId);

		try {
			if($object->getBackend() !== $backend || $object->getUri() !== $calendarURI) {
				return $this->move($object, $calendarId, $objectURI, $userId);
			}

			$this->checkBackendSupports($backend, \OCA\Calendar\Backend\UPDATE_OBJECT);

			$api = &$this->backends->find($backend)->api;

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

	public function delete($calendarId, $objectURI, $userId) {
		list($backend, $calendarURI) = $this->getBackendAndRealURIFromURI($calendarId);
		$this->checkBackendEnabled($backend);

		try {
			$this->checkBackendSupports($backend, \OCA\Calendar\Backend\DELETE_OBJECT);

			$api = &$this->backends->find($backend)->api;
			$api->deleteObject($calendarURI, $objectURI, $userId);

			if($api->cacheObjects($calendarURI, $userId)) {
				$this->mapper->delete($calendar);
			}

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
		list($backend, $calendarURI) = $this->getBackendAndRealURIFromURI($calendarId);

		if($calendarBusinessLayer->isCalendarURIAvailable($object->getBackend(), $object->getUri(), $userId)) {
			throw new BusinessLayerException('Can not move object to another calendar. Calendar does not exist');
		}

		try {
			$oldBackend = $backend;
			$newBackend = $calendar->getBackend();

			$oldBackendsAPI = &$this->backends->find($oldBackend)->api;
			$newBackendsAPI = &$this->backends->find($newBackend)->api;

			if($oldBackend == $newBackend && $oldBackendsAPI->implementsActions(\OCA\Calendar\Backend\MOVE_OBJECT)) {
				$object = $newBackendsAPI->moveObject($object, $calendarURI, $objectURI, $userId);
			} else {
				$this->checkBackendSupports($oldBackend, \OCA\Calendar\Backend\DELETE_OBJECT);
				$this->checkBackendSupports($newBackend, \OCA\Calendar\Backend\CREATE_OBJECT);

				$status = $newBackendsAPI->createObject($object);
				if($status) {
					$object = $this->backends->find($object->getBackend())->api->createObject();
				} else {
					throw new BusinessLayerException('Could not move object to another calendar.');
				}
			}

			$cacheObjectsInOldBackend = $oldBackendsAPI->cacheObjects($calendarURI, $userId);
			if($cacheObjectsInOldBackend) {
				$this->mapper->delete($object, $calendarURI, $objectURI, $userId);
			}

			$cacheObjectsInNewBackend = $newBackendsAPI->cacheObjects($calendarURI, $userId);
			if($cacheObjectsInNewBackend) {
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
	 * Find the objects $objectURI of calendar $calendarId of user $userId
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $objectURI UID of the object
	 * @param string $userId
	 * @throws BusinessLayerException
	 * @return array containing all items
	 */
	public function moveAll($calendarId, $objectURI, $userId) {
		//todo missing parameter for new calendarid
		return $this->calendarBusinessLayer->move($calendarId, $userId);
	}

	/**
	 * Find the objects $objectURI of calendar $calendarId of user $userId
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $objectURI UID of the object
	 * @param string $userId
	 * @throws BusinessLayerException
	 * @return array containing all items
	 */
	public function touch($calendarId, $objectURI, $userId) {
		list($backend, $calendarURI) = $this->getBackendAndRealURIFromURI($calendarId);

		try {
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
	 * Find the objects $objectURI of calendar $calendarId of user $userId
	 * @param string $calendarId global uri of calendar e.g. local-work
	 * @param string $userId
	 * @throws BusinessLayerException
	 * @return array containing all items
	 */
	public function touchAll($calendarId, $userId) {
		list($backend, $calendarURI) = $this->getBackendAndRealURIFromURI($calendarId);
		try {
			$objects = $this->findAll($calendarId, $userId);
			foreach($objects as $object) {
				$this->touch($calendarId, $object->getUid(), $userId);
			}
		} catch(DoesNotImplementException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		} catch(BackendException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	/**
	 * make sure that uri does not already exist when creating a new calendar
	 * @param string $backend
	 * @param string $calendarURI
	 * @param string $userId
	 * @throws BusinessLayerException if uri is already taken
	 */
	private function allowNoObjectURITwice($backend, $calendarURI, $userId){
		$isAvailable = $this->isObjectURIAvailable($backend, $calendarURI, $userId);
		if(!$isAvailable) {
			throw new BusinessLayerException('Can not add object: UID exists already');
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
	private function suggestObjectURI($backend, $calendarURI, $objectURI, $userId) {
		while(!$this->isObjectURIAvailable($backend, $calendarURI, $objectURI, $userId)) {
			$objectURI = substr(md5(rand().time().rand()),rand(0,11),20);
		}
		return $objectURI;
	}

	/**
	 * checks if a uri is available
	 * @param string $backend
	 * @param string $calendarURI
	 * @param string $userId
	 * @return boolean
	 */
	private function isObjectURIAvailable($backend, $calendarURI, $objectURI, $userId) {
		$existingObjects = $this->mapper->find($backend, $calendarURI, $objectURI, $userId);
		if(count($existingObjects) > 0) {
			return false;
		}

		$existingRemoteObjects = $this->backends->find($backend)->api->findObject($calendarURI, $objectURI, $userId);
		if(count($existingRemoteObjects) > 0) {
			return false;
		}

		return true;
	}
}