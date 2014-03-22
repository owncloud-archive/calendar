<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Db;

use \OCA\Calendar\AppFramework\Db\Entity;

use \Sabre\VObject\Component\VCalendar;

class CalendarCollection extends Collection {

	/**
	 * @brief get a collection of all enabled calendars within collection
	 * @return CalendarCollection of all enabled calendars
	 */
	public function enabled() {
		return $this->search('enabled', true);
	}

	/**
	 * @brief get a collection of all disabled calendars within collection
	 * @return CalendarCollection of all disabled calendars
	 */
	public function disabled() {
		return $this->search('enabled', false);
	}

	/**
	 * @brief get a collection of all calendars owned by a certian user
	 * @param string userId of owner
	 * @return CalendarCollection of all calendars owned by user
	 */
	public function ownedBy($userId) {
		return $this->search('ownerId', $userId);
	}

	/**
	 * @brief get a collection of calendars that supports certian components
	 * @param int $component use \OCA\Calendar\Db\ObjectType to get wanted component code
	 * @return CalendarCollection of calendars that supports certian components
	 */
	public function components($component) {
		$newCollection = new CalendarCollection();

		$this->iterate(function($object, $param) {
			$collection = $param[0];
			$component = $param[1];

			if($object->getComponents() & $component) {
				$collection->add(clone $object);
			}
		}, array(&$newCollection, $component));

		return $newCollection;
	}

	/**
	 * @brief get a collection of calendars with a certain permission
	 * @param int $cruds use \OCA\Calendar\Db\Permissions to get wanted permission code
	 * @return CalendarCollection of calendars with a certian permission
	 */
	public function permissions($cruds) {
		$newCollection = new CalendarCollection();

		$this->iterate(function($object, $param) {
			$collection = $param[0];
			$cruds = $param[1];

			if($object->getCruds() & $cruds) {
				$collection->add(clone $object);
			}
		}, array(&$newCollection, $cruds));

		return $newCollection;
	}
}