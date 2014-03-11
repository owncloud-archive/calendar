<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Db;

use \OCA\Calendar\AppFramework\Db\Entity;
use \OCA\Calendar\Backend\CalendarInterface;

class Backend extends Entity {

	public $id;
	public $backend;
	public $classname;
	public $arguments;
	public $enabled;

	public $api;

	/**
	 * @brief init Backend object with data from db row
	 * @param array $fromRow
	 */
	public function __construct($fromRow=null){
		if($fromRow){
			$this->fromRow($fromRow);
		}
	}

	/**
	 * registers an API for a backend
	 * @param CalendarInterface $api
	 * @return Backend
	 */
	public function registerAPI(CalendarInterface $api){
		$this->api = $api;
		return $this;
	}

	/**
	 * disables a backend
	 * @return Backend
	 */
	public function disable() {
		$this->setEnabled(false);
		return $this;
	}

	/**
	 * enables a backend
	 * @return Backend
	 */
	public function enable() {
		$this->setEnabled(true);
		return $this;
	}
}