<?php
//set classpath for every single class
OC::$CLASSPATH['OCA\Calendar'] = 'apps/calendar/lib/calendar.php';
OC::$CLASSPATH['OCA\Calendar\Attendees'] = 'apps/calendar/lib/attendees.php';
OC::$CLASSPATH['OCA\Calendar\Export'] = 'apps/calendar/lib/export.php';
OC::$CLASSPATH['OCA\Calendar\Hooks'] = 'apps/calendar/lib/hooks.php';
OC::$CLASSPATH['OCA\Calendar\Import'] = 'apps/calendar/lib/import.php';
OC::$CLASSPATH['OCA\Calendar\Recurrence'] = 'apps/calendar/lib/recurrence.php';
OC::$CLASSPATH['OCA\Calendar\Reminder'] = 'apps/calendar/lib/reminder.php';
OC::$CLASSPATH['OCA\Calendar\Repeat'] = 'apps/calendar/lib/repeat.php';
OC::$CLASSPATH['OCA\Calendar\Search'] = 'apps/calendar/lib/search.php';
OC::$CLASSPATH['OCA\Calendar\Util'] = 'apps/calendar/lib/util.php';
//load some backend stuff
OC::$CLASSPATH['OCA\Calendar\Backend\CalendarInterface'] = 'apps/calendar/lib/backends/interface.php';
OC::$CLASSPATH['OCA\Calendar\Backend\Backend'] = 'apps/calendar/lib/backends/backend.php';
//set classpath for every backend
OC::$CLASSPATH['OCA\Calendar\Backend\Database'] = 'apps/calendar/lib/backends/database.php';
OC::$CLASSPATH['OCA\Calendar\Backend\LocalStorage'] = 'apps/calendar/lib/backends/localstorage.php';
OC::$CLASSPATH['OCA\Calendar\Backend\Share'] = 'apps/calendar/lib/backends/share.php';
OC::$CLASSPATH['OCA\Calendar\Backend\WebCal'] = 'apps/calendar/lib/backends/webcal.php';
//other classes
OC::$CLASSPATH['OC_Connector_Sabre_CalDAV'] = 'apps/calendar/lib/sabre/backend.php';
OC::$CLASSPATH['OC_Connector_Sabre_CalDAV_CalendarRoot'] = 'apps/calendar/lib/sabre/calendarroot.php';
OC::$CLASSPATH['OC_Connector_Sabre_CalDAV_UserCalendars'] = 'apps/calendar/lib/sabre/usercalendars.php';
OC::$CLASSPATH['OC_Connector_Sabre_CalDAV_Calendar'] = 'apps/calendar/lib/sabre/calendar.php';
OC::$CLASSPATH['OC_Connector_Sabre_CalDAV_CalendarObject'] = 'apps/calendar/lib/sabre/object.php';
OC::$CLASSPATH['OC_Share_Backend_Calendar'] = 'apps/calendar/lib/share/calendar.php';
OC::$CLASSPATH['OC_Share_Backend_Event'] = 'apps/calendar/lib/share/event.php';
//register preinstalled calendar backends
require_once('backends.php');
//init some basic variables
OCA\Calendar\Util::$l10n = new OC_L10N('calendar');
OCA\Calendar\Util::$tz = OCP\Config::getUserValue(OCP\User::getUser(), 'calendar', 'timezone', date_default_timezone_get());
//general hooks
require_once('hooks.php');
//load always needed scripts and styles
OCP\Util::addscript('calendar','loader');
OCP\Util::addscript("3rdparty", "chosen/chosen.jquery.min");
OCP\Util::addStyle("3rdparty", "chosen/chosen");
OCP\Util::addStyle('3rdparty/miniColors', 'jquery.miniColors');
OCP\Util::addscript('3rdparty/miniColors', 'jquery.miniColors.min');
//add navigation entry for calendar app
OCP\App::addNavigationEntry( array(
  'id' => 'calendar_index',
  'order' => 10,
  'href' => OCP\Util::linkTo( 'calendar', 'index.php' ),
  'icon' => OCP\Util::imagePath( 'calendar', 'icon.svg' ),
  'name' => OCA\Calendar\Util::$l10n->t('Calendar')));
//register for ownCloud's search and sharing
OC_Search::registerProvider('OC_Search_Provider_Calendar');
OCP\Share::registerBackend('calendar', 'OC_Share_Backend_Calendar');
OCP\Share::registerBackend('event', 'OC_Share_Backend_Event');