<?php

class WC_Gateway_Lemonway_Request {
	
	
	/**
	 * Pointer to gateway making the request.
	 * @var WC_Gateway_Lemonway
	 */
	protected $gateway;
	
	/**
	 * Endpoint for notification from Lemonway.
	 * @var string
	 */
	protected $notify_url;
	
	
	/**
	 * Constructor.
	 * @param WC_Gateway_Lemonway $gateway
	 */
	public function __construct( $gateway ) {
		$this->gateway    = $gateway;
		$this->notify_url = WC()->api_request_url( 'WC_Gateway_Lemonway' );
	}
	
	
	/**
	 * Get the Lemonway Webkit request URL for an order.
	 * @param  WC_Order $order
	 * @param  bool     $isTestMode
	 * @return string
	 */
	public function get_request_url( $order) {
		
		//Build args with the order

		$amount = $order->get_total();
		$amountCom = $amount;
		
		if( function_exists( 'is_plugin_active' ) ) {
			if ( is_plugin_active( 'lemonwaymkt/lemonwaymkt.php' ) ) {
				$amountCom = $order->get_total() - $order->get_subtotal();
			}
		}
		
		$params = array(
				'wkToken'=>$order->id,
				'wallet'=> $this->gateway->get_option(WC_Gateway_Lemonway::WALLET_MERCHANT_ID),
				'amountTot'=> $this->formatAmount($amount),
				'amountCom'=>$this->formatAmount($amountCom),
				'comment'=>'',
				'returnUrl'=>$this->notify_url,//esc_url_raw( $this->gateway->get_return_url( $order )),
				'cancelUrl'=>esc_url_raw( $order->get_cancel_order_url_raw() ),
				'errorUrl'=>esc_url_raw( $order->get_cancel_order_url_raw() ), //@TODO change for a specific error url
				'autoCommission'=>0,
				'registerCard'=>0, //For Atos
				'useRegisteredCard'=>0, //For payline
		);
		
		WC_Gateway_Lemonway::log(print_r($params,true));
		
		//Call APi MoneyInWebInit in correct MODE with the args
		$moneyInWeb = $this->gateway->getDirectkit()->MoneyInWebInit($params);
		
		WC_Gateway_Lemonway::log(print_r($moneyInWeb,true));
		
		//Return Webkit url
		return $this->gateway->getDirectkit()->formatMoneyInUrl($moneyInWeb->TOKEN,$this->gateway->get_option(WC_Gateway_Lemonway::CSS_URL));
		
		
	}
	
	protected function formatAmount($amount){
		return sprintf("%.2f" ,(float)$amount);
	}
	
}