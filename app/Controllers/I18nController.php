<?php
Namespace BogoDeals\Controllers;

use BogoDeals\Traits\Singleton;


/**
 * Internationalization Controller
 */
class I18nController {
	use Singleton;

	
	/**
	 * Domain to i18n
	 * @var string
	 */
	protected string $domain = 'wc-bogo-deals';


	/**
	 * Construct
	 * Define the locale for this plugin for internationalization.
	 */
	public function __construct() {
		\add_action( 'plugins_loaded', [ $this, 'loadTextdomain' ] );
	}


	/**
	 * Call text domain
	 * @return void
	 */
	public function loadTextdomain(): void {
		\load_plugin_textdomain(
			$this->domain,
			false,
			\dirname( BOGO_DEALS_BASE ) . '/resources/languages/'
		);
	}
}