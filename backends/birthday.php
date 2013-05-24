<?php
/**
 * Copyright (c) 2013 Georg Ehrke <developer at georgehrke dot com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Backend;

use \OCA\Calendar\Db\Calendar;

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
		$calendar = new Calendar();
		$calendar->setUserId($userid);
		$calendar->setBackend('birthday');
		$calendar->setURI('birthdays');
		$calendar->setDisplayname();
		$calendar->setComponents('VEVENT');
		$calendar->setTimezone('UTC');
		$calendar->setEnabled(true);
		$calendar->setWritable(false);
		return array($calendar);
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
}