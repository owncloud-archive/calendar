<?php
/**
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
/*
 * This class manages alarms for calendars
 */
class OC_Calendar_Alarm extends \OC\BackgroundJob\TimedJob{

	public function __construct() {
		$this->interval = 60;
	}

	public function run($argument) {
		self::sendEmailAlarm();
	}

	public function sendEmailAlarm() {
		try{
			$tz = OC_Calendar_App::getTimezone();

			$sql = 'SELECT userid, displayname, summary, startdate, enddate, alarms.id as alarmId
                    FROM *PREFIX*clndr_objects AS objects
                    JOIN *PREFIX*clndr_calendars AS calendars ON (objects.calendarid=calendars.id)
                    JOIN *PREFIX*clndr_alarms AS alarms ON (objects.id=alarms.objid)
                    WHERE alarms.senddate <= UTC_TIMESTAMP() AND alarms.sended = 0 AND alarms.type = ?
                    ORDER BY userid, startdate, enddate';

			$query = \OCP\DB::prepare($sql);
			$result = $query->execute(array('EMAIL'));

			$alarmsIdsSended = array();
			while($row = $result->fetchRow()){
				$lang = OC_Preferences::getValue($row['userid'], 'core', 'lang');
				$l = OC_L10N::get('calendar', $lang);

				$startDate = new DateTime($row['startdate'], new DateTimeZone('UTC'));
				$endDate = new DateTime($row['enddate'], new DateTimeZone('UTC'));
				$startDate->setTimezone(new DateTimeZone($tz));
				$endDate->setTimezone(new DateTimeZone($tz));

				$timeFormat = OCP\Config::getUserValue($row['userid'], 'calendar', 'timeformat', '24') == 24 ? 'H:i' : 'h:i a';
				$dateFormat = OCP\Config::getUserValue($row['userid'], 'calendar', 'dateformat', 'dd-mm-yy') == 'dd-mm-yy' ? 'd-m-Y' : 'm-d-Y';
				$dateTimeFormat = $dateFormat.' '.$timeFormat;

				$tpl = new \OCP\Template('calendar', 'event.alarm');
				$tpl->assign('calendarName', $row['displayname']);
				$tpl->assign('event', $row['summary']);
				$tpl->assign('date', $startDate->format($dateTimeFormat).' - '.$endDate->format($dateTimeFormat));
				$tpl->assign('when', $l->t('When: '));
				$tpl->assign('calendar', $l->t('Calendar: '));

				$message = $tpl->fetchPage();

				$email = OC_Preferences::getValue($row['userid'], 'settings', 'email');
				OC_Mail::send($email, $row['userid'], $l->t('Reminder: %s', $row['summary']), $message, OC_Config::getValue('mail_smtpname', ''), $l->t('Owncloud Event Reminder'), true);

				$alarmsIdsSended[] = $row['alarmId'];
			}

			$stmt = OCP\DB::prepare('UPDATE `*PREFIX*clndr_alarms` SET sended = 1 WHERE `id` IN(?)');
			$stmt->execute(array(implode(',', $alarmsIdsSended)));
		} catch(\Exception $e){
			OC_Log::write('calendar', __METHOD__.', Exception: '.$e->getMessage(), OCP\Util::DEBUG);
			return false;
		}

		return true;
	}

}