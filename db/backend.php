<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Db;

use \OCA\Calendar\AppFramework\Db\Entity;

class Backend extends Entity {

	public $id;
	public $backend;
	public $classname;
	public $arguments;
	public $enabled;

	public $api;

	public function __construct($fromRow=null){
		if($fromRow){
			$this->fromRow($fromRow);
		}
	}

	public function registerAPI($api){
		$this->api = $api;
		return $this;
	}

	public function disable() {
		$this->setEnabled(false);
		return $this;
	}

	public function enable() {
		$this->setEnabled(true);
		return $this;
	}
}