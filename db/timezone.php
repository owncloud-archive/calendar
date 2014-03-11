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

class Timezone extends Entity {

	public $id;
	public $tzId;
	public $tzData;

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
		
	}

	/**
	 * @brief get VObject from Calendar Object
	 * @return VCalendar Object
	 */
	public function getVObject() {
		
	}

	/**
	 * @brief check if object is valid
	 * @return Calendar
	 */
	public function isValid() {
		
	}
}