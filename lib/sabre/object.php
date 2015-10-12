<?php
/**
 * ownCloud - \OCA\Calendar\Sabre\Object
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

/**
 * This class overrides \Sabre\CalDAV\CalendarObject::getACL()
 * to return read/write permissions based on user and shared state.
*/
class Object extends \Sabre\CalDAV\CalendarObject {

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
				$object = \Sabre\VObject\Reader::read($this->objectData['calendardata']);
				$sharedCalendar = \OCP\Share::getItemSharedWithBySource('calendar', $this->calendarInfo['id']);
				$sharedAccessClassPermissions = \OC_Calendar_Object::getAccessClassPermissions($object);
				if ($sharedCalendar && ($sharedCalendar['permissions'] & Constants::PERMISSION_READ) && ($sharedAccessClassPermissions & Constants::PERMISSION_READ)) {
					$readprincipal = 'principals/' . User::getUser();
				}
				if ($sharedCalendar && ($sharedCalendar['permissions'] & Constants::PERMISSION_UPDATE) && ($sharedAccessClassPermissions & Constants::PERMISSION_UPDATE)) {
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
		);

	}

}
