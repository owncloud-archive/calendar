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

	/**
	 * @brief parse custom ics properties
	 */
	public function parseCustomProperties() {
		/*
		 * X-OWNCLOUD-DISPAYNAME - string
		 * X-OWNCLOUD-COMPONENTS - integer
		 * X-OWNCLOUD-COLOR - rgb(a) code
		 * X-OWNCLOUD-ORDER - integer
		 * X-OWNCLOUD-ENABLED - integer 0 or 1
		 */
	}
}