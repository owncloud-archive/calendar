<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Db;

class ObjectType {
	const EVENT		= 1;
	const JOURNAL	= 2;
	const TODO		= 4;
	const ALL		= 7;

	public static function getAsString($type) {
		$types = array();

		if($type & self::EVENT) {
			$types[] = 'VEVENT';
		}
		if($type & self::JOURNAL) {
			$types[] = 'VJOURNAL';
		}
		if($type & self::TODO) {
			$types[] = 'VTODO';
		}

		$string = implode(', ', $types);
		return $string;
	}

	public static function getAsReadableString($type) {
		$types = array();

		if($type & self::EVENT) {
			$types[] = 'events';
		}
		if($type & self::JOURNAL) {
			$types[] = 'journals';
		}
		if($type & self::TODO) {
			$types[] = 'todos';
		}

		$string = implode(', ', $types);
		return $string; 
	}
}