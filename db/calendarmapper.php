<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Db;

use \OCA\Calendar\AppFramework\Core\API;
use \OCA\Calendar\AppFramework\Db\Mapper;
use \OCA\Calendar\AppFramework\Db\DoesNotExistException;

use \OCA\Calendar\Db\Calendar;

class CalendarMapper extends Mapper {
	private $tableName;
	private $keyValueTableName;

	/**
	 * @param API $api: Instance of the API abstraction layer
	 */
	public function __construct($api, $tablename='clndr_calcache'){
		parent::__construct($api, $tablename);

		$this->tableName = '*PREFIX*' . $tablename;
	}

	/**
	 * Finds an item from user by it's uri
	 * @throws DoesNotExistException: if the item does not exist
	 * @return the item
	 */
	public function find($backend, $uri, $userId){
		$sql = 'SELECT * FROM `' . $this->tableName . '` WHERE `backend` = ? AND `uri` = ? AND `userid` = ?';
		$row = $this->findOneQuery($sql, array($backend, $uri, $userId));
		return new Calendar($row);
	}

	/**
	 * Finds an item from user by it's uri
	 * @throws DoesNotExistException: if the item does not exist
	 * @return the item
	 */
	public function countFind($backend, $uri, $userId){
		$sql = 'SELECT COUNT(*) AS `count` FROM `' . $this->tableName . '` WHERE `backend` = ? AND `uri` = ? AND `userid` = ?';
		$row = $this->findOneQuery($sql, array($backend, $uri, $userId));
		return $row['count'];
	}

	/**
	 * Finds all Items from user
	 * @return array containing all items
	 */
	public function findAll($userId, $limit, $offset){
		$sql = 'SELECT * FROM `'. $this->tableName . '` WHERE `userid` = ? ORDER BY `order`';
		return $this->findEntities($sql, array($userId), $limit, $offset);
	}

	/**
	 * inserts an item
	 * @param Calendar $calendar
	 * @param string $backend
	 * @param string $calendarURI
	 * @param string $userId
	 * @return null
	 */
	public function insertEntity($calendar, $backend, $calendarURI, $userId) {
		$sql  = 'INSERT INTO `'. $this->tableName . '` ';
		$sql .= '(`backend`, `uri`, `displayname`, `components`, `ctag`, `timezone`, ';
		$sql .= '`color`, `order`, `enabled`, `cruds`, `userid`, `ownerid`) ';
		$sql .= 'VALUES(?,?,?,?,?,?,?,?,?,?,?,?)';
		$result = $this->api->prepareQuery($sql)->execute(array(
			$calendar->getBackend(),
			$calendar->getUri(),
			$calendar->getDisplayname(),
			$calendar->getComponents(),
			$calendar->getCtag(),
			$calendar->getTimezone(),
			$calendar->getColor(),
			$calendar->getOrder(),
			$calendar->getEnabled(),
			$calendar->getCruds(),
			$calendar->getUserId(),
			$calendar->getOwnerId(),
		));
	}

	/**
	 * updates an item
	 * @param Calendar $calendar
	 * @param string $backend
	 * @param string $calendarURI
	 * @param string $userId
	 * @return null
	 */
	public function updateEntity($calendar, $backend, $calendarURI, $userId) {
		$sql  = 'UPDATE `'. $this->tableName . '` SET ';
		$sql .= '`backend` = ?, `uri` = ?, `displayname` = ?, `components` = ?, `ctag` = ?, `timezone` = ?, ';
		$sql .= '`color` = ?, `order` = ?, `enabled` = ?, `cruds` = ?, `userid` = ?, `ownerid` = ? ';
		$sql .= 'WHERE `backend` = ? AND `uri` = ? AND `userid` = ?';
		$result = $this->api->prepareQuery($sql)->execute(array(
			$calendar->getBackend(),
			$calendar->getUri(),
			$calendar->getDisplayname(),
			$calendar->getComponents(),
			$calendar->getCtag(),
			$calendar->getTimezone(),
			$calendar->getColor(),
			$calendar->getOrder(),
			$calendar->getEnabled(),
			$calendar->getCruds(),
			$calendar->getUserId(),
			$calendar->getOwnerId(),
			$backend,
			$calendarURI,
			$userId,
		));
	}

	/**
	 * deletes an item
	 * @param string $backend
	 * @param string $calendarURI
	 * @param string $userId
	 * @return null
	 */
	public function deleteEntity($backend, $calendarURI, $userId) {
		$sql = 'DELETE FROM `'. $this->tableName . '` WHERE `backend` = ? AND `uri` = ? AND `userid` = ?';
		$this->execute($sql, array($backend, $calendarURI, $userId));
	}
}