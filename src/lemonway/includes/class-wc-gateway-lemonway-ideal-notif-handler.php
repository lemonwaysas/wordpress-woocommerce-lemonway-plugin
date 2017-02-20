<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles responses from Lemonway IDeal Notification.
 */
class WC_Gateway_Lemonway_Ideal_Notif_Handler extends WC_Gateway_Lemonway_Notif_Handler {
	/**
	 * Constructor.
	 */
	public function __construct( $gateway ) {
		add_action( 'woocommerce_api_wc_gateway_lemonway_ideal', array( $this, 'check_response' ) );
		add_action( 'valid-lemonway-ideal-notif-request', array( $this, 'valid_response' ) );
		$this->gateway = $gateway;
	}

	/**
	 * Check for Notification IPN Response.
	 */
	public function check_response() {
		if($this->isGet()){
			global $wpdb;

			$wktoken = end(explode(".", $_GET['reference']));
			$orderId = $wpdb->get_var("SELECT `id_cart` 
										FROM `" . $wpdb->prefix . "lemonway_wktoken` 
										WHERE `wktoken` = '" . $wktoken . "';");
			$this->order = wc_get_order($orderId);
			if(!$this->order){
				wp_die( 'Lemonway notification Request Failure. No Order Found!', 'Lemonway Notification', array( 'response' => 500 ) );
			}

			WC_Gateway_Lemonway_Ideal::log( 'Found order #' . $this->order->id );
			WC_Gateway_Lemonway::log( 'GET: ' . print_r($_GET, true));
			WC_Gateway_Lemonway::log( 'POST: ' . print_r($_POST, true));

			if ( $this->validate_notif( $_GET['code'], $wktoken) ) {
				do_action( 'valid-lemonway-ideal-notif-request', $this->order );
				wp_redirect(esc_url_raw( $this->gateway->get_return_url( $this->order ))) ;
				exit;
			} elseif ( ($_GET['code'] == "309") ) {
				wp_redirect(esc_url_raw($this->order->get_cancel_order_url_raw() )) ;
				exit;
			} else {
				wp_die( 'An error has been occurred while processing the payment.', 'Lemonway Notification', array( 'response' => 500 ) );
			}
		}
		
		wp_die( 'Lemonway notification Request Failure.', 'Lemonway Notification', array( 'response' => 500 ) );
	}

		/**
		 * Check Lemonway Notification validity.
		 */
		protected function validate_notif($code, $wktoken)
	    {

	    	if($code != "200") {
                return false;
            }
			
			/* @var $operation Operation */
			$operation = $this->MoneyInIDealConfirm($wktoken);

			if($operation)
			{		
				if($operation->STATUS == 3)
				{
					return true;
				}
			}

			return false;
		}

		protected function MoneyInIDealConfirm($wktoken){
			return $this->gateway->getDirectkit()->MoneyInIDealConfirm($wktoken);
		}
}
