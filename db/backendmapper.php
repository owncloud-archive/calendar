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

use \OCA\Calendar\Db\Backend;

class BackendMapper {

	private $api;
	private $backendCollection;
	private $didChange;

	/**
	 * @brief Constructor
	 * @param API $api: Instance of the API abstraction layer
	 */
	public function __construct($api){
		$this->api = $api;
		$this->didChange(false);

		$backends = $this->api->getSystemValue('calendar_backends');

		if($backends === null) {
			throw new Exception('BackendMapper::__construct(): No calendar backend configuration found!');
		}

		$backendCollection = new BackendCollection();
		for($i = 0;$i < count($backends); $i++) {
			$backends[$i]['id'] = $i;
			$backend = new Backend($backends[$i]);
			$backendCollection->add($backend);
		}

		$this->backendCollection = $backendCollection;
	}

	/**
	 * @brief Destructor - write changes
	 * @param API $api: Instance of the API abstraction layer
	 */
	public function __destruct() {
		if($this->didChange === true) {
			$this->api->setSystemValue('calendar_backend', $this->backendCollection->getObjects());
		}
	}

	/**
	 * @brief Finds an item by it's name
	 * @param string $backend name of backend
	 * @throws DoesNotExistException: if the item does not exist
	 * @return the backend item
	 */
	public function find($backend){
		return $this->backendCollection->search('backend', $backend)->current();
	}

	/**
	 * Finds all Items
	 * @return array containing all items
	 */
	public function findAll(){
		return $this->backendCollection;
	}

	/**
	 * Finds all Items where enabled is ?
	 * @return array containing all items where enabled is ?
	 */
	public function findWhereEnabledIs($isEnabled){
		return $this->backendCollection->search('enabled', $isEnabled);
	}

	/**
	 * Saves an item into the database
	 * @param Item $item: the item to be saved
	 * @return $this
	 */
	public function save(Entity $item){
		$this->backendCollection->add($item);
		$this->didChange(true);
		return $this;
	}

	/**
	 * Updates an item
	 * @param Item $item: the item to be updated
	 * @return $this
	 */
	public function update(Entity $item){
		$oldItem = $this->backendCollection->search('id', $item->getId());
		$this->backendCollection->removeByEntity($oldItem);
		$this->backendCollection->add($item);
		$this->didChange(true);
		return $this;
	}

	/**
	 * Deletes an item
	 * @param Entity $item: the item to be deleted
	 * @return $this
	 */
	public function delete(Entity $item){
		$this->backendCollection->removeByEntity($item);
		$this->didChange(true);
		return $this;
	}

	/**
	 * sets didChange property
	 * @param boolean $didChange
	 * @return $this
	 */
	private function didChange($didChange) {
		$this->didChange = $didChange;
		return $this;
	}
}