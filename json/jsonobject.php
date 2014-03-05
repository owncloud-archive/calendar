<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\JSON;

use Sabre\VObject\Reader;
use Sabre\VObject\Component\VEvent;
use Sabre\VObject\Component\VJournal;
use Sabre\VObject\Component\VTodo;

use \OCA\Calendar\Db\Object;

class JSONObject extends JSON{

	public $calendarId;
	public $objectURI;
	public $url;
	public $type;
	public $data;

	private $objectObject;

	/**
	 * @brief init JSONObject object with data from Object object
	 * @param Object $object
	 */
	public function __construct(Object $object) {
		$this->properties = array(
			'type',
			'deleteAt',
			'objectURI',
		);
		parent::__construct($object);

		$this->objectObject = $object;

		$this->setCalendarId();
		$this->setURL();
		$this->setData();
	}

	/**
	 * @brief set public calendar id
	 */
	private function setCalendarId() {
		$backend = $this->objectObject->getBackend();
		$calendarURI = $this->objectObject->getCalendarURI();

		$this->calendarId = strtolower($backend . '-' . $calendarURI);
	}

	/**
	 * @brief set api url to object
	 */
	private function setURL() {
		$properties = array(
			'calendarId' => $this->calendarId,
			'objectId' => $this->objectURI,
		);
		$url = \OCP\Util::linkToRoute('calendar.objects.show', $properties);
		$this->url = \OCP\Util::linkToAbsolute('', substr($url, 1));
	}

	/**
	 * @brief set data array
	 */
	private function setData() {
		$calendarData = $this->objectObject->getCalendarData();

		$childOfInterest = null;

		$vObject = Reader::read($calendarData);
		echo $vObject->serialize();
		print_r($vObject);
		$children = $vObject->children();
		foreach($children as $child) {
			if( !($child instanceof VEvent) && 
			 	!($child instanceof VJournal) &&
			 	!($child instanceof VTodo) ) {
				continue;
			}
			$childOfInterest = $child;
		}
		
		$jcal = $childOfInterest->jsonSerialize();
	}
}