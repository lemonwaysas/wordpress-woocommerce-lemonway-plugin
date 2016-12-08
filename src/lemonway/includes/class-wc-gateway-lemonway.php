<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once 'services/DirectkitJson.php';
include_once( 'class-wc-gateway-lemonway-notif-handler.php' );

/**
 * WC_Gateway_Lemonway class.
 *
 * @extends WC_Payment_Gateway
 */
class WC_Gateway_Lemonway extends WC_Payment_Gateway {
	
	/** @var bool Whether or not logging is enabled */
	public static $log_enabled = false;
	
	/** @var WC_Logger Logger instance */
	public static $log = false;
	
	/**
	 * 
	 * @var string $apiLogin
	 */
	protected $apiLogin;
	
	/**
	 * 
	 * @var string $apiPassword
	 */
	protected $apiPassword;
	
	/**
	 * 
	 * @var string $merchantId
	 */
	protected $merchantId;
	
	/**
	 *
	 * @var string $directkitUrl
	 */
	protected $directkitUrl;
	
	/**
	 *
	 * @var string $directkitUrlTest
	 */
	protected $directkitUrlTest;
	
	/**
	 *
	 * @var string $webkitUrl
	 */
	protected $webkitUrl;
	
	/**
	 *
	 * @var string $webkitUrlTest
	 */
	protected $webkitUrlTest;
	
	/**
	 *
	 * @var bool $oneclicEnabled
	 */
	protected $oneclicEnabled;
	
	/**
	 *
	 * @var bool $isTestMode
	 */
	protected $isTestMode;
	
	/**
	 *
	 * @var bool $debug
	 */
	protected $debug;
	
	/**
	 * 
	 * @var DirectkitJson $directkit
	 */
	protected $directkit;
	
	/**
	 * 
	 * @var WC_Gateway_Lemonway_Notif_Handler $notifhandler
	 */
	protected $notifhandler;
	
	//API CONFIGURATION
	const API_LOGIN = 'api_login';
	const API_PASSWORD = 'api_password';
	const WALLET_MERCHANT_ID = 'merchant_id';
	const DIRECTKIT_URL = 'directkit_url';
	const WEBKIT_URL = 'webkit_url';
	const DIRECTKIT_URL_TEST = 'directkit_url_test';
	const WEBKIT_URL_TEST = 'webkit_url_test';
	const IS_TEST_MODE = 'is_test_mode';
	
	//METHOD CONFIGURATION
	const ENABLED = 'enabled';
	const TITLE = 'title';
	const DESCRIPTION = 'description';
	const DEBUG = 'debug';
	const CSS_URL = 'css_url';
	const ONECLIC_ENABLED = 'oneclic_enabled';
	
	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id  = 'lemonway';
		$this->icon = ''; //@TODO
		$this->has_fields = true;
		$this->method_title = __( 'Lemonway', LEMONWAY_TEXT_DOMAIN );
		$this->method_description = __('Secured payment solutions for Internet marketplaces, e-Commerce, and crowdfunding. Payment API. BackOffice management. Compliance. Regulatory reporting.', LEMONWAY_TEXT_DOMAIN);

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();
	
		//API informations
		$this->apiLogin = $this->get_option( self::API_LOGIN );
		$this->apiPassword = $this->get_option( self::API_PASSWORD );
		$this->merchantId = $this->get_option( self::WALLET_MERCHANT_ID);
		$this->directkitUrl = $this->get_option( self::DIRECTKIT_URL );
		$this->webkitUrl = $this->get_option( self::WEBKIT_URL);
		$this->directkitUrlTest = $this->get_option( self::DIRECTKIT_URL_TEST );
		$this->webkitUrlTest = $this->get_option( self::WEBKIT_URL_TEST);
		$this->oneclicEnabled = 'yes' === $this->get_option( self::ONECLIC_ENABLED, 'no' );
		$this->testMode = 'yes' === $this->get_option( self::IS_TEST_MODE, 'no' );
		
		// Define user set variables.
		$this->title = $this->get_option( self::TITLE );
		$this->description = $this->get_option( self::DESCRIPTION );
		$this->debug = 'yes' === $this->get_option( self::DEBUG, 'no' );
		
		$directkitUrl = $this->testMode ? $this->directkitUrlTest : $this->directkitUrl;
		$webkitUrl = $this->testMode ? $this->webkitUrlTest : $this->webkitUrl;
		
		$this->directkit = new DirectkitJson($directkitUrl, $webkitUrl, $this->apiLogin, $this->apiPassword, get_locale());
	
		self::$log_enabled = $this->debug;
	
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		//Init notification handler
		$this->notifhandler =  new WC_Gateway_Lemonway_Notif_Handler( $this );
	}
	
	/**
	 * @return WC_Gateway_Lemonway_Notif_Handler
	 */
	public function getNotifhandler(){
		return $this->notifhandler;
	}
	
	/**
	 * If There are no payment fields show the description if set.
	 * Override this in your gateway if you have some.
	 */
	public function payment_fields() {

		if($this->oneclicEnabled) {

			$this->oneclic_form();
		}
		else {	
		
			if ( $description = $this->get_description() ) {
				echo wpautop( wptexturize( esc_html( $description ) ) );
			}
		}
	}
	
	public function getMerchantWalletId(){
		return $this->merchantId;
	}
	
	/**
	 * Oneclic form.
	 *
	 * @param  array $args
	 * @param  array $fields
	 */
	public function oneclic_form( $args = array(), $fields = array() ) {
	
		$oneclic_fields = array(
				'register_card' => '<p class="form-row form-row-wide">
				<label for="' . esc_attr( $this->id ) . '_register_card"><input id="' . esc_attr( $this->id ) . '_register_card" class="input-checkbox" value="register_card" type="checkbox" name="oneclic" />' 
				. __( 'Save your card data for a next buy.', LEMONWAY_TEXT_DOMAIN ) . '</label>
			</p>'
		);
		
		$cardId = get_user_meta(get_current_user_id(), '_lw_card_id', true);
		$cardNum = get_user_meta(get_current_user_id(), '_lw_card_num', true);
		//$cardExp = get_user_meta(get_current_user_id(), '_lw_card_exp', true);
		
		if(!empty($cardId) && !empty($cardNum)) {
			$oneclic_fields = array(
				'use_card' => '<p class="form-row form-row-wide">
				<label for="' . esc_attr( $this->id ) . '_use_card"><input id="' . esc_attr( $this->id ) . '_use_card" class="input-radio" checked="checked" value="use_card" type="radio" name="oneclic" />' 
					. sprintf(__( 'Use my recorded card: %s', LEMONWAY_TEXT_DOMAIN ),$cardNum) . '</label>
				
			</p>',
			'register_card' => '<p class="form-row form-row-wide">
				<label for="' . esc_attr( $this->id ) . '_register_card"><input id="' . esc_attr( $this->id ) . '_register_card" class="input-radio" value="register_card" type="radio" name="oneclic" />' 
					. __( 'Save new card data.', LEMONWAY_TEXT_DOMAIN ) .'</label>
				
			</p>',
			'no_use_card' => '<p class="form-row form-row-wide">
				<label for="' . esc_attr( $this->id ) . '_no_use_card"><input id="' . esc_attr( $this->id ) . '_no_use_card" class="input-radio" value="no_use_card" type="radio" name="oneclic" />' 
					. __( 'Not use recorded card data.', LEMONWAY_TEXT_DOMAIN ) .'</label>
				
			</p>'
			);
		}
	
		$fields = wp_parse_args( $fields, apply_filters( 'lemonway_oneclic_form_fields', $oneclic_fields, $this->id ) );
		?>
			<fieldset id="<?php echo esc_attr( $this->id ); ?>-oneclic-form">
				<?php do_action( 'lemonway_oneclic_form_start', $this->id ); ?>
				<?php
					foreach ( $fields as $field ) {
						echo $field;
					}
				?>
				<?php do_action( 'lemonway_oneclic_form_end', $this->id ); ?>
				<div class="clear"></div>
			</fieldset>
			<?php
		}
	
	/**
	 * Process the payment and return the result.
	 * @param  int $order_id
	 * @return array
	 */
	public function process_payment( $order_id ) {
		include_once( 'class-wc-gateway-lemonway-request.php' );
	
		$order          = wc_get_order( $order_id );
		$lw_request = new WC_Gateway_Lemonway_Request( $this );
	
		return array(
				'result'   => 'success',
				'redirect' => $lw_request->get_request_url( $order)
		);
	}
	
	/**
	 * @return DirectkitJson
	 */
	public function getDirectkit(){
		return $this->directkit;
	}
	
	/**
	 * Logging method.
	 * @param string $message
	 */
	public static function log( $message ) {
		if ( self::$log_enabled ) {
			if ( empty( self::$log ) ) {
				self::$log = new WC_Logger();
			}
			self::$log->add( 'lemonway', $message );
		}
	}
	
	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = include( 'settings-lemonway.php' );
	}

}