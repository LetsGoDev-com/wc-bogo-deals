<?php
Namespace BogoDeals\Controllers;

use BogoDeals\Traits\Singleton;

class NoticeController {
	use Singleton;


	/**
	 * __construct
	 */
	public function __construct() {

		// Notice in product page
		\add_action( 'woocommerce_before_single_product_summary', [ $this, 'displayProductPage' ], 15 );

		// Notice in shop page
		\add_action( 'woocommerce_after_shop_loop_item_title', [ $this, 'displayShopPage' ], 5 );
	}


	/**
	 * Deal Notice by Product
	 * @return void
	 */
	public function displayProductPage(): void {
		global $product;


		// Is not product page
		if ( ! \is_product() ) {
			return;
		}

		$dealsByProduct = DealController::getInstance()->getDealsByProduct( $product->get_id(), 'product' );

		if ( empty( $dealsByProduct ) ) {
			return;
		}

		$html = '<span class="onsale" style="margin: 0 1px;">[deal_number]</span>';

		echo \wp_kses_post( \apply_filters(
			'bogodeals/notice/product',
			\str_replace( '[deal_number]', '2x1', $html ),
			$dealsByProduct
		) );
	}


	/**
	 * Notice in Shop page
	 * @return void
	 */
	public function displayShopPage(): void {
		global $product;

		// Is shop page
		if ( ! \is_shop() ) {
			return;
		}

		$dealsByProduct = DealController::getInstance()->getDealsByProduct( $product->get_id(), 'shop' );

		if ( empty( $dealsByProduct ) ) {
			return;
		}

		$html = '<span class="onsale" style="margin: 0 1px;">[deal_number]</span>';

		echo \wp_kses_post( \apply_filters(
			'bogodeals/notice/shop',
			\str_replace( '[deal_number]', '2x1', $html ),
			$dealsByProduct
		) );
	}
}
