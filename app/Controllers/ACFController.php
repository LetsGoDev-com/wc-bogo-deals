<?php
Namespace BogoDeals\Controllers;

use BogoDeals\Traits\Singleton;


class ACFController {
	use Singleton;

	/**
	 * LoadACF variable
	 * @var boolean
	 */
	protected bool $loadACF = false;


	/**
	 * Construct
	 */
	public function __construct() {

		$this->loadACF();
		$this->hooks();

	}

	/**
	 * Load ACF libraries
	 * @return void
	 */
	public function loadACF(): void {
		
		if ( ! \function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Check if ACF PRO is active
		if ( \is_plugin_active( 'advanced-custom-fields-pro/acf.php' ) ) {
			// Abort all bundling, ACF PRO plugin takes priority
			return;
		}

		// Check if ACF FREE is active
		if ( \is_plugin_active( 'advanced-custom-fields/acf.php' ) ) {
			return;
		}

		// Check if another plugin or theme has bundled ACF
		if ( \defined( 'BOGO_DEALS_ACF_PATH' ) ) {
		    return;
		}

		$this->loadACF = true;

		\define( 'BOGO_DEALS_ACF_PATH', BOGO_DEALS_DIR . '/vendor/wpackagist-plugin/advanced-custom-fields/' );
		\define( 'BOGO_DEALS_ACF_URL', BOGO_DEALS_URL . '/vendor/wpackagist-plugin/advanced-custom-fields/' );

		// Include the ACF plugin.
		include_once BOGO_DEALS_ACF_PATH . 'acf.php';
	}


	/**
	 * Hooks
	 * @return void
	 */
	public function hooks(): void {

		if ( ! $this->loadACF ) {
			return;
		}

		// Get Settings URL
		\add_filter( 'acf/settings/url', [ $this, 'getSettingsURL' ] );

		// Hide the ACF admin menu item.
		\add_filter( 'acf/settings/show_admin', '__return_false' );
    	
    	// Hide the ACF Updates menu
    	\add_filter( 'acf/settings/show_updates', '__return_false', 100 );
	}

	/**
	 * Get Settings URL
	 * @return string
	 */
	public function getSettingsURL(): string {
		return BOGO_DEALS_ACF_URL;
	}
}