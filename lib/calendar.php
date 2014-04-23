<?php
/**
 * Copyright (c) 2011 Jakob Sack <mail@jakobsack.de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
/**
 *
 * The following SQL statement is just a help for developers and will not be
 * executed!
 *
 * CREATE TABLE clndr_calendars (
 *     id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
 *     userid VARCHAR(255),
 *     displayname VARCHAR(100),
 *     uri VARCHAR(100),
 *     ctag INTEGER UNSIGNED NOT NULL DEFAULT '0',
 *     calendarorder INTEGER UNSIGNED NOT NULL DEFAULT '0',
 *     calendarcolor VARCHAR(10),
 *     timezone TEXT,
 *     components VARCHAR(20)
 * );
 *
 */

/**
 * This class manages our calendars
 */
class OC_Calendar_Calendar{
	/**
	 * @brief Returns the list of calendars for a specific user.
	 * @param string $uid User ID (Note: shared calendars are still retrieved for the logged in user only)
	 * @param boolean $active Only return calendars with this $active state, default(=false) is don't care
	 * @return array
	 */
	public static function allCalendars($uid, $active=false) {
		$stmt = OCP\DB::prepare( 'SELECT `id`, `userid`, `displayname`, `uri`, `ctag`, `calendarorder`, `calendarcolor`, `timezone`, `components`, 1 AS active, `displayname` AS default_displayname FROM `*PREFIX*clndr_calendars` WHERE `userid` = ?' );
		$result = $stmt->execute(array($uid));

		$calendars = array();
		$owned_calendar_ids = array();
		while( $row = $result->fetchRow()) {
			$row['permissions'] = OCP\PERMISSION_CREATE
				| OCP\PERMISSION_READ | OCP\PERMISSION_UPDATE
				| OCP\PERMISSION_DELETE | OCP\PERMISSION_SHARE;
			$row['description'] = '';
			$calendars[$row['id']] = $row;
			$owned_calendar_ids[] = $row['id'];
		}

		// Include calendar-user preferences (May override default
		// values retrieved above).
		// Note, we only need to do this for the calendars owned by the
		// user, as the shared calendars retrieved below will have their
		// preferences loaded in OC_Calendar_Calendar::find().
		foreach ($calendars as $key => $calendar) {
			$calendars[$key] = self::addPreferencesToCalendarArray($calendar);
		}

		$shared_calendars = OCP\Share::getItemsSharedWith('calendar', OC_Share_Backend_Calendar::FORMAT_CALENDAR);
		// Remove shared calendars that are already owned by the user.
		foreach ($shared_calendars as $key => $calendar) {
			if (in_array($calendar['id'], $owned_calendar_ids)) {
				unset($shared_calendars[$key]);
			}
		}

		$calendars = array_merge($calendars, $shared_calendars);

		foreach($calendars as $key => $calendar) {
		    // Remove inactive calendars if we are restricting to active calendars only.
		    if (!is_null($active) && $active && $calendars[$key]['active'] !== true) {
		        unset($calendars[$key]);
		    }
		}

		return $calendars;
	}

	/**
	 * @brief Returns the list of calendars for a principal (DAV term of user)
	 * @param string $principaluri
	 * @return array
	 */
	public static function allCalendarsWherePrincipalURIIs($principaluri) {
		$uid = self::extractUserID($principaluri);
		return self::allCalendars($uid);
	}

	/**
	 * @brief Gets the data of one calendar
	 * @param integer $id
	 * @param boolean $includePreferences (If false, query time is saved, and this is sometimes needed if we do not want to trigger the adding of default preferences (such as preventing recursion when already attempting to add default preferences))
	 * @return associative array
	 */
	public static function find($id, $includePreferences = true) {
		$uid = OCP\USER::getUser();
		$stmt = OCP\DB::prepare( 'SELECT `id`, `userid`, `displayname`, `uri`, `ctag`, `calendarorder`, `calendarcolor`, `timezone`, `components`, 1 AS active, `displayname` AS default_displayname FROM `*PREFIX*clndr_calendars` WHERE `id` = ?' );
		$result = $stmt->execute(array($id));

		$row = $result->fetchRow();

		// Include current user's permissions.
		if($row['userid'] != $uid) {
			$sharedCalendar = OCP\Share::getItemSharedWithBySource('calendar', $id);
			if ($sharedCalendar && ($sharedCalendar['permissions'] & OCP\PERMISSION_READ)) {
				$row['permissions'] = $sharedCalendar['permissions'];
			}
		} else {
			$row['permissions'] = OCP\PERMISSION_ALL;
		}

		if ($includePreferences) {
			$row = self::addPreferencesToCalendarArray($row);
		}
        
		return $row; // Even if the user has no permissions, the row must be returned so e.g. OC_Calendar_Object::getowner() works.
	}

	/**
	 * @brief Creates a new calendar
	 * @param string $userid
	 * @param string $name
	 * @param string $components Default: "VEVENT,VTODO,VJOURNAL"
	 * @param string $timezone Default: null
	 * @param integer $order Default: 1
	 * @param string $color Default: null, format: '#RRGGBB(AA)'
	 * @return insertid
	 */
	public static function addCalendar($userid,$name,$components='VEVENT,VTODO,VJOURNAL',$timezone=null,$order=0,$color=null) {
		$all = self::allCalendars($userid);
		$uris = array();
		foreach($all as $i) {
			$uris[] = $i['uri'];
		}

		$uri = self::createURI($name, $uris );

		$stmt = OCP\DB::prepare( 'INSERT INTO `*PREFIX*clndr_calendars` (`userid`,`displayname`,`uri`,`ctag`,`calendarorder`,`calendarcolor`,`timezone`,`components`) VALUES(?,?,?,?,?,?,?,?)' );
		$result = $stmt->execute(array($userid,$name,$uri,1,$order,$color,$timezone,$components));

		$insertid = OCP\DB::insertid('*PREFIX*clndr_calendars');
		OCP\Util::emitHook('OC_Calendar', 'addCalendar', $insertid);

		return $insertid;
	}

	/**
	 * @brief Gets the display names of the calendars a user has preferences saved for.
	 * @param string $userid
	 * @return associative array of calendar ids and calendar names.
	 */
	public static function getCalendarDisplayNames($userid) {
		$calendars = self::allCalendars($userid);
		$calendar_names = array();
		foreach ($calendars as $calendar) {
			$calendar_names[$calendar['id']] = $calendar['displayname'];
		}
		return $calendar_names;
	}

	/**
	 * @brief Returns a version of the given display name
	 * that is unique for this user's calendar display names.
	 * @param string $userid
	 * @param string $display_name
	 * @param string $calendar_userid
	 * @return array of calendar names.
	 */
	public static function getUniqueDisplayName($userid, $display_name, $calendar_userid = false) {
		if ($calendar_userid && $calendar_userid != $userid) {
			$display_name = $display_name . ' (' . $calendar_userid . ')';
		}
		$existing_calendar_names = self::getCalendarDisplayNames($userid);
		$suffix = 0;
		$temp_name = $display_name;
		while (in_array($temp_name, $existing_calendar_names)) {
			$suffix++;
			$temp_name = $display_name . ' - ' . $suffix;
		}
		return $temp_name;
	}

	/**
	 * @brief Adds preferences that override values in a calendar array.
	 * @param array $calendar
	 * @return array
	 */
	public static function addPreferencesToCalendarArray($calendar) {
		$userid = OCP\User::getUser();
		$calendarid = $calendar['id'];
		$stmt = OCP\DB::prepare( 'SELECT `key`, `value` FROM `*PREFIX*clndr_user_preferences` WHERE `userid` = ? AND `calendarid` = ?' );
		$result = $stmt->execute(array($userid, $calendarid));

		// Get the first row.
		$pref_row = $result->fetchRow();

		if (empty($pref_row)) {
			// If the first row is empty, it means the user has no preferences for this calendar.
			// Therefore, we need to add their default user preferences, and re-execute the query.
			self::setCalendarDefaultUserPreferences($calendarid);
			$result = $stmt->execute(array($userid, $calendarid));
		}
		else {
			$calendar[$pref_row['key']] = $pref_row['value'];
		}

   		// Continue iterating through results.
		while($pref_row = $result->fetchRow()) {
			$calendar[$pref_row['key']] = $pref_row['value'];
   		}

		// Make sure the active property is boolean (as it may have come from a clob preference).
		if (isset($calendar['active'])) {
			$calendar['active'] = ($calendar['active'] == 1) ? true : false;
		}

		return $calendar;
	}

	/**
	 * @brief Sets this user's default calendar-user preferences for a calendar.
	 * @param string $calendarid
	 */
	// NOTE: An owner may not have default preferences for a while (Only when find is called).
	public static function setCalendarDefaultUserPreferences($calendarid) {
		$userid = OCP\User::getUser();
		$calendar = self::find($calendarid, false);
		self::setCalendarUserPreference($calendarid, 'active', 1, true);
		self::setCalendarUserPreference($calendarid, 'calendarcolor', $calendar['calendarcolor'], true);

		// Get a unique displayname if the user doesn't already own the calendar.
		// We must get the unique names after we have already set some preferences above.
		// This is to ensure we do not cause recursion if getUniqueDisplayName() calls find(), finds no preferences, and calls this function again.
		$displayname = ($calendar['userid'] == $userid) ? $calendar['displayname'] : self::getUniqueDisplayName($userid, $calendar['displayname'], $calendar['userid']);
		self::setCalendarUserPreference($calendarid, 'displayname', $displayname, true);
	}

	/**
	 * @brief Sets a specific calendar-user preference.
	 * @param string $calendarid
	 * @param string $key
	 * @param string $value
	 * @param boolean $insertOnly
	 * @return boolean
	 */
	public static function setCalendarUserPreference($calendarid, $key, $value, $insertOnly = false) {
		$userid = OCP\User::getUser();
		$calendar = self::find($calendarid, false);

		// Check permissions.
		if (!(isset($calendar['permissions'])) || !($calendar['permissions'] & OCP\PERMISSION_READ)) {
			throw new Exception(
				OC_Calendar_App::$l10n->t(
					'You do not have the permissions to update this calendar preference.'
				)
			);
		}

		if ($insertOnly) {
			$stmt = OCP\DB::prepare( 'INSERT INTO `*PREFIX*clndr_user_preferences` (`userid`, `calendarid`, `key`, `value`) SELECT ?,?,?,? WHERE NOT EXISTS (SELECT 1 FROM `*PREFIX*clndr_user_preferences` WHERE `userid` = ? AND `calendarid` = ? AND `key` = ?)' );
			$stmt->execute(array($userid,$calendarid,$key,$value,$userid,$calendarid,$key));
		}
		else {
			$stmt = OCP\DB::prepare( 'UPDATE `*PREFIX*clndr_user_preferences` SET `value` = ? WHERE `userid` = ? AND `calendarid` = ? AND `key` = ?' );
			$rowsUpdated = $stmt->execute(array($value,$userid,$calendarid,$key));

			if ($rowsUpdated <= 0) {
				// If no rows were updated, insert a new row.
				$stmt = OCP\DB::prepare( 'INSERT INTO `*PREFIX*clndr_user_preferences` (`userid`, `calendarid`, `key`, `value`) VALUES(?,?,?,?)' );
				$stmt->execute(array($userid,$calendarid,$key,$value));
			}
		}

		return true;
	}

	/**
	 * @brief Creates default calendars
	 * @param string $userid
	 * @return boolean
	 */
	public static function addDefaultCalendars($userid = null) {
		if(is_null($userid)) {
			$userid = OCP\USER::getUser();
		}
		
		$id = self::addCalendar($userid,OC_Calendar_App::$l10n->t('Personal'));

		return true;
	}

	/**
	 * @brief Creates a new calendar from the data sabredav provides
	 * @param string $principaluri
	 * @param string $uri
	 * @param string $name
	 * @param string $components
	 * @param string $timezone
	 * @param integer $order
	 * @param string $color format: '#RRGGBB(AA)'
	 * @return insertid
	 */
	public static function addCalendarFromDAVData($principaluri,$uri,$name,$components,$timezone,$order,$color) {
		$userid = self::extractUserID($principaluri);

		$stmt = OCP\DB::prepare( 'INSERT INTO `*PREFIX*clndr_calendars` (`userid`,`displayname`,`uri`,`ctag`,`calendarorder`,`calendarcolor`,`timezone`,`components`) VALUES(?,?,?,?,?,?,?,?)' );
		$result = $stmt->execute(array($userid,$name,$uri,1,$order,$color,$timezone,$components));

		$insertid = OCP\DB::insertid('*PREFIX*clndr_calendars');
		OCP\Util::emitHook('OC_Calendar', 'addCalendar', $insertid);

		return $insertid;
	}

	/**
	 * @brief Edits a calendar
	 * @param integer $id
	 * @param string $name Default: null
	 * @param string $components Default: null
	 * @param string $timezone Default: null
	 * @param integer $order Default: null
	 * @param string $color Default: null, format: '#RRGGBB(AA)'
	 * @return boolean
	 *
	 * Values not null will be set
	 */
	public static function editCalendar($id,$name=null,$components=null,$timezone=null,$order=null,$color=null) {
		// Update this user's calendar preferences.
		self::editCalendarPreferences($id,$name,$color);

		// Need these ones for checking uri
		$calendar = self::find($id);
		if ($calendar['userid'] != OCP\User::getUser()) {
			$sharedCalendar = OCP\Share::getItemSharedWithBySource('calendar', $id);
			if (!$sharedCalendar || !($sharedCalendar['permissions'] & OCP\PERMISSION_UPDATE)) {
				throw new Exception(
					OC_Calendar_App::$l10n->t(
						'You do not have the permissions to update this calendar.'
					)
				);
			}
		}

		// Keep old stuff
		if(is_null($name)) $name = $calendar['displayname'];
		if(is_null($components)) $components = $calendar['components'];
		if(is_null($timezone)) $timezone = $calendar['timezone'];
		if(is_null($order)) $order = $calendar['calendarorder'];
		if(is_null($color)) $color = $calendar['calendarcolor'];

		if ($calendar['userid'] == OCP\User::getUser()) {
			// This user is the owner of the calendar, so update global calendar settings and default values for calendar preferences.
			$stmt = OCP\DB::prepare( 'UPDATE `*PREFIX*clndr_calendars` SET `displayname`=?,`calendarorder`=?,`calendarcolor`=?,`timezone`=?,`components`=?,`ctag`=`ctag`+1 WHERE `id`=?' );
			$result = $stmt->execute(array($name,$order,$color,$timezone,$components,$id));
		}
		else {
			// This user is not the owner of the calendar, so only update global calendar settings.
			$stmt = OCP\DB::prepare( 'UPDATE `*PREFIX*clndr_calendars` SET `calendarorder`=?,`timezone`=?,`components`=?,`ctag`=`ctag`+1 WHERE `id`=?' );
			$result = $stmt->execute(array($order,$timezone,$components,$id));
		}

		OCP\Util::emitHook('OC_Calendar', 'editCalendar', $id);
		return true;
	}

	/**
	 * @brief Edits a user's preferences for a calendar
	 * @param integer $id
	 * @param string $name Default: null
	 * @param string $color Default: null, format: '#RRGGBB(AA)'
	 * @return boolean
	 *
	 * Values not null will be set
	 */
	public static function editCalendarPreferences($id,$name=null,$color=null) {
		// Keep old stuff
		if(is_null($name)) $name = $calendar['displayname'];
		if(is_null($color)) $color = $calendar['calendarcolor'];
		self::setCalendarUserPreference($id,'displayname',$name);
		self::setCalendarUserPreference($id,'calendarcolor',$color);
	}

	/**
	 * @brief Sets a calendar (in)active
	 * @param integer $id
	 * @param boolean $active
	 * @return boolean
	 */
	public static function setCalendarActive($id,$active) {
		return self::setCalendarUserPreference($id,'active',(int)$active);
	}

	/**
	 * @brief Updates ctag for calendar
	 * @param integer $id
	 * @return boolean
	 */
	public static function touchCalendar($id) {
		$stmt = OCP\DB::prepare( 'UPDATE `*PREFIX*clndr_calendars` SET `ctag` = `ctag` + 1 WHERE `id` = ?' );
		$stmt->execute(array($id));

		return true;
	}

	/**
	 * @brief removes a calendar
	 * @param integer $id
	 * @return boolean
	 */
	public static function deleteCalendar($id) {
		$calendar = self::find($id);
		if ($calendar['userid'] != OCP\User::getUser()) {
			$sharedCalendar = OCP\Share::getItemSharedWithBySource('calendar', $id);
			if (!$sharedCalendar || !($sharedCalendar['permissions'] & OCP\PERMISSION_DELETE)) {
				throw new Exception(
					OC_Calendar_App::$l10n->t(
						'You do not have the permissions to delete this calendar.'
					)
				);
			}
		}
		$stmt = OCP\DB::prepare( 'DELETE FROM `*PREFIX*clndr_calendars` WHERE `id` = ?' );
		$stmt->execute(array($id));

		$stmt = OCP\DB::prepare( 'DELETE FROM `*PREFIX*clndr_objects` WHERE `calendarid` = ?' );
		$stmt->execute(array($id));

		$stmt = OCP\DB::prepare( 'DELETE FROM `*PREFIX*clndr_user_preferences` WHERE `calendarid` = ?' );
		$stmt->execute(array($id));

		OCP\Share::unshareAll('calendar', $id);

		OCP\Util::emitHook('OC_Calendar', 'deleteCalendar', $id);
		if(OCP\USER::isLoggedIn() and count(self::allCalendars(OCP\USER::getUser())) == 0) {
			self::addDefaultCalendars(OCP\USER::getUser());
		}

		return true;
	}

	/**
	 * @brief merges two calendars
	 * @param integer $id1
	 * @param integer $id2
	 * @return boolean
	 */
	public static function mergeCalendar($id1, $id2) {
		$calendar = self::find($id1);
		if ($calendar['userid'] != OCP\User::getUser()) {
			$sharedCalendar = OCP\Share::getItemSharedWithBySource('calendar', $id1);
			if (!$sharedCalendar || !($sharedCalendar['permissions'] & OCP\PERMISSION_UPDATE)) {
				throw new Exception(
					OC_Calendar_App::$l10n->t(
						'You do not have the permissions to add to this calendar.'
					)
				);
			}
		}
		$stmt = OCP\DB::prepare('UPDATE `*PREFIX*clndr_objects` SET `calendarid` = ? WHERE `calendarid` = ?');
		$stmt->execute(array($id1, $id2));
		self::touchCalendar($id1);
		self::deleteCalendar($id2);
	}

	/**
	 * @brief Creates a URI for Calendar
	 * @param string $name name of the calendar
	 * @param array  $existing existing calendar URIs
	 * @return string uri
	 */
	public static function createURI($name,$existing) {
		$strip=array(' ','/','?','&');//these may break sync clients
		$name=str_replace($strip,'',$name);
		$name = strtolower($name);

		$newname = $name;
		$i = 1;
		while(in_array($newname,$existing)) {
			$newname = $name.$i;
			$i = $i + 1;
		}
		return $newname;
	}

	/**
	 * @brief gets the userid from a principal path
	 * @return string
	 */
	public static function extractUserID($principaluri) {
		list($prefix,$userid) = Sabre_DAV_URLUtil::splitPath($principaluri);
		return $userid;
	}

	/**
	 * @brief returns the possible color for calendars
	 * @return array
	 */
	public static function getCalendarColorOptions() {
		return array(
			'#ff0000', // "Red"
			'#b3dc6c', // "Green"
			'#ffff00', // "Yellow"
			'#808000', // "Olive"
			'#ffa500', // "Orange"
			'#ff7f50', // "Coral"
			'#ee82ee', // "Violet"
			'#9fc6e7', // "light blue"
		);
	}

	/**
	 * @brief generates the Event Source Info for our JS
	 * @param array $calendar calendar data
	 * @return array
	 */
	public static function getEventSourceInfo($calendar) {
		return array(
			'url' => OCP\Util::linkTo('calendar', 'ajax/events.php').'?calendar_id='.$calendar['id'],
			'backgroundColor' => $calendar['calendarcolor'],
			'borderColor' => '#888',
			'textColor' => self::generateTextColor($calendar['calendarcolor']),
			'cache' => true,
		);
	}

	/*
	 * @brief checks if a calendar name is available for a user
	 * @param string $calendarname
	 * @param string $userid
	 * @return boolean
	 */
	public static function isCalendarNameavailable($calendarname, $userid) {
		$calendars = self::allCalendars($userid);
		foreach($calendars as $calendar) {
			if($calendar['displayname'] == $calendarname) {
				return false;
			}
		}
		return true;
	}

	/*
	 * @brief generates the text color for the calendar
	 * @param string $calendarcolor rgb calendar color code in hex format (with or without the leading #)
	 * (this function doesn't pay attention on the alpha value of rgba color codes)
	 * @return boolean
	 */
	public static function generateTextColor($calendarcolor) {
		if(substr_count($calendarcolor, '#') == 1) {
			$calendarcolor = substr($calendarcolor,1);
		}
		$red = hexdec(substr($calendarcolor,0,2));
		$green = hexdec(substr($calendarcolor,2,2));
		$blue = hexdec(substr($calendarcolor,4,2));
		//recommendation by W3C
		$computation = ((($red * 299) + ($green * 587) + ($blue * 114)) / 1000);
		return ($computation > 130)?'#000000':'#FAFAFA';
	}

	/**
	 * @brief Get the email address of a user
	 * @returns the email address of the user

	 * This method returns the email address of selected user.
	 */
	public static function getUsersEmails($names) {
		return \OCP\Config::getUserValue(\OCP\User::getUser(), 'settings', 'email');
	}

	/**
	 * @brief Remove unused preferences for users of a calendar.
	 * This method removes calendar preferences for the given users and calendar
	 * where the calendar is not shared with the user and the user does not own the calendar.
	 */
	public static function removeUnusedPreferencesForCalendar($calendarid, $userids) {
		$usersToDeletePreferences = array();
		$calendar = OC_Calendar_Calendar::find($calendarid);
		foreach ($userids as $userid) {
			if ($calendar['userid'] != $userid) {
				$sharedCalendar = OCP\Share::getItemSharedWithUser('calendar', $calendarid, $userid);
				if (!$sharedCalendar || !($sharedCalendar['permissions'] & OCP\PERMISSION_READ)) {
					$usersToDeletePreferences[] = $userid;
				}
			}
		}
		if (!empty($usersToDeletePreferences)) {
			$placeholders = implode(',', array_fill(0, count($usersToDeletePreferences), '?'));
			$stmt = OCP\DB::prepare( 'DELETE FROM `*PREFIX*clndr_user_preferences` WHERE `userid` IN (' . $placeholders . ') AND `calendarid`=?' );
			$params = array_merge($usersToDeletePreferences, array($calendarid));
			$result = $stmt->execute($params);
		}
	}

	/**
	 * @brief Remove unused preferences for calendars of a user.
	 * This method removes calendar preferences for the given user where the
	 * calendar is not shared with the user.
	 */
	public static function removeUnusedPreferencesForUser($userid) {
		// Get all of the calendar ids that a user has preferences for, and where they are not the owner of the calendar.
		$stmt = OCP\DB::prepare( 'SELECT DISTINCT `p`.`calendarid` FROM `*PREFIX*clndr_user_preferences` `p` JOIN `*PREFIX*clndr_calendars` `c` ON `c`.`id` = `p`.`calendarid`  WHERE `p`.`userid`=? AND NOT `c`.`userid` = `p`.`userid`' );
		$result = $stmt->execute(array($userid));

		// Filter list of calendars to those where the user does not have access.
		$calendarsToDeletePreferences = array();
		while ($calendarWithPreferences = $result->fetchRow()) {
			$calendarid = $calendarWithPreferences['calendarid'];
			$sharedCalendar = OCP\Share::getItemSharedWithUser('calendar', $calendarid, $userid);
			if (!$sharedCalendar || !($sharedCalendar['permissions'] & OCP\PERMISSION_READ)) {
   				$calendarsToDeletePreferences[] = $calendarid;
			}
		}

		if (!empty($calendarsToDeletePreferences)) {
			$placeholders = implode(',', array_fill(0, count($calendarsToDeletePreferences), '?'));
			$stmt = OCP\DB::prepare( 'DELETE FROM `*PREFIX*clndr_user_preferences` WHERE `userid`=? AND `calendarid` IN (' . $placeholders . ')' );
			$params = array_merge(array($userid), $calendarsToDeletePreferences);
			$result = $stmt->execute($params);
		}
	}
}