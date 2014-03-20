<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
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

class JSONObject extends JSON {

	/**
	 * @brief get json-encoded string containing all information
	 */
	public function serialize($convenience=true) {
		$VObject = $this->object->getVObject();

		if($convenience === true) {
			JSONUtility::addConvenience($VObject);
		}

		return $VObject->jsonSerialize();
	}
}