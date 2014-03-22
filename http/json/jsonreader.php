<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Http\JSON;

use \OCA\Calendar\Db\Collection;

abstract class JSONReader {

	protected $data;
	protected $object;

	/**
	 * @brief Constructor
	 */
	public function __construct($json=null) {
		$this->object = null;
		if(is_array($json) === false) {
			if($json === null) {
				throw new JSONReaderException('Given json string is empty!');
			}
	
			$data = json_decode($json, true);
			if($data === false) {
				throw new JSONReaderException('Could not parse given json string!');
			}

			$this->data = $data;
		} else {
			$this->data = $json;
		}
	}

	public function getObject() {
		if($this->object === null) {
			$this->parse();
		}

		return $this->object;
	}

	abstract public function parse();
}