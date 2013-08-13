<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Db;

use Sabre\VObject\Reader;
use Sabre\VObject\Component\VEvent;
use Sabre\VObject\Component\VJournal;
use Sabre\VObject\Component\VTodo;

class JSONObject extends JSON{

	public $calendarId;
	public $objectURI;
	public $url;
	public $type;
	public $deleteAt;
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

		$vObject = Reader::read($calendarData);
		$children = $vObject->children();
		foreach($children as $child) {
			if( !($child instanceof VEvent) && 
			 	!($child instanceof VJournal) &&
			 	!($child instanceof VTodo) ) {
				continue;
			}
			$properties = $child->children();
			foreach($properties as $property) {
				$this->setDataProperty($property);
			}
			break;
		}
	}

	/**
	 * @brief set single property in data array
	 * @param mixed $property
	 * $property may be:
	 *  - Sabre\VObject\Property
	 *  - Sabre\VObject\Property\DateTime
	 *  - Sabre\VObject\Component\VAlarm
	 */
	private function setDataProperty($property) {
		$name = strtolower($property->name);
		switch($name) {
			//properties that may only occur once
			case 'class':
			case 'description':
			case 'duration':
			case 'geo':
			case 'location':
			case 'organizer':
			case 'percent':
			case 'priority':
			case 'seq':
			case 'status':
			case 'summary':
			case 'transp':
			case 'uid':
			case 'url':
				$this->setKeyValueData($name, $property);
				break;

			case 'completed':
			case 'created':
			case 'dtstamp':
			case 'last-mod':
				$this->setUTCData($name, $property);
				break;

			case 'dtend':
			case 'dtstart':
			case 'due':
			case 'recurid':
				$this->setDateTimeData($name, $property);
				break;

			//properties that may occur more than once
			case 'attach':
			case 'attendee':
			case 'comment':
			case 'contact':
			case 'exdate':
			case 'exrule':
			case 'related':
			case 'resources':
			case 'rdate':
			case 'rrule':
			case 'rstatus':
				$this->setKeyValueDataMO($name, $property);
				break;

			case 'categories':
				$this->setCategoriesData($name, $property);
				break;
				
			case 'valarm':
				$this->setVAlarmData($name, $property);
				break;

			default: //x-properties
				$this->setKeyValueData($name, $property);
				break;
		}
	}

	/**
	 * @brief create array for property in data array if it doesn't already exist
	 * @param string $name property name (lower case!)
	 */
	private function createDataArrayByName($name) {
		if(!array_key_exists($name, $this->data)) {
			$this->data[$name] = array();
		}
	}

	/**
	 * @brief set key value pair in data array - use for properties that may only occur once
	 * @param string $name - property name (lower case!)
	 * @param mixed $property 
	 */
	private function setKeyValueData($name, $property) {
		$this->data[$name] = $this->generateKeyValueData($name, $property);
	}

	/**
	 * @brief set key value pair in data array - use for properties that may occur more than once
	 * @param string $name - property name (lower case!)
	 * @param mixed $property 
	 */
	private function setKeyValueDataMO($name, $property) {
		$this->createDataArrayByName($name);
		$this->data[$name][] = $this->generateKeyValueData($name, $property);
	}

	/**
	 * @brief generate array for key value data
	 * @param string $name - property name (lower case!)
	 * @param mixed $property
	 * @return array
	 */
	private function generateKeyValueData($name, $property) {
		$parameters = $property->parameters;
		$array = array(
			'value' => $property->value,
		);
		foreach($parameters as $parameter) {
			$array['parameters'][strtolower($parameter->name)] = $parameter->value;
		}
		return $array;
	}

	/**
	 * @brief set data for utc only datetime value
	 * @param string $name - property name (lower case!)
	 * @param mixed $property
	 * @return array
	 */
	private function setUTCData($name, $property) {
		$dateTime = new \DateTime($property->value);
		$utcData = $this->generateKeyValueData($name, $property);
		$utcData['rfc2822'] = $dateTime->format(\DateTime::RFC2822 );
		$this->data[$name] = $utcData;
	}

	/**
	 * @brief set data for various datetime value
	 * @param string $name - property name (lower case!)
	 * @param mixed $property
	 */
	private function setDateTimeData($name, $property) {
		$dateTime = $property->getDateTime(\DateTime::RFC2822);
		$dateTimeData = $this->generateKeyValueData($name, $property);
		$dateType = $property->getDateType();

		$rfc2822 = $dateTime->format(\DateTime::RFC2822);
		$isLocal = ($dateType === \Sabre\VObject\Property\DateTime::LOCAL ||
					$dateType === \Sabre\VObject\Property\DateTime::DATE);
		if($isLocal) {
			$dateLength = strlen($rfc2822);
			// strlen(' +0000') = 6
			$rfc2822 = substr($rfc2822, 0, $dateLength - 6);
		}
		$dateTimeData['rfc2822'] = $rfc2822;
		$dateTimeData['dateType'] = $dateType;
		$this->data[$name] = $dateTimeData;
	}

	/**
	 * @brief set valarm data
	 * @param string $name - property name (lower case!)
	 * @param Sabre\VObject\Component\VAlarm $property
	 */
	private function setVAlarmData($name, $property) {
		$this->createDataArrayByName($name);
		$children = $property->children();
		$properties = array();
		foreach($children as $child) {
			$childsName = strtolower($child->name);
			$properties[$childsName] = $this->generateKeyValueData($childsName, $child);
		}
		$this->data[$name][] = $properties;
	} 

	/**
	 * @brief set categories
	 * @param string $name - property name (lower case!)
	 * @param Sabre\VObject\Component\VAlarm $property
	 */
	private function setCategoriesData($name, $property) {
		$this->createDataArrayByName($name);
		//TODO implement
	}
}