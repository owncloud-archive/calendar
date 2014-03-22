<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Http\JSON;

use Sabre\VObject\Reader;

class JSONObjectReader {

	public function parse() {
		$data = &$this->data;

		try{
			$vcalendar = Reader::readJSON($data);

			$numComponents = 0;
			$numComponents += count($vcalendar->VEVENT);
			$numComponents += count($vcalendar->VJOURNAL);
			$numComponents += count($vcalendar->VTODO);

			if($numComponents !== 1) {
				throw new JSONObjectReaderException('parsing multiple objects at once is not implemented yet.');
			}

			$object = new Object();
			$object->fromVObject($vcalendar);

			$this->object = $object;
		} catch(Exception $e /* What exception is being thrown??? */) {
			throw new JSONObjectReaderException($ex->getMessage());
		}
	}
}

class JSONObjectReaderException extends Exception{}