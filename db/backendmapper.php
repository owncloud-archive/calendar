<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Db;

use \OCA\Calendar\AppFramework\Core\API;
use \OCA\Calendar\AppFramework\Db\Mapper;
use \OCA\Calendar\AppFramework\Db\DoesNotExistException;
use \OCA\Calendar\AppFramework\Db\Entity;

use \OC\Files\Filesystem;

use \OCA\Calendar\Db\Backend;

class BackendMapper {

	private $api;
	private $backends;

	/**
	 * @param API $api: Instance of the API abstraction layer
	 */
	public function __construct($api){
		$this->api = $api;
		$entities = $this->api->getSystemValue('calendar_backends');

		$backends = array();
		$i = 0;
		foreach($entities as $entity) {
			$entity['id'] = $i;
			$backend = new Backend($entity);
			$backends[] = $backend;
			$i++;
		}

		$this->backends = $backends;
	}

	/**
	 * Finds an item by id
	 * @throws DoesNotExistException: if the item does not exist
	 * @return the item
	 */
	public function find($id){
		try {
			$backends = $this->findAll();
			foreach($backends as $backend) {
				if($id === $backend->getBackend()) {
					return $backend;
				}
			}
		} catch (Exception $ex) {
			throw new DoesNotExistException('Backend "' . $id . '" does not exist');
		}
	}

	/**
	 * Finds all Items
	 * @return array containing all items
	 */
	public function findAll(){
		return $this->backends;
	}

	/**
	 * Finds all Items where enabled is ?
	 * @return array containing all items where enabled is ?
	 */
	public function findWhereEnabledIs($isenabled){
		$backends = $this->findAll();
		$enabledBackends = array();
		foreach($backends as $backend) {
			if($isenabled === $backend->getEnabled()){
				$enabledBackends[] = $backend;
			}
		}
		return $enabledBackends;
	}

	/**
	 * Saves an item into the database
	 * @param Item $item: the item to be saved
	 * @return the item with the filled in id
	 */
	public function save(Entity $item){
		$this->backends[] = $item;
		$this->writeChanges();
	}

	/**
	 * Updates an item
	 * @param Item $item: the item to be updated
	 */
	public function update(Entity $item){
		$this->delete($item->getBackend());
		$this->save($item);
	}

	/**
	 * Deletes an item
	 * @param int $id: the id of the item
	 */
	public function delete(Entity $id){
		$backends = $this->findAll();
		$newBackends = array();
		foreach($backends as $backend) {
			if($id !== $backend->getBackend()) {
				$newBackends[] = $backend;
			}
		}
		$this->backends = $newBackends;
		$this->didChange=true;
		return true;
	}
}