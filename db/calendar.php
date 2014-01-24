<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Db;

use \OCA\Calendar\AppFramework\Db\Entity;

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
	public $x = array();

	/**
	 * @brief init Calendar object with data from db row
	 * @param array $fromRow
	 */
	public function __construct($fromRow=null){
		if($fromRow){
			$this->fromRow($fromRow);
		}
	}

	/**
	 * @brief increment ctag
	 */
	public function touch() {
		$this->ctag++;
		return $this;
	}

	public function getVObject() {
		
	}

	public function readFromVObject($vobject) {
		
	}
}