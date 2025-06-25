<?php
Namespace BogoDeals\Controllers;

use BogoDeals\Traits\Singleton;
use function BogoDeals\getSubtotalByOrder;


class OrderController {
	use Singleton;


	/**
	 * Construct
	 */
	public function __construct() {
		
		// Add the discount found in order item meta 
		\add_action( 'woocommerce_checkout_create_order_line_item', [ $this, 'createItemMeta' ], 10, 3 );

		// Remove item meta to display
		\add_filter( 'woocommerce_order_item_get_formatted_meta_data', [ $this, 'itemMetaToDisplay' ], 10, 2 );

		// Recalculate subtotal to order->get_subtotal()
		\add_filter( 'woocommerce_order_get_subtotal', [ $this, 'calculateSubtotal' ], 10, 2 );

		// Recalculate subtotal to order review
		\add_filter( 'woocommerce_get_order_item_totals', [ $this, 'calculateSubtotalOrderReview' ], 10, 3 );

		// Display item order subtotal like OnSale format
		\add_filter( 'woocommerce_order_formatted_line_subtotal', [ $this, 'itemOrderSubtotal' ], 10, 3 );

		// Save $order meta
		\add_action( 'woocommerce_checkout_create_order', [ $this, 'saveOrderMeta' ] );

	}


	/**
	 * Create orden item meta with the discount information
	 * Note: In this hook, the calcule_totals method was executed by WC
	 * @param  \WC_Order_Item_Product $item       
	 * @param  string                 $cartItemKey
	 * @param  array                  $cartItem   
	 * @return void
	 */
	public function createItemMeta( \WC_Order_Item_Product $item, string $cartItemKey, array $cartItem ): void {

		$product        = $item->get_product();
		$discountToSame = DiscountController::getInstance()->getDiscountToSame();

		if ( ! isset( $discountToSame[ $product->get_id() ] ) ) {
			return;
		}

		$item->set_props( [
			'subtotal' => $discountToSame[ $product->get_id() ]['subtotal_all'],
			'total'    => $discountToSame[ $product->get_id() ]['subtotal_pay'],
		] );

		$item->add_meta_data( 'wc_bogo_deals', $discountToSame[ $product->get_id() ] );
	}


	/**
	 * Item meta to display in order detail
	 * @param  array  $formattedMeta
	 * @param  \WC_Order_Item $item
	 * @return array
	 */
	public function itemMetaToDisplay( array $formattedMeta, \WC_Order_Item $item ): array {

		if ( ! $item->meta_exists( 'wc_bogo_deals' ) ) {
			return $formattedMeta;
		}

		$discountMeta = $item->get_meta( 'wc_bogo_deals', true );

		$formattedMeta[ $item->get_id() ] = (object) [
			'key'           => \esc_html__( 'Bogo Deals', 'wc-bogo-deals' ),
			'value'         => $discountMeta['deal_label'],
			'display_key'   => \esc_html__( 'Bogo Deals', 'wc-bogo-deals' ),
			'display_value' => $discountMeta['deal_label'],
		];

		return $formattedMeta;
	}


	/**
	 * Calculate subtotal. Apply to $order->get_subtotal() method
	 * @param  float     $subtotal
	 * @param  \WC_Order $order   
	 * @return float
	 */
	public function calculateSubtotal( float $subtotal, \WC_Order $order ): float {

		$discountToSame = 0;

		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();
			
			if ( $item->meta_exists( 'wc_bogo_deals' ) ) {
				$discountMeta    = $item->get_meta( 'wc_bogo_deals', true );
				$discountToSame += $discountMeta[ 'subtotal_no_pay' ];
			}
		}

		if ( $discountToSame === 0 ) {
			return $subtotal;
		}

		return (float) ( $subtotal - $discountToSame );
	}


	/**
	 * Calculate subtotal in WC Order.
	 * Note: When checkout is processing, subtotal cart must recalculate, totals cart no
	 * @param  array     $totalRows
	 * @param  \WC_Order $order
	 * @param  string $taxDisplay
	 * @return array
	 */
	public function calculateSubtotalOrderReview( array $totalRows, \WC_Order $order, string $taxDisplay ): array {

		$discountToSame = 0;

		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();
			
			if ( $item->meta_exists( 'wc_bogo_deals' ) ) {
				$discountMeta    = $item->get_meta( 'wc_bogo_deals', true );
				$discountToSame += $discountMeta[ 'subtotal_no_pay' ];
			}
		}

		if ( $discountToSame === 0 ) {
			return $totalRows;
		}

		\remove_filter( 'woocommerce_order_get_subtotal', [ $this, 'calculateSubtotal' ], 10, 2 );

		$totalRows['cart_subtotal']['value'] = \wc_price(
			getSubtotalByOrder( $taxDisplay, $order ) - $discountToSame
		);

		\add_filter( 'woocommerce_order_get_subtotal', [ $this, 'calculateSubtotal' ], 10, 2 );

		return $totalRows;
	}


	/**
	 * Item subtotal in WC Order
	 * @param  string                 $subtotal
	 * @param  \WC_Order_Item_Product $item 
	 * @return string
	 */
	public function itemOrderSubtotal( string $subtotal, \WC_Order_Item_Product $item ): string {

		if ( ! $item->meta_exists( 'wc_bogo_deals' ) ) {
			return $subtotal;
		}

		$discountToSame = $item->get_meta( 'wc_bogo_deals', true );

		$subtotal = \wc_format_sale_price(
			$discountToSame['subtotal_all'],
			$discountToSame['subtotal_pay'],
		);

		$subtotalLabel = \sprintf(
			'%s <small>(%s)</small>', $subtotal, $discountToSame['deal_label']
		);

		return $subtotalLabel;
	}


	/**
	 * Set a meta value to order
	 * @param  \WC_Order $order
	 * @return void
	 */
	public function saveOrderMeta( \WC_Order $order ): void {
		$discountToSame     = DiscountController::getInstance()->getDiscountToSame();
		$discountToMultiple = DiscountController::getInstance()->getDiscountToMultiple();

		if ( empty( $discountToSame ) && empty( $discountToMultiple ) ) {
			return;
		}

		$dealIDs = [];

		if ( ! empty( $discountToSame ) ) {
			$dealIDs = \array_merge( $dealIDs, \wp_list_pluck( $discountToSame, 'deal_id' ) );
		}

		if ( ! empty( $discountToMultiple ) ) {
			$dealIDs = \array_merge( $dealIDs, \wp_list_pluck( $discountToMultiple, 'deal_id' ) );
		}

		if ( empty( $dealIDs ) ) {
			return;
		}

		foreach ( $dealIDs as $dealID ) {
			$order->add_meta_data( \sprintf( '_wc_bogo_deals_%d', $dealID ), true, true );
		}
	}
}