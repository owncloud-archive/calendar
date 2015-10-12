<?php
/**
 * ownCloud - \OCA\Calendar\Sabre\UserCalendars
 *
 * @author Thomas Tanghus
 * @copyright 2012 Thomas Tanghus (thomas@tanghus.net)
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
use Sabre\CalDAV\Calendar;

namespace OCA\Calendar\Sabre;

/**
 * This class overrides \Sabre\CalDAV\CalendarHome::getChildren()
 * to instantiate \OCA\Calendar\Sabre\Calendars.
*/
class UserCalendars extends \Sabre\CalDAV\CalendarHome {

	/**
	 * Returns a single calendar, by name
	 *
	 * @param string $name
	 * @return Calendar
	 */
	function getChild($name) {
		$children = $this->getChildren();

		foreach($children as $child) {
			if ($child->getName() === $name) {
				return $child;
			}
		}

		return parent::getChild($name);
	}

		/**
	* Returns a list of calendars
	*
	* @return array
	*/
	public function getChildren() {

		$calendars = $this->caldavBackend->getCalendarsForUser($this->principalInfo['uri']);
		$objs = array();
		foreach($calendars as $calendar) {
			$objs[] = new Calendar($this->caldavBackend, $calendar);
		}
		$objs[] = new \Sabre\CalDAV\Schedule\Outbox($this->principalInfo['uri']);
		return $objs;

	}

}
