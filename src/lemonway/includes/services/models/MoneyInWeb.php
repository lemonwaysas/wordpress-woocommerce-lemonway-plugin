<?php
class MoneyInWeb{
	
	public function __construct($response){
		
		$this->TOKEN = $response->MONEYINWEB->TOKEN;
		$this->ID = $response->MONEYINWEB->ID;
		$this->CARD = $response->MONEYINWEB->CARD;
		
	}
	
	public $TOKEN;
	
	public $ID;
	
	public $CARD;
}