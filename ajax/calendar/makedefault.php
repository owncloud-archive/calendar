<?php
/**
 * Copyright (c) 2014 Volkan Gezer <volkangezer at gmail dot com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */


OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('calendar');
OCP\JSON::callCheck();

$cal = $_POST["calendarid"];

try {
	$del = OC_Calendar_Calendar::makeDefault($cal);
	if($del == true) {
		OCP\JSON::success();
	}else{
		OCP\JSON::error(array('error'=>'dberror'));
	}
} catch(Exception $e) {
	OCP\JSON::error(array('message'=>$e->getMessage()));
	exit;
}
