<?php
/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Backend;
interface CalendarInterface {
	/**
	* @brief Check if backend implements actions
	* @param $actions bitwise-or'ed actions
	* @returns boolean
	*
	* Returns the supported actions as int to be
	* compared with OC_CALENDAR_BACKEND_CREATE_CALENDAR etc.
	*/
	public function implementsActions($actions);

	/**
	* @brief should the calendar be cached?
	* @returns array with all calendar informations
	*
	* Get information if the calendar should be cached
	*/
	public function cacheIt();
	
	/**
	* @brief is the calendar $uri writable by a specific user
	* @param $uri - uri of the calendar
	* @returns boolean true/false
	*
	* Get information if the calendar is writable by a specific user
	*/
	public function isCalendarWritableByUser($uri, $userid);
	
	/**
	* @brief Get information about a calendars
	* @returns array with all calendar informations
	*
	* Get all calendar informations the backend provides.
	*/
	public function findCalendar($uri);

	/**
	* @brief Get a list of all calendars
	* @returns array with all calendars
	*
	* Get a list of all calendars.
	*/
	public function getCalendars($rw);

	/**
	* @brief Get information about an event
	* @returns array with all event informations
	*
	* Get icalendar of an event
	*/
	public function findObject($uri, $uid);

	/**
	* @brief Get a list of all objects
	* @returns array with all object
	*
	* Get a list of all object.
	*/
	public function getObjects($calid);
}