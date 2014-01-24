<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\JSON;

use \OCA\Calendar\AppFramework\Db\Entity;

abstract class JSON {

	protected $properties;

	public function __construct($object) {
		foreach($this->properties as $property) {
			$this->$property = $object->$property;
		}
	}
}