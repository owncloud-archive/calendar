<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Http\JSON;

use \OCA\Calendar\Db\Collection;

abstract class JSONCollection {

	protected $collection;

	/**
	 * @brief Constructor
	 */
	public function __construct(Collection $collection) {
		$this->collection = $collection;
	}

	/**
	 * @brief get object JSONObject was initialized with.
	 */
	public function getCollection() {
		return $this->collection;
	}

	/**
	 * @brief get json-encoded string containing all information
	 */
	abstract public function serialize();
}