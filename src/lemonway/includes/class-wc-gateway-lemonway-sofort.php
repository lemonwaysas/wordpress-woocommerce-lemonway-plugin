<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once('class-wc-gateway-lemonway.php');

/**
 * WC_Gateway_Lemonway_Sofort class.
 *
 * @extends WC_Gateway_Lemonway
 */
class WC_Gateway_Lemonway_Sofort extends WC_Gateway_Lemonway {
	public function __construct() {
		$lwGateway = new WC_Gateway_Lemonway();

		$this->id                 = 'lemonway_sofort';
		$this->icon 			  = ''; //@TODO
		$this->has_fields         = true;
		$this->method_title       = __( 'Lemonway Sofort', LEMONWAY_SOFORT_TEXT_DOMAIN );
		$this->method_description = __('Secured payment solutions for Internet marketplaces, e-Commerce, and crowdfunding. Payment API. BackOffice management. Compliance. Regulatory reporting.',LEMONWAY_SOFORT_TEXT_DOMAIN);

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		$this->title          = $this->get_option( self::TITLE );
		$this->description    = $this->get_option( self::DESCRIPTION );
		$this->debug          = 'yes' === $this->get_option( self::DEBUG, 'no' );

		$this->merchantId = $lwGateway->getMerchantWalletId();
		$this->directkit = $lwGateway->getDirectkit();
		self::$log_enabled = $this->debug;

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		include_once( 'class-wc-gateway-lemonway-sofort-notif-handler.php' );
		new WC_Gateway_Lemonway_Sofort_Notif_Handler($this);
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = include( 'settings-lemonway-sofort.php' );
	}

	/**
	 * Process the payment and return the result.
	 * @param  int $order_id
	 * @return array
	 */
	public function process_payment( $order_id ) {
		include_once( 'class-wc-gateway-lemonway-sofort-request.php' );
		
		$order = wc_get_order( $order_id );
		$lw_request = new WC_Gateway_Lemonway_Sofort_Request( $this );
	
		return array(
				'result'   => 'success',
				'redirect' => $lw_request->get_request_url( $order)
		);
	}
}
?>