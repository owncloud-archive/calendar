<?php
/**
 * Copyright (c) 2011 Bart Visscher <bartv@thisnet.nl>
 * Copyright (c) 2012 Georg Ehrke <georg@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 *
 * This class manages our app actions
 */
 
OC_Calendar_App::$l10n = OCP\Util::getL10N('calendar');
OC_Calendar_App::$tz = OC_Calendar_App::getTimezone();
class OC_Calendar_App{
	const CALENDAR = 'calendar';
	const EVENT = 'event';
	/**
	 * @brief language object for calendar app
	 */
	public static $l10n;

	/**
	 * @brief categories of the user
	 */
	protected static $categories = null;

	/**
	 * @brief timezone of the user
	 */
	public static $tz;

	/**
	 * @brief returns informations about a calendar
	 * @param int $id - id of the calendar
	 * @param bool $security - check access rights or not
	 * @param bool $shared - check if the user got access via sharing
	 * @return mixed - bool / array
	 */
	public static function getCalendar($id, $security = true, $shared = false) {
		if(! is_numeric($id)) {
			return false;
		}

		$calendar = OC_Calendar_Calendar::find($id);
		// FIXME: Correct arguments to just check for permissions
		if($security === true && $shared === false) {
			if(OCP\User::getUser() === $calendar['userid']){
				return $calendar;
			}else{
				return false;
			}
		}
		if($security === true && $shared === true) {
			if(OCP\Share::getItemSharedWithBySource('calendar', $id)) {
				return $calendar;
			}
		}
		return $calendar;
	}

	/**
	 * @brief returns informations about an event
	 * @param int $id - id of the event
	 * @param bool $security - check access rights or not
	 * @param bool $shared - check if the user got access via sharing
	 * @return mixed - bool / array
	 */
	public static function getEventObject($id, $security = true, $shared = false) {
		if(! is_numeric($id)) {
			return false;
		}
		$event = OC_Calendar_Object::find($id);
		// link-shared event
		if ( ($shared === true) && ($security === true) ) {
			return $event;
		} elseif($shared === true || $security === true) {
			$permissions = self::getPermissions($id, self::EVENT);
			if(self::getPermissions($id, self::EVENT)) {
				return $event;
			}
		} else {
			return $event;
		}

		return false;
	}

	/**
	 * @brief returns the parsed calendar data
	 * @param int $id - id of the event
	 * @param bool $security - check access rights or not
	 * @return mixed - bool / object
	 */
	public static function getVCalendar($id, $security = true, $shared = false) {
		$event_object = self::getEventObject($id, $security, $shared);
		if($event_object === false) {
			return false;
		}
		$vobject = OC_VObject::parse($event_object['calendardata']);
		if(is_null($vobject)) {
			return false;
		}
		return $vobject;
	}

	/**
	 * @brief checks if an event was edited and dies if it was
	 * @param (object) $vevent - vevent object of the event
	 * @param (int) $lastmodified - time of last modification as unix timestamp
	 * @return (bool)
	 */
	public static function isNotModified($vevent, $lastmodified) {
		$last_modified = $vevent->__get('LAST-MODIFIED');
		if($last_modified && $lastmodified != $last_modified->getDateTime()->format('U')) {
			OCP\JSON::error(array('modified'=>true));
			exit;
		}
		return true;
	}

	/**
	 * @brief returns the default categories of ownCloud
	 * @return (array) $categories
	 */
	public static function getDefaultCategories() {
		return array(
			(string)self::$l10n->t('Birthday'),
			(string)self::$l10n->t('Business'),
			(string)self::$l10n->t('Call'),
			(string)self::$l10n->t('Clients'),
			(string)self::$l10n->t('Deliverer'),
			(string)self::$l10n->t('Holidays'),
			(string)self::$l10n->t('Ideas'),
			(string)self::$l10n->t('Journey'),
			(string)self::$l10n->t('Jubilee'),
			(string)self::$l10n->t('Meeting'),
			(string)self::$l10n->t('Other'),
			(string)self::$l10n->t('Personal'),
			(string)self::$l10n->t('Projects'),
			(string)self::$l10n->t('Questions'),
			(string)self::$l10n->t('Work'),
		);
	}

	/**
	 * @brief returns the vcategories object of the user
	 * @return (object) $vcategories
	 */
	public static function getVCategories() {
		if (is_null(self::$categories)) {
			$categories = \OC::$server->getTagManager()->load('event');
			if($categories->isEmpty('event')) {
				self::scanCategories();
			}
			self::$categories = \OC::$server->getTagManager()
				->load('event', self::getDefaultCategories());
		}
		return self::$categories;
	}

	/**
	 * @brief returns the categories of the vcategories object
	 * @return (array) $categories
	 */
	public static function getCategoryOptions() {
		$getNames = function($tag) {
			return $tag['name'];
		};
		$categories = self::getVCategories()->getTags();
		$categories = array_map($getNames, $categories);
		return $categories;
	}

	/**
	 * scan events for categories.
	 * @param $events VEVENTs to scan. null to check all events for the current user.
	 */
	public static function scanCategories($events = null) {
		if (is_null($events)) {
			$calendars = OC_Calendar_Calendar::allCalendars(OCP\USER::getUser());
			if(count($calendars) > 0) {
				$events = array();
				foreach($calendars as $calendar) {
					if($calendar['userid'] === OCP\User::getUser()) {
						$calendar_events = OC_Calendar_Object::all($calendar['id']);
						$events = $events + $calendar_events;
					}
				}
			}
		}
		if(is_array($events) && count($events) > 0) {
			$vcategories = \OC::$server->getTagManager()->load('event');
			$getName = function($tag) {
				return $tag['name'];
			};
			$tags = array_map($getName, $vcategories->getTags());
			$vcategories->delete($tags);
			foreach($events as $event) {
				$vobject = OC_VObject::parse($event['calendardata']);
				if(!is_null($vobject)) {
					$object = null;
					if (isset($calendar->VEVENT)) {
						$object = $calendar->VEVENT;
					} else
					if (isset($calendar->VTODO)) {
						$object = $calendar->VTODO;
					} else
					if (isset($calendar->VJOURNAL)) {
						$object = $calendar->VJOURNAL;
					}
					if ($object && isset($object->CATEGORIES)) {
						$vcategories->addMultiple($object->CATEGORIES->getParts(), true, $event['id']);
					}
				}
			}
		}
	}

	/**
	 * check VEvent for new categories.
	 * @see \OCP\ITags::addMultiple()
	 */
	public static function loadCategoriesFromVCalendar($id, OC_VObject $calendar) {
		$object = null;
		if (isset($calendar->VEVENT)) {
			$object = $calendar->VEVENT;
		} else
		if (isset($calendar->VTODO)) {
			$object = $calendar->VTODO;
		} else
		if (isset($calendar->VJOURNAL)) {
			$object = $calendar->VJOURNAL;
		}
		if ($object && isset($object->CATEGORIES)) {
			self::getVCategories()->addMultiple($object->CATEGORIES->getParts(), true, $id);
		}
	}

 	/**
	 * @brief returns the options for the access class of an event
	 * @return array - valid inputs for the access class of an event
	 */
	public static function getAccessClassOptions() {
		return OC_Calendar_Object::getAccessClassOptions(self::$l10n);
	}

	/**
	 * @brief returns the options for the repeat rule of an repeating event
	 * @return array - valid inputs for the repeat rule of an repeating event
	 */
	public static function getRepeatOptions() {
		return OC_Calendar_Object::getRepeatOptions(self::$l10n);
	}

	/**
	 * @brief returns the options for the end of an repeating event
	 * @return array - valid inputs for the end of an repeating events
	 */
	public static function getEndOptions() {
		return OC_Calendar_Object::getEndOptions(self::$l10n);
	}

	/**
	 * @brief returns the options for an monthly repeating event
	 * @return array - valid inputs for monthly repeating events
	 */
	public static function getMonthOptions() {
		return OC_Calendar_Object::getMonthOptions(self::$l10n);
	}

	/**
	 * @brief returns the options for an weekly repeating event
	 * @return array - valid inputs for weekly repeating events
	 */
	public static function getWeeklyOptions() {
		return OC_Calendar_Object::getWeeklyOptions(self::$l10n);
	}

	/**
	 * @brief returns the options for an yearly repeating event
	 * @return array - valid inputs for yearly repeating events
	 */
	public static function getYearOptions() {
		return OC_Calendar_Object::getYearOptions(self::$l10n);
	}

	/**
	 * @brief returns the options for an yearly repeating event which occurs on specific days of the year
	 * @return array - valid inputs for yearly repeating events
	 */
	public static function getByYearDayOptions() {
		return OC_Calendar_Object::getByYearDayOptions();
	}

	/**
	 * @brief returns the options for an yearly repeating event which occurs on specific month of the year
	 * @return array - valid inputs for yearly repeating events
	 */
	public static function getByMonthOptions() {
		return OC_Calendar_Object::getByMonthOptions(self::$l10n);
	}

	/**
	 * @brief returns the options for an yearly repeating event which occurs on specific week numbers of the year
	 * @return array - valid inputs for yearly repeating events
	 */
	public static function getByWeekNoOptions() {
		return OC_Calendar_Object::getByWeekNoOptions();
	}

	/**
	 * @brief returns the options for an yearly or monthly repeating event which occurs on specific days of the month
	 * @return array - valid inputs for yearly or monthly repeating events
	 */
	public static function getByMonthDayOptions() {
		return OC_Calendar_Object::getByMonthDayOptions();
	}

	/**
	 * @brief returns the options for an monthly repeating event which occurs on specific weeks of the month
	 * @return array - valid inputs for monthly repeating events
	 */
	public static function getWeekofMonth() {
		return OC_Calendar_Object::getWeekofMonth(self::$l10n);
	}

	/**
	 * @return (string) $timezone as set by user or the default timezone
	 */
	public static function getTimezone() {

		// are we in a user session?
		if (OCP\User::isLoggedIn()) {
			// aye, let's use the normal infrastructure
			return OCP\Config::getUserValue(OCP\User::getUser(),
						  'calendar',
						  'timezone',
						  date_default_timezone_get());

		// nope! probably link-shared stuff (no need to check that)
		} else {
			// is the timezone set in session vars?
			if (\OC::$session->exists('public_link_timezone')) {
				// aye, using that
				return \OC::$session->get('public_link_timezone');
			
			// is it a shared calendar or event??
			} elseif (\OC::$session->exists('public_link_owner')) {
				// let's try to get the shared calendar
				return OCP\Config::getUserValue(\OC::$session->get('public_link_owner'),
						  'calendar',
						  'timezone',
						  date_default_timezone_get());
			
			// nope!
			} else {
				// use the default already!
				return date_default_timezone_get();
			}
		}
	}

	/**
	 * @brief Get the permissions for a calendar / an event
	 * @param (int) $id - id of the calendar / event
	 * @param (string) $type - type of the id (calendar/event)
	 * @return (int) $permissions - CRUDS permissions
	 * @param (string) $accessclass - access class (rfc5545, section 3.8.1.3)
	 * @see OCP\Share
	 */
	public static function getPermissions($id, $type, $accessclass = '') {
		 $permissions_all = OCP\PERMISSION_ALL;

		if($type == self::CALENDAR) {
			$calendar = self::getCalendar($id, false, false);
			if($calendar['userid'] == OCP\USER::getUser()) {
				return $permissions_all;
			} else {
				$sharedCalendar = OCP\Share::getItemSharedWithBySource('calendar', $id);
				if ($sharedCalendar) {
					return $sharedCalendar['permissions'];
				}
			}
		}
		elseif($type == self::EVENT) {
			if(OC_Calendar_Object::getowner($id) == OCP\USER::getUser()) {
				return $permissions_all;
			} else {
				$object = OC_Calendar_Object::find($id);
				$sharedCalendar = OCP\Share::getItemSharedWithBySource('calendar', $object['calendarid']);
				$sharedEvent = OCP\Share::getItemSharedWithBySource('event', $id);
				$calendar_permissions = 0;
				$event_permissions = 0;
				if ($sharedCalendar) {
					$calendar_permissions = $sharedCalendar['permissions'];
				}
				if ($sharedEvent) {
					$event_permissions = $sharedEvent['permissions'];
				}
				if ($accessclass === 'PRIVATE') {
					return 0;
				} elseif ($accessclass === 'CONFIDENTIAL') {
					return OCP\PERMISSION_READ;
				} else {
					return max($calendar_permissions, $event_permissions);
				}
			}
		}
		return 0;
	}

	/*
	 * @brief Get the permissions for an access class 
	 * @param (string) $accessclass - access class (rfc5545, section 3.8.1.3)
	 * @return (int) $permissions - CRUDS permissions
	 * @see OCP\Share
	 */
	public static function getAccessClassPermissions($accessclass = '') {

		switch($accessclass) {
			case 'CONFIDENTIAL':
				return OCP\PERMISSION_READ;
			case 'PUBLIC':
			case '':
				return (OCP\PERMISSION_READ | OCP\PERMISSION_UPDATE | OCP\PERMISSION_DELETE);
			default:
				return 0;
		}
	}

	/**
	 * @brief analyses the parameter for calendar parameter and returns the objects
	 * @param (string) $calendarid - calendarid
	 * @param (int) $start - unixtimestamp of start
	 * @param (int) $end - unixtimestamp of end
	 * @return (array) $events
	 */
	public static function getrequestedEvents($calendarid, $start, $end) {
		$events = array();
		if($calendarid == 'shared_events') {
			$singleevents = OCP\Share::getItemsSharedWith('event', OC_Share_Backend_Event::FORMAT_EVENT);
			$calendars = OC_Calendar_Calendar::allCalendars(OCP\USER::getUser());
			foreach($singleevents as $singleevent) {
				// Skip this single event if the whole calendar is already shared with the user.
				$calendarShared = false;
				foreach ($calendars as $calendar) {
					if ($singleevent['calendarid'] === $calendar['id']) {
						$calendarShared = true;
						break;
					}
				}
				if ($calendarShared === true) {
					continue;
				}
				$singleevent['summary'] .= ' (' . self::$l10n->t('by') .  ' ' . OC_Calendar_Object::getowner($singleevent['id']) . ')';
				$events[] =  $singleevent;
			}
		}else{
			if (is_numeric($calendarid)) {
				$calendar = self::getCalendar($calendarid);
				OCP\Response::enableCaching(0);
				OCP\Response::setETagHeader($calendar['ctag']);
				$events = OC_Calendar_Object::allInPeriod($calendarid, $start, $end, $calendar['userid'] !== OCP\User::getUser());
			} else {
				OCP\Util::emitHook('OC_Calendar', 'getEvents', array('calendar_id' => $calendarid, 'events' => &$events));
			}
		}
		return $events;
	}

	/**
	 * @brief generates the output for an event which will be readable for our js
	 * @param (mixed) $event - event object / array
	 * @param (int) $start - DateTime object of start
	 * @param (int) $end - DateTime object of end
	 * @return (array) $output - readable output
	 */
	public static function generateEventOutput(array $event, $start, $end) {
		if(!isset($event['calendardata']) && !isset($event['vevent'])) {
			return false;
		}
		if(!isset($event['calendardata']) && isset($event['vevent'])) {
			$event['calendardata'] = "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:ownCloud's Internal iCal System\n"
				. $event['vevent']->serialize() .  "END:VCALENDAR";
		}
		try{
			$object = OC_VObject::parse($event['calendardata']);
			if(!$object) {
				\OCP\Util::writeLog('calendar', __METHOD__.' Error parsing event: '.print_r($event, true), \OCP\Util::DEBUG);
				return array();
			}
	
			$output = array();
	
			if($object->name === 'VEVENT') {
				$vevent = $object;
			} elseif(isset($object->VEVENT)) {
				$vevent = $object->VEVENT;
			} else {
				\OCP\Util::writeLog('calendar', __METHOD__.' Object contains not event: '.print_r($event, true), \OCP\Util::DEBUG);
				return $output;
			}
			$id = $event['id'];
			if(OC_Calendar_Object::getowner($id) !== OCP\USER::getUser()) {
				// do not show events with private or unknown access class
				if (isset($vevent->CLASS)
					&& ($vevent->CLASS->value === 'PRIVATE'
					|| $vevent->CLASS->value === ''))
				{
					return $output;
				}
				$object = OC_Calendar_Object::cleanByAccessClass($id, $object);
			}
			$allday = ($vevent->DTSTART->getDateType() == Sabre\VObject\Property\DateTime::DATE)?true:false;
			$last_modified = @$vevent->__get('LAST-MODIFIED');
			$lastmodified = ($last_modified)?$last_modified->getDateTime()->format('U'):0;
			$staticoutput = array('id'=>(int)$event['id'],
							'title' => (!is_null($vevent->SUMMARY) && $vevent->SUMMARY->value != '')? strtr($vevent->SUMMARY->value, array('\,' => ',', '\;' => ';')) : self::$l10n->t('unnamed'),
							'description' => isset($vevent->DESCRIPTION)?strtr($vevent->DESCRIPTION->value, array('\,' => ',', '\;' => ';')):'',
							'lastmodified'=>$lastmodified,
							'allDay'=>$allday);
			if(OC_Calendar_Object::isrepeating($id) && OC_Calendar_Repeat::is_cached_inperiod($event['id'], $start, $end)) {
				$cachedinperiod = OC_Calendar_Repeat::get_inperiod($id, $start, $end);
				foreach($cachedinperiod as $cachedevent) {
					$dynamicoutput = array();
					if($allday) {
						$start_dt = new DateTime($cachedevent['startdate'], new DateTimeZone('UTC'));
						$end_dt = new DateTime($cachedevent['enddate'], new DateTimeZone('UTC'));
						$dynamicoutput['start'] = $start_dt->format('Y-m-d');
						$dynamicoutput['end'] = $end_dt->format('Y-m-d');
					}else{
						$start_dt = new DateTime($cachedevent['startdate'], new DateTimeZone('UTC'));
						$end_dt = new DateTime($cachedevent['enddate'], new DateTimeZone('UTC'));
						$start_dt->setTimezone(new DateTimeZone(self::$tz));
						$end_dt->setTimezone(new DateTimeZone(self::$tz));
						$dynamicoutput['start'] = $start_dt->format('Y-m-d H:i:s');
						$dynamicoutput['end'] = $end_dt->format('Y-m-d H:i:s');
					}
					$output[] = array_merge($staticoutput, $dynamicoutput);
				}
			}else{
				if(OC_Calendar_Object::isrepeating($id) || $event['repeating'] == 1) {
					$object->expand($start, $end);
				}
				foreach($object->getComponents() as $singleevent) {
					if(!($singleevent instanceof Sabre\VObject\Component\VEvent)) {
						continue;
					}
					$dynamicoutput = OC_Calendar_Object::generateStartEndDate($singleevent->DTSTART, OC_Calendar_Object::getDTEndFromVEvent($singleevent), $allday, self::$tz);
					$output[] = array_merge($staticoutput, $dynamicoutput);
				}
			}
			return $output;
		}catch(Exception $e) {
			$uid = 'unknown';
			if(isset($event['uri'])){
				$uid = $event['uri'];
			}
			\OCP\Util::writeLog('calendar', 'Event (' . $uid . ') contains invalid data: ' . $e->getMessage(),\OCP\Util::WARN);
		}
	}
	
	/**
	 * @brief use to create HTML emails and send them
	 * @param $eventid The event id
	 * @param $location The location
	 * @param $description The description
	 * @param $dtstart The start date
	 * @param $dtend The end date
	 *
	 */
	public static function sendEmails($eventid, $summary, $location, $description, $dtstart, $dtend) {
		$user = \OCP\User::getUser();
		$eventsharees = array();
		$eventShareesNames = array();
		$emails = array();
		$sharedwithByEvent = \OCP\Share::getItemShared('event', $eventid);
		if (is_array($sharedwithByEvent)) {
			foreach ($sharedwithByEvent as $share) {
				if ($share['share_type'] === \OCP\Share::SHARE_TYPE_USER || $share['share_type'] === \OCP\Share::SHARE_TYPE_GROUP) {
					$eventsharees[] = $share;
				}
			}
			foreach ($eventsharees as $sharee) {
				$eventShareesNames[] = $sharee['share_with'];
			}
		}
		foreach ($eventShareesNames as $name) {
			$result = OC_Calendar_Calendar::getUsersEmails($name);
			$emails[] = $result;
		}
		$useremail = OC_Calendar_Calendar::getUsersEmails($user);
		foreach ($emails as $email) {
			if($email === null) {
				continue;
			}

			$subject = 'Calendar Event Shared';

			$headers = 'MIME-Version: 1.0\r\n';
			$headers .= 'Content-Type: text/html; charset=utf-8\r\n';
			$headers .= 'From:' . $useremail;

			$message  = '<html><body>';
			$message .= '<table style="border:1px solid black;" cellpadding="10">';
			$message .= "<tr style='background: #eee;'><td colspan='2'><strong>" . $user . '</strong><strong> has shared with you an event</strong></td></tr>';
			$message .= '<tr><td><strong>Summary:</strong> </td><td>' . \OCP\Util::sanitizeHTML($summary) . '</td></tr>';
			$message .= '<tr><td><strong>Location:</strong> </td><td>' . \OCP\Util::sanitizeHTML($location) . '</td></tr>';
			$message .= '<tr><td><strong>Description:</strong> </td><td>' . \OCP\Util::sanitizeHTML($description) . '</td></tr>';
			$message .= '</table>';
			$message .= '</body></html>';

			OCP\Util::sendMail($email, "User", $subject, $message, $useremail, $user, $html = 1, $altbody = '', $ccaddress = '', $ccname = '', $bcc = '');
		}
	}
}
