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
	* @returns array with all calendar informations
	*
	* Get information if the calendar should be cached
	*/
	public function cacheIt(){
		return false;
	}
	
	public function isCalendarWritableByUser($uri, $userid){
		//no need to really check, 'cause all db calendars are writable anyhow
		return true;
	}
	
	/**
	* @brief Get information about a calendar
	* @param $calid calendarid
	* @returns array with all calendar informations
	*
	* Get all calendar informations the backend provides.
	* 
	* [uri => calendar's uri, 
	*  userid => owner's uid, 
	*  displayname => public visible display name,
	*  ctag => current ctag,
	*  color => calendar's color,
	*  timezone => default timezone of calendar,
	*  components => supported components]
	*
	*/
	public function findCalendar($uri){
		$stmt = \OCP\DB::prepare( 'SELECT * FROM `*PREFIX*calendar_calendars` WHERE `uri` = ?' );
		$result = $stmt->execute(array($uri));
		return $result->fetchRow();
	}

	/**
	* @brief Get a list of all calendars
	* @param $rw boolean about read&write support
	* @returns array with all calendars
	*
	* Get a list of all calendars.
	* 
	* [1=>
	*  [uri => calendar's uri, 
	*   userid => owner's uid, 
	*   displayname => public visible display name,
	*   ctag => current ctag,
	*   color => calendar's color,
	*   timezone => default timezone of calendar,
	*   components => supported components],
	*  2=>
	*   ...
	* ]
	*/
	public function getCalendars($userid, $rw = null){
		$stmt = \OCP\DB::prepare( 'SELECT * FROM `*PREFIX*calendar_calendars` WHERE `userid` = ?' );
		$result = $stmt->execute(array($userid));
		$calendars = array();
		while( $row = $result->fetchRow()){
			$calendars[] = $row;
		}
		return $calendars;
	}

	/**
	* @brief Get information about an event
	* @param $uniqueid
	* @returns array with all event informations
	*
	* Get icalendar of an event
	*/
	public function findObject($uri, $uid){
		$stmt = \OCP\DB::prepare( 'SELECT * FROM `*PREFIX*calendar_objects` WHERE `uid` = ?' );
		$result = $stmt->execute(array($uniqueid));
		$result =  $result->fetchRow();
		return $result['calendardata'];
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

		$calendarobjects = array();
		while( $row = $result->fetchRow()){
			$calendarobjects[] = $row;
		}
		return $calendarobjects;
	}
	
	/**
	* @brief create a new calendar
	* @param $userid uid of the user
	* @param $name human readable name
	* @param $components list of supported components
	* @param $timezone timezone of calendar
	* @param $order order of calendar in a list
	* @param $color color of calendar
	* @returns boolean if a calendar exists or not
	*
	* create a new calendar
	*/
	public static function createCalendar($uid, $name, $components='VEVENT,VTODO,VJOURNAL',$timezone=null,$order=0,$color=null){
		return false;
	}
	
	/**
	* @brief edit a calendar
	* @param $calid calendarid
	* @param $userid uid of the user
	* @param $name human readable name
	* @param $components list of supported components
	* @param $timezone timezone of calendar
	* @param $order order of calendar in a list
	* @param $color color of calendar
	* @param $calid calendarid
	* @returns boolean if a calendar exists or not
	*
	* edit a calendar
	*/
	public static function editCalendar($calid, $uid, $name, $components='VEVENT,VTODO,VJOURNAL',$timezone=null,$order=0,$color=null){
		return false;
	}
	
	/**
	* @brief delete a calendar
	* @param $calid calendarid
	* @returns boolean if a calendar exists or not
	*
	* delete a calendar by it's id
	*/
	public static function deleteCalendar($calid){
		return false;
	}
	
	/**
	* @brief touch a calendar
	* @param $calid calendarid
	* @returns boolean if a calendar exists or not
	*
	* touch a calendar
	*/
	public static function touchCalendar($calid){
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
		$stmt = \OCP\DB::prepare( 'SELECT * FROM `*PREFIX*calendar_objects` WHERE `calendarid` = ?'
		.' AND ((`startdate` >= ? AND `startdate` <= ? AND `repeating` = 0)'
		.' OR (`enddate` >= ? AND `enddate` <= ? AND `repeating` = 0)'
		.' OR (`startdate` <= ? AND `repeating` = 1))' );
		
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
		return date('Y-m-d H:i', $datetime->format('U') - $datetime->getOffset());
	}
	
	private static function getCalendarIdByURI($uri){
		$stmt = \OCP\DB::prepare( 'SELECT * FROM `*PREFIX*calendar_calendars` WHERE `uri` = ?' );
		$result = $stmt->execute(array($uri));
		$result =  $result->fetchRow();
		return $result['id'];
	}
}
