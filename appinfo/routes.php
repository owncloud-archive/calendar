<?php
/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de> 
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar;
require_once(__DIR__ . '/bootstrap.php');

use \OCA\AppFramework\App as App;

/**
 * Normal Routes
 */
$this->create('calendar_index', '/')->action(
	function($params){
		App::main('CalendarController', 'index', $params, new DIContainer());
	}
);

$this->create('calendar_index_param', '/test/{test}')->action(
	function($params){
		App::main('CalendarController', 'index', $params, new DIContainer());
	}
);

$this->create('apptemplate_advanced_index_redirect', '/redirect')->action(
	function($params){
		App::main('ItemController', 'redirectToIndex', $params, new DIContainer());
	}
);

/**
 * Ajax Routes
 */
$this->create('apptemplate_advanced_ajax_setsystemvalue', '/setsystemvalue')->post()->action(
	function($params){
		App::main('ItemController', 'setSystemValue', $params, new DIContainer());
	}
);