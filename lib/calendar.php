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
 *     active INTEGER UNSIGNED NOT NULL DEFAULT '0',
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
	 * @param string $uid User ID
	 * @param boolean $active Only return calendars with this $active state, default(=false) is don't care
	 * @param boolean $createIfNecessary create calendars if no exist yet
	 * @return array
	 */
	public static function allCalendars($uid, $active=false, $createIfNecessary=true) {
		$values = array($uid);
		$active_where = '';
		if (!is_null($active) && $active) {
			$active_where = ' AND `active` = ?';
			$values[] = (int)$active;
		}
		$stmt = OCP\DB::prepare( 'SELECT * FROM `*PREFIX*clndr_calendars` WHERE `userid` = ?' . $active_where );
		$result = $stmt->execute($values);

		$calendars = array();
		$owned_calendar_ids = array();
		while( $row = $result->fetchRow()) {
			$row['permissions'] = OCP\PERMISSION_CREATE
				| OCP\PERMISSION_READ | OCP\PERMISSION_UPDATE
				| OCP\PERMISSION_DELETE | OCP\PERMISSION_SHARE;
			$row['description'] = '';
			$calendars[] = $row;
			$owned_calendar_ids[] = $row['id'];
		}

		if ($active === false && count($calendars) === 0 && $createIfNecessary === true) {
			self::addDefaultCalendars($uid);
			return self::allCalendars($uid, false);
		}

		$shared_calendars = OCP\Share::getItemsSharedWith('calendar', OC_Share_Backend_Calendar::FORMAT_CALENDAR);
		// Remove shared calendars that are already owned by the user.
		foreach ($shared_calendars as $key => $calendar) {
			if (in_array($calendar['id'], $owned_calendar_ids)) {
				unset($shared_calendars[$key]);
			}
		}

		$calendars = array_merge($calendars, $shared_calendars);

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
	 * @return associative array
	 */
	public static function find($id) {
		$stmt = OCP\DB::prepare( 'SELECT * FROM `*PREFIX*clndr_calendars` WHERE `id` = ?' );
		$result = $stmt->execute(array($id));

		$row = $result->fetchRow();
		if($row['userid'] != OCP\USER::getUser() && !OC_Group::inGroup(OCP\User::getUser(), 'admin')) {
			$sharedCalendar = OCP\Share::getItemSharedWithBySource('calendar', $id);
			if (!$sharedCalendar || !($sharedCalendar['permissions'] & OCP\PERMISSION_READ)) {
				return $row; // I have to return the row so e.g. OC_Calendar_Object::getowner() works.
			}
			$row['permissions'] = $sharedCalendar['permissions'];
		} else {
			$row['permissions'] = OCP\PERMISSION_ALL;
		}
		return $row;
	}

	/**
	 * @brief Creates a new calendar
	 * @param string $userid
	 * @param string $name
	 * @param string $components Default: "VEVENT,VTODO,VJOURNAL"
	 * @param string $timezone Default: null
	 * @param integer $order Default: 1
	 * @param string $color Default: null, format: '#RRGGBB(AA)'
	 * @return int
	 */
	public static function addCalendar($userid,$name,$components='VEVENT,VTODO,VJOURNAL',$timezone=null,$order=0,$color=null) {
		$all = self::allCalendars($userid, false, false);
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
	 * @return int
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
		// Need these ones for checking uri
		$calendar = self::find($id);
		if ($calendar['userid'] != OCP\User::getUser()) {{
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

		$stmt = OCP\DB::prepare( 'UPDATE `*PREFIX*clndr_calendars` SET `displayname`=?,`calendarorder`=?,`calendarcolor`=?,`timezone`=?,`components`=?,`ctag`=`ctag`+1 WHERE `id`=?' );
		$result = $stmt->execute(array($name,$order,$color,$timezone,$components,$id));

		OCP\Util::emitHook('OC_Calendar', 'editCalendar', $id);
		return true;
	}

	/**
	 * @brief Sets a calendar (in)active
	 * @param integer $id
	 * @param boolean $active
	 * @return boolean
	 */
	public static function setCalendarActive($id,$active) {
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
		$stmt = OCP\DB::prepare( 'UPDATE `*PREFIX*clndr_calendars` SET `active` = ? WHERE `id` = ?' );
		$stmt->execute(array((int)$active, $id));

		return true;
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
		if (!self::isAllowedToDeleteCalendar($calendar)) {
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
		if ($calendar['userid'] != OCP\User::getUser() && !OC_Group::inGroup(OCP\User::getUser(), 'admin')) {
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
		list($prefix,$userid) = \Sabre\DAV\URLUtil::splitPath($principaluri);
		return $userid;
	}

	/**
	 * @brief returns the possible color for calendars
	 * @return array
	 */
	public static function getCalendarColorOptions() {
		return array(
//			'#190000', // Red              dark 90%
//			'#190C00', // Orange           dark 90%
//			'#191900', // Yellow           dark 90%
//			'#0C1900', // Chartreuse Green dark 90%
//			'#001900', // Green            dark 90%
//			'#00190C', // Spring Green     dark 90%
//			'#001919', // Cyan             dark 90%
//			'#000C19', // Azure Blue       dark 90%
//			'#000019', // Blue             dark 90%
//			'#0C0019', // Violet           dark 90%
//			'#190019', // Magenta          dark 90%
//			'#19000C', // Rose             dark 90%
//			'#141414', // Gray 20
//			'#320000', // Red              dark 80%
//			'#321900', // Orange           dark 80%
//			'#323200', // Yellow           dark 80%
//			'#193200', // Chartreuse Green dark 80%
//			'#003200', // Green            dark 80%
//			'#003219', // Spring Green     dark 80%
//			'#003232', // Cyan             dark 80%
//			'#001932', // Azure Blue       dark 80%
//			'#000032', // Blue             dark 80%
//			'#190032', // Violet           dark 80%
//			'#320032', // Magenta          dark 80%
//			'#320019', // Rose             dark 80%
//			'#202020', // Gray 32
//			'#4C0000', // Red              dark 70%
//			'#4C2600', // Orange           dark 70%
//			'#4C4C00', // Yellow           dark 70%
//			'#264C00', // Chartreuse Green dark 70%
//			'#004C00', // Green            dark 70%
//			'#004C26', // Spring Green     dark 70%
//			'#004C4C', // Cyan             dark 70%
//			'#00264C', // Azure Blue       dark 70%
//			'#00004C', // Blue             dark 70%
//			'#26004C', // Violet           dark 70%
//			'#4C004C', // Magenta          dark 70%
//			'#4C0026', // Rose             dark 70%
//			'#2C2C2C', // Gray 44
			'#660000', // Red              dark 60%
			'#663200', // Orange           dark 60%
			'#666600', // Yellow           dark 60%
			'#326600', // Chartreuse Green dark 60%
			'#006600', // Green            dark 60%
			'#006632', // Spring Green     dark 60%
			'#006666', // Cyan             dark 60%
			'#003266', // Azure Blue       dark 60%
			'#000066', // Blue             dark 60%
			'#320066', // Violet           dark 60%
			'#660066', // Magenta          dark 60%
			'#660032', // Rose             dark 60%
			'#383838', // Gray 56
//			'#7F0000', // Red              dark 50%
//			'#7F3F00', // Orange           dark 50%
//			'#7F7F00', // Yellow           dark 50%
//			'#3F7F00', // Chartreuse Green dark 50%
//			'#007F00', // Green            dark 50%
//			'#007F3F', // Spring Green     dark 50%
//			'#007F7F', // Cyan             dark 50%
//			'#003F7F', // Azure Blue       dark 50%
//			'#00007F', // Blue             dark 50%
//			'#3F007F', // Violet           dark 50%
//			'#7F007F', // Magenta          dark 50%
//			'#7F003F', // Rose             dark 50%
//			'#444444', // Gray 68
			'#990000', // Red              dark 40%
			'#994C00', // Orange           dark 40%
			'#999900', // Yellow           dark 40%
			'#4C9900', // Chartreuse Green dark 40%
			'#009900', // Green            dark 40%
			'#00994C', // Spring Green     dark 40%
			'#009999', // Cyan             dark 40%
			'#004C99', // Azure Blue       dark 40%
			'#000099', // Blue             dark 40%
			'#4C0099', // Violet           dark 40%
			'#990099', // Magenta          dark 40%
			'#99004C', // Rose             dark 40%
			'#505050', // Gray 80
//			'#B20000', // Red              dark 30%
//			'#B25800', // Orange           dark 30%
//			'#B2B200', // Yellow           dark 30%
//			'#58B200', // Chartreuse Green dark 30%
//			'#00B200', // Green            dark 30%
//			'#00B258', // Spring Green     dark 30%
//			'#00B2B2', // Cyan             dark 30%
//			'#0058B2', // Azure Blue       dark 30%
//			'#0000B2', // Blue             dark 30%
//			'#5800B2', // Violet           dark 30%
//			'#B200B2', // Magenta          dark 30%
//			'#B20058', // Rose             dark 30%
//			'#5C5C5C', // Gray 92
			'#CC0000', // Red              dark 20%
			'#CC6500', // Orange           dark 20%
			'#CCCC00', // Yellow           dark 20%
			'#65CC00', // Chartreuse Green dark 20%
			'#00CC00', // Green            dark 20%
			'#00CC65', // Spring Green     dark 20%
			'#00CCCC', // Cyan             dark 20%
			'#0065CC', // Azure Blue       dark 20%
			'#0000CC', // Blue             dark 20%
			'#6500CC', // Violet           dark 20%
			'#CC00CC', // Magenta          dark 20%
			'#CC0065', // Rose             dark 20%
			'#686868', // Gray 104
//			'#E50000', // Red              dark 10%
//			'#E57200', // Orange           dark 10%
//			'#E5E500', // Yellow           dark 10%
//			'#72E500', // Chartreuse Green dark 10%
//			'#00E500', // Green            dark 10%
//			'#00E572', // Spring Green     dark 10%
//			'#00E5E5', // Cyan             dark 10%
//			'#0072E5', // Azure Blue       dark 10%
//			'#0000E5', // Blue             dark 10%
//			'#7200E5', // Violet           dark 10%
//			'#E500E5', // Magenta          dark 10%
//			'#E50072', // Rose             dark 10%
//			'#747474', // Gray 116
			'#FF0000', // Red
			'#FF7F00', // Orange
			'#FFFF00', // Yellow
			'#7FFF00', // Chartreuse Green
			'#00FF00', // Green
			'#00FF7F', // Spring Green
			'#00FFFF', // Cyan
			'#007FFF', // Azure Blue
			'#0000FF', // Blue
			'#7F00FF', // Violet
			'#FF00FF', // Magenta
			'#FF007F', // Rose
			'#808080', // Gray 128
//			'#FF1919', // Red              tint 10%
//			'#FF8B19', // Orange           tint 10%
//			'#FFFF19', // Yellow           tint 10%
//			'#8BFF19', // Chartreuse Green tint 10%
//			'#19FF19', // Green            tint 10%
//			'#19FF8B', // Spring Green     tint 10%
//			'#19FFFF', // Cyan             tint 10%
//			'#198BFF', // Azure Blue       tint 10%
//			'#1919FF', // Blue             tint 10%
//			'#8B19FF', // Violet           tint 10%
//			'#FF19FF', // Magenta          tint 10%
//			'#FF198B', // Rose             tint 10%
//			'#8C8C8C', // Gray 140
			'#FF3333', // Red              tint 20%
			'#FF9833', // Orange           tint 20%
			'#FFFF33', // Yellow           tint 20%
			'#98FF33', // Chartreuse Green tint 20%
			'#33FF33', // Green            tint 20%
			'#33FF98', // Spring Green     tint 20%
			'#33FFFF', // Cyan             tint 20%
			'#3398FF', // Azure Blue       tint 20%
			'#3333FF', // Blue             tint 20%
			'#9833FF', // Violet           tint 20%
			'#FF33FF', // Magenta          tint 20%
			'#FF3398', // Rose             tint 20%
			'#989898', // Gray 152
//			'#FF4C4C', // Red              tint 30%
//			'#FFA54C', // Orange           tint 30%
//			'#FFFF4C', // Yellow           tint 30%
//			'#A5FF4C', // Chartreuse Green tint 30%
//			'#4CFF4C', // Green            tint 30%
//			'#4CFFA5', // Spring Green     tint 30%
//			'#4CFFFF', // Cyan             tint 30%
//			'#4CA5FF', // Azure Blue       tint 30%
//			'#4C4CFF', // Blue             tint 30%
//			'#A54CFF', // Violet           tint 30%
//			'#FF4CFF', // Magenta          tint 30%
//			'#FF4CA5', // Rose             tint 30%
//			'#A4A4A4', // Gray 164
			'#FF6666', // Red              tint 40%
			'#FFB266', // Orange           tint 40%
			'#FFFF66', // Yellow           tint 40%
			'#B2FF66', // Chartreuse Green tint 40%
			'#66FF66', // Green            tint 40%
			'#66FFB2', // Spring Green     tint 40%
			'#66FFFF', // Cyan             tint 40%
			'#66B2FF', // Azure Blue       tint 40%
			'#6666FF', // Blue             tint 40%
			'#B266FF', // Violet           tint 40%
			'#FF66FF', // Magenta          tint 40%
			'#FF66B2', // Rose             tint 40%
			'#B0B0B0', // Gray 176
//			'#FF7F7F', // Red              tint 50%
//			'#FFBF7F', // Orange           tint 50%
//			'#FFFF7F', // Yellow           tint 50%
//			'#BFFF7F', // Chartreuse Green tint 50%
//			'#7FFF7F', // Green            tint 50%
//			'#7FFFBF', // Spring Green     tint 50%
//			'#7FFFFF', // Cyan             tint 50%
//			'#7FBFFF', // Azure Blue       tint 50%
//			'#7F7FFF', // Blue             tint 50%
//			'#BF7FFF', // Violet           tint 50%
//			'#FF7FFF', // Magenta          tint 50%
//			'#FF7FBF', // Rose             tint 50%
//			'#BCBCBC', // Gray 188
			'#FF9999', // Red              tint 60%
			'#FFCB99', // Orange           tint 60%
			'#FFFF99', // Yellow           tint 60%
			'#CBFF99', // Chartreuse Green tint 60%
			'#99FF99', // Green            tint 60%
			'#99FFCB', // Spring Green     tint 60%
			'#99FFFF', // Cyan             tint 60%
			'#99CBFF', // Azure Blue       tint 60%
			'#9999FF', // Blue             tint 60%
			'#CB99FF', // Violet           tint 60%
			'#FF99FF', // Magenta          tint 60%
			'#FF99CB', // Rose             tint 60%
			'#C8C8C8', // Gray 200
//			'#FFB2B2', // Red              tint 70%
//			'#FFD8B2', // Orange           tint 70%
//			'#FFFFB2', // Yellow           tint 70%
//			'#D8FFB2', // Chartreuse Green tint 70%
//			'#B2FFB2', // Green            tint 70%
//			'#B2FFD8', // Spring Green     tint 70%
//			'#B2FFFF', // Cyan             tint 70%
//			'#B2D8FF', // Azure Blue       tint 70%
//			'#B2B2FF', // Blue             tint 70%
//			'#D8B2FF', // Violet           tint 70%
//			'#FFB2FF', // Magenta          tint 70%
//			'#FFB2D8', // Rose             tint 70%
//			'#D4D4D4', // Gray 212
//			'#FFCCCC', // Red              tint 80%
//			'#FFE5CC', // Orange           tint 80%
//			'#FFFFCC', // Yellow           tint 80%
//			'#E5FFCC', // Chartreuse Green tint 80%
//			'#CCFFCC', // Green            tint 80%
//			'#CCFFE5', // Spring Green     tint 80%
//			'#CCFFFF', // Cyan             tint 80%
//			'#CCE5FF', // Azure Blue       tint 80%
//			'#CCCCFF', // Blue             tint 80%
//			'#E5CCFF', // Violet           tint 80%
//			'#FFCCFF', // Magenta          tint 80%
//			'#FFCCE5', // Rose             tint 80%
//			'#E0E0E0', // Gray 224
//			'#FFE5E5', // Red              tint 90%
//			'#FFF2E5', // Orange           tint 90%
//			'#FFFFE5', // Yellow           tint 90%
//			'#F2FFE5', // Chartreuse Green tint 90%
//			'#E5FFE5', // Green            tint 90%
//			'#E5FFF2', // Spring Green     tint 90%
//			'#E5FFFF', // Cyan             tint 90%
//			'#E5F2FF', // Azure Blue       tint 90%
//			'#E5E5FF', // Blue             tint 90%
//			'#F2E5FF', // Violet           tint 90%
//			'#FFE5FF', // Magenta          tint 90%
//			'#FFE5F2', // Rose             tint 90%
//			'#ECECEC', // Gray 236
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
		return \OCP\Config::getUserValue($names, 'settings', 'email');
	}


	/**
	 * @param array $calendar
	 * @param string $userId
	 * @return boolean
	 */
	private static function isAllowedToDeleteCalendar($calendar) {
		$userId = OCP\User::getUser();

		//in case it is called by command line or cron
		if($userId == '') {
			return true;
		}
		if ($calendar['userid'] === $userId) {
			return true;
		}
		if (OC_User::isAdminUser($userId)) {
			return true;
		}
		if (OC_SubAdmin::isUserAccessible($userId, $calendar['userid'])) {
			return true;
		}

		return false;
	}
}
