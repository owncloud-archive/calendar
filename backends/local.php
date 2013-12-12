<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * Copyright (c) 2012 Bart Visscher <bartv@thisnet.nl>
 * Copyright (c) 2011 Jakob Sack <mail@jakobsack.de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 *
 * database structur of database backend
 *
 * CREATE TABLE clndr_calendars (
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
 * CREATE TABLE clndr_objects (
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

use \OCA\Calendar\AppFramework\Core\API;
use \OCA\Calendar\AppFramework\Db\Mapper;
use \OCA\Calendar\AppFramework\Db\DoesNotExistException;
use \OCA\Calendar\AppFramework\Db\MultipleObjectsReturnedException;

use \OCA\Calendar\Db\Calendar;
use \OCA\Calendar\Db\Object;
use \OCA\Calendar\Db\ObjectType;

class Local extends Backend {
	
	private $calTableName;
	private $objTableName;

	public function __construct($api, $parameters){
		$this->calTableName = '*PREFIX*clndr_calendars';
		$this->objTableName = '*PREFIX*clndr_objects';
		parent::__construct($api, 'local');
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
		return false;
	}

	/**
	 * @brief returns whether or not a calendar should be cached
	 * @param string $calendarURI
	 * @param string $userId
	 * @returns boolean
	 * @throws DoesNotExistException if uri does not exist
	 * 
	 * This method returns a boolen. true if the calendar should be cached, false if the calendar shouldn't be cached
	 * This method is mandatory!
	 */
	public function cacheObjects($calendarURI, $userId) {
		$this->findCalendar($calendarURI, $userId);
		return false;
	}

	/**
	 * @brief returns information about calendar $calendarURI of the user $userId
	 * @param string $calendarURI
	 * @param string $userId
	 * @returns array with \OCA\Calendar\Db\Calendar object
	 * @throws DoesNotExistException if uri does not exist
	 * @throws MultipleObjectsReturnedException if multiple calendars exist
	 * 
	 * This method returns an array of \OCA\Calendar\Db\Calendar object.
	 * This method is mandatory!
	 */
	public function findCalendar($calendarURI, $userId) {
		$sql = 'SELECT * FROM `'. $this->calTableName . '` WHERE `uri` = ? AND `userid` = ?';
		$result = $this->api->prepareQuery($sql)->execute(array($calendarURI, $userId));
		$row = $result->fetchRow();

		if($row === false || $row === null){
			throw new DoesNotExistException('No matching entry found');
		}
		$row2 = $result->fetchRow();
		//MDB2 returns null, PDO and doctrine false when no row is available
		if( ! ($row2 === false || $row2 === null )) {
			throw new MultipleObjectsReturnedException('More than one result');
		} else {
			$calendar = new Calendar($row);
			$calendar->setBackend($this->backend);
		}
	}

	/**
	 * @brief returns all calendars of the user $userId
	 * @param string $userId
	 * @returns array with \OCA\Calendar\Db\Calendar objects
	 * 
	 * This method returns an array of \OCA\Calendar\Db\Object objects.
	 * This method is mandatory!
	 */
	public function findCalendars($userId) {
		$sql = 'SELECT * FROM `' . $this->calTableName . '` WHERE `userid` = ?';
		$result = $this->api->prepareQuery($sql)->execute(array($userId));

		$entities = array();
		while($row = $result->fetchRow()){
			$entity = new Calendar($row);
			array_push($entities, $entity);
		}
		return $entities;
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
		$sql  = 'SELECT `' . $this->objTableName . '`.* FROM `' . $this->objTableName . '`, `' . $this->calTableName . '` ';
		$sql .= 'WHERE `' . $this->objTableName . '`.`calendarid`=`' . $this->calTableName . '`.`id` ';
		$sql .= 'AND `' . $this->calTableName . '`.`uri` = ? AND `' . $this->calTableName . '`.`userid` = ? AND `' . $this->objTableName . '`.`uri`= ?';
		$result = $this->api->prepareQuery($sql)->execute(array($calendarURI, $userId, $objectURI));
		$row = $result->fetchRow();

		if($row === false || $row === null){
			throw new DoesNotExistException('No matching entry found');
		}
		$row2 = $result->fetchRow();
		//MDB2 returns null, PDO and doctrine false when no row is available
		if( ! ($row2 === false || $row2 === null )) {
			throw new MultipleObjectsReturnedException('More than one result');
		} else {
			$calendar = new Object($row);
			$calendar->setBackend($this->backend);
		}
		$entity = new Object($row);
		$entity->setBackend($this->backend);
		$entity->setType($row['objecttype']);
		$entity->setObjectURI($row['uri']);
		$entity->setCalendarURI($calendarURI);
		$entity->setCalendarData($row['calendardata']);

		return $entity;
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
	public function findObjects($calendarURI, $userId) {
		$sql  = 'SELECT `' . $this->objTableName . '`.* FROM `' . $this->objTableName . '`, `' . $this->calTableName . '` ';
		$sql .= 'WHERE `' . $this->objTableName . '`.`calendarid`=`' . $this->calTableName . '`.`id` ';
		$sql .= 'AND `' . $this->calTableName . '`.`uri` = ? AND `' . $this->calTableName . '`.`userid` = ?';
		$result = $this->api->prepareQuery($sql)->execute(array($calendarURI, $userId));

		$entities = array();
		while($row = $result->fetchRow()){
			$entity = new Object($row);
			$entity->setBackend($this->backend);
			$entity->setType($row['objecttype']);
			$entity->setObjectURI($row['uri']);
			$entity->setCalendarURI($calendarURI);
			$entity->setCalendarData($row['calendardata']);
			array_push($entities, $entity);
		}
		return $entities;
	}

	/**
	 * @brief creates a new calendar
	 * @param \OCA\Calendar\Db\Calendar $calendar
	 * @returns \OCA\Calendar\Db\Calendar $calendar - may differ from $calendar given as param!
	 * @throws BackendLayerException if uri already exists
	 * 
	 * This method returns an \OCA\Calendar\Db\Calendar object.
	 * The returned object may differ from the given one!
	 * This method is optional.
	 */
	public function createCalendar(Calendar $calendar) {
		$sql  = 'INSERT INTO `' . $this->calTableName . '` ';
		$sql .= '(`userid`, `displayname`, `uri`, `active`, `ctag`, `calendarorder`, `calendarcolor`, `timezone`, `components`) ';
		$sql .= 'VALUES(?,?,?,1,?,?,?,?,?)';
		$result = $this->api->prepareQuery($sql)->execute(array());
		$result = $stmt->execute( array(
			$calendar->getUserid(),
			$calendar->getDisplayName(),
			$calendar->getUri(),
			$calendar->getCtag(),
			$calendar->getOrder(),
			$calendar->getColor(),
			$calendar->getTimezone(),
			$calendar->getComponents(),
		));

		$insertid = OCP\DB::insertid('*PREFIX*clndr_calendars');
		OCP\Util::emitHook('OCA\Calendar\Backend\Local', 'addCalendar', $insertid);

		return $calendar;
	}
	
	/*
	 * @brief updates a calendar
	 * @param string $calendarURI - old uri
	 * @param \OCA\Calendar\Db\Calendar $calendar
	 * @returns \OCA\Calendar\Db\Calendar $calendar - may differ from $calendar given as param!
	 * 
	 * This method returns an \OCA\Calendar\Db\Calendar object.
	 * The returned object may differ from the given one!
	 * This method is optional.
	 */
	public function updateCalendar(Calendar $calendar) {

	}
	
	/*
	 * @brief deletes a calendar and all of it's objects
	 * @param \OCA\Calendar\Db\Calendar $calendar
	 * 
	 * The returned object may differ from the given one!
	 * This method is optional.
	 */
	public function deleteCalendar(Calendar $calendar) {
		$calendarURI = $calendar->getURI();
		$userId = $calendar->getUserId();
		//delete all objects
		$sqlObjs = 'DELETE * FROM `' . $this->objTableName . '` LEFT OUTER JOIN `' . $this->calTableName . '` ON `' . $this->objTableName . '.calendarid`=`' . $this->calTableName . '.id` WHERE `uri` = ? and `userid` = ?';
		$resultObjs = $this->api->prepareQuery($sqlObjs)->execute(array($calendarURI, $userId));
		//delete the calendar itself
		$sqlCal = 'DELETE * FROM `' . $this->calTableName . '` where `uri` = ? AND `userid` = ?';
		$resultCal = $this->api->prepareQuery($sqlCal)->execute(array($calendarURI, $userId));
	}
	
	/**
	* @brief check if a calendar exists
	* @param $calid calendarid
	* @returns boolean if a calendar exists or not
	*
	* Get all calendar informations the backend provides.
	*/
	public function mergeCalendar() {
		return false;
	}
	
	/**
	* @brief check if a calendar exists
	* @param $calid calendarid
	* @returns boolean if a calendar exists or not
	*
	* Get all calendar informations the backend provides.
	*/
	public function createObject(Object $object) {

	}
	
	/**
	* @brief check if a calendar exists
	* @param $calid calendarid
	* @returns boolean if a calendar exists or not
	*
	* Get all calendar informations the backend provides.
	*/
	public function updateObject() {
		return false;
	}
	
	/**
	* @brief check if a calendar exists
	* @param $calid calendarid
	* @returns boolean if a calendar exists or not
	*
	* Get all calendar informations the backend provides.
	*/
	public function deleteObject(Object $object){
		
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
	public function findObjectsInPeriod($calendarURI, $start, $end, $userId){
		$calendarid = self::getCalendarIdByURI($calendarURI);
		$sql =  'SELECT * FROM `*PREFIX*calendar_objects` WHERE `calendarid` = ?';
		$sql .= 'AND ((`startdate` >= ? AND `startdate` <= ?  AND `repeating` = 0)';
		$sql .= ' OR (`enddate` >= ? AND `enddate` <= ? AND `repeating` = 0)';
		$sql .= ' OR (`repeating` = 1))';
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

	public function findObjectsByType($calendarURI, $type, $userId) {
		$sql  = 'SELECT `' . $this->objTableName . '`.* FROM `' . $this->objTableName . '`, `' . $this->calTableName . '` ';
		$sql .= 'WHERE `' . $this->objTableName . '`.`calendarid`=`' . $this->calTableName . '`.`id` ';
		$sql .= 'AND `' . $this->calTableName . '`.`uri` = ? and `' . $this->calTableName . '`.`userid` = ? AND `' . $this->objTableName . '`.`objecttype` = ?';
		$result = $this->api->prepareQuery($sql)->execute(array($calendarURI, $userId, $type));

		$entities = array();
		while($row = $result->fetchRow()){
			$entity = new Object($row);
			$entity->setBackend($this->backend);
			$entity->setType($row['objecttype']);
			$entity->setObjectURI($row['uri']);
			$entity->setCalendarURI($calendarURI);
			$entity->setCalendarData($row['calendardata']);
			array_push($entities, $entity);
		}
		return $entities;
	}
	
	public function findObjectsByTypeInPeriod($calendarURI, $type, $start, $end, $userId) {
		
	}
	
	private function getUTCforMDB($datetime){
		return date('Y-m-d H:i:s', $datetime->format('U'));
	}
}