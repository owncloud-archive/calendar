<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Db;

use \OCA\Calendar\AppFramework\Db\Entity;

class Timezone extends Entity {

	public $id;
	public $tzid;
	public $tzurl;
	public $lastModified;
	public $standard = array();
	public $daylight = array();
	public $comment;
	public $type;
	public $x = array();

	public function __construct($from) {
		
	}

}

class TimezoneProperties extends Entity{
	public $dtstart;
	public $rrule;
	public $rdate;
	public $tzoffsetfrom;
	public $tzoffsetto;
	public $tzname;
	public $x = array();
}

class Daylight extends TimezoneProperties{};
class Standard extends TimezoneProperties{};