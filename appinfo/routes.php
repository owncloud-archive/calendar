<?php
/**
 * Copyright (c) 2014 Georg Ehrke
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Calendar;

use \OCA\Calendar\AppFramework\routing\RouteConfig;
use \OCA\Calendar\DependencyInjection\DIContainer;

require_once(__DIR__ . '/../dependencyinjection/dicontainer.php');

$routeConfig = new RouteConfig(new DIContainer(), $this, __DIR__ . '/routes.yaml');
$routeConfig->register();