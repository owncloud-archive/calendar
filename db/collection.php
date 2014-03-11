<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Db;

abstract class Collection {

	protected $objects = array();

	public function __construct($objects=null) {
		$this->objects = array();

		if($objects !== null) {
			if(is_array($objects)) {
				//TODO: check if all objects are entitys
				$this->objects = $objects
			} else if($objects instanceof Entity) {
				$this->objects[] = $objects;
			} else if($objects instanceof Collection) {
				$this->objects = $objects->getObjects();
			}
		}
	}

	public function add(Entity $object, $nth=null) {
		
	}

	public function remove($nth=null) {
		
	}

	public function count() {
		return count($this->objects);
	}

	public function current() {
		return current($this->objects);
	}

	public function key() {
		return key($this->objects);
	}

	public function next() {
		next($this->objects);
		return $this;
	}

	public function prev() {
		prev($this->objects);
		return $this;
	}

	public function reset() {
		reset($this->objects);
		return $this;
	}

	public function end() {
		end($this->objects);
		return $this;
	}

	public function getObjects() {
		return $this->objects;
	}

	public function inCollection(Entity $object) {
		return in_array($object, $this->objects);
	}

	/**
	 * @brief get one VCalendar object containing all information
	 * @return VCalendar object
	 */
	public function getVObject() {
		$vobject = new VCalendar();

		foreach($this->objects as $object) {
			$vobject->add($object->getVObject());
		}

		return $vobject;
	}

	/**
	 * @brief get an array of VCalendar objects
	 * @return array of VCalendar object
	 */
	public function getVObjects() {
		$vobjects = array();

		foreach($this->objects as $object) {
			$vobjects[] = $object->getVObject();
		}

		return $vobjects;
	}

	public function search();
}