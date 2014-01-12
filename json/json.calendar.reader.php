<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Calendar\Db;

class JSONCalendarReader {

	private $data;
	private $calendar;

	public function __construct($json=null) {
		if($json === null) {
			throw new JSONCalendarReaderException('Given json string is empty!');
		}

		$data = json_decode($json, true);
		if($data === false) {
			throw new JSONCalendarReaderException('Could not parse given json string!');
		}

		$this->data = $data;
	}

	public function extractData() {
		$this->calendar = new Calendar();

		try{
			foreach($data as $key => $value) {
				switch(strtolower($key)) {
					//strings
					case 'displayname':
					case 'calendarURI':
						$this->parseString($key, $value);
						break;
	
					case 'color':
						$this->parseColor($key, $value);
						break;
	
					//ints
					case 'ctag':
					case 'order':
						$this->parseInteger($key, $value);
						break;
	
					//boolean
					case 'enabled':
						$this->parseBoolean($key, $value);
						break;
	
					//arrays
					case 'owner':
					case 'user':
						$this->parseUserArray($key, $value);
						break;
	
					case 'components':
						$this->parseComponents($key, $value);
						break;
	
					case 'timezone':
						$this->parseTimeZone($key, $value);
						break;
	
					case 'cruds':
						$this->parseCruds($key, $value);
						break;
					
					default:
						//ignore custom values
						break;
				}
			}
		}catch(Exception $ex) {
			throw new JSONCalendarReaderException('Error: "' . $ex->getMessage() . '"');
		}
		
	}

	private function parseString($key, $value) {
		$this->calendar->set{ucfirst($key)}((string) $value);
	}

	private function parseColor($key, $value) {
		//validate and try to fix $value
		$this->calendar->setColor($value);
	}

	private function parseInteger($key, $value) {
		$this->calendar->set{ucfirst($key)}((int) $value);
	}

	private function parseBoolean($key, $value) {
		$this->calendar->set{ucfirst($key)}((boolean) $value);	
	}

	private function parseUserArray($key, $value) {
		if(array_key_exists('userid', $value) === false) {
			throw new JSONCalendarReaderException('The key "' . $key . '" does not contain an userid!');
		}

		$this->calendar->set{ucfirst($key)}((string) $value['userid']);
	}

	private function parseComponents($key, $value) {
		$components = 0;

		if(is_array($value) === false {
			throw new JSONCalendarReaderException('Components must be an array!')
		}

		if(array_key_exists('vevent', $value) && $value['vevent'] === 'true') {
			$components += ObjectType::EVENT;
		}
		if(array_key_exists('vjournal', $value) && $value['vjournal'] === 'true') {
			$components += ObjectType::JOURNAL;
		}
		if(array_key_exists('vtodo', $value) && $value['vtodo'] === 'true') {
			$components += ObjectType::TODO;
		}

		$this->calendar->setComponents($components);
	}

	private function parseTimeZone($key, $value) {
		
	}

	private function parseCruds($key, $value) {
		$cruds = 0;

		if(is_array($value) === false {
			throw new JSONCalendarReaderException('Cruds must be an array!')
		}

		//use code if given
		if(array_key_exists('code', $value) && (int) $value['code'] >= 0 && (int) $value['code'] <= 31) {
			$cruds = (int) $value['code']
		} else {
			if(array_key_exists('create', $value) && $value['create'] === 'true') {
				$cruds += Permissions::CREATE;
			}
			if(array_key_exists('update', $value) && $value['update'] === 'true') {
				$cruds += Permissions::UPDATE;
			}
			if(array_key_exists('delete', $value) && $value['delete'] === 'true') {
				$cruds += Permissions::DELETE;
			}
			if(array_key_exists('read', $value) && $value['read'] === 'true') {
				$cruds += Permissions::READ;
			}
			if(array_key_exists('share', $value) && $value['share'] === 'true') {
				$cruds += Permissions::SHARE;
			}
		}

		$this->calendar->setCruds($cruds);
	}

	private function getCalendar() {
		return $this->calendar;
	}
}