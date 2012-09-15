<?php
/**
 * Copyright (c) 2011, 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
//is the user logged in at all?
OCP\JSON::checkLoggedIn();
//is the calendar app enabled at al?
OCP\JSON::checkAppEnabled('calendar');
session_write_close();
//setup all registered calendar backends
OCA\Calendar::setupBackends();
//get the calendarid
if(array_key_exists($_GET['calendar_id'], $_SESSION['calendar']['calid'])){
	//yeah, calendarid found
	$calendarid = $_SESSION['calendar']['calid'][$_GET['calendar_id']];
}else{
	//calendar not found
	OCP\JSON::error(array('message'=>'calendar not found'));
}
//get the start DateTime object
$start = (version_compare(PHP_VERSION, '5.3.0', '>='))?DateTime::createFromFormat('U', $_GET['start']):new DateTime('@' . $_GET['start']);
//get the end DateTime object
$end = (version_compare(PHP_VERSION, '5.3.0', '>='))?DateTime::createFromFormat('U', $_GET['end']):new DateTime('@' . $_GET['end']);
//get an array of all requested events
$requestedEvents = OCA\Calendar::allObjectsInPeriod($calendarid, $start, $end);
//array for events to send to client
$outputEvents = array();
//generate the output for each single event
foreach($requestedEvents as $requestedEvent){
	//is this a calendar object at all?
	if(!isset($requestedEvent['calendardata']) && !isset($requestedEvent['vevent'])){
		continue;
	}
	//is there the raw calendardata or just the VEvent object?
	if(!isset($requestedEvent['calendardata']) && isset($requestedEvent['vevent'])){
		//generate raw calendardata
		$requestedEvent['calendardata'] = "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:ownCloud's Internal iCal System\n" . $requestedEvent['vevent']->serialize() .  "END:VCALENDAR";
	}
	//generate an calendar object using the raw calendardata
	$object = OC_VObject::parse($requestedEvent['calendardata']);
	//there must be a VEvent to proceed
	if(!$object->VEVENT){
		continue;
	}
	//get the VEvent object
	$vevent = $object->VEVENT;
	//get the 
	$id = $requestedEvent['id'];
	//is the event allday?
	$allday = ($vevent->DTSTART->getDateType() == Sabre_VObject_Element_DateTime::DATE)?true:false;
	//when was the event modified the last time?
	$lastmodified = @$vevent->__get('LAST-MODIFIED');
	//get the lastmodified as unixtime
	$lastmodified_unixtime = ($last_modified)?$lastmodified->getDateTime()->format('U'):0;
	//generate the output that keeps the same for repeating events
					//objectid
	$staticoutput = array('objectid'=>(int)$requestedEvent['id'],
					//title 
					'title' => ($vevent->SUMMARY)?$vevent->SUMMARY->value: 'unnamed',
					//description
					'description' => isset($vevent->DESCRIPTION)?$vevent->DESCRIPTION->value:'',
					//last modification
					'lastmodified'=>$lastmodified_unixtime,
					//is it allday?
					'allDay'=>$allday);
	//TODO - reimplemenet repeating events caching
	//is the event repeating?
	/*if(($vevent->RRULE || $vevent->RDATE) /*&& OC_Calendar_Repeat::is_cached_inperiod($event['id'], $start, $end)*//*){
		//$cachedinperiod = OC_Calendar_Repeat::get_inperiod($id, $start, $end);
		foreach($cachedinperiod as $cachedevent){
			$dynamicoutput = array();
			if($allday){
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
			$outputEvents[] = array_merge($staticoutput, $dynamicoutput);
		}
	}else{*/
		if($vevent->RRULE || $event['repeating'] == 1){
			$object->expand($start, $end);
		}
		foreach($object->getComponents() as $singleevent){
			if(!($singleevent instanceof Sabre_VObject_Component_VEvent)){
				continue;
			}
			$start_dt = $singleevent->DTSTART->getDateTime();
			
			$end_dt = $singleevent->DTEND->getDateTime();
			$dynamicoutput = array();
			if($allday){
				$dynamicoutput['start'] = $start_dt->format('Y-m-d');
				$end_dt->modify('-1 minute');
				while($start_dt >= $end_dt){
					$end_dt->modify('+1 day');
				}
				$dynamicoutput['end'] = $end_dt->format('Y-m-d');
			}else{
				$start_dt->setTimezone(new DateTimeZone($tz));
				$end_dt->setTimezone(new DateTimeZone($tz));
				$dynamicoutput['start'] = $start_dt->format('Y-m-d H:i:s');
				$dynamicoutput['end'] = $end_dt->format('Y-m-d H:i:s');
			}
			$outputEvents[] = array_merge($staticoutput, $dynamicoutput);			
		}
	//}
}
OCP\JSON::encodedPrint(OCP\Util::sanitizeHTML($outputEvents));
