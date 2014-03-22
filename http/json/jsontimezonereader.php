<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Http\JSON;

use Sabre\VObject\Reader;

class JSONTimezoneReader extends JSON{

	public function parse() {
		$data = &$this->data;

		try{
			$vcalendar = Reader::readJSON($data);

			$numTimezones = count($vcalendar->VTIMEZONE);

			if($numTimezones !== 1) {
				throw new JSONTimezoneReaderException('parsing multiple timezones at once is not implemented yet.');
			}

			$timezone = new Timezone();
			$timezone->fromVObject($vcalendar);

			$this->object = $timezone;
		} catch(Exception $ex /* What exception is being thrown??? */) {
			throw new JSONTimezoneReaderException($ex->getMessage());
		}
	}
}

class JSONTimezoneReaderException extends Exception{}