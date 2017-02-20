<?php
/*
 Plugin Name: Lemonway IDeal
 Plugin URI: https://www.lemonway.com
 Description: Secured payment solutions for Internet marketplaces, eCommerce, and crowdfunding. Payment API. BackOffice management. Compliance. Regulatory reporting.
 Version: 1.1.2
 Author: Dat Pham <dpham@lemonway.com>
 Author URI: https://www.lemonway.com
 License: GPL2
 */

if(!defined('ABSPATH')) exit; // Exit if accessed directly

final class Lemonway_Ideal {
	
	/**
	 * @var Lemonway_Ideal The single instance of the class
	 */
	protected static $_instance = null;
	
	protected $name = "Lemon Way IDeal";
	protected $slug = 'lemonway-ideal';
	
	/**
	 * Pointer to gateway making the request.
	 * @var WC_Gateway_Lemonway_Ideal
	 */
	protected $gateway;
	
	const DB_VERSION = '1.0.0';
     
     
     /**
      * Constructor
      */
     public function __construct(){
     
     	// Define constants
     	$this->define_constants();
     	
     	// Check plugin requirements
     	$this->check_requirements();
      
     	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
     	add_action( 'plugins_loaded', array( $this, 'init_gateway' ), 0 );
     	add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );
     	
     	//Add menu elements
     	add_action('admin_menu', array($this, 'add_admin_menu'), 57);

     	$this->load_plugin_textdomain();
     	
     }
     
     /**
      * Add menu Lemonway IDeal
      */
     public function add_admin_menu(){
     	add_submenu_page('lemonway', __( 'Configuration IDeal', LEMONWAY_IDEAL_TEXT_DOMAIN ), __( 'Configuration IDeal', LEMONWAY_IDEAL_TEXT_DOMAIN ), 'manage_product_terms', $this->slug . 'configuration', array($this, 'redirect_configuration'));
     }
     
     public function init_gateway() {
      if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
        return;
      }
   
      // Includes
      include_once( 'includes/class-wc-gateway-lemonway-ideal.php' );
      $this->gateway = new WC_Gateway_Lemonway_Ideal();
    }
     
     /**
      * Load Localisation files.
      *
      * Note: the first-loaded translation file overrides any following ones if
      * the same translation is present.
      *
      * Locales found in:
      *      - WP_LANG_DIR/lemonway/woocommerce-gateway-lemonway-LOCALE.mo
      *      - WP_LANG_DIR/plugins/lemonway-LOCALE.mo
      */
     public function load_plugin_textdomain() {
     	$locale = apply_filters( 'plugin_locale', get_locale(), LEMONWAY_IDEAL_TEXT_DOMAIN );
     	$dir    = trailingslashit( WP_LANG_DIR );
     
     	load_textdomain( LEMONWAY_IDEAL_TEXT_DOMAIN, $dir . 'lemonway/lemonway-' . $locale . '.mo' );
     	load_plugin_textdomain( LEMONWAY_IDEAL_TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
     }
     
     /**
      * Add the gateway to methods
      */
     public function add_gateway( $methods ) {  
     	$methods[] = 'WC_Gateway_Lemonway_Ideal';
     	return $methods;
     }
     
     public function redirect_configuration(){
     	wp_redirect(admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_lemonway_ideal' ),301);
     }
     
     /**
      * Add relevant links to plugins page
      * @param  array $links
      * @return array
      */
     public function plugin_action_links( $links ) {

     	$plugin_links = array(
     			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_lemonway_ideal' ) . '">' . __( 'Settings', LEMONWAY_IDEAL_TEXT_DOMAIN ) . '</a>',
     	);
     	return array_merge( $plugin_links, $links );
     }
     
     /**
      * Main Lemonway_Ideal Instance
      *
      * Ensures only one instance of Lemonway_Ideal is loaded or can be loaded.
      *
      * @static
      * @see LW_ideal()
      * @return Lemonway_Ideal - Main instance
      */
     public static function instance() {
     	if ( is_null( self::$_instance ) ) {
     		self::$_instance = new self();
     	}
     	return self::$_instance;
     }
     
     /**
      * Define Constants
      *
      * @access private
      */
     private function define_constants() {
      define( 'LEMONWAY_IDEAL_NAME', $this->name );
      define( 'LEMONWAY_IDEAL_TEXT_DOMAIN', $this->slug );
     }

     /**
      * Checks that the WordPress setup meets the plugin requirements.
      *
      * @access private
      * @return boolean
      */
     private function check_requirements() {     
      require_once(ABSPATH.'/wp-admin/includes/plugin.php');
     
      //@TODO version compare
     
      if( function_exists( 'is_plugin_active' ) ) {
        if ( !is_plugin_active( 'lemonway/lemonway.php' ) ) {
          add_action('admin_notices', array( &$this, 'alert_lw_not_active' ) );
          return false;
        }
      }
     
      return true;
     }

     /**
      * Display the Lemonway_Ideal requirement notice.
      *
      * @access static
      */
     static function alert_lw_not_active() {
      echo '<div id="message" class="error"><p>';
      echo sprintf( __('Sorry, <strong>%s</strong> requires Lemonway to be installed and activated first. Please install Lemonway plugin first.', LEMONWAY_IDEAL_TEXT_DOMAIN), LEMONWAY_IDEAL_NAME );
      echo '</p></div>';
     }
}

function LW_ideal(){
	return Lemonway_Ideal::instance();
}
LW_ideal();