<?php
/**
 * Copyright (c) 2013 Georg Ehrke <developer at georgehrke dot com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Backend;

class Item {

	private $id;
	private $backend;
	private $classname;
	private $arguments;
	private $enabled;
	
	public $api;

	public function __construct($fromRow=null){
		if($fromRow){
			$this->fromRow($fromRow);
		}
	}

	public function fromRow($row){
		$this->id = $row['id'];
		$this->backend = $row['backend'];
		$this->classname = $row['classname'];
		$this->arguments = $row['arguments'];
		$this->enabled = $row['enabled'];
	}


	public function getId(){
		return $this->id;
	}

	public function getBackend(){
		return $this->backend;
	}
	
	public function getClassname(){
		return $this->classname;
	}
	
	public function getArguments(){
		return $this->arguments;
	}

	public function getEnabled(){
		return $this->user;
	}


	public function registerAPI($api){
		$this->api = $api;
	}


	public function setId($id){
		$this->id = $id;
	}

	public function setBackend($backend){
		$this->backend = $backend;
	}

	public function setClassname($classname){
		$this->classname = $classname;
	}

	public function setArguments($arguments){
		$this->arguments = $arguments;
	}

	public function setEnabled($enabled){
		$this->enabled = $enabled;
	}

}