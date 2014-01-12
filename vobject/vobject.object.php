<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Db;

use Sabre\VObject\Reader;
use Sabre\VObject\Component\VEvent;
use Sabre\VObject\Component\VJournal;
use Sabre\VObject\Component\VTodo;

class VObjectObject extends VObject {

	private $vobject;
	private $object;

	public function __construct($object) {
		if( !($object instaceof Object)) {
			throw new VObjectObjectException('given object is not an instance of \OCA\Calendar\Db\Object');
		}

		$calendarData = $object->getCalendarData();

		try {
			$vobject = Reader::read($calendarData);
		} catch(Exception $ex) {
			throw new VObjectObjectException('Error parsing calendardata: "' . $ex->getMessage() . '"');
		}

		$this->vobject = $vobject;
	}

	public function getVObject() {
		return $this->vobject;
	}
}