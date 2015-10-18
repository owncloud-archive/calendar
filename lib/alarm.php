<?php

OC::$CLASSPATH['Curl'] = 'calendar/3rdparty/curl.php';

/**
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
/*
 * This class manages alarms for calendars
*/
class OC_Calendar_Alarm extends \OC\BackgroundJob\TimedJob
{
    public function __construct()
    {
        $this->interval = 60;
    }

    public function run($argument)
    {
        // OCP\Util::writeLog('calendar background job', 'Started', OCP\Util::DEBUG);
        try {
            self::dispatch();
        } catch (\Exception $e) {
            OC_Log::write('calendar background job', 'Exception: '.$e->getMessage(), OCP\Util::DEBUG);

            return false;
        }
        // OCP\Util::writeLog('calendar background job', 'Finished', OCP\Util::DEBUG);
    }

    /**
     * Fetch all callable alarms and execute them
     * using the convenient function for a given alarm type.
     * @return null
     */
    private static function dispatch()
    {
        $alarms = array('EMAIL' => array(), 'WEBHOOK' => array());
        $sql = 'SELECT userid, displayname, summary, startdate, enddate, alarms.id AS alarmId, alarms.type AS alarmType, alarms.optionfield AS alarmOptionfield
        FROM *PREFIX*clndr_objects AS objects
        JOIN *PREFIX*clndr_calendars AS calendars ON (objects.calendarid=calendars.id)
        JOIN *PREFIX*clndr_alarms AS alarms ON (objects.id=alarms.objid)
        WHERE alarms.senddate <= ? AND alarms.sent = 0 AND alarms.type IN(?, ?)
        ORDER BY userid, startdate, enddate';
        $query = \OCP\DB::prepare($sql);
        $results = $query->execute(array(gmdate('Y-m-d H:i:s'), 'EMAIL', 'WEBHOOK'));
        while ($row = $results->fetchRow()) {
            switch ($row['alarmType']) {
                case 'EMAIL':
                    $alarms['EMAIL'][] = $row;
                break;
                case 'WEBHOOK':
                    $alarms['WEBHOOK'][] = $row;
                break;
            }
        }

        if (!empty($alarms['EMAIL'])) {
            try {
                OCP\Util::writeLog('calendar background job', 'Will send '.count($alarms['EMAIL']).' emails', OCP\Util::DEBUG);
                static::sendEmailsAlarm($alarms['EMAIL']);
            } catch (\Exception $e) {
                OC_Log::write('calendar background job', 'Exception(EMAIL): '.$e->getMessage(), OCP\Util::DEBUG);

                return false;
            }
        }
        if (!empty($alarms['WEBHOOK'])) {
            try {
                OCP\Util::writeLog('calendar background job', 'Will call '.count($alarms['WEBHOOK']).' webhooks', OCP\Util::DEBUG);
                static::sendWebhooksAlarm($alarms['WEBHOOK']);
            } catch (\Exception $e) {
                OC_Log::write('calendar background job', 'Exception(WEBHOOK): '.$e->getMessage(), OCP\Util::DEBUG);

                return false;
            }
        }
    }

    private static function sendWebhooksAlarm($WebhookAlarms)
    {
        foreach ($WebhookAlarms as $alarm) {

            $tpl = new \OCP\Template('calendar', 'event.alarm.text');
            $message = urlencode(static::genMessage($tpl, $alarm));

            $queryTag = null;
            $queryValue = null;

            $matches = array();
            preg_match_all('/([^?=&]+)(=([^&]*))?/', $alarm['alarmOptionfield'], $matches);

            // search in group 3 for $message
            if (!empty($matches[3])) {
                foreach ($matches[3] as $i => $qV) {
                    if (strtolower($qV) == '$message') {
                        $queryTag = $matches[1][$i];
                        $queryValue = $qV;
                        break;
                    }
                }
            }

            $escR = function($string) {
                return preg_quote($string, '. \ + * ? [ ^ ] $ ( ) { } = ! < > | : -');
            };

            if (!empty($queryTag) AND !empty($queryValue)) {
                $url = preg_replace('/([&?])'.$escR($queryTag).'='.$escR($queryValue).'/', '${1}'.$queryTag."=$message", $alarm['alarmOptionfield']);
                // OCP\Util::writeLog('calendar background job', 'calling '.$url, OCP\Util::DEBUG);
                $curl = new Curl($url);
                $curl->createCurl();
                // OCP\Util::writeLog('calendar background job', 'url called '.$curl->getHttpStatus().' '.print_r($curl->getContentType(), true).' '.print_r($curl->getInfos(), true), OCP\Util::DEBUG);
                // OCP\Util::writeLog('calendar background job', 'Webhook called '.$alarm['alarmOptionfield'], OCP\Util::DEBUG);
            } else {
                // maybe the option field should not be saved in log since it can contain some credentials
                OCP\Util::writeLog('calendar background job', 'failed to parse url '.$alarm['alarmOptionfield'], OCP\Util::DEBUG);
            }

            static::setAlarmSent($alarm['alarmId']);
        }
    }

    /**
     * Send all programmed Email alarms
     * @param  Array $emailAlarms   pdo array collection of alarms
     * @return Bool  true
     */
    private static function sendEmailsAlarm($emailAlarms)
    {
        foreach ($emailAlarms as $alarm) {
            $lang = OCP\Config::getUserValue($alarm['userid'], 'calendar', 'lang');
            $l = OC_L10N::get('calendar', $lang);

            $tpls = array('tpl' => new \OCP\Template('calendar', 'event.alarm'), 'tpltxt' => new \OCP\Template('calendar', 'event.alarm.text'));
            $tplclb = function ($tpl) use ($alarm) {
               return static::genMessage($tpl, $alarm);
            };
            $tpls = array_map($tplclb, $tpls);

            $email = OCP\Config::getUserValue($alarm['userid'], 'settings', 'email');
            $fromEmail = OC_Config::getValue('mail_smtpname');
            if ($fromEmail === null) {
                $fromEmail = OC_Config::getValue('mail_from_address').'@'.OC_Config::getValue('mail_domain');
            }

            // OCP\Util::writeLog('calendar background job', 'Send mail to '.$email, OCP\Util::DEBUG);
            $mailer = \OC::$server->getMailer();
            $message = $mailer->createMessage();
            $message->setSubject($l->t('Reminder: %s', $alarm['summary']));
            $message->setFrom(array($fromEmail => 'Owncloud Calendar'));
            $message->setTo(array($email => $alarm['userid']));
            $message->setHtmlBody($tpls['tpl']);
            $message->setPlainBody($tpls['tpltxt']);
            $mailer->send($message);
            //OCP\Util::writeLog('calendar background job', 'Mail sent to '.$email, OCP\Util::DEBUG);

            // static::setAlarmSent($alarm['alarmId']);
        }

        return true;
    }

   /**
     * Set an alarm as sent in database
     * @param [type] $alarmId [description]
     */
    private static function setAlarmSent($alarmId)
    {
        $stmt = OCP\DB::prepare('UPDATE `*PREFIX*clndr_alarms` SET sent = 1 WHERE `id` = ?');
        $stmt->execute(array($alarmId));
    }

    /**
     * Generates the message to send to the user
     * @param  \OCP\Template    &$tpl      the template
     * @param  Array            $alarm     the pdo row to user
     * @return string                      the rendered template
     */
    private static function genMessage(&$tpl, $alarm)
    {
        $tz = OC_Calendar_App::getTimezone();
        $lang = OCP\Config::getUserValue($alarm['userid'], 'calendar', 'lang');
        $l = OC_L10N::get('calendar', $lang);

        $startDate = new DateTime($alarm['startdate'], new DateTimeZone('UTC'));
        $endDate = new DateTime($alarm['enddate'], new DateTimeZone('UTC'));
        $startDate->setTimezone(new DateTimeZone($tz));
        $endDate->setTimezone(new DateTimeZone($tz));
        $timeFormat = OCP\Config::getUserValue($alarm['userid'], 'calendar', 'timeformat', '24') == 24 ? 'H:i' : 'h:i a';
        $dateFormat = OCP\Config::getUserValue($alarm['userid'], 'calendar', 'dateformat', 'dd-mm-yy') == 'dd-mm-yy' ? 'd-m-Y' : 'm-d-Y';
        $dateTimeFormat = $dateFormat.' '.$timeFormat;

        $tpl->assign('calendarName', $alarm['displayname']);
        $tpl->assign('event', $alarm['summary']);
        $tpl->assign('date', $startDate->format($dateTimeFormat).' - '.$endDate->format($dateTimeFormat));
        $tpl->assign('when', $l->t('When: '));
        $tpl->assign('calendar', $l->t('Calendar: '));
        return $tpl->fetchPage();
    }
}
