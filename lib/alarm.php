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
	        OCP\Util::writeLog('calendar background job', 'Started', OCP\Util::DEBUG);
		self::sendEmailAlarm();
		OCP\Util::writeLog('calendar background job', 'Finished', OCP\Util::DEBUG);
	}

	private static function sendEmailAlarm() {
		try{
			$tz = OC_Calendar_App::getTimezone();

			$sql = 'SELECT userid, displayname, summary, startdate, enddate, alarms.id as alarmId
                    FROM *PREFIX*clndr_objects AS objects
                    JOIN *PREFIX*clndr_calendars AS calendars ON (objects.calendarid=calendars.id)
                    JOIN *PREFIX*clndr_alarms AS alarms ON (objects.id=alarms.objid)
                    WHERE alarms.senddate <= UTC_TIMESTAMP() AND alarms.sent = 0 AND alarms.type = ?
                    ORDER BY userid, startdate, enddate';

			$query = \OCP\DB::prepare($sql);
			$result = $query->execute(array('EMAIL'));
			
			OCP\Util::writeLog('calendar background job', 'Will send '.$result->rowCount().' emails', OCP\Util::DEBUG);
			while($row = $result->fetchRow()){
				$lang = OCP\Config::getUserValue($row['userid'], 'calendar', 'lang');
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

				$content = $tpl->fetchPage();

				$email = OCP\Config::getUserValue($row['userid'], 'settings', 'email');
				$fromEmail = OC_Config::getValue('mail_smtpname');
				if($fromEmail === NULL){
					$fromEmail = OC_Config::getValue('mail_from_address').'@'.OC_Config::getValue('mail_domain');
				}
				
				//OCP\Util::writeLog('calendar background job', 'Send mail to '.$email, OCP\Util::DEBUG);
				$mailer = \OC::$server->getMailer();
				$message = $mailer->createMessage();
				$message->setSubject($l->t('Reminder: %s', $row['summary']));
				$message->setFrom(array($fromEmail => 'Owncloud Calendar'));
				$message->setTo(array($email => $row['userid']));
				$message->setPlainBody($content);
				$mailer->send($message);
				
				//OCP\Util::writeLog('calendar background job', 'Mail sent to '.$email, OCP\Util::DEBUG);							
				$stmt = OCP\DB::prepare('UPDATE `*PREFIX*clndr_alarms` SET sent = 1 WHERE `id` = ?');
				$stmt->execute(array($row['alarmId']));
			}
		} catch(\Exception $e){
			OC_Log::write('calendar background job', 'Exception: '.$e->getMessage(), OCP\Util::DEBUG);		
			return false;
		}
		return true;
	}

}
