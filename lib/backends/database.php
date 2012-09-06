<?php
/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Backend;
class Database extends OCA\Calendar\Backend;Backend {
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
	* @returns boolean if a calendar exists or not
	*
	* Get all calendar informations the backend provides.
	*/
	public static function getInPeriod(){
		return array();
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
}
