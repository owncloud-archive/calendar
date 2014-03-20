<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 * 
 * Example output:
 * ```json
 * {
 *   "displayname" : "Work",
 *   "calendarURI" : "local-work",
 *   "owner" : {
 *     "userid" : "developer42",
 *     "displayname" : "developer42"
 *   },
 *   "ctag" : 0,
 *   "url" : "https://owncloud/index.php/apps/calendar/calendars/local-work",
 *   "color" : "#000000",
 *   "order" : 0,
 *   "enabled" : true,
 *   "components" : {
 *     "vevent" : true,
 *     "vjournal" : false,
 *     "vtodo" : true
 *   },
 *   "timezone" : {}, //see JSONTIMEZONE
 *   "user" : {
 *     "userid" : "developer42",
 *     "displayname" : "developer42"
 *   },
 *   "cruds" : {
 *     "create" : true,
 *     "update" : true,
 *     "delete" : true,
 *     "code" : 31,
 *     "read" : true,
 *     "share" : true
 *   }
 * }
 * ```
 */
namespace OCA\Calendar\JSON;

use \OCA\Calendar\Db\Calendar;
use \OCA\Calendar\Db\ObjectType;
use \OCA\Calendar\Db\Permissions;

class JSONCalendar extends JSON {

	private $jsonArray;

	public function serialize($convenience=true) {
		$properties = get_object_vars($this->object);

		foreach($properties as $property) {
			$propertyGetter = 'get' . ucfirst($property);
			$key = strtolower($property);
			$value = $object->{$propertyGetter}();

			switch($property) {
				case 'color':
				case 'displayname':
					$this->jsonArray[$key] = (string) $value;
					break;

				case 'ctag':
				case 'order':
					$this->jsonArray[$key] = (int) $value;
					break;

				case 'enabled':
					$this->jsonArray[key] = (bool) $value;
					break;

				case 'components':
					$this->jsonArray[$key] = JSONUtility::getComponents($value);
					break;

				case 'cruds':
					$this->jsonArray[$key] = JSONUtility::getCruds($value);
					break;

				case 'ownerId':
				case 'userId':
					$key = substr($key, 0, (strlen($key) - 2));
					$this->jsonArray[$key] = JSONUtility::getUserInformation($value);
					break;

				case 'timezone':
					$this->jsonArray[$key] = JSONUtility::getTimeZone($value, $convenience);
					break;

				//blacklist
				case 'id':
				case 'backend':
				case 'uri':
					break;

				default:
					$this->jsonArray[$key] = $value;
					break;
				
			}
		}

		$this->setCalendarURI();
		$this->setCalendarURL();

		return json_encode($this->jsonArray);
	}

	/**
	 * @brief set public calendar uri
	 */
	private function setCalendarURI() {
		$backend = $this->object->getBackend();
		$calendarURI = $this->object->getUri();

		$calendarURI = CalendarUtility::getURI($backend, $calendarURI);

		$this->jsonArray['calendarURI'] = $calendarURI;
	}

	/**
	 * @brief set api url to calendar
	 */
	private function setCalendarURL() {
		$calendarURI = $this->jsonArray['calendarURI'];

		$calendarURL = JSONUtility::getURL($calendarURI);

		$this->jsonArray['url'] = $calendarURL;
	}
}