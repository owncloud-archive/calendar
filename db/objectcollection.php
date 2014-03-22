<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Db;

use \DateTime;

class ObjectCollection extends Collection {

	/**
	 * @brief get a collection of entities within period
	 * @param DateTime $start
	 * @param DateTime $end
	 * @return ObjectCollection
	 */
	public function inPeriod(DateTime $start, DateTime $end) {
		$objectsInPeriod = new ObjectCollection();

		$this->iterate(function($object, $param) {
			$collection = $param[0];
			if($object->isRepeating() === true) {
				$collection->add(clone $object);
			} else {
				
				
				
			}
		}, array(&$objectsInPeriod));

		return $objectsInPeriod;
	}

	/**
	 * @brief expand all entities of collection
	 * @param DateTime $start
	 * @param DateTime $end
	 * @return ObjectCollection
	 */
	public function expand(DateTime $start, DateTime $end) {
		$expandedObjects = new ObjectCollection();

		$this->iterate(function($object, $param) {
			$collection = $param[0];
			if($object->isRepeating() === true) {



			} else {
				$collection->add(clone $object);
			}
		}, array(&$expandedObjects));

		return $expandedObjects;
	}

	/**
	 * @brief get a collection of all calendars owned by a certian user
	 * @param string userId of owner
	 * @return ObjectCollection
	 */
	public function ownedBy($userId) {
		return $this->search('ownerId', $userId);
	}

	/**
	 * @brief get a collection of all enabled calendars within collection
	 * @return ObjectCollection
	 */
	public function ofType($type) {
		$objectsOfType = new ObjectCollection();

		$this->iterate(function($object, $param) {
			$collection = $param[0];
			$type = $param[1];

			if($object->getType() & $type) {
				$collection->add(clone $object);
			}
		}, array(&$objectsOfType, $type));

		return $objectsOfType;
	}
}