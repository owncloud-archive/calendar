<?php
/**
 * Copyright (c) 2013 Georg Ehrke <developer at georgehrke dot com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Backend;

class WebCal extends Backend {

	public function findCalendar($calid = ''){
		$source = \OCA\Calendar\Source::find($calid);
		
		return false;
	}

	public function getCalendars($userid, $rw){
		return array();
	}

	public function findObject($uri, $uid){
		return false;
	}

	public function getObjects($calid){
		return array();
	}
	
	public function getCustomAttributes(){
		return array('url');
	}
}