<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Http\JSON;

use \OCA\Calendar\AppFramework\Db\Entity;

abstract class JSON {

	protected $object;

	/**
	 * @brief Constructor
	 */
	public function __construct($object) {
		$this->object = $object;
	}

	/**
	 * @brief get object JSONObject was initialized with.
	 */
	protected function getObject() {
		return $this->object;
	}

	/**
	 * @brief get json-encoded string containing all information
	 */
	abstract public function serialize();
}