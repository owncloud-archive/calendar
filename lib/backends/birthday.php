<?php
/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Backend;
class Birthday extends Backend {
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
	public function getCalendars($userid, $rw){
		if(!$this->isByGroup()){
			//create a new calendar object
			$calendar = \Sabre\VObject\Component::create('VCALENDAR');
			//split calendarid to get backend and uri
			list($backendname, $uri) = explode('.', $row['calendarid']);
			//add some informations like name of the backend, 
			$calendar->add('X-OWNCLOUD-BACKEND', $backendname);
			//color of the calendar,
			$calendar->add('X-OWNCLOUD-CALENDARCOLOR', $row['color']);
			//id of the calendar,
			$calendar->add('X-OWNCLOUD-CALENADRID', $row['calendarid']);
			//the supported components,
			$calendar->add('X-OWNCLOUD-COMPONENTS', $row['components']);
			//name of the calendar,
			$calendar->add('X-OWNCLOUD-DISPLAYNAME', $row['displayname']);
			//is the calendar editable or readonly,
			$calendar->add('X-OWNCLOUD-ISEDITABLE', $row['writable']);
			//order of the calendar,
			$calendar->add('X-OWNCLOUD-ORDER', $row['order']);
			//the calendar's timezone,
			$calendar->add('X-OWNCLOUD-TZ', $row['timezone']);
			//uri of the calendar,
			$calendar->add('X-OWNCLOUD-URI', $uri);
			//userid of the owner,
			$calendar->add('X-OWNCLOUD-USERID', $row['userid']);
			//is the calendar enabled, disabled or hidden
			$calendar->add('X-OWNCLOUD-VISIBILITY', $row['visibility']);
			//return the created calendar object
			return $calendar;
		}
		return array();
	}

	/**
	* @brief Get information about an event
	* @param $uid - unique id 
	* @returns array with all event informations
	*
	* Get icalendar of an event
	*/
	public function findObject($uri, $uid){
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
	
	private final function isByGroup(){
		return (bool) \OCP\getAppValue('calendar', 'birthday-backend-byGroup', false);
	}
}