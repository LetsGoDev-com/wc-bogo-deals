<?php
namespace BogoDeals\Controllers;

use BogoDeals\Traits\Singleton;

class LinksController {
    use Singleton;

    public function __construct() {
        \add_filter( 'plugin_row_meta', [ $this, 'pluginInfo' ], 10, 2 );
    }

    /**
	 * Plugin Info
	 * @param  array  $links
	 * @param  string $file
	 * @return array
	 */
	public function pluginInfo( array $links = [], string $file = '' ) {

		if( $file != 'wc-bogo-deals/wc-bogo-deals.php' ) {
			return $links;
		}

		$newLinks = [
			'premium' => sprintf(
				'<a href="%s" target="_blank" title="%s">%s</a>',
				\esc_url( 'https://www.letsgodev.com/product/woocommerce-bogo-deals/' ),
				\esc_html__( 'Bogo Deals PRO', 'letsgodev' ),
				\esc_html__( 'Bogo Deals PRO', 'letsgodev' )
			),
		];

		return \array_merge( $links, $newLinks );
	}
}