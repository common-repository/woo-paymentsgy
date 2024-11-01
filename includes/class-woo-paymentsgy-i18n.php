<?php
if ( ! defined( 'ABSPATH')){
	exit;
}

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://github.com/carlHandy/
 * @since       0.0.8
 *
 * @package    Payments.GY
 * @subpackage Paymentsgy/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Payments.GY
 * @subpackage Paymentsgy/includes
 * @author     Carl Handy <carl@intellectstorm.com>
 */
class Woo_Paymentsgy_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'woo-paymentsgy',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
