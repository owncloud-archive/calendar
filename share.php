<?php
/**
 * Copyright (c) 2014 Michał "rysiek" Woźniak <rysiek@hackerspace.pl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

OCP\App::checkAppEnabled('calendar');

function calendar404($msg=null) {
  $errorTemplate = new OCP\Template('calendar', 'part.404', '');
  if ($msg !== null) $errorTemplate->assign('message', $msg);
  $errorContent = $errorTemplate->fetchPage();

  header('HTTP/1.0 404 Not Found');
  $tmpl = new OCP\Template('', '404', 'guest');
  $tmpl->assign('content', $errorContent);
  $tmpl->printPage();
  exit();
}

function calendar403() {
  header('HTTP/1.0 403 Forbidden');
  $tmpl = new OCP\Template('', '403', 'guest');
  $tmpl->printPage();
  exit();
}

if (\OC_Appconfig::getValue('core', 'shareapi_allow_links', 'yes') !== 'yes')
  calendar404('Link-sharing is disabled by admin.');

if (isset($_GET['t'])) {
  $token = $_GET['t'];
  $linkItem = OCP\Share::getShareByToken($token, false);
  if (is_array($linkItem) && isset($linkItem['uid_owner'])) {
    // seems to be a valid share
    $rootLinkItem = OCP\Share::resolveReShare($linkItem);
  }
}

// apparently, we have something to work with
if (isset($rootLinkItem)) {

  // is there a type?
  if (!isset($linkItem['item_type'])) {
    // nope -> 404
    OCP\Util::writeLog('share', 'No item type set for share id: ' . $linkItem['id'], \OCP\Util::ERROR);
    calendar404('No such share.');
  }
  
  // the full URL
  $url = OCP\Util::linkToPublic('calendar') . '&t=' . $token;
  // let's set the token in the session for further reference
  \OC::$session->set('public_link_token', $token);
  \OC::$session->set('public_link_owner', $linkItem['uid_owner']);

  // do we have a password on this share?
  if (isset($linkItem['share_with'])) {

    // do we have a password in POST?
    if (isset($_POST['password'])) {
    
      // cool, let's use it, shall we?
      $password = $_POST['password'];
      
      // there is a single SHARE_TYPE that is of use here: SHARE_TYPE_LINK
      if ($linkItem['share_type'] == OCP\Share::SHARE_TYPE_LINK) {

        // Check Password
        $hasher = new PasswordHash(8, (CRYPT_BLOWFISH != 1));
        // does the password match?
        if (!($hasher->CheckPassword($password.OC_Config::getValue('passwordsalt', ''),
                       $linkItem['share_with']))) {
          // NOPE! Chuck Testa! Log it.
          OCP\Util::writeLog('share', 'Wrong password!', \OCP\Util::ERROR);
          // inform the user
          $tmpl = new OCP\Template('calendar', 'authenticate', 'guest');
          $tmpl->assign('URL', $url);
          $tmpl->assign('wrongpw', true);
          $tmpl->printPage();
          exit();
        } else {
          // Save item id in session for future requests
          \OC::$session->set('public_link_authenticated', $linkItem['id']);
        }

      // this only works for SHARE_TYPE_LINK, hence...
      } else {
        // ...if it is not SHARE_TYPE_LINK, complain!
        OCP\Util::writeLog('share', 'Unknown share type '.$linkItem['share_type']
                       .' for share id '.$linkItem['id'], \OCP\Util::ERROR);
        calendar404('Unknown share type.');
      }

    } else {
      // Check if item id is set in session
      if ( ! \OC::$session->exists('public_link_authenticated')
        || \OC::$session->get('public_link_authenticated') !== $linkItem['id']
      ) {
        // Prompt for password
        $tmpl = new OCP\Template('calendar', 'authenticate', 'guest');
        $tmpl->assign('URL', $url);
        $tmpl->printPage();
        exit();
      }
    }
  }

  // Download the item
  if (isset($_GET['download'])) {
    OCP\Util::writeLog('calendar', __FILE__ . ' : ' . __METHOD__, OCP\Util::ERROR);
    // calendar
    if ($linkItem['item_type'] === 'calendar') {
      OCP\Util::writeLog('calendar', __FILE__ . ' : ' . __METHOD__, OCP\Util::ERROR);
      $data = OC_Calendar_App::getCalendar($rootLinkItem['item_source'], true, true);
      $type = OC_Calendar_Export::CALENDAR;
    // event
    } else {
      OCP\Util::writeLog('calendar', __FILE__ . ' : ' . __METHOD__, OCP\Util::ERROR);
      $data = OC_Calendar_App::getEventObject($rootLinkItem['item_source'], true, true);
      $type = OC_Calendar_Export::EVENT;
    }
    if(!$data) {
      OCP\Util::writeLog('share', 'forbidden!', \OCP\Util::ERROR);
      header('HTTP/1.0 403 Forbidden');
      exit;
    }
    header('Content-Type: text/calendar');
    header('Content-Disposition: inline; filename=' . str_replace(' ', '-', $data['displayname']) . '.ics');
    // export the data
    // if it is a link-shared concrete event, ignore security
    // calendars should be shared *with* security enabled, so as to not divulge private/busy events
    echo OC_Calendar_Export::export($rootLinkItem['item_source'], $type, ($type !== OC_Calendar_Export::EVENT) );
    exit();
   
  // Display the calendar
  } elseif ($linkItem['item_type'] === 'calendar') {
    OCP\Util::addscript('calendar/3rdparty/fullcalendar', 'fullcalendar');
    OCP\Util::addStyle('calendar/3rdparty/fullcalendar', 'fullcalendar');
    OCP\Util::addscript('3rdparty/timepicker', 'jquery.ui.timepicker');
    OCP\Util::addStyle('3rdparty/timepicker', 'jquery.ui.timepicker');
    OCP\Util::addscript('calendar', 'calendar');
    OCP\Util::addStyle('calendar', 'style');
    OCP\Util::addStyle('calendar', 'tooltips');
    OCP\Util::addscript('', 'jquery.multiselect');
    OCP\Util::addStyle('', 'jquery.multiselect');
    OCP\Util::addscript('calendar','jquery.multi-autocomplete');
    OCP\Util::addscript('','tags');
    OCP\Util::addscript('calendar','on-event');
    OCP\Util::addscript('calendar','settings');
    OCP\App::setActiveNavigationEntry('calendar_index');
    $tmpl = new OCP\Template('calendar', 'calendar', 'base');
    $tmpl->assign('link_shared_calendar_name', $linkItem['item_target']);
    $tmpl->assign('link_shared_calendar_owner', $linkItem['uid_owner']);
    $tmpl->assign('link_shared_calendar_url', $url);
    $tmpl->assign('timezone', OC_Calendar_App::$tz);
    $tmpl->assign('timezones',DateTimeZone::listIdentifiers());
    $tmpl->printPage();

  // Display the event
  } elseif ($linkItem['item_type'] === 'event') {
    OCP\Util::addStyle('calendar', 'style');
    OCP\Util::addStyle('calendar', 'tooltips');
    OCP\Util::addscript('calendar','settings');
    OCP\App::setActiveNavigationEntry('calendar_index');
    $tmpl = new OCP\Template('calendar', 'event', 'base');
    $tmpl->assign('link_shared_event', $linkItem);
    $tmpl->assign('link_shared_event_url', $url);
    $tmpl->assign('timezone', OC_Calendar_App::$tz);
    $tmpl->assign('timezones',DateTimeZone::listIdentifiers());
    $tmpl->printPage();
  }
  exit();
} else {
  OCP\Util::writeLog('share', 'could not resolve linkItem', \OCP\Util::DEBUG);
}

calendar404();
