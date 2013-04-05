<?php
/**
 * Copyright (c) 2013 Georg Ehrke <developer at georgehrke dot com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Mapper;

use \OCA\AppFramework\Core\API;
use \OCA\AppFramework\Db\Mapper;
use \OCA\AppFramework\Db\DoesNotExistException;

use \OCA\Calendar\Backend\Item;


class Object extends Mapper {


	private $tableName;

	/**
	 * @param API $api: Instance of the API abstraction layer
	 */
	public function __construct($api){
		parent::__construct($api);
		$this->tableName = '*PREFIX*calendar_backends';
	}


	/**
	 * Finds an item by id
	 * @throws DoesNotExistException: if the item does not exist
	 * @return the item
	 */
	public function find($id){
		$row = $this->findQuery($this->tableName, $id);
		return new Item($row);
	}


	/**
	 * Finds all Items
	 * @return array containing all items
	 */
	public function findAll(){
		$result = $this->findAllQuery($this->tableName);

		$entityList = array();
		while($row = $result->fetchRow()){
			$entity = new Item($row);
			array_push($entityList, $entity);
		}

		return $entityList;
	}


	/**
	 * Finds all Items where enabled is ?
	 * @return array containing all items where enabled is ?
	 */
	public function findWhereEnabledIs($isenabled){
		$sql = 'SELECT * FROM `'. $this->tableName . '` WHERE `visibility` = ?';
		$result = $this->execute($sql, array($isenabled));

		$entityList = array();
		while($row = $result->fetchRow()){
			$entity = new Item($row);
			array_push($entityList, $entity);
		}

		return $entityList;
	}


	/**
	 * Saves an item into the database
	 * @param Item $item: the item to be saved
	 * @return the item with the filled in id
	 */
	public function save($item){
		$sql = 'INSERT INTO `'. $this->tableName . '`(`backend`, `classname`, `arguments`, `enabled`)'.
				' VALUES(?, ?, ?, ?)';

		$params = array(
			$item->getBackend(),
			$item->getClassname(),
			$item->getArguments(),
			$item->getEnabled()
		);

		$this->execute($sql, $params);

		$item->setId($this->api->getInsertId($this->tableName));
	}

	/**
	 * Updates an item
	 * @param Item $item: the item to be updated
	 */
	public function update($item){
		$sql = 'UPDATE `'. $this->tableName . '` SET
				`backend` = ?,
				`classname` = ?,
				`arguments` = ?,
				`enabled` = ?
				WHERE `id` = ?';

		$params = array(
			$item->getBackend(),
			$item->getClassname(),
			$item->getArguments(),
			$item->getEnabled(),
			$item->getId()
		);

		$this->execute($sql, $params);
	}


	/**
	 * Deletes an item
	 * @param int $id: the id of the item
	 */
	public function delete($id){
		$this->deleteQuery($this->tableName, $id);
	}


}