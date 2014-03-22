<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * Copyright (c) 2014 Bart Visscher <bartv@thisnet.nl>
 * Copyright (c) 2014 Jakob Sack <mail@jakobsack.de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
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

class Local extends Backend {
	
	private $calTableName;
	private $objTableName;

	private $typeMapper = array(
		ObjectType::EVENT	=> 'VEVENT',
		ObjectType::JOURNAL => 'VJOURNAL',
		ObjectType::TODO	=> 'VTODO',
	);

	private $reverseTypeMapper = array(
		'VEVENT'	=> ObjectType::EVENT,
		'VJOURNAL'	=> ObjectType::JOURNAL,
		'VTODO'		=> ObjectType::TODO,
	);

	public function __construct($api, $parameters){
		$this->calTableName = '*PREFIX*clndr_calendars';
		$this->objTableName = '*PREFIX*clndr_objects';
		parent::__construct($api, 'local');
	}

	/**
	 * Shall calendars be cached?
	 * @param string $calendarURI
	 * @param string $userId
	 * @return boolean
	 */
	public function cacheObjects($calendarURI, $userId) {
		return false;
	}

	/**
	 * Find a calendar
	 * @param string $calendarURI
	 * @param string $userId
	 * @throws DoesNotExistException if calendar does not exist
	 * @throws MultipleObjectsReturnedException if more than one result found
	 * @return Calendar
	 */
	public function findCalendar($calendarURI, $userId) {
		$sql = 'SELECT * FROM `'. $this->calTableName . '` WHERE `uri` = ? AND `userid` = ?';
		$result = $this->api->prepareQuery($sql)->execute(array($calendarURI, $userId));
		$row = $result->fetchRow();

		if($row === false || $row === null){
			$msg  = 'Backend\Local::findCalendar(): Internal Error: ';
			$msg .= 'No matching entry found';
			throw new CacheOutDatedException($msg);
		}

		$row2 = $result->fetchRow();
		if(($row2 === false || $row2 === null ) === false) {
			$msg  = 'Backend\Local::findCalendar(): Internal Error: ';
			$msg .= 'More than one result';
			throw new MultipleObjectsReturnedException($msg);
		}

		return $this->createCalendarFromRow($row);
	}

	/**
	 * Find all calendars
	 * @param string $userId
	 * @throws DoesNotExistException if calendar does not exist
	 * @throws MultipleObjectsReturnedException if more than one result found
	 * @return Calendar
	 */
	public function findCalendars($userId, $limit, $offset) {
		$sql = 'SELECT * FROM `' . $this->calTableName . '` WHERE `userid` = ?';
		$result = $this->api->prepareQuery($sql)->execute(array($userId));

		$calendarCollection = new CalendarCollection();
		while($row = $result->fetchRow()){
			try{
				$calendar = $this->createCalendarFromRow($row);
			} catch(CorruptCalendarException $ex) {
				//log error message
				//if this happened, there is an corrupt entry
				continue;
			}

			$calendarCollection->add($calendar);
		}

		return $calendarCollection;
	}

	/**
	 * counts number of calendars
	 * @param string $userId
	 * @return integer
	 */
	public function countCalendars($userId) {
		$sql  = 'SELECT COUNT(*) FROM `' . $this->calTableName . '`';
		$sql .= ' WHERE `userid` = `?`';

		$result	= $this->api->prepareQuery($sql)->execute(array(
			$userId
		));
		$count = $result->fetchOne();

		if(gettype($count) !== 'integer') {
			$count = intval($count);
		}

		return $count;
	}

	/**
	 * checks if a calendar exists
	 * @param string $uri
	 * @param string $userId
	 * @return boolean
	 */
	public function doesCalendarExist($uri, $userId) {
		$sql  = 'SELECT COUNT(*) FROM `' . $this->calTableName . '`';
		$sql .= ' WHERE `uri` = `?` AND `userid` = `?`';

		$result	= $this->api->prepareQuery($sql)->execute(array(
			$uri,
			$userId
		));
		$count = $result->fetchOne();

		if(gettype($count) !== 'integer') {
			$count = intval($count);
		}

		if($count === 0) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * get ctag of a calendar
	 * @param string $uri
	 * @param string $userId
	 * @return integer
	 */
	public function getCalendarsCTag($uri, $userId) {
		$sql  = 'SELECT `ctag` FROM `' . $this->calTableName . '`';
		$sql .= ' WHERE `uri` = `?` AND `userid` = `?`';

		$result	= $this->api->prepareQuery($sql)->execute(array(
			$uri,
			$userId
		));
		$ctag = $result->fetchOne();

		if(gettype($ctag) !== 'integer') {
			$ctag = intval($ctag);
		}

		return $ctag;
	}

	/**
	 * Create a calendar
	 * @param Calendar $calendar
	 * @throws BackendException if calendar already exists
	 * @return Calendar
	 */
	public function createCalendar(Calendar &$calendar) {
		$uri = $calendar->getUri();
		$userId = $calendar->getUserId();

		if($this->doesCalendarExist($uri, $userId) === true) {
			$msg  = 'Backend\Local::createCalendar(): Internal Error: ';
			$msg .= 'Calendar with uri and userid combination already exists!';
			throw new CacheOutDatedException($msg);
		}

		$sql  = 'INSERT INTO `' . $this->calTableName . '` ';
		$sql .= '(`userid`, `displayname`, `uri`, `active`, `ctag`, `calendarorder`, ';
		$sql .= '`calendarcolor`, `timezone`, `components`) ';
		$sql .= 'VALUES(?,?,?,?,?,?,?,?,?)';
		$result = $this->api->prepareQuery($sql)->execute(array(
			$calendar->getUserId(),
			$calendar->getDisplayname(),
			$calendar->getUri(),
			$calendar->getEnabled(),
			$calendar->getCtag(),
			$calendar->getOrder(),
			$calendar->getColor(),
			$calendar->getTimezone(),
			$calendar->getComponents(),
		));

		return $calendar;
	}

	/**
	 * update a calendar
	 * @param Calendar $calendar
	 * @param string $uri
	 * @param string $userId
	 * @throws BackendException if calendar does not exist
	 * @return Calendar
	 */
	public function updateCalendar(Calendar &$calendar, $uri, $userId) {
		if($this->doesCalendarExist($uri, $userId) === false) {
			$msg  = 'Backend\Local::updateCalendar(): Internal Error: ';
			$msg .= 'Calendar with uri and userid combination not found!';
			throw new CacheOutDatedException($msg);
		}

		$sql  = 'UPDATE `' . $this->calTableName . '` SET ';
		$sql .= '`userid` = ?, `displayname` = ?, `uri` = ?, `active` = ?, `ctag` = ?, ';
		$sql .= '`calendarorder` = ?, `calendarcolor` = ?, `timezone` = ?, `components` = ? ';
		$sql .= 'WHERE `userid` = ? AND `uri` = ?';
		$result = $this->api->prepareQuery($sql)->execute(array(
			$calendar->getUserId(),
			$calendar->getDisplayname(),
			$calendar->getUri(),
			$calendar->getEnabled(),
			$calendar->getCtag(),
			$calendar->getOrder(),
			$calendar->getColor(),
			$calendar->getTimezone(),
			$calendar->getComponents(),
			$userId,
			$uri,
		));

		return $calendar;
	}

	/**
	 * delete a calendar
	 * @param string $uri
	 * @param string $userId
	 * @return boolean
	 */
	public function deleteCalendar($uri, $userId) {
		$sql  = 'DELETE FROM `' . $this->calTableName . '` ';
		$sql .= '`uri` = ? AND `userid` = ?';
		$result = $this->api->prepareQuery($sqlCalendar)->execute(array(
			$uri,
			$userId
		));

		return $result;
	}

	/**
	 * merge two calendar
	 * @param string $uri
	 * @param string $userId
	 * @return boolean
	 */
	public function mergeCalendar(Calendar $calendar, $oldURI, $oldUserId) {
		$newUri = $calendar->getUri();
		$newUserId = $calendar->getUserId();

		//TODO - implement

		/*$newCalendarDBId = $this->getCalendarDBId($newCalendarId, $newUserId);
		$oldCalendarDBId = $this->getCalendarDBId($calendarId, $userId);

		$sqlObjects  = 'UPDATE `' . $this->objTableName . '` SET `calendarid` = `?` WHERE `calendarid` = `?`';
		$rsltObjects = $this->api->prepareQuery($sqlObjects)->execute(array($newCalendarDBId, $oldCalendarDBId));

		$sqlCalendar  = 'DELETE * FROM `' . $this->calTableName . '` where `uri` = ? AND `userid` = ?';
		$rsltCalendar = $this->api->prepareQuery($sqlCalendar)->execute(array($calendarId, $userId));

		return true;*/
	}

	/**
	 * find object
	 * @param string $calendarURI
	 * @param string $objectURI
	 * @param string $userId
	 * @return boolean
	 */
	public function findObject($calendarURI, $objectURI, $userId) {
		$sql  = 'SELECT `' . $this->objTableName . '`.* FROM `' . $this->objTableName . '`, `' . $this->calTableName . '` ';
		$sql .= 'WHERE `' . $this->objTableName . '`.`calendarid`=`' . $this->calTableName . '`.`id` ';
		$sql .= 'AND `' . $this->calTableName . '`.`uri` = ? AND `' . $this->calTableName . '`.`userid` = ? AND `' . $this->objTableName . '`.`uri`= ?';
		$result = $this->api->prepareQuery($sql)->execute(array($calendarURI, $userId, $objectURI));
		$row = $result->fetchRow();

		if($row === false || $row === null){
			$msg  = 'Backend\Local::findObject(): Internal Error: ';
			$msg .= 'No matching entry found';
			throw new CacheOutDatedException($msg);
		}

		$row2 = $result->fetchRow();
		if(($row2 === false || $row2 === null ) === false) {
			$msg  = 'Backend\Local::findObject(): Internal Error: ';
			$msg .= 'More than one result';
			throw new MultipleObjectsReturnedException($msg);
		}

		return $this->createObjectFromRow();
	}

	/**
	 * find objects
	 * @param string $calendarURI
	 * @param string $objectURI
	 * @param string $userId
	 * @return boolean
	 */
	public function findObjects($calendarId, $userId, $limit, $offset) {
		$sql  = 'SELECT `' . $this->objTableName . '`.* FROM `' . $this->objTableName . '`, `' . $this->calTableName . '` ';
		$sql .= 'WHERE `' . $this->objTableName . '`.`calendarid`=`' . $this->calTableName . '`.`id` ';
		$sql .= 'AND `' . $this->calTableName . '`.`uri` = ? AND `' . $this->calTableName . '`.`userid` = ?';
		$result = $this->api->prepareQuery($sql)->execute(array($calendarId, $userId));

		$objectCollection = new ObjectCollection();
		while($row = $result->fetchRow()){
			try{
				$object = $this->createObjectFromRow($row);
			} catch(CorruptObjectException $ex) {
				//log error message
				//if this happened, there is an corrupt entry
				continue;
			}

			$objectCollection->add($calendar);
		}

		return $objectCollection;
	}

	public function countObjects($calendarURI, $userId) {
		$sql  = 'SELECT COUNT(*) FROM `' . $this->objTableName . '`, `' . $this->calTableName . '` ';
		$sql .= 'WHERE `' . $this->objTableName . '`.`calendarid`=`' . $this->calTableName . '`.`id` ';
		$sql .= 'AND `' . $this->calTableName . '`.`uri` = ? AND `' . $this->calTableName . '`.`userid` = ?';

		//TODO validate if sql query is correct

		$result	= $this->api->prepareQuery($sql)->execute(array(
			$calendarURI,
			$userId
		));
		$count = $result->fetchOne();

		if(gettype($count) !== 'integer') {
			$count = intval($count);
		}

		return $count;
	}

	public function doesObjectExist($calendarURI, $objectURI, $userId) {
		$sql  = 'SELECT COUNT(*) FROM `' . $this->objTableName . '`, `' . $this->calTableName . '` ';
		$sql .= 'WHERE `' . $this->objTableName . '`.`calendarid`=`' . $this->calTableName . '`.`id` ';
		$sql .= 'AND `' . $this->calTableName . '`.`uri` = ? AND `' . $this->calTableName . '`.`userid` = ?';

		//TODO validate if sql query is correct

		$result	= $this->api->prepareQuery($sql)->execute(array(
			$uri,
			$userId
		));
		$count = $result->fetchOne();

		if(gettype($count) !== 'integer') {
			$count = intval($count);
		}

		if($count === 0) {
			return false;
		} else {
			return true;
		}
	}

	public function getObjectsETag($calendarURI, $objectURI, $userId) {
		$object = $this->findObject($calendarURI, $objectURI, $userId);
		return $object->getEtag();
	}

	public function findObjectsInPeriod($calendarId, $start, $end, $userId, $limit, $offset){
		$sql  = 'SELECT `' . $this->objTableName . '`.* FROM `' . $this->objTableName . '`, `' . $this->calTableName . '` ';
		$sql .= 'WHERE `' . $this->objTableName . '`.`calendarid`=`' . $this->calTableName . '`.`id`';
		$sql .= ' AND `' . $this->calTableName . '`.`uri` = ? AND `' . $this->calTableName . '`.`userid` = ?';
        $sql .= ' AND ((`' . $this->objTableName . '.startdate` >= ? AND `' . $this->objTableName . '.enddate` <= ? AND `' . $this->objTableName . '.repeating` = 0)';
        $sql .= ' OR (`' . $this->objTableName . '.enddate` >= ? AND `' . $this->objTableName . '.startdate` <= ? AND `' . $this->objTableName . '.repeating` = 0)';
        $sql .= ' OR (`' . $this->objTableName . '.startdate` <= ? AND `' . $this->objTableName . '.repeating` = 1))';

		$start	= $this->getUTCforMDB($start);
		$end	= $this->getUTCforMDB($end);
		$result	= $stmt->execute(array(
					$calendarid, $userId,
					$start, $end,
					$start, $end,
					$end));

		$objectCollection = array();
		while($row = $result->fetchRow()){
			$entity = new Object($row);
			$this->completeObjectEntity($entity, $row);
			$objectCollection->add($entity);
		}

		return $objectCollection;
	}

	public function findObjectsByType($calendarId, $type, $userId, $limit, $offset) {
		$sql  = 'SELECT `' . $this->objTableName . '`.* FROM `' . $this->objTableName . '`, `' . $this->calTableName . '` ';
		$sql .= 'WHERE `' . $this->objTableName . '`.`calendarid`=`' . $this->calTableName . '`.`id` ';
		$sql .= 'AND `' . $this->calTableName . '`.`uri` = ? and `' . $this->calTableName . '`.`userid` = ? AND `' . $this->objTableName . '`.`objecttype` = ?';
		$result = $this->api->prepareQuery($sql)->execute(array($calendarId, $userId, $type));

		$objectCollection = array();
		while($row = $result->fetchRow()){
			$entity = new Object($row);
			$this->completeObjectEntity($entity, $row);
			$objectCollection->add($entity);
		}

		return $objectCollection;
	}

	public function findObjectsByTypeInPeriod($calendarId, $type, $start, $end, $userId, $limit, $offset) {
		$sql  = 'SELECT `' . $this->objTableName . '`.* FROM `' . $this->objTableName . '`, `' . $this->calTableName . '` ';
		$sql .= 'WHERE `' . $this->objTableName . '`.`calendarid`=`' . $this->calTableName . '`.`id`';
		$sql .= ' AND `' . $this->calTableName . '`.`uri` = ? AND `' . $this->calTableName . '`.`userid` = ?';
        $sql .= ' AND ((`' . $this->objTableName . '.startdate` >= ? AND `' . $this->objTableName . '.enddate` <= ? AND `' . $this->objTableName . '.repeating` = 0)';
        $sql .= ' OR (`' . $this->objTableName . '.enddate` >= ? AND `' . $this->objTableName . '.startdate` <= ? AND `' . $this->objTableName . '.repeating` = 0)';
        $sql .= ' OR (`' . $this->objTableName . '.startdate` <= ? AND `' . $this->objTableName . '.repeating` = 1))';
        $sql .= ' AND `' . $this->objTableName . '.objecttype` = `?`';

		$start	= $this->getUTCforMDB($start);
		$end	= $this->getUTCforMDB($end);
		$result	= $stmt->execute(array(
					$calendarid, $userId,
					$start, $end,
					$start, $end,
					$end,
					$type));

		$objectCollection = array();
		while($row = $result->fetchRow()){
			$entity = new Object($row);
			$this->completeObjectEntity($entity, $row);
			$objectCollection->add($entity);
		}

		return $objectCollection;
	}

	public function createObject(Object $object, $userId) {
		$calendarId		= $object->getCalendarid();
		$userId			= $object->getUserId();
		$calendarDBId	= $this->getCalendarDBId($calendarId, $userId);

		$sql  = 'INSERT INTO `' . $this->objTableName . '` ';
		$sql .= '(`calendarid`,`objecttype`,`startdate`,`enddate`,`repeating`,`summary`,`calendardata`,`uri`,`lastmodified`) ';
		$sql .= 'VALUES(?,?,?,?,?,?,?,?,?)';
		$result = $this->api->prepareQuery($sql)->execute(array(
			$calendarDBId,
			$object->getType(),
			$object->getStartDate(),
			$object->getEndDate(),
			$object->getRepeating(),
			$object->getSummary(),
			$object->getCalendarData(),
			$object->getObjectURI(),
			$object->gerLastModified(),
		));

		return $object;
	}

	public function updateObject(Object $object, $calendarId, $uri, $userId) {
		$calendarId		= $object->getCalendarid();
		$userId			= $object->getUserId();
		$calendarDBId	= $this->getCalendarDBId($calendarId, $userId);

		$sql  = 'INSERT INTO `' . $this->objTableName . '` ';
		$sql .= '(`calendarid`,`objecttype`,`startdate`,`enddate`,`repeating`,`summary`,`calendardata`,`uri`,`lastmodified`) ';
		$sql .= 'VALUES(?,?,?,?,?,?,?,?,?)';
		$result = $this->api->prepareQuery($sql)->execute(array(
			$calendarDBId,
			$object->getType(),
			$object->getStartDate(),
			$object->getEndDate(),
			$object->getRepeating(),
			$object->getSummary(),
			$object->getCalendarData(),
			$object->getObjectURI(),
			$object->gerLastModified(),
		));

		return $object;
	}

	public function deleteObject(Object $object){
		$userId		= $object->getUserId();
		$calendarId	= $object->getCalendarId();
		$objectURI	= $object->getObjectURI();

		$sql  = 'DELETE * FROM `' . $this->objTableName . '`';
		$sql .= 'LEFT OUTER JOIN `' . $this->calTableName . '` ON ';
		$sql .= '`' . $this->objTableName . '.calendarid`=`' . $this->calTableName . '.id`';
		$sql .= 'WHERE `' . $this->calTableName . '.uri` = ? AND `' . $this->calTableName . '.userid` = ?';
		$sql .= ' AND `' . $this->objTableName . '.uri` = `?`';
		$result = $this->api->prepareQuery($sql)->execute(array(
			$calendarId, $userId,
			$objectURI));

		return true;
	}

	public function searchByProperties($properties=array(), $calendarId=null, $userId=null) {
		if($calendarId === null || $userId === null) {
			return array();
		}

		if(empty($properties)) {
			return $this->findObjects($calendarId, $userId);
		}

		$sql  = 'SELECT `' . $this->objTableName . '`.* FROM `' . $this->objTableName . '`, `' . $this->calTableName . '` ';
		$sql .= 'WHERE `' . $this->objTableName . '`.`calendarid`=`' . $this->calTableName . '`.`id` ';
		$sql .= 'AND `' . $this->calTableName . '`.`uri` = ? AND `' . $this->calTableName . '`.`userid` = ?';

		$parameters = array($calendarId, $userId);
		$sqlWhereClauses = array();

		foreach($properties as $key => $value) {
			$key	= strtoupper($key);
			$value	= strtolower($value);

			$sqlWhereClauses[] = 'WHERE UPPER(`' . $this->objTableName . '.calendardata`) LIKE `%?%`';
			$sqlWhereClauses[] = 'WHERE LOWER(`' . $this->objTableName . '.calendardata`) LIKE `%?%`';

			$parameters[] = $key;
			$parameters[] = $value;
			
		}

		$sql .= 'AND (';
		$sql .= implode(' AND ', $sqlWhereClauses);
		$sql .= ')';

		$result = $this->api->prepareQuery($sql)->execute($parameters);

		$objectCollection = array();
		while($row = $result->fetchRow()){
			$entity = new Object($row);
			$this->completeObjectEntity($entity, $row);
			$objectCollection->add($entity);
		}

		return $objectCollection;
	}

	private function getUTCforMDB($datetime){
		return date('Y-m-d H:i:s', $datetime->format('U'));
	}

	private function getCalendarDBId($calendarId=null, $userId=null) {
		if($calendarId === null || $userId === null) {
			return null;
		}

		$sql	= 'SELECT id from `' . $this->calTableName . '` WHERE `uri` = `?` AND `userid` = `?`';
		$result	= $this->api->prepareQuery($sql)->execute(array($calendarURI, $userId));

		$calendarId	= $result->fetchOne();
		return $calendarId;
	}

	private function createCalendarFromRow(&$row) {
		$calendar = new Calendar($row);
		$calendar->setBackend($this->backend);
		$calendar->setCruds(Permissions::ALL);
		if($row['calendarcolor'] === null) {
			$calendar->setColor('#1d2d44');
		}
		if($row['enabled'] === null) {
			$calendar->setEnabled(true);
		}
		if($row['timezone'] !== null) {
			
		}
		$calendar->setComponents($row['components']);
		$calendar->setUserId($row['userid']);
		$calendar->setOwnerId($row['userid']);

		if($calendar->isValid() !== true) {
			//try to fix the calendar
			$calendar->fix();

			//check again
			if($calendar->isValid() !== true) {
				$msg  = 'Backend\Local::createCalendarFromRow(): Internal Error: ';
				$msg .= 'Received calendar data is not valid and not fixable! ';
				$msg .= '(user:"' . $calendar->getUser() . '"; ';
				$msg .= 'calendar:"' . $calendar->getUri() . '")';
				throw new CorruptCalendarException($msg);
			}
		}

	}

	private function createObjectFromRow(&$row) {
		$object = new Object($row);
		$object->setBackend($this->backend);
		$object->setType($row['objecttype']);
		$object->setObjectURI($row['uri']);
		$object->setCalendarURI('test');
		$object->setCalendarData($row['calendardata']);
		$object->generateETag();

		if($object->isValid() !== true) {
			//try to fix the calendar
			$object->fix();

			//check again
			if($object->isValid() !== true) {
				$msg  = 'Backend\Local::createObjectFromRow(): Internal Error: ';
				$msg .= 'Received object data is not valid and not fixable! ';
				$msg .= '(user:"' . $object->getUser() . '"; ';
				$msg .= 'calendar:"' . $object->getCalendarUri() . '"; ';
				$msg .= 'object:"' . $object->getObjectUri() . '";)';
				throw new CorruptObjectException($msg);
			}
		}

		return $object;
	}
}