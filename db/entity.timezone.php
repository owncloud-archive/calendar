<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Db;

use \OCA\Calendar\AppFramework\Db\Entity;

class Timezone extends Entity {

	public $id;
	public $name;
	public $isDST;
	public $stdOffset;
	public $dstOffset;

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
	}
}