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
	 * @brief get a collection of all enabled calendars within collection
	 * @param string $key - property that's supposed to be searched
	 * @param mixed $value - expected value, can be a regular expression when 3rd param is set to true
	 * @param boolean $regex - disable / enable search based on regular expression
	 * @return CalendarCollection of all calendars that meet criteria
	 */
	public function search($key, $value, $regex=false) {
		$matchingObjects = new ObjectCollection();

		$propertyGetter = 'get' . ucfirst(strtolower($key));

		foreach($this->objects as $object) {
			if(is_callable(array($object, $propertyGetter)) && $object->{$propertyGetter}($key)) === $value) {
				$matchingObjects->add($object);
			}
		}

		return $matchingObjects;
	}

	/**
	 * @brief get a collection of calendars with a certain permission
	 * @param int $cruds use \OCA\Calendar\Db\Permissions to get wanted permission code
	 * @return CalendarCollection of all disabled calendars
	 */
	public function permissions($cruds) {
		$allCalendarsWithPermission = new CalendarCollection();

		foreach($this->objects as $object) {
			if($object->getCruds() & $cruds) {
				$allCalendarsWithPermission->add($object);
			}
		}

		return $allCalendarsWithPermission;
	}

	/**
	 * @brief set a property for all calendars
	 * @param string $key key for property
	 * @param mixed $value value to be set
	 * @return CalendarCollection with new properties
	 */
	public function setProperty($key, $value) {
		$calendarsWithNewProperty = new CalendarCollection();

		$propertySetter = 'set' . ucfirst(strtolower($key));

		foreach($this->objects as $object) {
			if(is_callable($object, $propertySetter)) {
				$object->{$propertySetter}($value);
			}
			$calendarsWithNewProperty->add($object);
		}

		return $calendarsWithNewProperty;
	}
}