<?php
/**
 * Copyright (c) 2011 Jakob Sack <mail@jakobsack.de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

/**
 * This class contains all hooks.
 */
class OC_Calendar_Hooks{
	/**
	 * @brief Creates default calendar for a user
	 * @param paramters parameters from postCreateUser-Hook
	 * @return array
	 */
	public static function createUser($parameters) {
		OC_Calendar_Calendar::addDefaultCalendars($parameters['uid']);

		return true;
	}

	/**
	 * @brief Deletes all calendars of a certain user
	 * @param paramters parameters from postDeleteUser-Hook
	 * @return array
	 */
	public static function deleteUser($parameters) {
		$calendars = OC_Calendar_Calendar::allCalendars($parameters['uid']);

		foreach($calendars as $calendar) {
			if($parameters['uid'] === $calendar['userid']) {
				OC_Calendar_Calendar::deleteCalendar($calendar['id']);
			}
		}

		// Remove user's calendar preferences.
		$stmt = OCP\DB::prepare( 'DELETE FROM `*PREFIX*clndr_user_preferences` WHERE `userid` = ?' );
		$stmt->execute(array($parameters['uid']));

		return true;
	}

	/**
	 * @brief Create aditional data for a calendar share
	 * @param paramters parameters from post_shared-Hook
	 * @return array
	 */
	public static function createShare($parameters) {
		if ($parameters['itemType'] == 'calendar') {
			if ($parameters['shareType'] == OCP\Share::SHARE_TYPE_USER) {
				OC_Calendar_Calendar::setCalendarDefaultUserPreferences($parameters['shareWith'], $parameters['itemSource']);
			}
			else if ($parameters['shareType'] == OCP\Share::SHARE_TYPE_GROUP) {
				$users = \OC_Group::usersInGroup($parameters['shareWith']);
				foreach ($users as $user) {
					OC_Calendar_Calendar::setCalendarDefaultUserPreferences($user, $parameters['itemSource']);
				}
			}
		}
		return true;
	}

	/**
	 * @brief Delete obsolete user preferences after a calendar share is deleted.
	 * @param paramters parameters from post_unshare-Hook
	 * @return array
	 */
	public static function postDeleteShare($parameters) {
		if ($parameters['itemType'] == 'calendar') {
			if ($parameters['shareType'] == OCP\Share::SHARE_TYPE_USER) {
				OC_Calendar_Calendar::cleanPreferences(array($parameters['shareWith']), $parameters['itemSource']);
			}
			else if ($parameters['shareType'] == OCP\Share::SHARE_TYPE_GROUP) {
				$users = \OC_Group::usersInGroup($parameters['shareWith']);
				OC_Calendar_Calendar::cleanPreferences($users, $parameters['itemSource']);
			}
		}
		return true;
	}

	/**
	 * @brief Add initial calendar user preferences for calendars
	 *        shared with a group when a user is added to the group.
	 * @param paramters parameters from post_addToGroup-Hook
	 * @return array
	 */
	public static function addToGroup($parameters) {
		// Get all calendar ids shared with the group.
		$stmt = OCP\DB::prepare( 'SELECT `item_source` FROM `*PREFIX*share` WHERE `item_type`=? AND `share_type`=? AND `share_with`=?' );
		$result = $stmt->execute(array('calendar', OCP\Share::SHARE_TYPE_GROUP, $parameters['gid']));
		while ($calendar = $result->fetchRow()) {
			// Add default user preferences for each calendar.
			OC_Calendar_Calendar::setCalendarDefaultUserPreferences($parameters['uid'], $calendar['item_source']);
		}
		return true;
	}

	/**
	 * @brief Delete obsolete calendar user preferences when a
	 *        user is removed from a group.
	 * @param paramters parameters from post_removeFromGroup-Hook
	 * @return array
	 */
	public static function postRemoveFromGroup($parameters) {
		// Get all calendar ids shared with the group.
		$stmt = OCP\DB::prepare( 'SELECT `item_source` FROM `*PREFIX*share` WHERE `item_type`=? AND `share_type`=? AND `share_with`=?' );
		$result = $stmt->execute(array('calendar', OCP\Share::SHARE_TYPE_GROUP, $parameters['gid']));
		while ($calendar = $result->fetchRow()) {
			OC_Calendar_Calendar::cleanPreferences(array($parameters['uid']), $calendar['item_source']);
        }
		return true;
	}

	/**
	 * @brief Delete obsolete calendar shares when a group is deleted.
	 * @param paramters parameters from post_deleteGroup-Hook
	 * @return array
	 */
	public static function postDeleteGroup($parameters) {
		// Get users in group. Will this work since the group is already deleted?
		$users = \OC_Group::usersInGroup($parameters['gid']);
		// Get all calendar ids shared with the group.
		$stmt = OCP\DB::prepare( 'SELECT `item_source` FROM `*PREFIX*share` WHERE `item_type`=? AND `share_type`=? AND `share_with`=?' );
		$result = $stmt->execute(array('calendar', OCP\Share::SHARE_TYPE_GROUP, $parameters['gid']));
		while ($calendar = $result->fetchRow()) {
			OC_Calendar_Calendar::cleanPreferences($users, $calendar['item_source']);
		}
		return true;
	}
}
