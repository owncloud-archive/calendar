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
	 * @param parameters parameters from postCreateUser-Hook
	 * @return array
	 */
	public static function createUser($parameters) {
		OC_Calendar_Calendar::addDefaultCalendars($parameters['uid']);

		return true;
	}

	/**
	 * @brief Deletes all calendars of a certain user
	 * @param parameters parameters from postDeleteUser-Hook
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
	 * @brief Delete obsolete user preferences after a calendar share is deleted.
	 * @param parameters parameters from post_unshare-Hook
	 * @return array
	 */
	public static function postDeleteShare($parameters) {
		if ($parameters['itemType'] == 'calendar') {
			if ($parameters['shareType'] == OCP\Share::SHARE_TYPE_USER) {
				OC_Calendar_Calendar::removeUnusedPreferencesForCalendar($parameters['itemSource'], array($parameters['shareWith']));
			}
			else if ($parameters['shareType'] == OCP\Share::SHARE_TYPE_GROUP) {
				$users = \OC_Group::usersInGroup($parameters['shareWith']);
				OC_Calendar_Calendar::removeUnusedPreferencesForCalendar($parameters['itemSource'], $users);
			}
		}
		return true;
	}

	/**
	 * @brief Delete obsolete calendar user preferences when a
	 *        user is removed from a group.
	 * @param parameters parameters from post_removeFromGroup-Hook
	 * @return array
	 */
	public static function postRemoveFromGroup($parameters) {
		OC_Calendar_Calendar::removeUnusedPreferencesForUser($parameters['uid']);
		return true;
	}

	/**
	 * @brief Delete obsolete calendar shares when a group is deleted.
	 * @param parameters parameters from post_deleteGroup-Hook
	 * @return array
	 */
	public static function postDeleteGroup($parameters) {
		// Get users in group. Will this work since the group is already deleted?
		$users = \OC_Group::usersInGroup($parameters['gid']);
		foreach ($users as $user) {
			OC_Calendar_Calendar::removeUnusedPreferencesForUser($user);
		}
		return true;
	}
}
