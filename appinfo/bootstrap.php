<?php
/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar;
//set classpath
\OC::$CLASSPATH['OCA\Calendar'] = 'apps/calendar/lib/calendar.php';
//backend classes
//\OCA\Calendar\Util::registerEnabledBackends();
\OC::$CLASSPATH['OCA\Calendar\Backend\Backend'] = 'apps/calendar/lib/backends/backend.php';
\OC::$CLASSPATH['OCA\Calendar\Backend\CalendarInterface'] = 'apps/calendar/lib/backends/interface.php';
\OC::$CLASSPATH['OCA\Calendar\Backend\Database'] = 'apps/calendar/lib/backends/database.php';
\OC::$CLASSPATH['OCA\Calendar\Backend\LocalStorage'] = 'apps/calendar/lib/backends/localstorage.php';
\OC::$CLASSPATH['OCA\Calendar\Backend\Share'] = 'apps/calendar/lib/backends/share.php';
\OC::$CLASSPATH['OCA\Calendar\Backend\WebCal'] = 'apps/calendar/lib/backends/webcal.php';
//export and import
\OC::$CLASSPATH['OCA\Calendar\Export'] = 'apps/calendar/lib/helper/export.php';
\OC::$CLASSPATH['OCA\Calendar\Import'] = 'apps/calendar/lib/helper/import.php';
//helper classes
\OC::$CLASSPATH['OCA\Calendar\Attendees'] = 'apps/calendar/lib/helper/attendees.php';
\OC::$CLASSPATH['OCA\Calendar\Hooks'] = 'apps/calendar/lib/helper/hooks.php';
\OC::$CLASSPATH['OCA\Calendar\Recurrence'] = 'apps/calendar/lib/helper/recurrence.php';
\OC::$CLASSPATH['OCA\Calendar\Reminder'] = 'apps/calendar/lib/helper/reminder.php';
\OC::$CLASSPATH['OCA\Calendar\Repeat'] = 'apps/calendar/lib/helper/repeat.php';
\OC::$CLASSPATH['OCA\Calendar\Search'] = 'apps/calendar/lib/search/search.php';
\OC::$CLASSPATH['OCA\Calendar\Util'] = 'apps/calendar/lib/helper/util.php';
//sabredav implementation
\OC::$CLASSPATH['OC_Connector_Sabre_CalDAV'] = 'apps/calendar/lib/sabre/backend.php';
\OC::$CLASSPATH['OC_Connector_Sabre_CalDAV_CalendarRoot'] = 'apps/calendar/lib/sabre/calendarroot.php';
\OC::$CLASSPATH['OC_Connector_Sabre_CalDAV_UserCalendars'] = 'apps/calendar/lib/sabre/usercalendars.php';
\OC::$CLASSPATH['OC_Connector_Sabre_CalDAV_Calendar'] = 'apps/calendar/lib/sabre/calendar.php';
\OC::$CLASSPATH['OC_Connector_Sabre_CalDAV_CalendarObject'] = 'apps/calendar/lib/sabre/object.php';
//sharing implementation
\OC::$CLASSPATH['OC_Share_Backend_Calendar'] = 'apps/calendar/lib/share/calendar.php';
\OC::$CLASSPATH['OC_Share_Backend_Event'] = 'apps/calendar/lib/share/event.php';
//objects for internel information interchange
\OC::$CLASSPATH['OCA\Calendar\Objects\Calendar'] = 'apps/calendar/lib/objects/calendar.php';
\OC::$CLASSPATH['OCA\Calendar\Objects\Event'] = 'apps/calendar/lib/objects/event.php';
\OC::$CLASSPATH['OCA\Calendar\Objects\Journal'] = 'apps/calendar/lib/objects/journal.php';
\OC::$CLASSPATH['OCA\Calendar\Objects\Todo'] = 'apps/calendar/lib/objects/todo.php';

\OC::$CLASSPATH['Sabre\VObject\Component'] = 'apps/calendar/3rdparty/VObject/Component.php';

//register for hooks
\OCP\Util::connectHook('OC_User', 'post_createUser', 'OC_Calendar_Hooks', 'createUser');
\OCP\Util::connectHook('OC_User', 'post_deleteUser', 'OC_Calendar_Hooks', 'deleteUser');
//calendar internal hooks
\OCP\Util::connectHook('OC_Calendar', 'addEvent', 'OC_Calendar_Repeat', 'generate');
\OCP\Util::connectHook('OC_Calendar', 'editEvent', 'OC_Calendar_Repeat', 'update');
\OCP\Util::connectHook('OC_Calendar', 'deleteEvent', 'OC_Calendar_Repeat', 'clean');
\OCP\Util::connectHook('OC_Calendar', 'moveEvent', 'OC_Calendar_Repeat', 'update');
\OCP\Util::connectHook('OC_Calendar', 'deleteCalendar', 'OC_Calendar_Repeat', 'cleanCalendar');

//load some javascript
\OCP\Util::addScript('calendar', 'fileactions');

//register search backend
\OC_Search::registerProvider('OC_Search_Provider_Calendar');
//\OC_Search::registerProvider('\OCA\Calendar\SearchProvider');

//register sharing backend
\OCP\Share::registerBackend('calendar', 'OC_Share_Backend_Calendar');
\OCP\Share::registerBackend('event', 'OC_Share_Backend_Event');
//\OCP\Share::registerBackend('calendar', '\OCA\Calendar\Share\Calendar');
//\OCP\Share::registerBackend('event', '\OCA\Calendar\Share\Event');

//register all the shipped backends
\OCA\Calendar::registerBackend('database', '\OCA\Calendar\Backend\Database');
//\OCA\Calendar::registerBackend('localstorage', '\OCA\Calendar\Backend\LocalStorage');
//\OCA\Calendar::registerBackend('share', '\OCA\Calendar\Backend\Share');
\OCA\Calendar::registerBackend('webcal', '\OCA\Calendar\Backend\WebCal');
//\OCA\Calendar::registerBackend('caldav', ...);
//\OCA\Calendar::registerBackend('activesync', ...);

//setup all registered calendar backends
\OCA\Calendar::setupBackends();