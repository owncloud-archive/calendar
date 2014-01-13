<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 *
 * naming schema:
 * sharing-{sharingid}
 * sharing-{sharingid}-{uid}
 */
namespace OCA\Calendar\Backend;

use \OC\AppFramework\Core\API;
use \OC\AppFramework\Db\Mapper;
use \OC\AppFramework\Db\DoesNotExistException;
use \OC\AppFramework\Db\MultipleObjectsReturnedException;

use \OCA\Calendar\Db\Calendar;
use \OCA\Calendar\Db\Object;
use \OCA\Calendar\Db\ObjectType;

use \OCA\Calendar\Db\Permissions;

class Sharing extends Backend {

	private $backend;

	public function __construct($api, $parameters, &$backendBusinessLayer){
		parent::__construct($api, 'sharing');
		$this->backend = $backendBusinessLayer;
	}

	public function cacheCalendars($userId) {
		//cache calendars so users can set custom colors and custom visibility
		return true;
	}

	public function cacheObjects($calendarURI, $userId) {
		return false;
	}

	public function findCalendar($calendarURI, $userId) {
		
	}

	public function findCalendars($userId, $limit, $offset) {

	}

	public function updateCalendar(Calendar $calendar, $calendarId, $userId) {

	}

	public function deleteCalendar(Calendar $calendar) {

	}
	
	public function mergeCalendar(Calendar $calendar, $calendarId=null, $userId=null) {

	}

	public function findObject($calendarURI, $objectURI, $userId) {

	}

	public function findObjects($calendarId, $userId, $limit, $offset) {

	}

	public function findObjectsInPeriod($calendarId, $start, $end, $userId, $limit, $offset){

	}

	public function findObjectsByType($calendarId, $type, $userId, $limit, $offset) {

	}

	public function findObjectsByTypeInPeriod($calendarId, $type, $start, $end, $userId, $limit, $offset) {

	}

	public function createObject(Object $object, $userId) {

	}

	public function updateObject(Object $object, $calendarId, $uri, $userId) {

	}

	public function deleteObject(Object $object){

	}

	public function searchByProperties($properties=array(), $calendarId=null, $userId=null) {
		
	}
}