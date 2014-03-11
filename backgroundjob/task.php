<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Backgroundjob;

use \OCA\Calendar\DependencyInjection\DIContainer;

class Task {
	static public function run() {
		$container = new DIContainer();

		$container['Updater']->beforeUpdate();
		$container['Updater']->update();
		$container['Updater']->afterUpdate();
	}
}