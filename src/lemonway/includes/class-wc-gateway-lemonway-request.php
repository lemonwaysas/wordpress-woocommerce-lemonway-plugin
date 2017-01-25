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
	public function get_request_url( $order ) {
		
		$registerCard = $useRegisteredCard = 0;
		$useCard = 0;
		if (isset($_POST['oneclic'])) {
			$oneclic = wc_clean( $_POST['oneclic'] );
			
			switch($oneclic) {
				case 'register_card':
					$registerCard = $useRegisteredCard = 1;
					break;
				case 'use_card':
					$useCard = 1;
					break;
			}
		}
		
		//Build args with the order

		$amount = $order->get_total();
		//$amountCom = $amount;
        $amountCom = 0;
		
		/*if( function_exists( 'is_plugin_active' ) ) {
			if ( is_plugin_active( 'lemonwaymkt/lemonwaymkt.php' ) ) {
				//@TODO manage mixted cart
				
				//Keep only subtotal for vendors because webkul plugin work like this :-(
				$amountCom = $order->get_total() - $order->get_subtotal();
			}
		}*/
	
		$comment = get_bloginfo( 'name' ) . " - " . sprintf(__('Order #%s by %s %s %s', LEMONWAY_TEXT_DOMAIN), $order->get_order_number(), $order->billing_last_name, $order->billing_first_name, $order->billing_email);
		$returnUrl = '';

		if (!$useCard) {
			$params = array(
					'wkToken' => $order->id,
					'wallet' => $this->gateway->get_option(WC_Gateway_Lemonway::WALLET_MERCHANT_ID),
					'amountTot' => $this->formatAmount($amount),
					'amountCom' => $this->formatAmount($amountCom),
                    'autoCommission' => 1,
					'comment' => $comment,
					'returnUrl' => $this->notify_url, //esc_url_raw( $this->gateway->get_return_url( $order )),
					'cancelUrl' => esc_url_raw( $order->get_cancel_order_url_raw() ),
					'errorUrl' => esc_url_raw( $order->get_cancel_order_url_raw() ), //@TODO change for a specific error url
					'registerCard' => $registerCard, //For Atos
					'useRegisteredCard' => $useRegisteredCard, //For payline
			);
			
			WC_Gateway_Lemonway::log(print_r($params, true));
			
			//Call APi MoneyInWebInit in correct MODE with the args
			$moneyInWeb = $this->gateway->getDirectkit()->MoneyInWebInit($params);
			
			//Save card ID
			if($registerCard || $useRegisteredCard){
				update_user_meta( get_current_user_id(), '_lw_card_id', $moneyInWeb->CARD->ID );
				update_post_meta( $order->id, '_register_card', true );
				WC_Gateway_Lemonway::log(sprintf(__("Card Saved for customer Id %s", LEMONWAY_TEXT_DOMAIN), get_current_user_id()));
			}
			
			WC_Gateway_Lemonway::log(print_r($moneyInWeb, true));
			$returnUrl = $this->gateway->getDirectkit()->formatMoneyInUrl($moneyInWeb->TOKEN, $this->gateway->get_option(WC_Gateway_Lemonway::CSS_URL));
		}
		else { //Customer want to use his last card, so we call MoneyInWithCardID directly
		
			$cardId = get_user_meta(get_current_user_id(), '_lw_card_id', true);
			
			//call directkit for MoneyInWithCardId
			$params = array(

					'wkToken' => $order->id,
					'wallet' => $this->gateway->get_option(WC_Gateway_Lemonway::WALLET_MERCHANT_ID),
					'amountTot' => $this->formatAmount($amount),
					'amountCom' => $this->formatAmount($amountCom),
                    'autoCommission' => 1,
					'comment' => $comment . " -- "  . sprintf(__('Oneclic mode (card id: %s)', LEMONWAY_TEXT_DOMAIN), $cardId),
					'cardId' => $cardId
			);
			
			WC_Gateway_Lemonway::log(print_r($params, true));
			
			$operation = $this->gateway->getDirectkit()->MoneyInWithCardId($params);
			
			WC_Gateway_Lemonway::log(print_r($operation, true));
			
			if($operation->STATUS == "3") {
				
				$transaction_id = $operation->ID;
				//Set transaction id to POST array. Needed on notif handler
				
				$_POST['response_transactionId'] = $transaction_id;
				
				//Process order status
				$this->gateway->getNotifhandler()->valid_response($order);
				
				//Return to original wc success page
				$returnUrl = $this->gateway->get_return_url($order);
			}
			else {
				throw new Exception(__('Error during payment', LEMONWAY_TEXT_DOMAIN));
			}
		}
		
		//Return redirect url
		return $returnUrl;
	}
	
	protected function formatAmount($amount){
		return sprintf("%.2f", (float)$amount);
	}
}