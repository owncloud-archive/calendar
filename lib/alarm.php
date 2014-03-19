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
			
				$subject = 'You have ' . $task_count . ' event' . (($task_count > 1)? 's': '') . ' scheduled today.';
				$message = 'Dear ' . $member . ',<br /><p style="text-indent: 50px;" >Your tasks scheduled today at ownCloud are listed below.</p>';
			
				$message .= '<table style="border: 1px black solid;"><tr><th style="border: 1px black solid;">Event</th><th style="border: 1px black solid;">Starting time</th><th style="border: 1px black solid;">Ending time</th></tr>';
			
				for($i = 0; $i < $task_count; $i++)
				{
					$message .= '<tr><td style="border: 1px black solid;">' . $event[$i] . '</td><td style="border: 1px black solid;">' . $starting[$i] . '</td><td style="border: 1px black solid;">' . $ending[$i] . '</td></tr>';
				}
			
				$message .= '</table><br />';
			
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
