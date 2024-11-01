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
	'wallet_hash' => array(
		'title' => __('Merchant wallet', 'woo-paymentsgy'),
		'type' 	=> 'select',
                'options' => array_merge([
                    '' => 'Choose your wallet'
                ], $this->get_option('wallets', [])),
		'label' => __('Select wallet'),
		'description' => __('Select wallet used for accepting payments.', 'woo-paymentsgy'),
		'desc_tip' => true,
                'sanitize_callback' => 'sanitize_text_field'
	),
);

