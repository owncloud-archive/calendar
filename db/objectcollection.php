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

class ObjectCollection extends Collection {

	public function inPeriod(DateTime $start, DateTime $end) {
		
	}

	public function expand(DateTime $start, DateTime $end) {
		
	}

	public function ofType($type) {
		if($type === ObjectType::ALL) {
			return (clone $this);
		}

		return $this->search('type', $type);
	}

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

	public function searchData($key, $value, $regex) {
		$matchingObjects = new ObjectCollection();

		$key = strtoupper($key);

		
	}

	public function getVObject() {
		$vobject = new VCalendar();

		foreach($this->objects as $object) {
			$vobject->add($object->getVObject());
		}

		return $vobject;
	}

	public function getVObjects() {
		$vobjects = array();

		foreach($this->objects as $object) {
			$vobjects[] = $object->getVObject();
		}

		return $vobjects;
	}
}