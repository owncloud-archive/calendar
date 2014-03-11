<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Db;

use \OCA\Calendar\AppFramework\Db\Entity;

use Sabre\VObject\Reader;

class Object extends Entity {

	public $id;
	public $userId;
	public $ownerId;
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
	public $etag;

	public $backend;
	public $calendarURI;

	/**
	 * @brief init Object object with data from db row
	 * @param array $fromRow
	 */
	public function __construct($fromRow=null){
		if($fromRow){
			$this->fromRow($fromRow);

			$this->startDate = new \DateTime($this->startDate);
			$this->endDate = new \DateTime($this->endDate);

			if($this->etag === null) {
				$this->updateETag();
			}
		}
	}

	public function fromVObject($vobject) {
		
	}

	public function getVObject() {
		$vobject = Reader::read($this->calendarData);
		return $vobject;
	}

	/**
	 * @brief expand an Array
	 * @param DateTime $start 
	 * @param DateTime $end
	 * @return array of Object objects
	 */
	public function expand($start=null, $end=null) {
		$objects = array();
		$objects[] = $this;

		if($start == null) {
			$start = new DateTime('01-01-' . date('Y') . ' 00:00:00', new DateTimeZone('UTC'));
			$start->modify('-5 years');
		}

		if($end == null) {
			$end = new DateTime('31-12-' . date('Y') . ' 23:59:59', new DateTimeZone('UTC'));
			$end->modify('+5 years');	
		}

		try {
			$vobject->expand($start, $end);
			foreach($vobject->getComponents() as $singleVObject) {
				if(!($singleVObject instanceof Sabre\VObject\Component\VEvent) &&
				   !($singleVObject instanceof Sabre\VObject\Component\VJournal) &&
				   !($singleVObject instanceof Sabre\VObject\Component\VTodo)) {
					continue;
				}

				$parsedVObject = new VObjectObjectReader($singleVObject);
				$objects[] = $parsedVObject;
			}
		} catch (Exception $ex) {}
	}

	/**
	 * @brief set lastModified to now and update ETag
	 */
	public function touch() {
		$this->updateLastModified();
		$this->updateCalendarData();
		$this->updateETag();
	}

	/**
	 * @brief updates calendar data
	 */
	public function updateCalendarData() {
		
	}

	/**
	 * @brief update Etag
	 */
	public function updateETag() {
		$this->etag = '"' . md5($this->calendarId . $this->objectURI . $this->calendarData . $this->lastModified) . '"';
	}

	/**
	 * @brief set lastModified to now
	 */
	public function updateLastModified() {
		$this->lastModified = time();
	}


}