<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Handles responses from Lemonway Notification.
 */
class WC_Gateway_Lemonway_Notif_Handler {

	

	/**
	 * Pointer to gateway making the request.
	 * @var WC_Gateway_Lemonway
	 */
	protected $gateway;
	
	/**
	 *
	 * @var Operation
	 */
	protected $_moneyin_trans_details = null;

	/**
	 * Constructor.
	 */
	public function __construct( $gateway ) {
		add_action( 'woocommerce_api_wc_gateway_lemonway', array( $this, 'check_response' ) );
		add_action( 'valid-lemonway-notif-request', array( $this, 'valid_response' ) );
		$this->gateway = $gateway;
	}

	/**
	 * Check for Notification IPN Response.
	 */
	public function check_response() {
		
		$orderId = $this->isGet() ? $_GET['response_wkToken'] : $_POST['response_wkToken'];
		$order = wc_get_order($orderId);
		if(!$order){
			wp_die( 'Lemonway notification Request Failure. No Order Found!', 'Lemonway Notification', array( 'response' => 500 ) );
		}
		WC_Gateway_Lemonway::log( 'Found order #' . $order->id );
		if($this->isGet()){
			
			wp_redirect(esc_url_raw( $this->gateway->get_return_url( $order ))) ;
			exit;
		}
		elseif ( ! empty( $_POST ) && $this->validate_notif( $_POST['response_code']) ) {
			//$posted = wp_unslash( $_POST );

			do_action( 'valid-lemonway-notif-request', $order );
			exit;
		}

		wp_die( 'Lemonway notification Request Failure', 'Lemonway Notification', array( 'response' => 500 ) );
	}
	

	/**
	 * There was a valid response.
	 * @param  WC_Order $order Woocommerce order
	 */
	public function valid_response( $order ) {

		$this->payment_status_completed($order);
		
	}
	protected function isGet()
	{
		return strtoupper($_SERVER['REQUEST_METHOD']) == 'GET';
	}
	
	protected function isPost(){
		return strtoupper($_SERVER['REQUEST_METHOD']) == 'POST';
	}

	/**
	 * Check Lemonway Notification validity.
	 */
	 protected function validate_notif($response_code)
    {

    	if($response_code != "0000")
    		return false;
		
		/* @var $operation Operation */
		$operation = $this->GetMoneyInTransDetails();
		 
		if($operation)
		{		
			if($operation->STATUS == 3)
				return true;
		}

		return false;
    
	}
	

	/**
	 *
	 * @return boolean|Operation
	 */
	protected function GetMoneyInTransDetails(){
		if(is_null($this->_moneyin_trans_details))
		{
			$orderId = $this->isGet() ? $_GET['response_wkToken'] : $_POST['response_wkToken'];
			//call directkit to get Webkit Token
			$params = array('transactionMerchantToken'=>$orderId);
	
			//Call api to get transaction detail for this order
			try {
	
				$operation = $this->gateway->getDirectkit()->GetMoneyInTransDetails($params);
	
	
			} catch (Exception $e) {
				WC_Gateway_Lemonway::log($e->getMessage());
				throw $e;
			}
	
	
			$this->_moneyin_trans_details = $operation;
	
		}
		return $this->_moneyin_trans_details;
	}
	
	/**
	 * Complete order, add transaction ID and note.
	 * @param  WC_Order $order
	 * @param  string   $txn_id
	 * @param  string   $note
	 */
	protected function payment_complete( $order, $txn_id = '', $note = '' ) {
		$order->add_order_note( $note );
		$order->payment_complete( $txn_id );
	}
	
	/**
	 * Handle a completed payment.
	 * @param WC_Order $order
	 */
	protected function payment_status_completed( $order ) {
		if ( $order->has_status( 'completed' ) ) {
			WC_Gateway_Lemonway::log( 'Aborting, Order #' . $order->id . ' is already complete.' );
			exit;
		}

		$this->payment_complete( $order, ( ! empty( $_POST['response_transactionId'] ) ? wc_clean( $_POST['response_transactionId'] ) : '' ), __( 'Notification payment completed', LEMONWAY_TEXT_DOMAIN ) );


	}
}
