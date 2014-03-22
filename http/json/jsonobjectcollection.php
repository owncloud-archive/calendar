<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Http\JSON;

class JSONObjectCollection extends JSONCollection {

	/**
	 * @brief get json-encoded string containing all information
	 */
	public function serialize($convenience=true) {
		$VObject = $this->collection->getVObject();

		if($convenience === true) {
			JSONUtility::addConvenience($VObject);
		}

		return $VObject->jsonSerialize();
	}
}