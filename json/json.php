<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\JSON;

use \OCA\Calendar\AppFramework\Db\Entity;

abstract class JSON {

	protected $object;
	protected $properties;

	/**
	 * @brief Constructor
	 */
	public function __construct($object) {
		foreach($this->properties as $property) {
			$this->$property = $object->$property;
		}
		$this->object = $object;
	}

	/**
	 * @brief get object JSONObject was initialized with.
	 */
	public function getObject() {
		return $this->object;
	}

	/**
	 * @brief get vobject containing all information
	 */
	public function getVObject() {
		return $this->object->getVObject();
	}

	/**
	 * @brief get json-encoded string containing all information
	 */
	public function serialize();
}