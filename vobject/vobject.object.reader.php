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

class VObjectObjectReader extends VObject {

	private $vobject;
	private $object;

	public function __construct($vobject) {
		if( !($vobject instanceof VEvent) &&
			!($vobject instanceof VJournal) &&
			!($vobject instanceof VTodo) ) {
			throw new VObjectObjectReaderException('Given object is not valid!');
		}

		$object = new Object();
		$object->setObjectURI()
			   ->setStartDate()
			   ->setEndDate()
			   ->setTimezone()
			   ->setRepeating()
			   ->setLastOccurence()
			   ->setSummary()
			   ->setCalendarData($vobject->serialize())
			   ->setLastModified()
			   ->setETag();

		if($vobject instanceof VEvent) {
			$object->setType(ObjectType::EVENT);
		}
		if($vobject instanceof VJournal) {
			$object->setType(ObjectType::JOURNAL);			
		}
		if($vobject instanceof VTodo) {
			$object->setType(ObjectType::TODO);
		}

		$this->object = $object;
	}

	public function getObject() {
		return $this->object;
	}
}