<?php
/**
 * ownCloud - \OCA\Calendar\Sabre\Calendar
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

namespace OCA\Calendar\Sabre;

use OCP\Constants;
use OCP\User;
use Sabre\CalDAV\CalendarObject;

/**
 * This class overrides \Sabre\CalDAV\Calendar::getACL() to return read/write
 * permissions based on user and shared state and it overrides
 * \Sabre\CalDAV\Calendar::getChild() and \Sabre\CalDAV\Calendar::getChildren()
 * to instantiate \OCA\Calendar\Sabre\Objects.
*/
class Calendar extends \Sabre\CalDAV\Calendar {

	/**
	* Returns a list of ACE's for this node.
	*
	* Each ACE has the following properties:
	*   * 'privilege', a string such as {DAV:}read or {DAV:}write. These are
	*     currently the only supported privileges
	*   * 'principal', a url to the principal who owns the node
	*   * 'protected' (optional), indicating that this ACE is not allowed to
	*      be updated.
	*
	* @return array
	*/
	public function getACL() {

		$readprincipal = $this->getOwner();
		$writeprincipal = $this->getOwner();
		$uid = \OC_Calendar_Calendar::extractUserID($this->getOwner());

		if($uid != User::getUser()) {
			if($uid === 'contact_birthdays') {
				$readprincipal = 'principals/' . User::getUser();
			} else {
				$sharedCalendar = \OCP\Share::getItemSharedWithBySource('calendar', $this->calendarInfo['id']);
				if ($sharedCalendar && ($sharedCalendar['permissions'] & Constants::PERMISSION_READ)) {
					$readprincipal = 'principals/' . User::getUser();
				}
				if ($sharedCalendar && ($sharedCalendar['permissions'] & Constants::PERMISSION_UPDATE)) {
					$writeprincipal = 'principals/' . User::getUser();
				}
			}
		}

		return array(
			array(
				'privilege' => '{DAV:}read',
				'principal' => $readprincipal,
				'protected' => true,
			),
			array(
				'privilege' => '{DAV:}write',
				'principal' => $writeprincipal,
				'protected' => true,
			),
			array(
				'privilege' => '{DAV:}read',
				'principal' => $readprincipal . '/calendar-proxy-write',
				'protected' => true,
			),
			array(
				'privilege' => '{DAV:}write',
				'principal' => $writeprincipal . '/calendar-proxy-write',
				'protected' => true,
			),
			array(
				'privilege' => '{DAV:}read',
				'principal' => $readprincipal . '/calendar-proxy-read',
				'protected' => true,
			),
			array(
				'privilege' => '{' . \Sabre\CalDAV\Plugin::NS_CALDAV . '}read-free-busy',
				'principal' => '{DAV:}authenticated',
				'protected' => true,
			),

		);

	}

	/**
	* Returns a calendar object
	*
	* The contained calendar objects are for example Events or Todo's.
	*
	* @param string $name
	* @return \Sabre\CalDAV\ICalendarObject
	* @throws \Sabre\DAV\Exception\NotFound
	*/
	public function getChild($name) {
		$obj = $this->caldavBackend->getCalendarObject($this->calendarInfo['id'],$name);
		if (!$obj) {
			throw new \Sabre\DAV\Exception\NotFound('Calendar object not found');
		}
		return new Object($this->caldavBackend,$this->calendarInfo,$obj);

	}

	/**
	* Returns the full list of calendar objects
	*
	* @return array
	*/
	public function getChildren() {

		$objs = $this->caldavBackend->getCalendarObjects($this->calendarInfo['id']);
		$children = array();
		foreach($objs as $obj) {
			$children[] = new Object($this->caldavBackend,$this->calendarInfo,$obj);
		}
		return $children;

	}

	/**
	 * This method receives a list of paths in it's first argument.
	 * It must return an array with Node objects.
	 *
	 * If any children are not found, you do not have to return them.
	 * 
	 * @return array
	 */
	public function getMultipleChildren(array $paths) {
		$objs = $this->caldavBackend->getMultipleCalendarObjects($this->calendarInfo['id'], $paths);
		$children = [];
		foreach($objs as $obj) {
			$children[] = new CalendarObject($this->caldavBackend,$this->calendarInfo,$obj);
		}
        	return $children;
        }

}
