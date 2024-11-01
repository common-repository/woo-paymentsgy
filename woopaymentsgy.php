<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * @link              https://intellectstorm.com
 * @since             0.0.8
 * @package           Paymentsgy
 *
 * @wordpress-plugin
 * Plugin Name:       Payments.GY
 * Plugin URI:        https://gitlab.com/intellectstorm/paymentsgy/paymentsgy-plugin
 * Description:       The safe and simple way to pay.
 * Version:           1.0.3
 * Author:            IntellectStorm Inc
 * Author URI:        https://intellectstorm.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woo-paymentsgy
 * Domain Path:       /languages
 *
 * Copyright (C) 2019 IntellectStorm Inc
 */

 // Register gateway class with WooCommerce
 add_action('plugins_loaded', 'init_woo_payments_gy');

 function init_woo_payments_gy() {
     if(!class_exists('WC_Payment_Gateway')) return;
     include_once('includes/class-woo-paymentsgy.php');     
     
     // Register class in WooCommerce
     add_filter('woocommerce_payment_gateways', 'regis_woo_paymentsgy');
     function regis_woo_paymentsgy($payment_gateways) {
         $payment_gateways[] = 'Woo_Paymentsgy';
         return $payment_gateways;
     }
}

// Add custom action links
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'woo_paymentsgy_links');
function woo_paymentsgy_links($links) {
    $plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=woopaymentsgy' ) . '">' . __( 'Settings', 'woo_paymentsgy' ) . '</a>',
    );
    return array_merge($plugin_links, $links);
}

// Add section to WooCommerce
add_filter('woocommerce_get_sections_checkout', 'add_section_woopaymentsgy');
function add_section_woopaymentsgy($section) {
    $section['woopaymentsgy'] = __('Payments.GY', 'woo-paymentsgy');
    return $section;
}
