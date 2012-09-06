<?php
/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
/**
 * error code for functions not provided by the user backend
 */
define('OC_CALENDAR_BACKEND_NOT_IMPLEMENTED',   -501);

/**
 * actions that user backends can define
 */
//for calendars
define('OC_CALENDAR_BACKEND_CREATE_CALENDAR', 	0x00000000001);
define('OC_CALENDAR_BACKEND_EDIT_CALENDAR',		0x00000000010);
define('OC_CALENDAR_BACKEND_DELETE_CALENDAR',	0x00000000100);
define('OC_CALENDAR_BACKEND_TOUCH_CALENDAR',	0x00000001000);
define('OC_CALENDAR_BACKEND_MERGE_CALENDAR',	0x00000010000);
//for objects
define('OC_CALENDAR_BACKEND_CREATE_OBJECT',		0x00000100000);
define('OC_CALENDAR_BACKEND_EDIT_OBJECT',		0x00001000000);
define('OC_CALENDAR_BACKEND_DELETE_OBJECT',		0x00010000000);
define('OC_CALENDAR_BACKEND_GET_IN_PERIOD',		0x00100000000);
define('OC_CALENDAR_BACKEND_MOVE_OBJECT',		0x01000000000);

/**
 * Abstract base class for calendar. Provides methods for querying backend
 * capabilities.
 *
 * Subclass this for your own backends, and see OCA\Calendar\Backend\Example for descriptions
 */
namespace OCA\Calendar\Backend;
abstract class Backend implements OCA\Calendar\Backend\Interface {

	protected $possibleActions = array(
		OC_CALENDAR_BACKEND_CREATE_CALENDAR => 'createCalendar',
		OC_CALENDAR_BACKEND_EDIT_CALENDAR	=> 'editCalendar',
		OC_CALENDAR_BACKEND_DELETE_CALENDAR => 'deleteCalendar',
		OC_CALENDAR_BACKEND_TOUCH_CALENDAR 	=> 'touchCalendar',
		OC_CALENDAR_BACKEND_MERGE_CALENDAR 	=> 'mergeCalendar',
		OC_CALENDAR_BACKEND_CREATE_OBJECT 	=> 'createObject',
		OC_CALENDAR_BACKEND_EDIT_OBJECT 	=> 'editObject',
		OC_CALENDAR_BACKEND_DELETE_OBJECT 	=> 'deleteObject',
		OC_CALENDAR_BACKEND_GET_IN_PERIOD 	=> 'getInPeriod',
		OC_CALENDAR_BACKEND_MOVE_OBJECT 	=> 'moveObject',
	);

	/**
	* @brief Get all supported actions
	* @returns bitwise-or'ed actions
	*
	* Returns the supported actions as int to be
	* compared with OC_CALENDAR_BACKEND_CREATE_CALENDAR etc.
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
	* @param $actions bitwise-or'ed actions
	* @returns boolean
	*
	* Returns the supported actions as int to be
	* compared with OC_CALENDAR_BACKEND_CREATE_CALENDAR etc.
	*/
	public function implementsActions($actions) {
		return (bool)($this->getSupportedActions() & $actions);
	}

	/**
	* @brief should the calendar be cached?
	* @returns array with all calendar informations
	*
	* Get information if the calendar should be cached
	*/
	public function cacheIt(){
		return true;
	}

	/**
	* @brief Get information about a calendars
	* @param $calid calendarid
	* @returns array with all calendar informations
	*
	* Get all calendar informations the backend provides.
	*/
	public function findCalendar($calid = ''){
		return false;
	}

	/**
	* @brief Get a list of all calendars
	* @param $rw boolean about read&write support
	* @returns array with all calendars
	*
	* Get a list of all calendars.
	*/
	public function getCalendars($rw){
		return array();
	}

	/**
	* @brief Get information about an event
	* @param $uid - unique id 
	* @returns array with all event informations
	*
	* Get icalendar of an event
	*/
	public function findObject($uid = ''){
		return false;
	}

	/**
	* @brief Get a list of all objects
	* @param $calid calendarid
	* @returns array with all object
	*
	* Get a list of all object.
	*/
	public function getObjects($calid){
		return array();
	}
}