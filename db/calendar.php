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

class Calendar extends Entity {

	public $id;
	public $userId;
	public $ownerId;
	public $backend;
	public $uri;
	public $displayname;
	public $components;
	public $ctag;
	public $timezone;
	public $color;
	public $order;
	public $enabled;
	public $cruds;

	/**
	 * @brief init Calendar object with data from db row
	 * @param mixed (array / VCalendar) $from
	 */
	public function __construct($from=null){
		//if $from is an array, parse it like a db row
		if(is_array($from)){
			$this->fromRow($from);
		}

		//if $from is an VObject, parse it like an VObject
		if($from instanceof VCalendar) {
			$this->fromVObject($from);
		}
	}

	/**
	 * @brief take data from VObject and put into this Calendar object
	 * @return VCalendar Object
	 */
	public function fromVObject($vobject) {
		//do some magic
	}

	/**
	 * @brief get VObject from Calendar Object
	 * @return VCalendar Object
	 */
	public function getVObject() {
		//do some more magic
	}

	/**
	 * @brief increment ctag
	 * @return Calendar
	 */
	public function touch() {
		$this->ctag++;
		return $this;
	}

	/**
	 * @brief check if object is valid
	 * @return Calendar
	 */
	public function isValid() {
		$strings = array(
			$this->userId, 
			$this->ownerId, 
			$this->backend,
			$this->uri,
			$this->displayname);

		foreach($strings as $string) {
			if(is_string($string) === false) {
				return false;
			}
			if(trim($string) === '') {
				return false;
			}
		}

		if(is_int($this->components) === false) {
			return false;
		}
		if($this->components <= 0 || $this->components >= ObjectType::ALL) {
			return false;
		}

		if(is_int($this->ctag) === false) {
			return false;
		}
		if($this->ctag < 0) {
			return false;
		}

		if($this->timezone instanceof Timezone && $this->timezone->isValid() === false) {
			return false;
		}

		if(is_string($this->color) === false) {
			return false;
		}
		if(preg_match('/#((?:[0-9a-fA-F]{2}){3}|(?:[0-9a-fA-F]{1}){3}|(?:[0-9a-fA-F]{1}){4}|(?:[0-9a-fA-F]{2}){4})$/', $this->color) !== 1) {
			return false;
		}

		if(is_int($this->order) === false) {
			return false;
		}

		if(is_bool($this->enabled) === false) {
			return false;
		}

		if(is_int($this->cruds) === false) {
			return false;
		}
		if($this->cruds <= 0 || $this->cruds >= Permissions::ALL) {
			return false;
		}

		return true;
	}
}