<?php
/**
 * Copyright (c) 2011, 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * Copyright (c) 2014 Michał "rysiek" Woźniak <rysiek@hackerspace.pl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

// is a user logged-in?
if (OCP\User::isLoggedIn()) {

  // is the app enabled?
  OCP\JSON::checkAppEnabled('calendar');
  session_write_close();

  // Look for the calendar id
  $calendar_id = null;
  if (strval(intval($_GET['calendar_id'])) == strval($_GET['calendar_id'])) { // integer for sure.
    $id = intval($_GET['calendar_id']);
    $calendarrow = OC_Calendar_App::getCalendar($id, true, false); // Let's at least security check otherwise we might as well use OC_Calendar_Calendar::find())
    if($calendarrow !== false) {
      $calendar_id = $id;
    }else{
      if(OCP\Share::getItemSharedWithBySource('calendar', $id) === false){
        OCP\JSON::encodedPrint(array());
        exit;
      }
    }
  }
  $calendar_id = (is_null($calendar_id)?strip_tags($_GET['calendar_id']):$calendar_id);

// no logged-in user? ookaay, do we have a token?
} elseif (\OC::$session->exists('public_link_token')) {

  // is the app enabled?
  OCP\JSON::checkAppEnabled('calendar');
  session_write_close();

  // shareapi enabled?
  if (\OC_Appconfig::getValue('core', 'shareapi_allow_links', 'yes') !== 'yes') {
    header('HTTP/1.0 404 Not Found');
    exit();
  }
  
  // check if we're being asked for something we can provide
  if ($_GET['calendar_id'] !== 'shared_events') {
    header('HTTP/1.0 404 Not Found');
    exit();
  }

  // get the data
  $linkItem = OCP\Share::getShareByToken(
    \OC::$session->get('public_link_token')
  );

  // did we get anything?
  if (!is_array($linkItem) || !isset($linkItem['uid_owner'])) {
    // nope! chuck testa!
    header('HTTP/1.0 404 Not Found');
    exit();
  }

  // resolve all the re-shares
  $rootLinkItem = OCP\Share::resolveReShare($linkItem);
  
  // did we get anything?
  if (!is_array($rootLinkItem) || !isset($rootLinkItem['uid_owner'])) {
    // nope! chuck testa!
    header('HTTP/1.0 404 Not Found');
    exit();
  }
  
  // do we have a password on this share?
  if (isset($linkItem['share_with'])) {
    // we're not going to check the password here, we're in AJAX mode
    // what we can do is to check for 'public_link_authenticated' session var
    if ( ! \OC::$session->exists('public_link_authenticated')
        || \OC::$session->get('public_link_authenticated') !== $linkItem['id']
      ) {
        header('HTTP/1.0 401 Unauthorized');
        exit();
      }
  }

  // just another check
  if (!OC_Calendar_App::getCalendar($rootLinkItem['item_source'], true, true)) {
    header('HTTP/1.0 403 Forbidden');
    exit();
  }

  // finally, get the calendar id
  $calendar_id = $rootLinkItem['item_source'];
  
// no user, no token...
} else {
  header('HTTP/1.0 404 Not Found');
  exit();
}

// data retrieval and formatting
$start = (version_compare(PHP_VERSION, '5.3.0', '>='))?DateTime::createFromFormat('U', $_GET['start']):new DateTime('@' . $_GET['start']);
$end = (version_compare(PHP_VERSION, '5.3.0', '>='))?DateTime::createFromFormat('U', $_GET['end']):new DateTime('@' . $_GET['end']);
$events = OC_Calendar_App::getrequestedEvents($calendar_id, $start, $end);
$output = array();
foreach($events as $event) {
	$result = OC_Calendar_App::generateEventOutput($event, $start, $end);
	if (is_array($result)) {
		$output = array_merge($output, $result);
	}
}

OCP\JSON::encodedPrint($output);
