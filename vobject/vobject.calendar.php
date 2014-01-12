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

class VObjectCalendar extends VObject {

	private $vcalendar;
	private $calendar;

	public function __construct($calendar) {
		if( !($calendar instaceof Calendar)) {
			throw new VObjectObjectException('given object is not an instance of \OCA\Calendar\Db\Calendar');
		}

		try {
			$vcalendar = Reader::read();
		} catch(Exception $ex) {
			throw new VObjectObjectException('Error parsing calendar object: "' . $ex->getMessage() . '"');
		}

		$this->vcalendar = $vcalendar;
	}

	public function getVCalendar() {
		return $this->vcalendar;
	}
}