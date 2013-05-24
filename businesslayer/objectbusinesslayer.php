<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\BusinessLayer;

use \OCA\AppFramework\Core\API;
use \OCA\AppFramework\Utility\TimeFactory;

use \OCA\Calendar\Db\Object;
use \OCA\Calendar\Db\ObjectCacheMapper;

class ObjectBusinessLayer {

	private $api;
	private $backends;
	private $calendars;
	private $mapper;

	public function __construct(ObjectCacheMapper $objectMapper,
		                        CalendarBusinessLayer $calendars,
		                        BackendBusinessLayer $backends,
	                            API $api){
		$this->mapper = $objectMapper;
		$this->calendars = $calendars;
		$this->backends = $backends;
		$this->api = $api;
	}
	
	public function find();
	
	public function delete();

		// !Object methods
	// !get information about objects
	
	/**
	 * @brief get all objects of a calendar
	 * @param $calendarid string
	 * @returns boolean
	 *
	 * Merge calendar two into calendar one, both with a specific calendarid
	 */
	public static function allObjects($calendarid, $type = null) {
		//validate the type param
		if(!is_null($type) && $type !== 'VEVENT' && $type !== 'VJOURNAL' && $type !== 'VTODO'){
			\OCP\Util::writeLog('calendar', __METHOD__.', Type: ' . $type. ' is no valid type', \OCP\Util::ERROR);
			return false;
		}
		//get the backend
		$backend = self::$_usedBackends[self::getBackendNameById($calendarid)];
		//get the object
		$objects = $backend->getObjects(self::getCalendarURIById($calendarid));
		//prepare objects
		for($i = 0; $i < count($objects); $i++) {
			//add objectid to event information
			$objects[$i]['objectid'] = $calendarid . '.' . $objects[$i]['uid'];
		}
		//TODO - only return objects of given type
		//return all objects
		return $objects;
	}
	
	/**
	 * @brief get all objects of a calendar in a specific period
	 * @param $calendarid string
	 * @param $start DateTime Object
	 * @param $end DateTime Object
	 * @returns boolean
	 *
	 * get all object of a calendar in a specific period
	 * ! $start and $end MUST be DateTime Objects !
	 */
	public static function allObjectsInPeriod($calendarid, $start, $end, $type = null) {
		//validate the type param
		if(!is_null($type) && $type !== 'VEVENT' && $type !== 'VJOURNAL' && $type !== 'VTODO'){
			\OCP\Util::writeLog('calendar', __METHOD__.', Type: ' . $type. ' is no valid type', \OCP\Util::ERROR);
			return false;
		}
		//get the backend object
		$backend = self::$_usedBackends[self::getBackendNameById($calendarid)];
		//does the backend support searching for objects in a specific period at all?
		if($backend->implementsActions(OC_CALENDAR_BACKEND_GET_IN_PERIOD)) {
			//yeah, it does :D
			$objects = $backend->getInPeriod(self::getCalendarURIById($calendarid), $start, $end);
		}else{
			//nope, it doesn't :(
			$allobjects = self::allObjects($calendarid);
			$objects = array();
			foreach($allobjects as $object) {
				//TODO - only put objects in the period into the objects array
			}
		}
		//prepare objects
		for($i = 0; $i < count($objects); $i++) {
			//add objectid to event information
			$objects[$i]['objectid'] = $calendarid . '.' . $objects[$i]['uid'];
		}
		//return all objects in period
		return $objects;
	}
	
	/**
	 * @brief get information about an object using it's objectid
	 * @param $calendarid1 string
	 * @param $calendarid2 string
	 * @returns boolean
	 *
	 * Merge calendar two into calendar one, both with a specific calendarid
	 */
	public static function findObject($objectid) {
		//get the backend object
		$backend = self::$_usedBackends[self::getBackendNameById($objectid)];
		//get the calendar info
		$object = $backend->findObject(self::getCalendarURIById($objectid), self::getObjectUIDById($objectid));
		//add the backendname to the URI
		$object['objectid'] = $objectid;
		//return the object information 
		return $object;
	}
	
	/**
	 * @brief merge calendar two into calendar one
	 * @param $calendarid1 string
	 * @param $calendarid2 string
	 * @returns boolean
	 *
	 * Merge calendar two into calendar one, both with a specific calendarid
	 * /
	public static function findObjectByUid($uid) {
		return self::findObject(self::getObjectIdByUID($uid));
	}*/
	
	// !modify objects
	
	/**
	 * @brief merge calendar two into calendar one
	 * @param $calendarid1 string
	 * @param $calendarid2 string
	 * @returns boolean
	 *
	 * Merge calendar two into calendar one, both with a specific calendarid
	 */
	public static function createObject($calendarid, $properties) {
		//get the backend object
		$backend = self::$_usedBackends[self::getBackendNameById($id)];
		//does the backend support creating objects at all?
		if($backend->implementsActions(OC_CALENDAR_BACKEND_CREATE_OBJECT)) {
			//create it
			$result = $backend->createObject(self::getCalendarURIById($calendarid), $properties);
			//was creating the object successful
			if($result) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * @brief merge calendar two into calendar one
	 * @param $calendarid1 string
	 * @param $calendarid2 string
	 * @returns boolean
	 *
	 * Merge calendar two into calendar one, both with a specific calendarid
	 */
	public static function editObject($objectid, $properties) {
		//get the backend object
		$backend = self::$_usedBackends[self::getBackendNameById($objectid)];
		//does the backend support editing objects at all?
		if($backend->implementsActions(OC_CALENDAR_BACKEND_CREATE_OBJECT)) {
			//edit it
			$result = $backend->editObject(self::getCalendarURIById($objectid), self::getObjectUIDById($objectid), $properties);
			//was editing the object successful
			if($result) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * @brief merge calendar two into calendar one
	 * @param $calendarid1 string
	 * @param $calendarid2 string
	 * @returns boolean
	 *
	 * Merge calendar two into calendar one, both with a specific calendarid
	 */
	public static function deleteObject($objectid) {
		//get the backend object
		$backend = self::$_usedBackends[self::getBackendNameById($objectid)];
		//does the backend support deleting objects at all?
		if($backend->implementsActions(OC_CALENDAR_BACKEND_DELETE_OBJECT)) {
			//delete it
			$result = $backend->deleteObject(self::getCalendarURIById($objectid), self::getObjectUIDById($objectid));
			//was deleting the object successful
			if($result) {
				return true;
			}
		//if deleting the object is not available, just hide it
		}else{
			self::hideObject($objectid);
			return true;
		}
		return false;
	}
	
	/**
	 * @brief merge calendar two into calendar one
	 * @param $calendarid1 string
	 * @param $calendarid2 string
	 * @returns boolean
	 *
	 * Merge calendar two into calendar one, both with a specific calendarid
	 */
	public static function moveObject($objectid, $newcalendarid) {
		$oldbackend = self::getBackendNameById($objectid);
		$newbackend = self::getBackendNameById($newcalendarid);
		if($oldbackend == $newbackend && self::$_usedBackends[$oldbackend]->implementsActions(OC_CALENDAR_BACKEND_MOVE_OBJECT)) {
			$backend = self::$_usedBackends[$oldbackend];
			$uid = self::getObjectUIDById($objectid);
			$newcalendar = self::getCalendarURIById($newcalendarid);
			$backend->moveObject($uid, $newcalendar);
		}else{
			//TODO
			//delete old object
			//create a new one with same properties
		}
	}
