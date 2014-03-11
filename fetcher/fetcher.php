<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Fetcher;


class Fetcher {

	private $fetchers;

	public function __construct(){
		$this->fetchers = array();
	}

	public function registerFetcher(IFeedFetcher $fetcher){
		array_push($this->fetchers, $fetcher);
	}

	public function fetch($url, $getFavicon=true){
		foreach($this->fetchers as $fetcher){
			if($fetcher->canHandle($url)){
				return $fetcher->fetch($url, $getFavicon);
			}
		}
	}
}