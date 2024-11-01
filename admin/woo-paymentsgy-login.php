<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 * 
 * @documentation https://docs.woocommerce.com/document/settings-api/
 *
 * @link       https://github.com/carlHandy/
 * @since       1.0
 *
 * @package    Payments.GY
 * @subpackage Paymentsgy/admin
 */

return array(
	'name' => array(
		'title' => __('Name', 'woo-paymentsgy'),
		'type' => 'text',
		'description' => __('Your application\'s name)', 'woo-paymentsgy'),
		'default'     => '',
		'desc_tip'    => true,
	),
	'api_email' => array(
		'title' => __('Payments.GY Email', 'woo-paymentsgy'),
		'type' => 'email',
		'id'   => 'api_email',
		'description' => __('Your Payments.GY email address (must be verified!)', 'woo-paymentsgy'),
		'default'     => '',
		'desc_tip'    => true,
	),
	'api_password' => array(
		'title'       => __( 'Payments.GY Password', 'woo-paymentsgy'),
		'type'        => 'password',
		'id'		  => 'api_password',
		'description' => __( 'Your Payments.GY password', 'woo-paymentsgy'),
		'default'     => '',
		'desc_tip'    => true,
                'sanitize_callback' => 'sanitize_text_field'
	),
	'environment' => array(
		'title'       => __( 'Environment', 'woo-paymentsgy'),
		'type'        => 'select',
		'description' => __( 'This setting specifies whether you will process live transactions, or whether you will process simulated transactions using the Payments.GY Sandbox.', 'woo-paymentsgy' ),
		'default'     => 'live',
		'desc_tip'    => true,
		'options'     => array(
			'https://api.developer.payments.gy'    => __( 'Live', 'woo-paymentsgy' ),
			'https://api.developer.sandbox.payments.gy' => __( 'Sandbox', 'woo-paymentsgy' ),
		),
                'id' => 'domain',
                'sanitize_callback' => 'sanitize_text_field'
	),
);

