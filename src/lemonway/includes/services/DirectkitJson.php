<?php
require_once 'models/Iban.php';
require_once 'models/KycDoc.php';
require_once 'models/LwError.php';
require_once 'models/LwModel.php';
require_once 'models/Operation.php';
require_once 'models/SddMandate.php';
require_once 'models/Wallet.php';
require_once 'models/MoneyInWeb.php';
require_once 'models/IdealInit.php';
require_once 'models/SofortInit.php';
require_once 'DirectkitException.php';

/**
 * 
 * 
 *
 */
final class DirectkitJson{
	
	/**
	 * 
	 * @var string $directkitUrl
	 */
	private $directkitUrl;
	
	/**
	 *
	 * @var string $webkitUrl
	 */
	private $webkitUrl;
	
	/**
	 *
	 * @var string $wlLogin
	 */
	private $wlLogin;
	
	/**
	 *
	 * @var string $wlPass
	 */
	private $wlPass;
	
	/**
	 *
	 * @var string $language
	 */
	private $language;
	
	/**
	 * Information about used plugin E.g: Prestashop-1.6.4 or Magento-1.9.3 ...
	 * @var string $pluginType
	 */
	private $pluginType;
	
	public function __construct($directkitUrl, $webkitUrl, $wlLogin, $wlPass, $language, $pluginType = 'Generic-1.0.0'){
		
		//@TODO validate args
		$this->directkitUrl = $directkitUrl . "/";
		$this->webkitUrl = $webkitUrl;
		$this->wlLogin = $wlLogin;
		$this->wlPass = $wlPass;

        $supportedLangs = array(
            'da' => 'da',
            'de' => 'ge',
            'en' => 'en',
            'es' => 'sp',
            'fi' => 'fi',
            'fr' => 'fr',
            'it' => 'it',
            'ko' => 'ko',
            'no' => 'no',
            'pt' => 'po',
            'sv' => 'sw'
        );
		$language = substr( $language, 0, 2 );
        if ( array_key_exists( $language, $supportedLangs ) ) {
            $this->language = $supportedLangs[$language];
        } else {
            $this->language = 'en';
        }

		$this->pluginType = $pluginType;
	}
	
	/**
	 * 
	 * @param array() $params
	 * @return stdClass
	 */
	public function RegisterWallet($params) {
		$response = self::sendRequest('RegisterWallet', $params, '1.1');
		return $response;
	}
	/*
	public function MoneyIn($params) {
		$res = self::sendRequest('MoneyIn', $params, '1.1');
		if (!isset($res->lwError)){
			$res->operations = array(new Operation($res->lwXml->TRANS->HPAY));
		}
		return $res;
	}
	public function UpdateWalletDetails($params) {
		$res = self::sendRequest('UpdateWalletDetails', $params, '1.0');
		if (!isset($res->lwError)){
			$res->wallet = new Wallet($res->lwXml->WALLET);
		}
		return $res;
	}*/
	public function GetWalletDetails($params) {
		$response = self::sendRequest('GetWalletDetails', $params, '1.5');
		
		return new Wallet($response->WALLET);
	}
	/*
	public function MoneyIn3DInit($params) {
		return self::sendRequest('MoneyIn3DInit', $params, '1.1');
	}
	public function MoneyIn3DConfirm($params) {
		return self::sendRequest('MoneyIn3DConfirm', $params, '1.1');
	}*/
	
	/**
	 * 
	 * @param array $params
	 * @return MoneyInWeb
	 */
	public function MoneyInWebInit($params) {
		$response =  self::sendRequest('MoneyInWebInit', $params, '1.3');
		return new MoneyInWeb($response);
	}
	/*
	public function RegisterCard($params) {
		return self::sendRequest('RegisterCard', $params, '1.1');
	}
	public function UnregisterCard($params) {
		return self::sendRequest('UnregisterCard', $params, '1.0');
	}*/
	public function MoneyInWithCardId($params) {
		$response = self::sendRequest('MoneyInWithCardId', $params, '1.1');
	
		return new Operation($response->TRANS->HPAY);

	}


	public function MoneyInIDealInit($params) {
		$response =  self::sendRequest('MoneyInIDealInit', $params, '1.0');
		return new IdealInit($response);
	}

	/**
	 * 
	 * @param array $params
	 * @return Operation
	 * @throws Exception
	 */
	public function MoneyInIDealConfirm($transactionId) {
		$params = array(
			'transactionId'=> $transactionId
		);
		
		$response = self::sendRequest('MoneyInIDealConfirm', $params, '1.0');
		return new Operation($response->TRANS->HPAY);
	}

	public function MoneyInSofortInit($params) {
		$response =  self::sendRequest('MoneyInSofortInit', $params, '1.0');
		return new SofortInit($response);
	}

	/*
	public function MoneyInValidate($params) {
		return self::sendRequest('MoneyInValidate', $params, '1.0');
	}*/
	public function SendPayment($params) {
		$response = self::sendRequest('SendPayment', $params, '1.0');
	
		foreach ($response->TRANS_SENDPAYMENT->HPAY as $HPAY){
			return new Operation($HPAY);
		}
		
		throw new Exception("No Result for sendPayment");

	}
	
	public function RegisterIBAN($params) {
		$response = self::sendRequest('RegisterIBAN', $params, '1.1');
		return new Iban($response->IBAN_REGISTER);
	}
	
	public function MoneyOut($params) {
		$response  = self::sendRequest('MoneyOut', $params, '1.3');
		return new Operation($response->TRANS->HPAY);
	}
	/*
	public function GetPaymentDetails($params) {
		$res = self::sendRequest('GetPaymentDetails', $params, '1.0');
		if (!isset($res->lwError)){
			$res->operations = array();
			foreach ($res->lwXml->TRANS->HPAY as $HPAY){
				$res->operations[] = new Operation($HPAY);
			}
		}
		return $res;
	}*/
	
	/**
	 * 
	 * @param array $params
	 * @return Operation
	 * @throws Exception
	 */
	public function GetMoneyInTransDetails($params) {
		/*$requiredFields = array(
				'transactionId'=>'',
				'transactionComment' => '',
				"transactionMerchantToken"=>'',
				"startDate" => '',
				"endDate" => ''
		);
		
		$params = array_merge($requiredFields,$params);*/
		
		$response = self::sendRequest('GetMoneyInTransDetails', $params, '1.8');

		foreach ($response->TRANS->HPAY as $HPAY){
			return new Operation($HPAY);
		}
		
		throw new Exception("No Result for getMoneyInTransDetails");
	}
	/*
	public function GetMoneyOutTransDetails($params) {
		$res = self::sendRequest('GetMoneyOutTransDetails', $params, '1.4');
		if (!isset($res->lwError)){
			$res->operations = array();
			foreach ($res->lwXml->TRANS->HPAY as $HPAY){
				$res->operations[] = new Operation($HPAY);
			}
		}
		return $res;
	}
	public function UploadFile($params) {
		$res = self::sendRequest('UploadFile', $params, '1.1');
		if (!isset($res->lwError)){
			$res->kycDoc = new KycDoc($res->lwXml->UPLOAD);
		}
		return $res;
	}
	public function GetKycStatus($params) {
		return self::sendRequest('GetKycStatus', $params, '1.5');
	}
	public function GetMoneyInIBANDetails($params) {
		return self::sendRequest('GetMoneyInIBANDetails', $params, '1.4');
	}
	public function RefundMoneyIn($params) {
		return self::sendRequest('RefundMoneyIn', $params, '1.2');
	}
	public function GetBalances($params) {
		return self::sendRequest('GetBalances', $params, '1.0');
	}
	public function MoneyIn3DAuthenticate($params) {
		return self::sendRequest('MoneyIn3DAuthenticate', $params, '1.0');
	}
	public function MoneyInIDealInit($params) {
		return self::sendRequest('MoneyInIDealInit', $params, '1.0');
	}
	public function MoneyInIDealConfirm($params) {
		return self::sendRequest('MoneyInIDealConfirm', $params, '1.0');
	}
	public function RegisterSddMandate($params) {
		$res = self::sendRequest('RegisterSddMandate', $params, '1.0');
		if (!isset($res->lwError)){
			$res->sddMandate = new SddMandate($res->lwXml->SDDMANDATE);
		}
		return $res;
	}
	public function UnregisterSddMandate($params) {
		return self::sendRequest('UnregisterSddMandate', $params, '1.0');
	}
	public function MoneyInSddInit($params) {
		return self::sendRequest('MoneyInSddInit', $params, '1.0');
	}
	public function GetMoneyInSdd($params) {
		return self::sendRequest('GetMoneyInSdd', $params, '1.0');
	}
	public function GetMoneyInChequeDetails($params) {
		return self::sendRequest('GetMoneyInChequeDetails', $params, '1.4');
	}
	*/
	

	private function sendRequest($methodName, $params, $version){
		$ua = '';
		if (isset($_SERVER['HTTP_USER_AGENT']))
			$ua = $_SERVER['HTTP_USER_AGENT'];
		$ua = $this->pluginType."/" . $ua;
			
		$ip = '';
		if (isset($_SERVER['REMOTE_ADDR']))
			$ip = $_SERVER['REMOTE_ADDR'];
		
		$baseParams = array(
				'wlLogin'  => $this->wlLogin,
				'wlPass'   => $this->wlPass,
				'language' => $this->language,
				'version'  => $version,
				'walletIp' => $ip,
				'walletUa' => $ua,
		);
		
		$requestParams = array_merge($baseParams,$params);
		$requestParams = array('p' => $requestParams);
        
		$headers = array(
			"Content-type: application/json; charset=utf-8",
			"Accept: application/json",
			"Cache-Control: no-cache",
			"Pragma: no-cache"
		);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->directkitUrl . $methodName);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestParams));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$response = curl_exec($ch);

		if(curl_errno($ch))
		{
			throw new Exception(curl_error($ch));
			
		} else {
			$responseCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			$this->throwErrorResponseCode($responseCode);
			
			if($responseCode == 200){
				//General parsing
				$response = json_decode($response);
				
				//Check error
				if(isset($response->d->E)){
					throw new DirectkitException($response->d->E->Msg,$response->d->E->Code);
				}
				
				
				//self::printDirectkitOutput($response);
					
				return $response->d;
			}
			
		}
	}
	
	/**
	 * Throw an Exception for HTTP CODE
	 * @param int $responseCode
	 * @throws Exception
	 */
	protected function throwErrorResponseCode($responseCode){
		
		switch($responseCode){
			case 200:
				break;
			case 400:
				throw new Exception("Bad Request : The server cannot or will not process the request due to something that is perceived to be a client error", 400);
				break;
			case 403:
				throw new Exception("IP is not allowed to access Lemon Way's API, please contact support@lemonway.fr", 403);
				break;
			case 404:
				throw new Exception("Check that the access URLs are correct. If yes, please contact support@lemonway.fr", 404);
				print "Check that the access URLs are correct. If yes, please contact support@lemonway.fr";
				break;
			case 500:
				throw new Exception("Lemon Way internal server error, please contact support@lemonway.fr", 500);
				break;
			default:
				throw  new Exception(sprintf("HTTP CODE %d IS NOT SUPPORTED",$responseCode), $responseCode);
				break;
		}
		
	}
	
	public function formatMoneyInUrl($moneyInToken, $cssUrl = ''){
		
		return $this->webkitUrl . "?moneyintoken=".$moneyInToken.'&p='.urlencode($cssUrl).'&lang='.$this->language;
	}
	
}
