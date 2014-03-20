<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Db;

class BackendCollection extends Collection {

	/**
	 * @brief get a collection of all enabled calendars within collection
	 * @return CalendarCollection of all enabled calendars
	 */
	public function enabled() {
		return $this->search('enabled', true);
	}

	/**
	 * @brief get a collection of all disabled calendars within collection
	 * @return CalendarCollection of all disabled calendars
	 */
	public function disabled() {
		return $this->search('enabled', false);
	}
}