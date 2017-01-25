<?php
require_once('class-wc-gateway-lemonway-request.php');

class WC_Gateway_Lemonway_Ideal_Request extends WC_Gateway_Lemonway_Request {
	/**
	 * Constructor.
	 * @param WC_Gateway_Lemonway_Ideal $gateway
	 */
	public function __construct( $gateway ) {
		$this->gateway    = $gateway;
		$this->notify_url = WC()->api_request_url( 'WC_Gateway_Lemonway_Ideal' );
	}

	/**
	 * Get the Lemonway Webkit request URL for an order.
	 * @param  WC_Order $order
	 * @return string
	 */
	public function get_request_url( $order) {
		//Build args with the order
		$amount = $order->get_total();
		//$amountCom = $amount;
        $amountCom = 0;
		$issuerId = $_POST['issuerId'];
		
		/*if( function_exists( 'is_plugin_active' ) ) {
			if ( is_plugin_active( 'lemonwaymkt/lemonwaymkt.php' ) ) {
				//@TODO manage mixted cart
				
				//Keep only subtotal for vendors because webkul plugin work like this :-(
				$amountCom = $order->get_total() - $order->get_subtotal();
			}
		}*/
		
		$comment = get_bloginfo( 'name' ) . " - " . sprintf(__('Order #%s by %s %s %s',LEMONWAY_IDEAL_TEXT_DOMAIN),$order->get_order_number(), $order->billing_last_name,$order->billing_first_name,$order->billing_email);
		$returnUrl = '';
		$params = array(
				'wallet' 			=> $this->gateway->getMerchantWalletId(),
				'amountTot' 		=> $this->formatAmount($amount),
				'amountCom' 		=> $this->formatAmount($amountCom),
				'issuerId' 			=> $issuerId,
				'comment' 			=> $comment,
				'returnUrl' 		=> $this->notify_url,
				'autoCommission' 	=> 1
		);

		WC_Gateway_Lemonway_Ideal::log(print_r($params, true));
		
		//Call APi MoneyInIdealInit in correct MODE with the args
		$idealInit = $this->gateway->getDirectkit()->MoneyInIdealInit($params);
		
		WC_Gateway_Lemonway_Ideal::log(print_r($idealInit, true));

		global $wpdb;

		$sql = "INSERT INTO `" . $wpdb->prefix . "lemonway_wktoken` (`wktoken`, `id_cart`) VALUES (%s, %d) ON DUPLICATE KEY UPDATE wktoken = %s";
		$sql = $wpdb->prepare($sql, $idealInit->ID, $order->get_order_number(), $idealInit->ID);
		$wpdb->query($sql);

		$returnUrl = urldecode($idealInit->actionUrl);
		
		//Return redirect url
		return $returnUrl;
	}
}
?>