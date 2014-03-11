<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 *
 * custom ics properties:
 */
namespace OCA\Calendar\Backend;

use \OCA\Calendar\AppFramework\Core\API;
use \OCA\Calendar\AppFramework\Db\Mapper;
use \OCA\Calendar\AppFramework\Db\DoesNotExistException;
use \OCA\Calendar\AppFramework\Db\MultipleObjectsReturnedException;

use \OCA\Calendar\Db\Calendar;
use \OCA\Calendar\Db\Object;
use \OCA\Calendar\Db\ObjectType;

use \OCA\Calendar\Db\Permissions;

class LocalStorage extends Backend {

	private $files = array();
	private $wasScanned = false;
	private $calendarMIME;

	public function __construct($api, $parameters){
		parent::__construct($api, 'localstorage');
		$this->calendarMIME = 'text/calendar';
	}

	public function cacheCalendars($userId) {
		return false;
	}

	public function cacheObjects($calendarURI, $userId) {
		return false;
	}

	public function findCalendar($calendarURI, $userId) {
		$this->checkFSWasScanned($userId);

	}

	public function findCalendars($userId, $limit, $offset) {
		$this->checkFSWasScanned($userId);

	}

	public function createCalendar(Calendar $calendar) {
		$this->checkFSWasScanned($userId);

	}

	public function updateCalendar(Calendar $calendar, $calendarId, $userId) {
		$this->checkFSWasScanned($userId);

	}

	public function deleteCalendar(Calendar $calendar) {
		$this->checkFSWasScanned($userId);

	}
	
	public function mergeCalendar(Calendar $calendar, $calendarId=null, $userId=null) {
		$this->checkFSWasScanned($userId);

	}

	public function findObject($calendarURI, $objectURI, $userId) {
		$this->checkFSWasScanned($userId);

	}

	public function findObjects($calendarId, $userId, $limit, $offset) {
		$this->checkFSWasScanned($userId);

	}

	public function findObjectsInPeriod($calendarId, $start, $end, $userId, $limit, $offset){
		$this->checkFSWasScanned($userId);

	}

	public function findObjectsByType($calendarId, $type, $userId, $limit, $offset) {
		$this->checkFSWasScanned($userId);

	}

	public function findObjectsByTypeInPeriod($calendarId, $type, $start, $end, $userId, $limit, $offset) {
		$this->checkFSWasScanned($userId);

	}

	public function createObject(Object $object, $userId) {
		$this->checkFSWasScanned($userId);

	}

	public function updateObject(Object $object, $calendarId, $uri, $userId) {
		$this->checkFSWasScanned($userId);

	}

	public function deleteObject(Object $object){
		$this->checkFSWasScanned($userId);

	}

	public function searchByProperties($properties=array(), $calendarId=null, $userId=null) {
		$this->checkFSWasScanned($userId);

	}

	private function checkFSWasScanned($userId) {
		if($scanned === false) {
			$this->scanFS($userId);
		}
	}

	private function scanFS($userId) {
		$files = \OCP\Files::searchByMime($this->calendarMIME);

		foreach($files as $file) {
			
		}

		$this->scanned = true;
		return true;
	}

	private function parseFile($path) {
		
	}
}