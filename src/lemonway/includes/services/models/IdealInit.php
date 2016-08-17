<?php
class IdealInit {
	
	public function __construct($response){
		$this->ID = $response->IDEALINIT->ID;
		$this->actionUrl = $response->IDEALINIT->actionUrl;
		
	}
	
	public $ID;
	
	public $actionUrl;
}