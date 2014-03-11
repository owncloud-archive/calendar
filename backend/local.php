<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * Copyright (c) 2012 Bart Visscher <bartv@thisnet.nl>
 * Copyright (c) 2011 Jakob Sack <mail@jakobsack.de>
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

	public function cacheObjects($calendarURI, $userId) {
		return false;
	}

	public function getDisplayName() {
		return $this->api->getTrans()->t('Database-Backend');
	}

	public function getDescription() {
		return $this->api->getTrans()->t('The database-backend stores all calendars and events, journals and todos in the database you used to setup ownCloud.');
	}

	public function findCalendar($calendarURI, $userId) {
		$sql = 'SELECT * FROM `'. $this->calTableName . '` WHERE `uri` = ? AND `userid` = ?';
		$result = $this->api->prepareQuery($sql)->execute(array($calendarURI, $userId));
		$row = $result->fetchRow();

		if($row === false || $row === null){
			throw new DoesNotExistException('No matching entry found');
		}
		$row2 = $result->fetchRow();
		//MDB2 returns null, PDO and doctrine false when no row is available
		if( ! ($row2 === false || $row2 === null ) ) {
			throw new MultipleObjectsReturnedException('More than one result');
		}

		$calendar = new Calendar($row);
		$this->completeCalendarEntity($calendar, $row);

		return $calendar;
	}

	public function findCalendars($userId, $limit, $offset) {
		$sql = 'SELECT * FROM `' . $this->calTableName . '` WHERE `userid` = ?';
		$result = $this->api->prepareQuery($sql)->execute(array($userId));

		$entities = array();
		while($row = $result->fetchRow()){
			$entity = new Calendar($row);
			$this->completeCalendarEntity($entity, $row);
			array_push($entities, $entity);
		}

		return $entities;
	}

	public function createCalendar(Calendar $calendar) {
		$sql  = 'INSERT INTO `' . $this->calTableName . '` ';
		$sql .= '(`userid`, `displayname`, `uri`, `active`, `ctag`, `calendarorder`, `calendarcolor`, `timezone`, `components`) ';
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

	public function updateCalendar(Calendar $calendar, $calendarId, $userId) {
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
			$calendarId,
		));

		return $calendar;
	}

	public function deleteCalendar($calendarURI, $userId) {
		//$sqlObjects  = 'DELETE `' . $this->objTableName . '` FROM `' . $this->objTableName . '` ';
		//$sqlObjects .= 'JOIN `' . $this->calTableName . '` ON ';
		//$sqlObjects .= '`' . $this->objTableName . '.calendarid` = `' . $this->calTableName . '.id` ';
		//$sqlObjects .= 'WHERE `' . $this->calTableName . '.uri` = ? AND `' . $this->calTableName . '.userid` = ?';
		//$resultObjs = $this->api->prepareQuery($sqlObjects)->execute(array($calendarURI, $userId)); 

		$sqlCalendar = 'DELETE FROM `' . $this->calTableName . '` where `uri` = ? AND `userid` = ?';
		$resultCal = $this->api->prepareQuery($sqlCalendar)->execute(array($calendarURI, $userId));

		return true;
	}
	
	public function mergeCalendar(Calendar $calendar, $calendarId=null, $userId=null) {
		$newCalendarId = $calendar->getCalendarId();
		$newUserId = $calendar->getUserId();

		if($calendarId === null || $userId === null || $newCalendarId === null || $newUserId === null) {
			throw new BackendException('Can\'t delete Calendar. Calendar object does not contain sufficient information');
		}

		$newCalendarDBId = $this->getCalendarDBId($newCalendarId, $newUserId);
		$oldCalendarDBId = $this->getCalendarDBId($calendarId, $userId);

		$sqlObjects  = 'UPDATE `' . $this->objTableName . '` SET `calendarid` = `?` WHERE `calendarid` = `?`';
		$rsltObjects = $this->api->prepareQuery($sqlObjects)->execute(array($newCalendarDBId, $oldCalendarDBId));

		$sqlCalendar  = 'DELETE * FROM `' . $this->calTableName . '` where `uri` = ? AND `userid` = ?';
		$rsltCalendar = $this->api->prepareQuery($sqlCalendar)->execute(array($calendarId, $userId));

		return true;
	}

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
		}

		$entity = new Object($row);
		$this->completeObjectEntity($entity, $row);

		return $entity;
	}

	public function findObjects($calendarId, $userId, $limit, $offset) {
		$sql  = 'SELECT `' . $this->objTableName . '`.* FROM `' . $this->objTableName . '`, `' . $this->calTableName . '` ';
		$sql .= 'WHERE `' . $this->objTableName . '`.`calendarid`=`' . $this->calTableName . '`.`id` ';
		$sql .= 'AND `' . $this->calTableName . '`.`uri` = ? AND `' . $this->calTableName . '`.`userid` = ?';
		$result = $this->api->prepareQuery($sql)->execute(array($calendarId, $userId));

		$entities = array();
		while($row = $result->fetchRow()){
			$entity = new Object($row);
			$this->completeObjectEntity($entity, $row);
			array_push($entities, $entity);
		}

		return $entities;
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

		$entities = array();
		while($row = $result->fetchRow()){
			$entity = new Object($row);
			$this->completeObjectEntity($entity, $row);
			array_push($entities, $entity);
		}

		return $entities;
	}

	public function findObjectsByType($calendarId, $type, $userId, $limit, $offset) {
		$sql  = 'SELECT `' . $this->objTableName . '`.* FROM `' . $this->objTableName . '`, `' . $this->calTableName . '` ';
		$sql .= 'WHERE `' . $this->objTableName . '`.`calendarid`=`' . $this->calTableName . '`.`id` ';
		$sql .= 'AND `' . $this->calTableName . '`.`uri` = ? and `' . $this->calTableName . '`.`userid` = ? AND `' . $this->objTableName . '`.`objecttype` = ?';
		$result = $this->api->prepareQuery($sql)->execute(array($calendarId, $userId, $type));

		$entities = array();
		while($row = $result->fetchRow()){
			$entity = new Object($row);
			$this->completeObjectEntity($entity, $row);
			array_push($entities, $entity);
		}
		return $entities;
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

		$entities = array();
		while($row = $result->fetchRow()){
			$entity = new Object($row);
			$this->completeObjectEntity($entity, $row);
			array_push($entities, $entity);
		}

		return $entities;
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

		$entities = array();
		while($row = $result->fetchRow()){
			$entity = new Object($row);
			$this->completeObjectEntity($entity, $row);
			array_push($entities, $entity);
		}

		return $entities;
	}

	private function getUTCforMDB($datetime=null){
		if($datetime === null) {
			return null;
		}

		return date('Y-m-d H:i:s', $datetime->format('U'));
	}

	private function completeCalendarEntity(&$entity, $row=null) {
		if($row === null) {
			return;
		}

		$entity->setBackend($this->backend);
		$entity->setCruds(Permissions::ALL);
		if($row['calendarcolor'] === null) {
			$entity->setColor('#1d2d44');
		}
		if($row['enabled'] === null) {
			$entity->setEnabled(true);
		}
		$entity->setComponents($row['components']);
		$entity->setUserId($row['userid']);
		$entity->setOwnerId($row['userid']);
	}

	private function completeObjectEntity(&$entity, $row=null) {
		if($row === null) {
			return;
		}

		$entity->setBackend($this->backend);
		$entity->setType($row['objecttype']);
		$entity->setObjectURI($row['uri']);
		$entity->setCalendarURI('test');
		$entity->setCalendarData($row['calendardata']);
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

	private function countCalendars($userId=null) {
		if($userId === null) {
			return 0;
		}

		$sql  = 'SELECT COUNT(*) FROM `' . $this->objTableName . '`, `' . $this->calTableName . '` ';
		$sql .= 'WHERE `' . $this->objTableName . '`.`calendarid`=`' . $this->calTableName . '`.`id` ';
		$sql .= 'AND `' . $this->calTableName . '`.`uri` = `?` AND `' . $this->calTableName . '`.`userid` = `?`';

		$result = $this->api->prepareQuery($sql)->execute(array($calendarURI, $userId));
		$count	= $result->fetchOne();

		return $count;
	}

	private function countObjects($calendarId=null, $userId=null) {
		if($calendarId === null || $userId === null) {
			return 0;
		}

		$sql = 'SELECT COUNT(*) FROM `' . $this->calTableName . '`';
		if($userId !== null) {
			$sql .= ' WHERE `userid` = `?`';
		}

		$result	= $this->api->prepareQuery($sql)->execute(array($userId));
		$count	= $result->fetchOne();

		return $count;
	}

	private function doesCalendarExist($calendarId=null, $userId=null) {
		if($calendarId === null || $userId === null) {
			return false;
		}

		$sql  = 'SELECT COUNT(*) FROM `' . $this->calTableName . '`';
		$sql .= ' WHERE `uri` = `?` AND `userid` = `?`';

		$result	= $this->api->prepareQuery($sql)->execute(array($calendarId, $userId));
		$count	= $result->fetchOne();

		if($count === 0) {
			return false;
		} else {
			return true;
		}
	}

	private function doesObjectExist($objectURI=null, $calendarId=null, $userId=null) {
		if($objectURI === null || $calendarId === null || $userId === null) {
			return false;
		}

		$sql  = 'SELECT COUNT(*) FROM `' . $this->objTableName . '`, `' . $this->calTableName . '` ';
		$sql .= 'WHERE `' . $this->objTableName . '`.`calendarid`=`' . $this->calTableName . '`.`id` ';
		$sql .= 'AND `' . $this->calTableName . '`.`uri` = `?` AND `' . $this->calTableName . '`.`userid` = `?` AND `' . $this->objTableName . '`.`uri`= `?`';

		$result = $this->api->prepareQuery($sql)->execute(array($calendarURI, $userId, $objectURI));
		$count	= $result->fetchOne();

		if($count === 0) {
			return false;
		} else {
			return true;
		}
	}
}