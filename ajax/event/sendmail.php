<?php
/**
 * Copyright (c) 2013 Visitha Baddegama <visithauom@gmail.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */


OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('calendar');
OCP\JSON::callCheck();

$id = $_POST['id'];
$eventId = $_POST['eventId'];
$location = $_POST['location'];
$description = $_POST['description'];
$dtstart = $_POST['dtstart'];
$dtend = $_POST['dtend'];

try {
	OC_Calendar_App::sendEmails($eventId, $location, $description, $dtstart, $dtend);
} catch(Exception $e) {
	OCP\JSON::error(array('data' => array('message'=>$e->getMessage())));
	exit;
}

OCP\JSON::success();
