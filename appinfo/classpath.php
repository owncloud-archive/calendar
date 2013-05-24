<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
//!DI Container
\OC::$CLASSPATH['OCA\Calendar\DIContainer'] = 'calendar/dependencyinjection/dicontainer.php';

//!Controllers
\OC::$CLASSPATH['OCA\Calendar\Controller\Backend']	= 'calendar/controllers/backend.controller.php';
\OC::$CLASSPATH['OCA\Calendar\Controller\Calendar']	= 'calendar/controllers/calendar.controller.php';
\OC::$CLASSPATH['OCA\Calendar\Controller\Object']	= 'calendar/controllers/object.controller.php';
\OC::$CLASSPATH['OCA\Calendar\Controller\Settings']	= 'calendar/controllers/settings.controller.php';
\OC::$CLASSPATH['OCA\Calendar\Controller\View']		= 'calendar/controllers/view.controller.php';

//!Mappers
\OC::$CLASSPATH['OCA\Calendar\Db\BackendMapper']			= 'calendar/db/backendmapper.php';
\OC::$CLASSPATH['OCA\Calendar\Db\CalendarCacheMapper']	= 'calendar/db/calendarcachemapper.php';
\OC::$CLASSPATH['OCA\Calendar\Db\ObjectCacheMapper']		= 'calendar/db/objectcachemapper.php';

//!Item classes
/*\OC::$CLASSPATH['OCA\Calendar\Backend\Item']	= 'calendar/lib/backend.php';
\OC::$CLASSPATH['OCA\Calendar\Calendar\Item']	= 'calendar/lib/calendar.php';
\OC::$CLASSPATH['OCA\Calendar\Object\Item']		= 'calendar/lib/object.php';*/

\OC::$CLASSPATH['OCA\Calendar\Db\ObjectType'] 	= 'calendar/db/objecttype.php';

//!Backends
\OC::$CLASSPATH['OCA\Calendar\Backend\Backend']				= 'calendar/lib/backends/backend.php';
\OC::$CLASSPATH['OCA\Calendar\Backend\CalendarInterface']	= 'calendar/lib/backends/interface.php';
\OC::$CLASSPATH['OCA\Calendar\Backend\Database']			= 'calendar/lib/backends/database.php';
\OC::$CLASSPATH['OCA\Calendar\Backend\LocalStorage']		= 'calendar/lib/backends/localstorage.php';
\OC::$CLASSPATH['OCA\Calendar\Backend\Share']				= 'calendar/lib/backends/share.php';
\OC::$CLASSPATH['OCA\Calendar\Backend\WebCal']				= 'calendar/lib/backends/webcal.php';

//!Export & Import
\OC::$CLASSPATH['OCA\Calendar\Export'] = 'calendar/lib/helper/export.php';
\OC::$CLASSPATH['OCA\Calendar\Import'] = 'calendar/lib/helper/import.php';

//!Helper classes
\OC::$CLASSPATH['OCA\Calendar\Attendees']	= 'calendar/lib/helper/attendees.php';
\OC::$CLASSPATH['OCA\Calendar\Hooks']		= 'calendar/lib/helper/hooks.php';
\OC::$CLASSPATH['OCA\Calendar\Recurrence']	= 'calendar/lib/helper/recurrence.php';
\OC::$CLASSPATH['OCA\Calendar\Reminder']	= 'calendar/lib/helper/reminder.php';
\OC::$CLASSPATH['OCA\Calendar\Repeat']		= 'calendar/lib/helper/repeat.php';
\OC::$CLASSPATH['OCA\Calendar\Util']		= 'calendar/lib/helper/util.php';

//!Implementations

//!~ of SabreDAV
\OC::$CLASSPATH['OCA\Calendar\Connector\Sabre_CalDAV']					= 'calendar/lib/sabre/backend.php';
\OC::$CLASSPATH['OCA\Calendar\Connector\Sabre_CalDAV_CalendarRoot']		= 'calendar/lib/sabre/calendarroot.php';
\OC::$CLASSPATH['OCA\Calendar\Connector\Sabre_CalDAV_UserCalendars']	= 'calendar/lib/sabre/usercalendars.php';
\OC::$CLASSPATH['OCA\Calendar\Connector\Sabre_CalDAV_Calendar']			= 'calendar/lib/sabre/calendar.php';
\OC::$CLASSPATH['OCA\Calendar\Connector\Sabre_CalDAV_CalendarObject']	= 'calendar/lib/sabre/object.php';

//!~ of searching
\OC::$CLASSPATH['OCA\Calendar\Search'] = 'calendar/lib/search/search.php';

//!~ of sharing
\OC::$CLASSPATH['OCA\Calendar\Share\Calendar']	= 'calendar/lib/share/calendar.php';
\OC::$CLASSPATH['OCA\Calendar\Share\Object']	= 'calendar/lib/share/object.php';

//!3rdparty
\OC::$CLASSPATH['Sabre\VObject\Component'] = 'calendar/3rdparty/php/VObject/Component.php';