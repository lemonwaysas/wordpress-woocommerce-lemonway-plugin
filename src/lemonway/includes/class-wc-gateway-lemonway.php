<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once 'services/DirectkitJson.php';

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
		$this->id                 = 'lemonway';
		$this->icon 			  = ''; //@TODO
		$this->has_fields         = true;
		$this->method_title       = __( 'Lemonway', LEMONWAY_TEXT_DOMAIN );
		$this->method_description = __('Secured payment solutions for Internet marketplaces, eCommerce, and crowdfunding. Payment API. BackOffice management. Compliance. Regulatory reporting.',LEMONWAY_TEXT_DOMAIN);

		
	
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
		$this->testMode       = 'yes' === $this->get_option( self::IS_TEST_MODE, 'no' );
		
		// Define user set variables.
		$this->title          = $this->get_option( self::TITLE );
		$this->description    = $this->get_option( self::DESCRIPTION );
		$this->debug          = 'yes' === $this->get_option( self::DEBUG, 'no' );
		
		$directkitUrl = $this->testMode ? $this->directkitUrlTest :$this->directkitUrl ;
		$webkitUrl = $this->testMode ? $this->webkitUrlTest :$this->webkitUrl;
		
		$this->directkit = new DirectkitJson($directkitUrl, $webkitUrl, $this->apiLogin, $this->apiPassword, get_locale());
	
		self::$log_enabled    = $this->debug;
	
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		
		include_once( 'class-wc-gateway-lemonway-notif-handler.php' );
		new WC_Gateway_Lemonway_Notif_Handler($this );
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