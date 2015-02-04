<?php

/**
 * ownCloud
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Calendar\Search;

/**
 * The updated calendar search provider
 */
class Provider extends \OCP\Search\Provider {

	/**
	 * Search for query in calendar events
	 *
	 * @param string $query
	 * @return array list of \OCA\Calendar\Search\Event
	 */
	function search($query) {
		$calendars = \OC_Calendar_Calendar::allCalendars(\OCP\USER::getUser(), true);
		// check if the calenar is enabled
		if (count($calendars) == 0 || !\OCP\App::isEnabled('calendar')) {
			return array();
		}
		$results = array();
		foreach ($calendars as $calendar) {
			$objects = \OC_Calendar_Object::all($calendar['id']);
			$date = strtotime($query);
			// search all calendar objects, one by one
			foreach ($objects as $object) {
				// skip non-events
				if ($object['objecttype'] != 'VEVENT') {
					continue;
				}
				// check the event summary string
				if (stripos($object['summary'], $query) !== false) {
					$results[] = new \OCA\Calendar\Search\Event($object);
					continue;
				}
				// check if the event is happening on a queried date
				$range = $this->getDateRange($object);
				if ($date && $this->fallsWithin($date, $range)) {
					$results[] = new \OCA\Calendar\Search\Event($object);
					continue;
				}
			}
		}
		return $results;
	}

	/**
	 * Test if a date falls within a range
	 *
	 * @param int $date in Unix time
	 * @param array $range [start, end] in Unix time
	 * @return boolean
	 */
	private function fallsWithin($date, $range) {
		if (!array($range) && !count($range) == 2) {
			return false;
		}
		// setup range
		list($start, $end) = $range;
		// test
		return $date >= $start && $date <= $end;
	}

	/**
	 * Return the start time and end time of a calendar object in Unix time
	 *
	 * @param array $calendarObject must contain a VEVENT
	 * @return array [start, end] in Unix time
	 */
	private function getDateRange($calendarObject) {
		$calendarData = \OC_VObject::parse($calendarObject['calendardata']);
		// set start
		$start = $calendarData->VEVENT->DTSTART->getDateTime();
		$start->setTimezone(Event::getUserTimezone());
		// set end
		$end = \OC_Calendar_Object::getDTEndFromVEvent($calendarData->VEVENT)->getDateTime();
		$end->setTimezone(Event::getUserTimezone());
		// return
		return array($start->getTimestamp(), $end->getTimestamp());
	}
}
