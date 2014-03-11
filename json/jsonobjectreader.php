<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\JSON;

use Sabre\VObject\Reader;

class JSONObjectReader {
	
	private $data;
	private $object;

	/**
	 * @brief init JSONObjectReader object with data from Request
	 * @param mixed (string / array) $json;
	 */
	public function __construct($json=null) {
		if(is_array($json) === false) {
			if($json === null) {
				throw new JSONObjectReaderException('Given json string is empty!');
			}
	
			$data = json_decode($json, true);
			if($data === false) {
				throw new JSONObjectReaderException('Could not parse given json string!');
			}

			$this->data = $data;
		} else {
			$this->data = $json;
		}

		$this->extractData();
	}

	/**
	 * @brief get object created from data
	 * @return Object $object
	 */
	public function getObject() {
		return $this->object;
	}

	private function extractData() {
		$this->object = new Object();

		$this->parseCalendarId();
		$this->parseObjectURI();
		$this->parseType();
		$this->parseData();
	}

	private function parseCalendarId() {
		$json = $this->data;

		if(array_key_exists('calendarId', $json)) {
			$calendarId = $json['calendarId'];
			$regex = '*';
			if(preg_match($regex, $calendarId)) {
				$this->object->setCalendarId($calendarId);
			}
		}
	}

	private function parseObjectURI() {
		$json = $this->data;

		if(array_key_exists('objectURI', $json)) {
			$objectURI = $json['objectURI'];
			$regex = '*';
			if(preg_match($regex, $objectURI)) {
				$this->object->setObjectURI($objectURI);
			}
		}
	}

	private function parseType() {
		$json = $this->data;

		if(array_key_exists('type', $json)) {
			$type = $json['type'];
			$regex = '*';
			if(preg_match($regex, $type)) {
				$this->object->setType($type);
			}
		}
	}

	private function parseData() {
		$json = $this->data;

		if(array_key_exists('data', $json) === false) {
			throw new JSONObjectReaderException('JSON does not contain index data');
		}
		$data = $json['data'];

		try {
			$vobject = Reader::readJson($data);
		} catch(Exception $e /* What exception is being thrown */);

		if($vobject !== null && $vobject !== false) {
			$this->object->fillWithVObject($vobject);
		} else {
			throw new JSONObjectReaderException('JSON does not contain valid data');
		}
	}
}