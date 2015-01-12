<?php
/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev@georgswebsite.de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
/**
 * Remainder support by Suresh Prakash <sureshsonait@gmail.com> <https://github.com/sureshprakash>
 * This class manages reminders for calendars
 */
class OC_Calendar_Alarm extends \OC\BackgroundJob\TimedJob
{
	public function __construct()
	{
		$this->interval = 60 * 60 * 24; // 1 day = 60 * 60 * 24 seconds
	}
	
	public function run($argument)
	{
		self::sendEmailRemainder();
	}
	
	public function sendEmailRemainder()
	{
		try
		{
			$l = OC_L10N::get('calendar');
			
			$separator = '~~~';
		
			$sql = 'SELECT `userid`, 
					GROUP_CONCAT(`summary` ORDER BY `enddate`,`startdate` SEPARATOR \'' . $separator . '\') AS events, 
					GROUP_CONCAT(`startdate` ORDER BY `enddate`,`startdate` SEPARATOR \'' . $separator . '\') AS startdate, 
					GROUP_CONCAT(`enddate` ORDER BY `enddate`,`startdate` SEPARATOR \'' . $separator . '\') AS enddate 
					FROM `oc_clndr_objects` AS objects 
					JOIN `oc_clndr_calendars` AS calendars ON (`objects`.`calendarid`=`calendars`.`id`) 
					WHERE CURDATE()>=DATE(`startdate`) AND CURDATE()<=DATE(`enddate`) 
					GROUP BY `userid` 
					ORDER BY `enddate`,`startdate`';
				
			$query = \OCP\DB::prepare($sql);
		
			$result = $query->execute(array());
		
			while($row = $result->fetchRow())
			{
				$member = $row['userid'];
				$event = explode($separator, $row['events']);
				$starting = explode($separator, $row['startdate']);
				$ending = explode($separator, $row['enddate']);
			
				$task_count = count($starting);
				
				$subject = $l->t('You have %d %s scheduled today.', array($task_count, ($task_count == 1)? $l->t('event'): $l->t('events')));
				
				$tpl = new \OCP\Template('calendar', 'event.reminder');
				$tpl->assign('member', $member);
				$tpl->assign('event', $event);
				$tpl->assign('starting', $starting);
				$tpl->assign('ending', $ending);
				
				$message = $tpl->fetchPage();
				
				/* $this->inc('event.reminder', 
						array("event" => $event,
							  "starting" => $starting,
							  "ending" => $ending)
						  ); */
						  
				OC_Mail::send(OC_Preferences::getValue($member, 'settings', 'email'), $member, $subject, $message, OC_Config::getValue('mail_smtpname', ''), 'Owncloud Event Reminder', true);
			}
		}
		catch(\Exception $e)
		{
			OC_Log::write('calendar', __METHOD__ . ', Exception: ' . $e->getMessage(), OCP\Util::DEBUG);
			return false;
		}
		
		return true;
	}
}
