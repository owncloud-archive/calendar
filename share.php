<?php
/**
 * Copyright (c) 2014 Michał "rysiek" Woźniak <rysiek@hackerspace.pl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

OCP\App::checkAppEnabled('calendar');

if (\OC_Appconfig::getValue('core', 'shareapi_allow_links', 'yes') !== 'yes') {
  header('HTTP/1.0 404 Not Found');
  $tmpl = new OCP\Template('', '404', 'guest');
  $tmpl->printPage();
  exit();
}

if (isset($_GET['t'])) {
  $token = $_GET['t'];
  $linkItem = OCP\Share::getShareByToken($token);
  //var_dump($linkItem);
  if (is_array($linkItem) && isset($linkItem['uid_owner'])) {
    // seems to be a valid share
    $rootLinkItem = OCP\Share::resolveReShare($linkItem);
  }
}
/*echo '<pre style="background:white; color:black">';
var_dump($linkItem);
var_dump($rootLinkItem);*/
// apparently, we have something to work with
if (isset($rootLinkItem)) {

  // is there a type?
  if (!isset($linkItem['item_type'])) {
    // nope -> 404
    OCP\Util::writeLog('share', 'No item type set for share id: ' . $linkItem['id'], \OCP\Util::ERROR);
    header('HTTP/1.0 404 Not Found');
    $tmpl = new OCP\Template('', '404', 'guest');
    $tmpl->printPage();
    exit();
  }
  
  // the full URL
  $url = OCP\Util::linkToPublic('files') . '&t=' . $token;
  // let's set the token in the session for further reference
  \OC::$session->set('public_link_token', $token);

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
          OCP\Util::addStyle('calendar', 'authenticate');
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
        header('HTTP/1.0 404 Not Found');
        $tmpl = new OCP\Template('', '404', 'guest');
        $tmpl->printPage();
        exit();
      }

    } else {
      // Check if item id is set in session
      if ( ! \OC::$session->exists('public_link_authenticated')
        || \OC::$session->get('public_link_authenticated') !== $linkItem['id']
      ) {
        // Prompt for password
        OCP\Util::addStyle('calendar', 'authenticate');
        $tmpl = new OCP\Template('calendar', 'authenticate', 'guest');
        $tmpl->assign('URL', $url);
        $tmpl->printPage();
        exit();
      }
    }
  }

  // Download the item
  if (isset($_GET['download'])) {
    $calendar = OC_Calendar_App::getCalendar($rootLinkItem['item_source'], true, true);
    if(!$calendar) {
      OCP\Util::writeLog('share', 'forbidden!', \OCP\Util::ERROR);
      header('HTTP/1.0 403 Forbidden');
      exit;
    }
    header('Content-Type: text/calendar');
    header('Content-Disposition: inline; filename=' . str_replace(' ', '-', $calendar['displayname']) . '.ics');
    echo OC_Calendar_Export::export($rootLinkItem['item_source'], OC_Calendar_Export::CALENDAR);
    exit();
   
  // Display the item
  } else {
    OCP\Util::addscript('calendar/3rdparty/fullcalendar', 'fullcalendar');
    OCP\Util::addStyle('calendar/3rdparty/fullcalendar', 'fullcalendar');
    OCP\Util::addscript('3rdparty/timepicker', 'jquery.ui.timepicker');
    OCP\Util::addStyle('3rdparty/timepicker', 'jquery.ui.timepicker');
    OCP\Util::addscript('calendar', 'calendar');
    OCP\Util::addStyle('calendar', 'style');
    OCP\Util::addscript('', 'jquery.multiselect');
    OCP\Util::addStyle('', 'jquery.multiselect');
    OCP\Util::addscript('calendar','jquery.multi-autocomplete');
    OCP\Util::addscript('','tags');
    OCP\Util::addscript('calendar','on-event');
    OCP\App::setActiveNavigationEntry('calendar_index');
    $tmpl = new OCP\Template('calendar', 'calendar', 'user');
    $tmpl->printPage();
  }
  exit();
} else {
  OCP\Util::writeLog('share', 'could not resolve linkItem', \OCP\Util::DEBUG);
}

$errorTemplate = new OCP\Template('calendar', 'part.404', '');
$errorContent = $errorTemplate->fetchPage();

header('HTTP/1.0 404 Not Found');
OCP\Util::addStyle('calendar', '404');
$tmpl = new OCP\Template('', '404', 'guest');
$tmpl->assign('content', $errorContent);
$tmpl->printPage();