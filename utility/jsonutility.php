<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Utility;

class JSONUtility extends Utility{

	public static function getUserInformation($userId) {
		if($userId === null) {
			$userId = \OCP\User::getUser();
		}

		return array(
			'userid' => $userId,
			'displayname' => \OCP\User::getDisplayName($userId),
		);
	}

	public static function parseUserArray($value) {
		if(array_key_exists('userid', $value) === false) {
			return false;
		} else {
			return $value['userid'];
		}
	}

	public static function getComponents($components) {
		return array(
			'vevent'	=> (bool) ($components & ObjectType::EVENT),
			'vjournal'	=> (bool) ($components & ObjectType::JOURNAL),
			'vtodo'		=> (bool) ($components & ObjectType::TODO),
		);
	}

	private function parseComponents($value) {
		if(is_array($value) === false) {
			return null;
		}

		$components = 0;

		if(array_key_exists('vevent', $value) && $value['vevent'] === true) {
			$components += ObjectType::EVENT;
		}
		if(array_key_exists('vjournal', $value) && $value['vjournal'] === true) {
			$components += ObjectType::JOURNAL;
		}
		if(array_key_exists('vtodo', $value) && $value['vtodo'] === true) {
			$components += ObjectType::TODO;
		}

		return $components;
	}

	public static function getCruds($cruds) {
		return array(
			'code' => 	$cruds,
			'create' =>	(bool) ($cruds & Permissions::CREATE),
			'read' => 	(bool) ($cruds & Permissions::READ),
			'update' =>	(bool) ($cruds & Permissions::UPDATE),
			'delete' =>	(bool) ($cruds & Permissions::DELETE),
			'share' =>	(bool) ($cruds & Permissions::SHARE),
		);
	}

	public static function parseCruds($value) {
		if(is_array($value) === false) {
			return null;
		}

		$cruds = 0;

		//use code if given
		if(array_key_exists('code', $value) && (int) $value['code'] >= 0 && (int) $value['code'] <= 31) {
			$cruds = (int) $value['code'];
		} else {
			if(array_key_exists('create', $value) && $value['create'] === true) {
				$cruds += Permissions::CREATE;
			}
			if(array_key_exists('update', $value) && $value['update'] === true) {
				$cruds += Permissions::UPDATE;
			}
			if(array_key_exists('delete', $value) && $value['delete'] === true) {
				$cruds += Permissions::DELETE;
			}
			if(array_key_exists('read', $value) && $value['read'] === true) {
				$cruds += Permissions::READ;
			}
			if(array_key_exists('share', $value) && $value['share'] === true) {
				$cruds += Permissions::SHARE;
			}
		}
	}

	public function parseCalendarURI($key, $value) {
		list($backend, $calendarURI) = CalendarUtility::splitURI($value);
		$this->calendar->setBackend($backend);
		$this->calendar->setUri($calendarURI);
	}

	public static function getTimeZone($timezone, $convenience) {
		$jsonTimezone = new JSONTimezone($timezone);
		return $jsonTimezone->serialize($convenience);
	}

	private function parseTimeZone($value) {
		$timezoneReader = new JSONTimezoneReader($value);
		return $timezoneReader->getObject();
	}

	public static function getURL($calendarURI) {
		$properties = array(
			'calendarId' => $calendarURI,
		);

		$url = \OCP\Util::linkToRoute('calendar.calendars.show', $properties);
		$this->url = \OCP\Util::linkToAbsolute('', substr($url, 1));
	}

	public static function addConvenience(&$vobject) {
		//add ATOM property to all time values
		//add X-OC-DTEND if duration exists
	}

	public static function removeConvenience(&$vobject) {
	}
}