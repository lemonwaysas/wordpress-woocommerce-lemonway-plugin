<?php

class Wallet{

	/**
     * ID as defined by merchant
     * @var string
     */
    public $ID;
	
	/**
     * LWID number ID as defined by Lemon Way
     * @var string
     */
    public $LWID;
	
	/**
     * STATUS {2,3,4,5,6,7,8,12}
     * @var string
     */
    public $STATUS;
	
	/**
     * BAL balance
     * @var string
     */
    public $BAL;
	
	/**
     * NAME full name
     * @var string
     */
    public $NAME;
	
	/**
     * EMAIL
     * @var string
     */
    public $EMAIL;
	
	/**
     * kycDocs
     * @var array KycDoc
     */
    public $kycDocs;
	
	/**
     * ibans 
     * @var array Iban
     */
    public $ibans;
	
	/**
     * sddMandates 
     * @var array SddMandate
     */
    public $sddMandates;
    
    public static $statuesLabel = array(1 => "Document only received",
    		2  => "Document checked and accepted",
    		3  => "Document checked but not accepted",
    		4  => "Document replaced by another document",
    		5  => "Document validity expired");
    
    public static $docsType = array(
    		0=>"ID card (UE)",
    		1=>"Proof of address",
    		2=>"RIB",
    		3=>"Passport (UE)",
    		4=>"Passport (Not UE)",
    		5=>"Residence permit",
    		7=>"Kbis",
    		11=>"Miscellaneous",
    );
	
	function __construct($WALLET) {
		$this->ID = $WALLET->ID;
		$this->LWID = $WALLET->LWID;
		$this->STATUS = (int)$WALLET->STATUS;
		$this->BAL = $WALLET->BAL;
		$this->NAME = $WALLET->NAME;
		$this->EMAIL = $WALLET->EMAIL;
		$this->kycDocs = array();
		if (isset($WALLET->DOCS))
			foreach ($WALLET->DOCS as $DOC){
				$this->kycDocs[] = new KycDoc($DOC);
			}
		$this->ibans = array();
		if (isset($WALLET->IBANS))
			foreach ($WALLET->IBANS as $IBAN){
				$this->ibans[] = new Iban($IBAN);
			}
		$this->sddMandates = array();
		if (isset($WALLET->SDDMANDATES))
			foreach ($WALLET->SDDMANDATES as $SDDMANDATE){
				$this->sddMandates[] = new SddMandate($SDDMANDATE);
			}
	}
	
	public function getStatusLabel(){
		if(isset(self::$statuesLabel[$this->STATUS])){
			return __(self::$statuesLabel[$this->STATUS],LEMONWAYMKT_TEXT_DOMAIN);
		}
		
		return __('N/A',LEMONWAYMKT_TEXT_DOMAIN);
	}
	
}

?>