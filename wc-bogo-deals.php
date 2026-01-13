<?php
/**
 * @link     https://www.letsgodev.com/
 * @since    1.0.0
 * @package  wc-bogo-deals
 * 
 * Plugin Name:          Bogo Deals For WooCommerce
 * Plugin URI:           https://blog.letsgodev.com/woocommerce-plugin/2x1-or-3x2-offers-in-woocommerce/
 * Description:          This plugin allows add bogo deals to the shop
 * Version:              1.0.6
 * Author:               Lets Go Dev
 * Author URI:           https://www.letsgodev.com/
 * Developer:            Alexander Gonzales
 * Developer URI:        https://vcard.gonzalesc.org/
 * License:              GPL-3.0+
 * License URI:          https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:          wc-bogo-deals
 * Requires Plugins:     woocommerce
 * Requires PHP:         7.4
 * WP stable tag:        6.8.0
 * WP requires at least: 6.8.0
 * WP tested up to:      6.9.0
 * WC requires at least: 9.6.0
 * WC tested up to:      10.4.3
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'BOGO_DEALS_FILE', __FILE__ );
define( 'BOGO_DEALS_DIR', plugin_dir_path( __FILE__ ) );
define( 'BOGO_DEALS_URL', plugin_dir_url( __FILE__ ) );
define( 'BOGO_DEALS_BASE', plugin_basename( __FILE__ ) );


// External Libraries
require_once BOGO_DEALS_DIR . 'vendor/autoload.php';

// Initialize BogoDeals
function bogodeals_init() {
	return \BogoDeals\Core\BogoDeals::getInstance();
}

bogodeals_init();


// Activate plugin
register_activation_hook(
	BOGO_DEALS_FILE,
	[ \BogoDeals\Controllers\ActivatorController::getInstance(), 'activate' ]
);

