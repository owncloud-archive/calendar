<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Db;

use \OCA\Calendar\AppFramework\Core\API;
use \OCA\Calendar\AppFramework\Db\Mapper;
use \OCA\Calendar\AppFramework\Db\DoesNotExistException;

use \OCA\Calendar\Db\Timezone;

class TimezoneMapper extends Mapper {
	private $tableName;
	private $keyValueTableName;

	/**
	 * @param API $api: Instance of the API abstraction layer
	 */
	public function __construct($api, $tablename='clndr_timezones'){
		parent::__construct($api, $tablename);

		$this->tableName = '*PREFIX*' . $tablename;
	}
}