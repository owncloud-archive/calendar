<?php
$l = OCP\Util::getL10N('calendar');
OC::$CLASSPATH['OC_Calendar_App'] = 'calendar/lib/app.php';
OC::$CLASSPATH['OC_Calendar_Calendar'] = 'calendar/lib/calendar.php';
OC::$CLASSPATH['OC_Calendar_Object'] = 'calendar/lib/object.php';
OC::$CLASSPATH['OC_Calendar_Hooks'] = 'calendar/lib/hooks.php';
OC::$CLASSPATH['OC_Calendar_Repeat'] = 'calendar/lib/repeat.php';
OC::$CLASSPATH['OC_Calendar_Export'] = 'calendar/lib/export.php';
OC::$CLASSPATH['OC_Calendar_Import'] = 'calendar/lib/import.php';
OC::$CLASSPATH['OC_Share_Backend_Calendar'] = 'calendar/lib/share/calendar.php';
OC::$CLASSPATH['OC_Share_Backend_Event'] = 'calendar/lib/share/event.php';
//General Hooks
OCP\Util::connectHook('OC_User', 'post_createUser', 'OC_Calendar_Hooks', 'createUser');
OCP\Util::connectHook('OC_User', 'post_deleteUser', 'OC_Calendar_Hooks', 'deleteUser');
//Repeating Events Hooks
OCP\Util::connectHook('OC_Calendar', 'addEvent', 'OC_Calendar_Repeat', 'generate');
OCP\Util::connectHook('OC_Calendar', 'editEvent', 'OC_Calendar_Repeat', 'update');
OCP\Util::connectHook('OC_Calendar', 'deleteEvent', 'OC_Calendar_Repeat', 'clean');
OCP\Util::connectHook('OC_Calendar', 'moveEvent', 'OC_Calendar_Repeat', 'update');
OCP\Util::connectHook('OC_Calendar', 'deleteCalendar', 'OC_Calendar_Repeat', 'cleanCalendar');

OCP\Util::addscript('calendar','loader');
OCP\Util::addScript('calendar/3rdparty/chosen', 'chosen.jquery.min');
OCP\Util::addStyle('calendar/3rdparty/chosen', 'chosen');
OCP\Util::addStyle('calendar/3rdparty/miniColors', 'jquery.miniColors');
OCP\Util::addscript('calendar/3rdparty/miniColors', 'jquery.miniColors.min');
OCP\App::addNavigationEntry( array(
  'id' => 'calendar_index',
  'order' => 10,
  'href' => OCP\Util::linkToRoute('calendar_index'),
  'icon' => OCP\Util::imagePath( 'calendar', 'calendar.svg' ),
  'name' => $l->t('Calendar')));
\OC_Search::registerProvider('OCA\Calendar\Search\Provider');
OCP\Share::registerBackend('calendar', 'OC_Share_Backend_Calendar');
OCP\Share::registerBackend('event', 'OC_Share_Backend_Event');

Sabre\VObject\Property::$classMap['SUMMARY'] = 'OC\VObject\StringProperty';
Sabre\VObject\Property::$classMap['DESCRIPTION'] = 'OC\VObject\StringProperty';
Sabre\VObject\Property::$classMap['LOCATION'] = 'OC\VObject\StringProperty';
