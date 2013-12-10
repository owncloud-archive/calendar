<?php

/**
 * ownCloud - App Framework
 *
 * @author Bernhard Posselt
 * @copyright 2012 Bernhard Posselt nukeawhale@gmail.com
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
namespace OCA\Calendar\AppFramework\DependencyInjection;

use OCA\Calendar\AppFramework\Core\API;
use OCA\Calendar\AppFramework\Http\Http;
use OCA\Calendar\AppFramework\Http\Request;
use OCA\Calendar\AppFramework\Http\Dispatcher;
use OCA\Calendar\AppFramework\Middleware\MiddlewareDispatcher;
use OCA\Calendar\AppFramework\Middleware\Http\HttpMiddleware;
use OCA\Calendar\AppFramework\Middleware\Security\SecurityMiddleware;
use OCA\Calendar\AppFramework\Utility\TimeFactory;

/**
 * This class extends Pimple (http://pimple.sensiolabs.org/) for reusability
 * To use this class, extend your own container from this. Should you require it
 * you can overwrite the dependencies with your own classes by simply redefining
 * a dependency
 */
class DIContainer extends \Pimple {


	/**
	 * Put your class dependencies in here
	 * @param string $appName the name of the app
	 */
	public function __construct($appName){

		$this['AppName'] = $appName;

		$this['API'] = $this->share(function($c){
			return new API($c['AppName']);
		});

		/**
		 * Http
		 */
		$this['Request'] = $this->share(function($c) {
			$params = json_decode(file_get_contents('php://input'), true);
			$params = is_array($params) ? $params: array();

			return new Request(
				array(
					'get' => $_GET,
					'post' => $_POST,
					'files' => $_FILES,
					'server' => $_SERVER,
					'env' => $_ENV,
					'session' => $_SESSION,
					'cookies' => $_COOKIE,
					'method' => (isset($_SERVER) && isset($_SERVER['REQUEST_METHOD']))
							? $_SERVER['REQUEST_METHOD']
							: null,
					'params' => $params,
					'urlParams' => $c['urlParams']
				)
			);
		});

		$this['Protocol'] = $this->share(function($c){
			if(isset($_SERVER['SERVER_PROTOCOL'])) {
				return new Http($_SERVER, $_SERVER['SERVER_PROTOCOL']);
			} else {
				return new Http($_SERVER);
			}
		});

		$this['Dispatcher'] = $this->share(function($c) {
			return new Dispatcher($c['Protocol'], $c['MiddlewareDispatcher']);
		});

		/**
		 * Middleware
		 */
		$this['SecurityMiddleware'] = $this->share(function($c){
			return new SecurityMiddleware($c['API'], $c['Request']);
		});

		$this['HttpMiddleware'] = $this->share(function($c){
			return new HttpMiddleware($c['API'], $c['Request']);
		});

		$this['MiddlewareDispatcher'] = $this->share(function($c){
			$dispatcher = new MiddlewareDispatcher();
			$dispatcher->registerMiddleware($c['HttpMiddleware']);
			$dispatcher->registerMiddleware($c['SecurityMiddleware']);

			return $dispatcher;
		});

		/**
		 * Utilities
		 */
		$this['FaviconFetcher'] = $this->share(function($c){
			return new FaviconFetcher($c['SimplePieAPIFactory']);
		});

		$this['TimeFactory'] = $this->share(function($c){
			return new TimeFactory();
		});
	}
}