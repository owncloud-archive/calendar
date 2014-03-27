<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Http\JSON;

use \OCA\Calendar\Db\Calendar;
use \OCA\Calendar\Db\ObjectType;
use \OCA\Calendar\Db\Permissions;

use \OCA\Calendar\Utility\CalendarUtility;

class JSONCalendarReader extends JSONReader{

	public function parse() {
		$data = &$this->data;

		try{
			//multiple calendars?
			if(array_key_exists(0, $data) === true && is_array($data[0]) === true) {
				throw new JSONCalendarReaderException('parsing multiple calendars at once is not implemented yet.');
			}

			$this->object = new Calendar();

			foreach($data as $key => $value) {
				$propertySetter = 'set' . ucfirst($key);
	
				switch($key) {
					case 'color':
					case 'displayname':
						$this->object->{$propertySetter}((string) $value);
						break;
	
					case 'ctag':
					case 'order':
						$this->object->{$propertySetter}((int) $value);
						break;
	
					case 'enabled':
						$this->object->{$propertySetter}((bool) $value);
						break;
	
					case 'components':
						$this->object->{$propertySetter}(JSONUtility::parseComponents($value));
						break;
	
					case 'cruds':
						$this->object->{$propertySetter}(JSONUtility::parseCruds($value));
						break;
	
					case 'owner':
					case 'user':
						$propertySetter .= 'Id';
						$this->object->{$propertySetter}(JSONUtility::parseUserInformation($value));
						break;
	
					case 'timezone':
						$this->object->{$propertySetter}(JSONUtility::parseTimeZone($value));
						break;

					case 'calendarURI':
						$this->object->setBackend(JSONUtility::parseCalendarURIForBackend($value));
						$this->object->setUri(JSONUtility::parseCalendarURIForURI($value));

					//blacklist:
					case 'url':
						break;

					default:
						break;
				}
			}
		} catch(Exception $ex /* What exception is being thrown??? */) {
			throw new JSONCalendarReaderException($ex->getMessage());
		}
	}

	public function sanitize() {
		$sanitize = array(
			'userId',
			'ownerId',
			'cruds',
			'ctag',
		);
		return parent::sanitize($sanitize);
	}
}

class JSONCalendarReaderException extends Exception{}