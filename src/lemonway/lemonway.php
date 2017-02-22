<?php
/*
 Plugin Name: Lemonway
 Plugin URI: https://www.lemonway.com
 Description: Secured payment solutions for Internet marketplaces, eCommerce, and crowdfunding. Payment API. BackOffice management. Compliance. Regulatory reporting.
 Version: 1.1.3
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
	
	/**
	 * Pointer to gateway making the request.
	 * @var WC_Gateway_Lemonway
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
     	
     	register_activation_hook( __FILE__, array($this,'lw_install') );
     	
     	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
     	add_action( 'plugins_loaded', array( $this, 'init_gateway' ), 0 );
     	add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );
     	
     	//Add menu elements
     	add_action('admin_menu', array($this, 'add_admin_menu'), 57);

     	$this->load_plugin_textdomain();
     	
     }
     
     /**
      * Add menu Lemonway
      */
     public function add_admin_menu(){

     	add_menu_page( __( 'Lemonway',LEMONWAY_TEXT_DOMAIN ),__( 'Lemonway ',LEMONWAY_TEXT_DOMAIN ), 'manage_product_terms', $this->slug, null, null, '58' );
     	add_submenu_page($this->slug, __( 'Moneyout ',LEMONWAY_TEXT_DOMAIN ), __( 'Moneyout ',LEMONWAY_TEXT_DOMAIN ), 'manage_product_terms', $this->slug, array($this, 'moneyout_html'));
     	add_submenu_page($this->slug, __( 'Configuration ',LEMONWAY_TEXT_DOMAIN ), __( 'Configuration ',LEMONWAY_TEXT_DOMAIN ), 'manage_product_terms', $this->slug . 'configuration', array($this, 'redirect_configuration'));

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
		$this->gateway = new WC_Gateway_Lemonway();
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
     
     public function redirect_configuration(){
     	wp_redirect(admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_lemonway' ),301);
     }
     
     public function moneyout_html(){
     	echo '<h1>'.get_admin_page_title().'</h1>';
     	
     	$walletId = $this->gateway->getMerchantWalletId();
     	if(empty($walletId)){
     		echo __('You need to enter your Wallet Id in',LEMONWAY_TEXT_DOMAIN) . '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_lemonway' ) . '">' . __( 'Settings', LEMONWAY_TEXT_DOMAIN ) . '</a>';
     		return;
     	}
     	
     	
     	$this->displayFormMoneyout($walletId);
          	
          	
 	}
 	
 	public function displayFormMoneyout($walletId){
 	
 		try {
 			/** @var $wallet Wallet **/
 			$wallet = $this->getWalletDetails($walletId);
 		} catch (Exception $e) {
 			echo esc_html( $e->getMessage() );
 			return;
 		}
 		
 		if ( isset( $_POST['amountToPay'] ) && check_admin_referer( 'moneyout_' . $walletId ) ) {
 			 
 			$amountToPay = (float)str_replace(",", ".", wc_clean( $_POST['amountToPay'] ) );
 			 
 			if ($amountToPay > $wallet->BAL) {
 				$message = sprintf(__("You can't paid amount upper of your balance amount: %s",LEMONWAY_TEXT_DOMAIN), wc_price($wallet->BAL));
 				echo '<div id="message" class="error notice-error is-dismissible"><p>' . $message. '</p></div>';
 			}
 			elseif($amountToPay <= 0) {
 				$message = __("Amount must be greater than 0",LEMONWAY_TEXT_DOMAIN);
 				echo '<div id="message" class="error notice-error is-dismissible"><p>' . esc_html( $message ). '</p></div>';
 				 
 			}
 			else
 			{
 				$ibanId = 0;
 		
 				if( isset( $_POST['iban_id'] ) && is_array( $_POST['iban_id'] ) && check_admin_referer( 'moneyout_' . $walletId ) ) {
 					$ibanId = current(wc_clean( $_POST['iban_id'] ) );
 					$iban = wc_clean( $_POST['iban_' . $ibanId] );
 					 
 					try {
 						$params = array(
 								"wallet" => $wallet->ID,
 								"amountTot" => sprintf("%.2f", $amountToPay),
 								"amountCom" => sprintf("%.2f", (float)0),
 								"message" => __("Moneyout from Wordpress Woocommerce module", LEMONWAY_TEXT_DOMAIN),
 								"ibanId" => $ibanId,
 						);
 		
 						$op = $this->gateway->getDirectkit()->MoneyOut($params);
 		
 						if($op->STATUS == "3"){
 							$wallet->BAL = $wallet->BAL - $amountToPay;
 							$message = sprintf(__("You paid %s to your Iban %s from your wallet <b>%s</b>",LEMONWAY_TEXT_DOMAIN),wc_price($amountToPay), $iban, $wallet->ID);
 							echo '<div id="message" class="updated notice is-dismissible"><p>' . $message . '</p></div>';
 			     
 						}
 						else {
 							$message = __("An error occurred. Please contact support.",LEMONWAY_TEXT_DOMAIN);
 							echo '<div id="message" class="error notice-error is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
 						}
 			    
 					} catch (Exception $e) {
 						echo '<div id="message" class="error notice-error is-dismissible"><p>' . esc_html( $e->getMessage() ) . '</p></div>';
 					}
 				}
 				else {
 					$message = __('Please select an IBAN at least',LEMONWAY_TEXT_DOMAIN) ;
 					echo '<div id="message" class="error notice-error is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
 				}
 			} 
 		} 
 		?>
 		          <form method="post" action="">
                    <?php wp_nonce_field('moneyout_' . $wallet->ID); ?>
 		          	<div class="card wallet-info" >
 		          		<h3><?php echo __('Wallet informations', LEMONWAY_TEXT_DOMAIN) ?></h3>
 		          <table >
 		            <tr>
 		                <td class="label"><label ><?php echo __('Wallet ID', LEMONWAY_TEXT_DOMAIN)?></label></td>
 		                <td class="value">
 		                    <strong><?php echo esc_html( $wallet->ID ) ?></strong>
 		                </td>
 		            </tr>
 		            <tr>
 		                <td class="label"><label ><?php echo __('Balance', LEMONWAY_TEXT_DOMAIN)?></label></td>
 		                <td class="value">
 		                    <strong><?php echo wc_price(esc_html( $wallet->BAL ) ) ?></strong>
 		                </td>
 		            </tr>
 		            <tr>
 		                <td class="label"><label ><?php echo __('Owner name', LEMONWAY_TEXT_DOMAIN)?></label></td>
 		                <td class="value">
 		                    <strong><?php echo esc_html( $wallet->NAME ) ?></strong>
 		                </td>
 		            </tr>
 		            <tr>
 		                <td class="label"><label ><?php echo __('Owner email', LEMONWAY_TEXT_DOMAIN)?></label></td>
 		                <td class="value">
 		                    <strong><?php echo esc_html( $wallet->EMAIL ) ?></strong>
 		                </td>
 		            </tr>
 		            <tr>
 		                <td class="label"><label ><?php echo __('Status', LEMONWAY_TEXT_DOMAIN)?></label></td>
 		                <td class="value">
 		                    <strong><?php echo esc_html( $wallet->getStatusLabel() ) ?></strong>
 		                </td>
 		            </tr>
 		        </table>
 		          	</div>
 		          	<div class="card iban-info">
 		          		<h3><?php echo __('Iban informations',LEMONWAY_TEXT_DOMAIN) ?></h3>
 		          		<?php if(count($wallet->ibans)) :?>
 				        <table>
 				        <tr><td colspan="2"><?php echo __('Select an Iban', LEMONWAY_TEXT_DOMAIN) ?></td></tr>
 					        <?php foreach ($wallet->ibans as $_iban) : /** @var $_iban Iban */?>
 					        <tr>
 					        	<td>
 					        		 <input type="hidden" value="<?php echo $_iban->IBAN ?>" name="iban_<?php echo $_iban->ID ?>" />
 					        	</td>
 					        	<td class="a-left">
 						        	<label for="iban_<?php echo $_iban->ID ?>" >
 						        	<input class="required-entry" id="iban_<?php echo $_iban->ID ?>" type="radio" name="iban_id[]" value="<?php echo $_iban->ID ?>" />
 						        		<strong><?php echo esc_html( $_iban->IBAN ) ?></strong>
 					                    <br />
 					                    <strong><?php echo esc_html( $_iban->BIC ) ?></strong>
 					                    <br />
 					                  <!--   <?php //echo __('Status',LEMONWAY_TEXT_DOMAIN)?>&nbsp;<strong><?php // echo $_iban->STATUS ?></strong> -->
 					                </label>
 								</td>
 					        </tr>
 					        <?php endforeach; ?>
 				        </table>
 				        <?php else:?>
 				        	<div class="box">
 						    	<h4><?php echo __("You don't have any Iban!", LEMONWAY_TEXT_DOMAIN)?></h4> 
 						    	<?php echo sprintf(__('Please create at least one for wallet <b>%s</b> in Lemonway BO.', LEMONWAY_TEXT_DOMAIN), esc_html( $wallet->ID )) ?>
 						    </div>
 				        <?php endif; ?>
 		          	</div>
 		          	
 		          	<?php if(count($wallet->ibans) && (float)$wallet->BAL > 0) :?>
 		          	<div class="card moneyout-form" >
 		          		<h3><?php echo __('Moneyout informations', LEMONWAY_TEXT_DOMAIN) ?></h3>
 		          		
 						    <table class="form-table">
 						    	<tbody>
 						    		<tr>
 						    			<th scope="row"><?php echo __("Amount to pay", IZIFLUX_TEXT_DOMAIN)?></th>
 							    		<td>
 							    			<input type="text" id="amountToPay" class="input-text not-negative-amount"  name="amountToPay" value="<?php echo esc_attr( $wallet->BAL ) ?>">
 							    		</td>
 							    	</tr>
 						    	</tbody>
 						    </table>
 							<?php submit_button(__("Do a moneyout", IZIFLUX_TEXT_DOMAIN)); ?>
 						   
 		          	</div>
 		          	<?php endif;?>
 		          	</form>
 		          	<?php 
 	
 	}
 	
 	/**
 	 *
 	 * @param string $walletId
 	 * @throws Exception
 	 * @return Wallet
 	 */
 	public function getWalletDetails($walletId) {
 		$kit = $this->gateway->getDirectkit();
 	
 		try {
 			return $kit->GetWalletDetails(array('wallet' => $walletId));
 		} catch (Exception $e) {
 			throw $e;
 		}
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
     			add_action('admin_notices', array( &$this, 'alert_woo_not_active' ) );
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
     static function alert_woo_not_active() {
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