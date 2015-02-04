<?php

/**
 * ownCloud
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

namespace OCA\Calendar\Search;

/**
 * A calendar event
 */
class Event extends \OCP\Search\Result {

	/**
	 * Type name; translated in templates
	 *
	 * @var string
	 */
	public $type = 'event';

	/**
	 * Used by the client JS to display additional information under the event summary
	 *
	 * @var string
	 */
	public $text;

	/**
	 * Start time for the event
	 *
	 * @var string human-readable string in RFC2822 format
	 */
	public $start_time;

	/**
	 * End time for the event
	 *
	 * @var string human-readable string in RFC2822 format
	 */
	public $end_time;

	/**
	 * Last time modified
	 *
	 * @var string human-readable string in RFC2822 format
	 */
	public $modified;

	/**
	 * Indicate whether the event is a repeating event
	 *
	 * @var boolean
	 */
	public $repeating;

	/**
	 * Constructor
	 *
	 * @param array $data
	 * @return \OCA\Calendar\Search\Event
	 */
	public function __construct(array $data = null) {
		// set default properties
		$this->id = $data['id'];
		$this->name = $data['summary'];
		$this->link = \OCP\Util::linkTo('calendar', 'index.php') . '?showevent=' . urlencode($data['id']);
		// do calendar-specific setup
		$l = new \OC_l10n('calendar');
		$calendar_data = \OC_VObject::parse($data['calendardata']);
		$vevent = $calendar_data->VEVENT;
		// get start time
		$dtstart = $vevent->DTSTART;
		$start_dt = $dtstart->getDateTime();
		$start_dt->setTimezone($this->getUserTimezone());
		$this->start_time = $start_dt->format('r');
		// get end time
		$dtend = \OC_Calendar_Object::getDTEndFromVEvent($vevent);
		$end_dt = $dtend->getDateTime();
		$end_dt->setTimezone($this->getUserTimezone());
		$this->end_time = $end_dt->format('r');
		// create text description
		if ($dtstart->getDateType() == \Sabre\VObject\Property\DateTime::DATE) {
			$end_dt->modify('-1 sec');
			if ($start_dt->format('d.m.Y') != $end_dt->format('d.m.Y')) {
				$this->text = $l->t('Date') . ': ' . $start_dt->format('d.m.Y') . ' - ' . $end_dt->format('d.m.Y');
			} else {
				$this->text = $l->t('Date') . ': ' . $start_dt->format('d.m.Y');
			}
		} else {
			$this->text = $l->t('Date') . ': ' . $start_dt->format('d.m.y H:i') . ' - ' . $end_dt->format('d.m.y H:i');
		}
		// set last modified time
		$this->modified = date('r', $data['lastmodified']);
		// set repeating property
		$this->repeating = (bool)$data['repeating'];
	}

	/**
	 * Cache the user timezone to avoid multiple requests (it looks like it
	 * uses a DB call in config to return this)
	 *
	 * @staticvar null $timezone
	 * @return DateTimeZone
	 */
	public static function getUserTimezone() {
		static $timezone = null;
		if ($timezone === null) {
			$timezone = new \DateTimeZone(\OC_Calendar_App::getTimezone());
		}
		return $timezone;
	}
}
