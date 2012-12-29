<?php
/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * Copyright (c) 2012 Bart Visscher <bartv@thisnet.nl>
 * Copyright (c) 2011 Jakob Sack <mail@jakobsack.de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 *
 * database structur of database backend
 *
 * CREATE TABLE calendar_calendars (
 *     id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
 *     userid VARCHAR(255),
 *     displayname VARCHAR(100),
 *     uri VARCHAR(100),
 *     ctag INTEGER UNSIGNED NOT NULL DEFAULT '0',
 *     color VARCHAR(10),
 *     timezone TEXT,
 *     components VARCHAR(20)
 * );
 *
 * CREATE TABLE calendar_objects (
 *     id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
 *     calendarid INTEGER UNSIGNED NOT NULL,
 *     objecttype VARCHAR(40) NOT NULL,
 *     startdate DATETIME,
 *     enddate DATETIME,
 *     repeating INT(1),
 *     summary VARCHAR(255),
 *     calendardata TEXT,
 *     uri VARCHAR(100),
 *     lastmodified INT(11)
 * );
 *
 */
namespace OCA\Calendar\Backend;
class Database extends \OCA\Calendar\Backend\Backend {
	/**
	* @brief should the calendar be cached?
	* @returns bool
	*
	* Get information if the calendar should be cached
	*/
	public function cacheIt(){
		//There's no need to cache the calendar.
		return false;
	}
	
	/**
	* @brief is the calendar $uri writeable by the user $user
	* @returns bool
	*
	* Get information if the calendar is writeable by the given user
	*/
	public function isCalendarWritableByUser($uri, $userid){
		//It's not necessary to check,
		//because all calendars in this backend are writeable anyhow.
		return true;
	}
	
	/**
	* @brief get the calendarobject of the calendar with the uri $uri
	* @param $uri string - URI of the searched calendar
	* @returns calendarobject
	*
	* get the calendarobject of the calendar with the uri $uri
	*/
	public function findCalendar($uri){
		//prepare sql statement
		$stmt = \OCP\DB::prepare( 'SELECT * FROM `*PREFIX*calendar_calendars` WHERE `uri` = ?' );
		//execute sql statement
		$result = $stmt->execute(array($uri));
		//check for errors
		if(\OCP\DB::isError($result)){
			\OCP\Util::writeLog('calendar', 'Database Backend - ' . __METHOD__ . ', An unknown database error occurred while selecting the calendar with the uri: ' . $uri, \OCP\Util::ERROR);
			return false;
		}
		//get the current database row
		$row = $result->fetchRow();
		//return a calendar object
		return self::getCalendarObjectByDatabaseBackendRow($row);
	}

	/**
	* @brief get a list of all calendars
	* @param $userid string ID of the user
	* @param $rw boolean Read-Write calendars only?
	* @returns array with calendarobjects of all calendars
	*
	* get a list with calendarobjects of all calendars
	*
	* The second parameter will have no influence on the result at all,
	* because all calendars in this backend are writable 
	*/
	public function getCalendars($userid, $rw = null){
		//prepare sql statement
		$stmt = \OCP\DB::prepare( 'SELECT * FROM `*PREFIX*calendar_calendars` WHERE `userid` = ?' );
		//execute sql statement
		$result = $stmt->execute(array($userid));
		//check for errors
		if(\OCP\DB::isError($result)){
			\OCP\Util::writeLog('calendar', 'Database Backend - ' . __METHOD__ . ', An unknown database error occurred while selecting all calendars of the user: ' . $userid, \OCP\Util::ERROR);
			return false;
		}
		//create empty array for all calendars
		$calendars = array();
		while( $row = $result->fetchRow()){
			//get the calendarobject of each calendar
			$calendars[] = self::getCalendarObjectByDatabaseBackendRow($row);
		}
		//return array with all calendar objects
		return $calendars;
	}

	/**
	* @brief get information about an event
	* @param $uri string - URI of the searched calendar
	* @param $uid string - UID of the searched object
	* @returns array with all event informations
	*
	* Get icalendar of an event
	*/
	public function findObject($uri, $uid){
		$stmt = \OCP\DB::prepare( 'SELECT * FROM `*PREFIX*calendar_objects` WHERE `uid` = ?' );
		$result = $stmt->execute(array($uniqueid));
		$row =  $result->fetchRow();
		return self::getObjectObjectByDatabaseBackendRow($row);
	}

	/**
	* @brief Get a list of all objects
	* @param $calid calendarid
	* @returns array with all object
	*
	* Get a list of all object.
	*/
	public function getObjects($uri){
		$stmt = \OCP\DB::prepare( 'SELECT * FROM `*PREFIX*calendar_objects` WHERE `uri` = ?' );
		$result = $stmt->execute(array($uri));

		$objects = array();
		while( $row = $result->fetchRow()){
			$objects[] = self::getObjectObjectByDatabaseBackendRow($row);
		}
		return $objects;
	}
	
	/**
	* @brief create a new calendar
	* @param $calendarobject
	* @returns boolean if a calendar exists or not
	*
	* create a new calendar
	*/
	public static function createCalendar($calendarobject){
		return false;
	}
	
	/**
	* @brief edit a calendar
	* @param $calendarobject
	* @returns boolean if a calendar exists or not
	*
	* edit a calendar
	*/
	public static function editCalendar($calendarobject){
		return false;
	}
	
	/**
	* @brief delete a calendar
	* @param $uri uri of the calendar
	* @returns boolean if a calendar exists or not
	*
	* delete a calendar by it's id
	*/
	public static function deleteCalendar($uri){
		return false;
	}
	
	/**
	* @brief touch a calendar
	* @param $uri uri of the calendar
	* @returns boolean if a calendar exists or not
	*
	* touch a calendar
	*/
	public static function touchCalendar($uri){
		return false;
	}
	
	/**
	* @brief check if a calendar exists
	* @param $calid calendarid
	* @returns boolean if a calendar exists or not
	*
	* Get all calendar informations the backend provides.
	*/
	public static function mergeCalendar(){
		return false;
	}
	
	/**
	* @brief check if a calendar exists
	* @param $calid calendarid
	* @returns boolean if a calendar exists or not
	*
	* Get all calendar informations the backend provides.
	*/
	public static function createObject(){
		return false;
	}
	
	/**
	* @brief check if a calendar exists
	* @param $calid calendarid
	* @returns boolean if a calendar exists or not
	*
	* Get all calendar informations the backend provides.
	*/
	public static function editObject(){
		return false;
	}
	
	/**
	* @brief check if a calendar exists
	* @param $calid calendarid
	* @returns boolean if a calendar exists or not
	*
	* Get all calendar informations the backend provides.
	*/
	public static function deleteObject(){
		return false;
	}
	
	/**
	* @brief check if a calendar exists
	* @param $calid calendarid
	* @param $start DateTime Object of start
	* @param $end DateTime Object of end
	* @returns boolean if a calendar exists or not
	*
	* Get all calendar informations the backend provides.
	*/
	public static function getInPeriod($uri, $start, $end){
		$calendarid = self::getCalendarIdByURI($uri);
		$sql =  'SELECT * FROM `*PREFIX*calendar_objects` WHERE `calendarid` = ?';
		$sql .= 'AND ((`startdate` >= ? AND `startdate` <= ?  AND `repeating` = 0)';
		$sql .= ' OR (`enddate` >= ? AND `enddate` <= ? AND `repeating` = 0)';
		$sql .= ' OR (`startdate` <= ? AND `repeating` = 1))';
		$stmt = \OCP\DB::prepare($sql);
		$start = self::getUTCforMDB($start);
		$end = self::getUTCforMDB($end);
		$result = $stmt->execute(array($calendarid,
					$start, $end,
					$start, $end,
					$end));
		$calendarobjects = array();
		while( $row = $result->fetchRow()){
			$calendarobjects[] = $row;
		}
		return $calendarobjects;
	}
	
	/**
	* @brief check if a calendar exists
	* @param $calid calendarid
	* @returns boolean if a calendar exists or not
	*
	* Get all calendar informations the backend provides.
	*/
	public static function moveObject(){
		return false;
	}
	
	private static function getUTCforMDB($datetime){
		return date('Y-m-d H:i:s', $datetime->format('U'));
	}
	
	private static function getCalendarIdByURI($uri){
		$stmt = \OCP\DB::prepare( 'SELECT * FROM `*PREFIX*calendar_calendars` WHERE `uri` = ?' );
		$result = $stmt->execute(array($uri));
		$result =  $result->fetchRow();
		return $result['id'];
	}
	
	private static function getCalendarObjectByDatabaseBackendRow($row){
		$calendar = \Sabre\VObject\Component::create('VCALENDAR');
		$calendar->add('X-OWNCLOUD-CALENDARCOLOR', $row['calendarcolor']);
		$calendar->add('X-OWNCLOUD-ISEDITABLE', TRUE);
		$calendar->add('X-OWNCLOUD-DISPLAYNAME', $row['displayname']);
		$calendar->add('X-OWNCLOUD-URI', $row['uri']);
		return $calendar;
	}
	
	private static function getObjectObjectByDatabaseBackendRow($row){
		
	}
}
