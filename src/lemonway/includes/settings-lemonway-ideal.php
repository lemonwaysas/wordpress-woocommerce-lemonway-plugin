<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for Lemonway IDeal Gateway.
 */
return array(
	'payment_configuration' => array(
			'title'       => __( 'Payment Configuration', LEMONWAY_IDEAL_TEXT_DOMAIN ),
			'type'        => 'title',
			'description' => '',
	),
	WC_Gateway_Lemonway_Ideal::ENABLED => array(
		'title'   => __( 'Enable/Disable', 'woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable Lemonway IDeal payment', LEMONWAY_IDEAL_TEXT_DOMAIN ),
		'default' => 'no'
	),
	WC_Gateway_Lemonway_Ideal::TITLE => array(
		'title'       => __( 'Title', 'woocommerce' ),
		'type'        => 'text',
		'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
		'default'     => __( 'IDeal', LEMONWAY_IDEAL_TEXT_DOMAIN ),
		'desc_tip'    => true,
	),
	WC_Gateway_Lemonway_Ideal::DESCRIPTION => array(
		'title'       => __( 'Description', 'woocommerce' ),
		'type'        => 'text',
		'desc_tip'    => true,
		'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce' ),
		'default'     => __( 'You will be redirect to payment page after you submit order.', LEMONWAY_IDEAL_TEXT_DOMAIN )
	),
	WC_Gateway_Lemonway_Ideal::DEBUG => array(
		'title'       => __( 'Debug Log', 'woocommerce' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable logging', 'woocommerce' ),
		'default'     => 'no',
		'description' => sprintf( __( 'Log Lemonway events, such as notification requests, inside <code>%s</code>', LEMONWAY_IDEAL_TEXT_DOMAIN ), wc_get_log_file_path( 'lemonway' ) )
	)
);
