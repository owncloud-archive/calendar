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

class VObjectCalendarReader extends VObject {

	private $vcalendar;
	private $calendar;

	public function __construct($vobject) {
		if( !($vobject instanceof VCalendar) ) {
			throw new VObjectObjectReaderException('Given object is not valid!');
		}

		$object = new Calendar();
		//$object->asd();
	}

	public function getCalendar() {
		return $this->calendar;
	}
}