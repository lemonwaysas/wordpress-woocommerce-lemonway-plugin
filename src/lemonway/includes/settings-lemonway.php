<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for Lemonway Gateway.
 */
return array(
	'api_configuration' => array(
		'title'       => __( 'API Configuration', LEMONWAY_TEXT_DOMAIN ),
		'type'        => 'title',
		'description' => ''
	),
	WC_Gateway_Lemonway::API_LOGIN => array(
		'title'       => __( 'Production Api login', LEMONWAY_TEXT_DOMAIN),
		'type'        => 'text',
		'description' => '',
		'default'     => '',
		'desc_tip'    => true,
		'placeholder' => ''
	),
	WC_Gateway_Lemonway::API_PASSWORD => array(
		'title'       => __( 'Production Api password', LEMONWAY_TEXT_DOMAIN),
		'type'        => 'password',
		'description' => '',
		'default'     => '',
		'desc_tip'    => true,
		'placeholder' => ''
	),
	WC_Gateway_Lemonway::WALLET_MERCHANT_ID => array(
		'title'       => __( 'Your wallet Id', LEMONWAY_TEXT_DOMAIN),
		'type'        => 'text',
		'description' => 'It\'s the wallet where your payments are credited.You must to create it in BO Lemonway',
		'default'     => '',
		'desc_tip'    => true,
		'placeholder' => ''
	),
	WC_Gateway_Lemonway::DIRECTKIT_URL => array(
		'title'       => __( 'Directkit url', LEMONWAY_TEXT_DOMAIN),
		'type'        => 'text',
		'description' => 'JSON2 only! E.g: https://ws.lemonway.fr/mb/xxx/yyy/directkitjson2/service.asmx',
		'default'     => '',
		'desc_tip'    => false,
		'placeholder' => ''
	),	
	WC_Gateway_Lemonway::WEBKIT_URL => array(
		'title'       => __( 'Webkit url', LEMONWAY_TEXT_DOMAIN),
		'type'        => 'text',
		'description' => 'E.g: https://m.lemonway.fr/mb/xxx/yyy/',
		'default'     => '',
		'desc_tip'    => false,
		'placeholder' => ''
	),
	WC_Gateway_Lemonway::DIRECTKIT_URL_TEST => array(
		'title'       => __( 'Directkit url test', LEMONWAY_TEXT_DOMAIN),
		'type'        => 'text',
		'description' => 'JSON2 only! E.g: https://ws.lemonway.fr/mb/xxx/dev/directkitjson2/service.asmx',
		'default'     => '',
		'desc_tip'    => false,
		'placeholder' => ''
	),
	WC_Gateway_Lemonway::WEBKIT_URL_TEST => array(
		'title'       => __( 'Webkit url test', LEMONWAY_TEXT_DOMAIN),
		'type'        => 'text',
		'description' => 'E.g: https://m.lemonway.fr/mb/xxx/dev/',
		'default'     => '',
		'desc_tip'    => false,
		'placeholder' => ''
	),
	WC_Gateway_Lemonway::IS_TEST_MODE => array(
		'title'       => __( 'Enable test mode', LEMONWAY_TEXT_DOMAIN ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable test mode', LEMONWAY_TEXT_DOMAIN ),
		'default'     => 'no',
		'description' =>  __( 'Call requests in test API Endpoint.', LEMONWAY_TEXT_DOMAIN)
	),
	'payment_configuration' => array(
		'title'       => __( 'Payment Configuration', LEMONWAY_TEXT_DOMAIN ),
		'type'        => 'title',
		'description' => ''
	),
	WC_Gateway_Lemonway::ENABLED => array(
		'title'   => __( 'Enable/Disable', 'woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable Lemonway payment', LEMONWAY_TEXT_DOMAIN ),
		'default' => 'no'
	),
	WC_Gateway_Lemonway::TITLE => array(
		'title'       => __( 'Title', 'woocommerce' ),
		'type'        => 'text',
		'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
		'default'     => __( 'Credit Card', LEMONWAY_TEXT_DOMAIN ),
		'desc_tip'    => true
	),
	WC_Gateway_Lemonway::DESCRIPTION => array(
		'title'       => __( 'Description', 'woocommerce' ),
		'type'        => 'text',
		'desc_tip'    => true,
		'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce' ),
		'default'     => __( 'You will be redirect to payment page after you submit order.', LEMONWAY_TEXT_DOMAIN )
	),
	WC_Gateway_Lemonway::CSS_URL => array(
		'title'       => __( 'Css url', 'woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Optionally enter the url of the page style you wish to use.', LEMONWAY_TEXT_DOMAIN ),
		'default'     => 'https://www.lemonway.fr/mercanet_lw.css',
		'desc_tip'    => true,
		'placeholder' => __( 'Optional', 'woocommerce' )
	),
	WC_Gateway_Lemonway::ONECLIC_ENABLED => array(
		'title'       => __( 'Enable Oneclic', LEMONWAY_TEXT_DOMAIN ),
		'type'        => 'checkbox',
		'description' => __( 'Display checkbox for allow customer to save his credit card.', LEMONWAY_TEXT_DOMAIN ),
		'label'   	  => __( 'Enable Oneclic', LEMONWAY_TEXT_DOMAIN ),
		'default'     => 'no',
		'desc_tip'    => true
	),
	WC_Gateway_Lemonway::DEBUG => array(
		'title'       => __( 'Debug Log', 'woocommerce' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable logging', 'woocommerce' ),
		'default'     => 'no',
		'description' => sprintf( __( 'Log Lemonway events, such as notification requests, inside <code>%s</code>', LEMONWAY_TEXT_DOMAIN ), wc_get_log_file_path( 'lemonway' ) )
	)
);
