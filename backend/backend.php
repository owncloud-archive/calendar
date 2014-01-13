<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Backend;

use \OC\AppFramework\Db\DoesNotExistException;

use \OCA\Calendar\Db\ObjectType;
use \OCA\Calendar\Db\Permissions;

//constants
define('OCA\Calendar\Backend\NOT_IMPLEMENTED',  	 	-501);
define('OCA\Calendar\Backend\CREATE_CALENDAR', 			0x00000000001);
define('OCA\Calendar\Backend\UPDATE_CALENDAR',			0x00000000010);
define('OCA\Calendar\Backend\DELETE_CALENDAR',			0x00000000100);
define('OCA\Calendar\Backend\MERGE_CALENDAR',			0x00000001000);
define('OCA\Calendar\Backend\CREATE_OBJECT',			0x00000010000);
define('OCA\Calendar\Backend\UPDATE_OBJECT',			0x00000100000);
define('OCA\Calendar\Backend\DELETE_OBJECT',			0x00001000000);
define('OCA\Calendar\Backend\FIND_IN_PERIOD',			0x00010000000);
define('OCA\Calendar\Backend\FIND_OBJECTS_BY_TYPE',		0x00100000000);
define('OCA\Calendar\Backend\FIND_IN_PERIOD_BY_TYPE',	0x01000000000);
define('OCA\Calendar\Backend\SEARCH_BY_PROPERTIES',		0x10000000000);

abstract class Backend implements CalendarInterface {

	protected $api;
	protected $backend;

	protected $possibleActions = array(
		CREATE_CALENDAR 		=> 'createCalendar',
		UPDATE_CALENDAR			=> 'updateCalendar',
		DELETE_CALENDAR 		=> 'deleteCalendar',
		MERGE_CALENDAR 			=> 'mergeCalendar',
		CREATE_OBJECT 			=> 'createObject',
		UPDATE_OBJECT 			=> 'updateObject',
		DELETE_OBJECT 			=> 'deleteObject',
		FIND_IN_PERIOD 			=> 'findObjectsInPeriod',
		FIND_OBJECTS_BY_TYPE	=> 'findObjectsByType',
		FIND_IN_PERIOD_BY_TYPE	=> 'findObjectsByTypeInPeriod',
		SEARCH_BY_PROPERTIES	=> 'searchByProperties',
	);

	public function __construct($api, $backend){
		$this->api = $api;
		$this->backend = strtolower($backend);
	}

	/**
	 * @brief get integer that represents supported actions 
	 * @returns integer
	 * 
	 * This method returns an integer.
	 * This method is mandatory!
	 */
	public function getSupportedActions() {
		$actions = 0;
		foreach($this->possibleActions AS $action => $methodName) {
			if(method_exists($this, $methodName)) {
				$actions |= $action;
			}
		}

		return $actions;
	}

	/**
	 * @brief Check if backend implements actions
	 * @param string $actions
	 * @returns integer
	 * 
	 * This method returns an integer.
	 * If the action is supported, it returns an integer that can be compared with \OC\Calendar\Backend\CREATE_CALENDAR, etc...
	 * If the action is not supported, it returns -501
	 * This method is mandatory!
	 */
	public function implementsActions($actions) {
		return (bool)($this->getSupportedActions() & $actions);
	}

	/**
	 * @brief returns whether or not calendars should be cached
	 * @param string $userId
	 * @returns boolean
	 * 
	 * This method returns a boolen. true if calendars should be cached, false if calendars shouldn't be cached
	 * This method is mandatory!
	 */
	public function cacheCalendars($userId) {
		return !($this->canStoreColor() &&
				 $this->canStoreComponents() &&
				 $this->canStoreDisplayname() &&
				 $this->canStoreEnabled() &&
				 $this->canStoreOrder() &&
				 $this->canStoreCustomTimezone());
	}

	/**
	 * @brief returns whether or not calendar objects should be cached
	 * @param string $calendarURI
	 * @param string $userId
	 * @returns boolean
	 * @throws DoesNotExistException if uri does not exist
	 * 
	 * This method returns a boolen. true if calendar objects should be cached, false if the calendar objects shouldn't be cached
	 * This method is mandatory!
	 */
	public function cacheObjects($calendarURI, $userId) {
		$this->findCalendar($calendarURI, $userId);
		return true;
	}

	/**
	 * @brief returns information about calendar $calendarURI of the user $userId
	 * @param string $calendarURI
	 * @param string $userId
	 * @returns array with \OCA\Calendar\Db\Calendar object
	 * @throws DoesNotExistException if uri does not exist
	 * 
	 * This method returns an array of \OCA\Calendar\Db\Calendar object.
	 * This method is mandatory!
	 */
	public function findCalendar($calendarURI, $userId) {
		throw new DoesNotExistException();
	}

	/**
	 * @brief returns all calendars of the user $userId
	 * @param string $userId
	 * @returns array with \OCA\Calendar\Db\Calendar objects
	 * @throws DoesNotExistException if uri does not exist
	 * 
	 * This method returns an array of \OCA\Calendar\Db\Object objects.
	 * This method is mandatory!
	 */
	public function findCalendars($userId, $limit, $offset) {
		return array();
	}

	/**
	 * @brief returns information about the object (event/journal/todo) with the uid $objectURI in the calendar $calendarURI of the user $userId 
	 * @param string $calendarURI
	 * @param string $objectURI
	 * @param string $userid
	 * @returns \OCA\Calendar\Db\Object object
	 * @throws DoesNotExistException if uri does not exist
	 * @throws DoesNotExistException if uid does not exist
	 *
	 * This method returns an \OCA\Calendar\Db\Object object.
	 * This method is mandatory!
	 */
	public function findObject($calendarURI, $objectURI, $userId) {
		throw new DoesNotExistException();
	}

	/**
	 * @brief returns all objects in the calendar $calendarURI of the user $userId
	 * @param string $calendarURI
	 * @param string $userId
	 * @returns array with \OCA\Calendar\Db\Object objects
	 * @throws DoesNotExistException if uri does not exist
	 * 
	 * This method returns an array of \OCA\Calendar\Db\Object objects.
	 * This method is mandatory!
	 */
	public function findObjects($calendarURI, $userId, $limit, $offset) {
		throw new DoesNotExistException();
	}

	/**
	 * @brief returns whether or not a backend can store a calendar's color
	 * @returns boolean
	 * 
	 * This method returns a boolean
	 * This method is mandatory!
	 */
	public function canStoreColor() {
		return false;
	}

	/**
	 * @brief returns whether or not a backend can store a calendar's supported components
	 * @returns boolean
	 * 
	 * This method returns a boolean
	 * This method is mandatory!
	 */
	public function canStoreComponents() {
		return false;
	}

	/**
	 * @brief returns whether or not a backend can store a calendar's displayname
	 * @returns boolean
	 * 
	 * This method returns a boolean
	 * This method is mandatory!
	 */
	public function canStoreDisplayname() {
		return false;
	}

	/**
	 * @brief returns whether or not a backend can store if a calendar is enabled
	 * @returns boolean
	 * 
	 * This method returns a boolean
	 * This method is mandatory!
	 */
	public function canStoreEnabled() {
		return false;
	}

	/**
	 * @brief returns whether or not a backend can store a calendar's order
	 * @returns boolean
	 * 
	 * This method returns a boolean
	 * This method is mandatory!
	 */
	public function canStoreOrder() {
		return false;
	}

	/**
	 * @brief get displayname for backend
	 * @returns boolean
	 * 
	 * This method returns a boolean
	 * This method is mandatory!
	 */
	public function getDisplayName() {
		return '';
	}

	/**
	 * @brief get description for backend
	 * @returns boolean
	 * 
	 * This method returns a boolean
	 * This method is mandatory!
	 */
	public function getDescription() {
		return '';
	}

	/**
	 * @brief get components for backend
	 * @returns boolean
	 * 
	 * This method returns a boolean
	 * This method is mandatory!
	 */
	public function getComponents() {
		return ObjectType::ALL;
	}
}