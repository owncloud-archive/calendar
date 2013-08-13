<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
//DI Container
\OC::$CLASSPATH['OCA\Calendar\DependencyInjection\DIContainer'] = 'calendar/appinfo/dicontainer.php';
//Controllers
\OC::$CLASSPATH['OCA\Calendar\Controller\BackendController']	= 'calendar/controllers/backend.php';
\OC::$CLASSPATH['OCA\Calendar\Controller\CalendarController']	= 'calendar/controllers/calendar.php';
\OC::$CLASSPATH['OCA\Calendar\Controller\ObjectController']		= 'calendar/controllers/object.php';
\OC::$CLASSPATH['OCA\Calendar\Controller\ObjectTypeController']	= 'calendar/controllers/objecttype.php';
\OC::$CLASSPATH['OCA\Calendar\Controller\EventsController']		= 'calendar/controllers/event.php';
\OC::$CLASSPATH['OCA\Calendar\Controller\JournalsController']	= 'calendar/controllers/journal.php';
\OC::$CLASSPATH['OCA\Calendar\Controller\TodosController']		= 'calendar/controllers/todo.php';
\OC::$CLASSPATH['OCA\Calendar\Controller\ViewController']		= 'calendar/controllers/view.php';
//Business Layer
\OC::$CLASSPATH['OCA\Calendar\BusinessLayer\BackendBusinessLayer']	= 'calendar/businesslayer/backend.php';
\OC::$CLASSPATH['OCA\Calendar\BusinessLayer\CalendarBusinessLayer']	= 'calendar/businesslayer/calendar.php';
\OC::$CLASSPATH['OCA\Calendar\BusinessLayer\ObjectBusinessLayer']	= 'calendar/businesslayer/object.php';
//Mappers
\OC::$CLASSPATH['OCA\Calendar\Db\BackendMapper']	= 'calendar/db/mapper.backend.php';
\OC::$CLASSPATH['OCA\Calendar\Db\CalendarMapper']	= 'calendar/db/mapper.calendar.php';
\OC::$CLASSPATH['OCA\Calendar\Db\ObjectMapper']		= 'calendar/db/mapper.object.php';
//Items
\OC::$CLASSPATH['OCA\Calendar\Db\Backend']	= 'calendar/db/entity.backend.php';
\OC::$CLASSPATH['OCA\Calendar\Db\Calendar']	= 'calendar/db/entity.calendar.php';
\OC::$CLASSPATH['OCA\Calendar\Db\Object']	= 'calendar/db/entity.object.php';
//JSON Items
\OC::$CLASSPATH['OCA\Calendar\Db\JSON']			= 'calendar/db/json.php';
\OC::$CLASSPATH['OCA\Calendar\Db\JSONCalendar']	= 'calendar/db/json.calendar.php';
\OC::$CLASSPATH['OCA\Calendar\Db\JSONObject']	= 'calendar/db/json.object.php';
//JSON Reader
\OC::$CLASSPATH['OCA\Calendar\Db\JSONCalendarReader']	= 'calendar/db/json.calendar.reader.php';
\OC::$CLASSPATH['OCA\Calendar\Db\JSONObjectReader']		= 'calendar/db/json.object.reader.php';
//consts
\OC::$CLASSPATH['OCA\Calendar\Db\ObjectType'] 	= 'calendar/db/types.object.php';
\OC::$CLASSPATH['OCA\Calendar\Db\Permissions'] 	= 'calendar/db/types.cruds.php';
//Exceptions
\OC::$CLASSPATH['OCA\Calendar\BusinessLayer\BusinessLayerException']	= 'calendar/exception/businesslayer.php';
\OC::$CLASSPATH['OCA\Calendar\Backend\BackendException']				= 'calendar/exception/backend.php';
\OC::$CLASSPATH['OCA\Calendar\Backend\DoesNotImplementException']		= 'calendar/exception/doesnotimplement.php';
//Backends
\OC::$CLASSPATH['OCA\Calendar\Backend\Backend']				= 'calendar/backends/backend.php';
\OC::$CLASSPATH['OCA\Calendar\Backend\CalendarInterface']	= 'calendar/backends/interface.php';
\OC::$CLASSPATH['OCA\Calendar\Backend\Anniversary']			= 'calendar/backends/anniversary.php';
\OC::$CLASSPATH['OCA\Calendar\Backend\Birthday']			= 'calendar/backends/birthday.php';
\OC::$CLASSPATH['OCA\Calendar\Backend\Local']				= 'calendar/backends/local.php';
\OC::$CLASSPATH['OCA\Calendar\Backend\LocalStorage']		= 'calendar/backends/localstorage.php';
\OC::$CLASSPATH['OCA\Calendar\Backend\Share']				= 'calendar/backends/share.php';
\OC::$CLASSPATH['OCA\Calendar\Backend\WebCal']				= 'calendar/backends/webcal.php';
//Implementation of SabreDAV
\OC::$CLASSPATH['OCA\Calendar\Connector\Sabre_CalDAV']					= 'calendar/lib/sabre/backend.php';
\OC::$CLASSPATH['OCA\Calendar\Connector\Sabre_CalDAV_CalendarRoot']		= 'calendar/lib/sabre/calendarroot.php';
\OC::$CLASSPATH['OCA\Calendar\Connector\Sabre_CalDAV_UserCalendars']	= 'calendar/lib/sabre/usercalendars.php';
\OC::$CLASSPATH['OCA\Calendar\Connector\Sabre_CalDAV_Calendar']			= 'calendar/lib/sabre/calendar.php';
\OC::$CLASSPATH['OCA\Calendar\Connector\Sabre_CalDAV_CalendarObject']	= 'calendar/lib/sabre/object.php';
//Implementation of searching
\OC::$CLASSPATH['OCA\Calendar\Search'] = 'calendar/lib/search/search.php';
//Implementation of sharing
\OC::$CLASSPATH['OCA\Calendar\Share\Calendar']	= 'calendar/lib/share/calendar.php';
\OC::$CLASSPATH['OCA\Calendar\Share\Object']	= 'calendar/lib/share/object.php';
//3rdparty
\OC::$CLASSPATH['Sabre\VObject\Component'] = 'calendar/3rdparty/php/VObject/Component.php';