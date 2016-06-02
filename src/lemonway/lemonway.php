<?php
/*
 Plugin Name: Lemonway
 Plugin URI: http://www.sirateck.com
 Description: Secured payment solutions for Internet marketplaces, eCommerce, and crowdfunding. Payment API. BackOffice management. Compliance. Regulatory reporting.
 Version: 1.0.2
 Author: Kassim Belghait <kassim@sirateck.com>
 Author URI: http://www.sirateck.com
 License: GPL2
 */

if(!defined('ABSPATH')) exit; // Exit if accessed directly

final class Lemonway {
	
	
	/**
	 * @var Lemonway The single instance of the class
	 */
	protected static $_instance = null;
	
	protected $name = "Secured payment solutions for Internet marketplaces, eCommerce, and crowdfunding. Payment API. BackOffice management. Compliance. Regulatory reporting.";
	protected $slug = 'lemonway';
	
	const DB_VERSION = '1.0.0';
     
     
     /**
      * Constructor
      */
     public function __construct(){
     
     	// Define constants
     	$this->define_constants();
     	
     	// Check plugin requirements
     	$this->check_requirements();
     	
     	register_activation_hook( __FILE__, array($this,'lw_install') );
     	
     	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
     	add_action( 'plugins_loaded', array( $this, 'init_gateway' ), 0 );
     	add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );

     	$this->load_plugin_textdomain();
     	
     }
     
     /**
      * Init Gateway
      */
     public function init_gateway() {
     	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
     		return;
     	}
     
     	// Includes
     	include_once( 'includes/class-wc-gateway-lemonway.php' );
     	include_once( 'includes/class-wc-gateway-lemonway-user-cards.php' );

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
     	$locale = apply_filters( 'plugin_locale', get_locale(), LEMONWAY_TEXT_DOMAIN );
     	$dir    = trailingslashit( WP_LANG_DIR );
     
     	load_textdomain( LEMONWAY_TEXT_DOMAIN, $dir . 'lemonway/lemonway-' . $locale . '.mo' );
     	load_plugin_textdomain( LEMONWAY_TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
     }
     
     /**
      * Add the gateway to methods
      */
     public function add_gateway( $methods ) {  
     	$methods[] = 'WC_Gateway_Lemonway';
     	return $methods;
     }
     
     /**
      * Add relevant links to plugins page
      * @param  array $links
      * @return array
      */
     public function plugin_action_links( $links ) {

     	$plugin_links = array(
     			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_lemonway' ) . '">' . __( 'Settings', LEMONWAY_TEXT_DOMAIN ) . '</a>',
     	);
     	return array_merge( $plugin_links, $links );
     }
     
     /**
      * Main Lemonway Instance
      *
      * Ensures only one instance of Lemonway is loaded or can be loaded.
      *
      * @static
      * @see LW()
      * @return Lemonway - Main instance
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
     
     	$woo_version_installed = get_option('woocommerce_version');
     	define( 'LEMONWAY_WOOVERSION', $woo_version_installed );
     	define( 'LEMONWAY_NAME', $this->name );
     	define( 'LEMONWAY_TEXT_DOMAIN', $this->slug );
     }
     
     
     /**
      * Checks that the WordPress setup meets the plugin requirements.
      *
      * @access private
      * @global string $wp_version
      * @return boolean
      */
     private function check_requirements() {
     	//global $wp_version, $woocommerce;
     
     	require_once(ABSPATH.'/wp-admin/includes/plugin.php');
     
     	//@TODO version compare
     
     	if( function_exists( 'is_plugin_active' ) ) {
     		if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
     			add_action('admin_notices', array( &$this, 'alert_woo_not_actvie' ) );
     			return false;
     		}
     	}
     
     	return true;
     }
     

     /**
      * Display the WooCommerce requirement notice.
      *
      * @access static
      */
     static function alert_woo_not_actvie() {
     	echo '<div id="message" class="error"><p>';
     	echo sprintf( __('Sorry, <strong>%s</strong> requires WooCommerce to be installed and activated first. Please <a href="%s">install WooCommerce</a> first.', LEMONWAY_TEXT_DOMAIN), LEMONWAY_NAME, admin_url('plugin-install.php?tab=search&type=term&s=WooCommerce') );
     	echo '</p></div>';
     }
     
     /**
      * Setup SQL
      */
      
     function lw_install(){
     	global $wpdb;
     	$charset_collate = $wpdb->get_charset_collate();

     
     	$sql = array();

		$sql[] = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'lemonway_oneclic` (
		    `id_oneclic` int(11) NOT NULL AUTO_INCREMENT,
			`id_customer` int(11) NOT NULL,
			`id_card` int(11) NOT NULL,
			`card_num` varchar(30) NOT NULL,
			`card_exp`  varchar(8) NOT NULL DEFAULT \'\',
			`card_type` varchar(20) NOT NULL DEFAULT \'\',
			`date_add` datetime NOT NULL,
		    `date_upd` datetime NOT NULL,
		    PRIMARY KEY  (`id_oneclic`)
		) ENGINE=InnoDB '.$charset_collate.';';
		
		$sql[] = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'lemonway_moneyout` (
		    `id_moneyout` int(11) NOT NULL AUTO_INCREMENT,
			`id_lw_wallet` varchar(255) NOT NULL,
			`id_customer` int(11) NOT NULL DEFAULT 0,
			`id_employee` int(11) NOT NULL DEFAULT 0,
			`is_admin` tinyint(1) NOT NULL DEFAULT 0,
			`id_lw_iban` int(11) NOT NULL,
			`prev_bal` decimal(20,6) NOT NULL,
			`new_bal`  decimal(20,6) NOT NULL,
			`iban` varchar(34) NOT NULL,
			`amount_to_pay`  decimal(20,6) NOT NULL,
			`date_add` datetime NOT NULL,
		    `date_upd` datetime NOT NULL,
		    PRIMARY KEY  (`id_moneyout`)
		) ENGINE=InnoDB '.$charset_collate.';';
		
		$sql[] = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'lemonway_iban` (
		    `id_iban` int(11) NOT NULL AUTO_INCREMENT,
			`id_lw_iban` int(11) NOT NULL,
			`id_customer` int(11) NOT NULL,
			`id_wallet` varchar(255) NOT NULL,
			`holder` varchar(100) NOT NULL,
			`iban` varchar(34) NOT NULL,
			`bic` varchar(50) NOT NULL DEFAULT \'\',
			`dom1` text NOT NULL DEFAULT \'\',
			`dom2` text NOT NULL DEFAULT \'\',
			`comment` text NOT NULL DEFAULT \'\',
			`id_status` int(2) DEFAULT NULL,
			`date_add` datetime NOT NULL,
		    `date_upd` datetime NOT NULL,
		    PRIMARY KEY  (`id_iban`),
			UNIQUE KEY (`id_lw_iban`)
		) ENGINE=InnoDB '.$charset_collate.';';
		
		$sql[] = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lemonway_wallet` (
		  `id_wallet` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Wallet ID',
		  `id_lw_wallet` varchar(190) NOT NULL COMMENT 'Lemonway Wallet ID',
		  `id_customer` int(11) NOT NULL COMMENT 'Customer ID',
		  `is_admin` smallint(6) NOT NULL COMMENT 'Is Admin',
		  `customer_email` varchar(255) NOT NULL COMMENT 'Email',
		  `customer_prefix` varchar(100) NOT NULL DEFAULT '' COMMENT 'Prefix',
		  `customer_firstname` varchar(255) NOT NULL COMMENT 'Firstname',
		  `customer_lastname` varchar(255) NOT NULL COMMENT 'Lastname',
		  `billing_address_street` varchar(255) DEFAULT NULL COMMENT 'Street',
		  `billing_address_postcode` varchar(255) DEFAULT NULL COMMENT 'Postcode',
		  `billing_address_city` varchar(255) DEFAULT NULL COMMENT 'City',
		  `billing_address_country` varchar(2) DEFAULT NULL COMMENT 'Country',
		  `billing_address_phone` varchar(255) DEFAULT NULL COMMENT 'Phone Number',
		  `billing_address_mobile` varchar(255) DEFAULT NULL COMMENT 'Mobile Number',
		  `customer_dob` datetime DEFAULT NULL COMMENT 'Dob',
		  `is_company` smallint(6) DEFAULT NULL COMMENT 'Is company',
		  `company_name` varchar(255) NOT NULL COMMENT 'Company name',
		  `company_website` varchar(255) NOT NULL COMMENT 'Company website',
		  `company_description` text COMMENT 'Company description',
		  `company_id_number` varchar(255) DEFAULT NULL COMMENT 'Company ID number',
		  `is_debtor` smallint(6) DEFAULT NULL COMMENT 'Is debtor',
		  `customer_nationality` varchar(2) DEFAULT NULL COMMENT 'Nationality',
		  `customer_birth_city` varchar(255) DEFAULT NULL COMMENT 'City of Birth',
		  `customer_birth_country` varchar(2) DEFAULT NULL COMMENT 'Birth country',
		  `payer_or_beneficiary` int(11) DEFAULT NULL COMMENT 'Payer or beneficiary',
		  `is_onetime_customer` smallint(6) NOT NULL COMMENT 'Is One time customer',
		  `is_default` smallint(6) NOT NULL COMMENT 'Is default',
		  `status` smallint(6) DEFAULT NULL COMMENT 'Enabled',
		  `date_add` datetime NOT NULL COMMENT 'Wallet Creation Time',
		  `date_upd` datetime NOT NULL COMMENT 'Wallet Modification Time',
		  PRIMARY KEY (`id_wallet`),
		  UNIQUE KEY (`id_lw_wallet`)
		) ENGINE=InnoDB ".$charset_collate." ;";
		
		$sql[] = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'lemonway_wktoken` (
					    `id_cart_wktoken` int(11) NOT NULL AUTO_INCREMENT,
						`id_cart` int(11) NOT NULL,
						`wktoken` varchar(190) NOT NULL,
					    PRIMARY KEY  (`id_cart_wktoken`),
		   				UNIQUE KEY `wktoken` (`wktoken`),
		   				UNIQUE KEY `id_cart` (`id_cart`)
					) ENGINE=InnoDB '.$charset_collate.';';
     
     	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
     
     	foreach($sql as $q){        		
     		dbDelta( $q );
     	}
     
     	add_option( 'lw_db_version', self::DB_VERSION);
     
     }
     
     
}

function LW(){
	return Lemonway::instance();
}
LW();