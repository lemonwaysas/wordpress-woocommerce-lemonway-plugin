<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once('class-wc-gateway-lemonway.php');

/**
 * WC_Gateway_Lemonway_Ideal class.
 *
 * @extends WC_Gateway_Lemonway
 */
class WC_Gateway_Lemonway_Ideal extends WC_Gateway_Lemonway {
	public function __construct() {
		$lwGateway = new WC_Gateway_Lemonway();

		$this->id                 = 'lemonway_ideal';
		$this->icon 			  = ''; //@TODO
		$this->has_fields         = true;
		$this->method_title       = __( 'Lemonway IDeal', LEMONWAY_IDEAL_TEXT_DOMAIN );
		$this->method_description = __('Secured payment solutions for Internet marketplaces, e-Commerce, and crowdfunding. Payment API. BackOffice management. Compliance. Regulatory reporting.', LEMONWAY_IDEAL_TEXT_DOMAIN);

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
		include_once( 'class-wc-gateway-lemonway-ideal-notif-handler.php' );
		new WC_Gateway_Lemonway_Ideal_Notif_Handler($this );
	}

	/**
	 * If There are no payment fields show the description if set.
	 * Override this in your gateway if you have some.
	 */
	public function payment_fields() {
		$this->issuerId_form();
	}

	public function issuerId_form( $args = array(), $fields = array() ) {
		$issuerId_fields = array(
			'21' => '<p class="form-row form-row-wide">
						<label for="' . esc_attr( $this->id ) . '_21"><input id="' . esc_attr( $this->id ) . '_21" class="input-radio" checked="checked"  value="21" type="radio" name="issuerId" />Rabobank</label></p>',
			'31' => '<p class="form-row form-row-wide">
						<label for="' . esc_attr( $this->id ) . '_31"><input id="' . esc_attr( $this->id ) . '_31" class="input-radio" value="31" type="radio" name="issuerId" />ABN Amro</label></p>',
			'721' => '<p class="form-row form-row-wide">
						<label for="' . esc_attr( $this->id ) . '_721"><input id="' . esc_attr( $this->id ) . '_721" class="input-radio" value="721" type="radio" name="issuerId" />ING</label></p>',
			'751' => '<p class="form-row form-row-wide">
						<label for="' . esc_attr( $this->id ) . '_751"><input id="' . esc_attr( $this->id ) . '_751" class="input-radio" value="751" type="radio" name="issuerId" />SNS Bank</label></p>',
			'161' => '<p class="form-row form-row-wide">
						<label for="' . esc_attr( $this->id ) . '_161"><input id="' . esc_attr( $this->id ) . '_161" class="input-radio" value="161" type="radio" name="issuerId" />Van Lanschot Bankiers</label></p>',
			'511' => '<p class="form-row form-row-wide">
						<label for="' . esc_attr( $this->id ) . '_511"><input id="' . esc_attr( $this->id ) . '_511" class="input-radio" value="511" type="radio" name="issuerId" />Triodos Bank</label></p>',
			'761' => '<p class="form-row form-row-wide">
						<label for="' . esc_attr( $this->id ) . '_761"><input id="' . esc_attr( $this->id ) . '_761" class="input-radio" value="761" type="radio" name="issuerId" /> ASN Bank</label></p>',
			'771' => '<p class="form-row form-row-wide">
						<label for="' . esc_attr( $this->id ) . '_771"><input id="' . esc_attr( $this->id ) . '_771" class="input-radio" value="771" type="radio" name="issuerId" />RegioBank</label></p>',
			'801' => '<p class="form-row form-row-wide">
						<label for="' . esc_attr( $this->id ) . '_801"><input id="' . esc_attr( $this->id ) . '_801" class="input-radio" value="801" type="radio" name="issuerId" /> Knab Bank</label></p>'
		);
		
		$fields = wp_parse_args( $fields, apply_filters( 'lemonway_issuerId_form_fields', $issuerId_fields, $this->id ) );
		?>
			<fieldset id="<?php echo $this->id; ?>-issuerId-form">
				<?php do_action( 'lemonway_issuerId_form_start', $this->id ); ?>
				<?php
					foreach ( $fields as $field ) {
						echo $field;
					}
				?>
				<?php do_action( 'lemonway_issuerId_form_end', $this->id ); ?>
				<div class="clear"></div>
			</fieldset>
			<?php
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = include( 'settings-lemonway-ideal.php' );
	}

	/**
	 * Process the payment and return the result.
	 * @param  int $order_id
	 * @return array
	 */
	public function process_payment( $order_id ) {
		include_once( 'class-wc-gateway-lemonway-ideal-request.php' );
		
		$order = wc_get_order( $order_id );
		$lw_request = new WC_Gateway_Lemonway_Ideal_Request( $this );
	
		return array(
				'result'   => 'success',
				'redirect' => $lw_request->get_request_url( $order)
		);
	}
}
?>