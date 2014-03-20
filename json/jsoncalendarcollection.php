<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\JSON;

use \OCA\Calendar\Db\Calendar;

class JSONCalendarCollection extends JSONCollection {

	/**
	 * @brief get json-encoded string containing all information
	 */
	public function serialize() {
		$jsonArray = array();

		foreach($this->collection as &$object) {
			if($object instanceof Calendar) {
				$jsonCalendar = new JSONCalendar($object);
				$jsonArray[] = $jsonCalendar->serialize();
			}
			if($object instanceof JSONCalendar) {
				$jsonArray[] = $object->serialize();
			}
		}

		return json_encode($jsonArray);
	}
}