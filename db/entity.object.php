<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Db;

use \OCA\Calendar\AppFramework\Db\Entity;

class Object extends Entity{

	public $id;
	public $calendarId;
	public $objectURI;
	public $type;
	public $startDate;
	public $endDate;
	public $timezone;
	public $repeating;
	public $lastOccurence;
	public $summary;
	public $calendarData;
	public $lastModified;
	public $deleteAt;

	public $backend;
	public $calendarURI;

	/**
	 * @brief init Object object with data from db row
	 * @param array $fromRow
	 */
	public function __construct($fromRow=null){
		if($fromRow){
			$this->fromRow($fromRow);
		}
		$this->startDate = new \DateTime($this->startDate);
		$this->endDate = new \DateTime($this->endDate);
	}

	/**
	 * @brief set lastModified to now
	 */
	public function touch() {
		$this->lastModified = time();
	}
}