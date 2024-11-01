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
	'enabled' => array(
		'title'   => __( 'Enable/Disable', 'woo-paymentsgy' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable Payments.GY', 'woo-paymentsgy' ),
		'default' => 'no',
	),
);

