<?php
class SofortInit {
	
	public function __construct($response){
		$this->ID = $response->SOFORTINIT->ID;
		$this->actionUrl = $response->SOFORTINIT->actionUrl;
		
	}
	
	public $ID;
	
	public $actionUrl;
}