<?php

Namespace BogoDeals;


/**
 * Get product categories from WC
 * @return array
 */
function getProductCategories(): array {

	$productCats = [];

	$terms = \get_terms( [
		'taxonomy'     => 'product_cat',
		'orderby'      => 'term_id',
		'hierarchical' => 1,
		'hide_empty'   => false,
		'fields'       => 'all'
	]);

	if ( ! empty( $terms ) && ! \is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$productCats[ $term->term_id ] = $term->name;
		}
	}

	return $productCats;
}


/**
 * Get Product Subtotal
 * @param  \WC_Product $product
 * @param  int         $quantity
 * @return float
 */
function getProductSubtotal( \WC_Product $product, int $quantity ): float {
	$price = $product->get_price();

	if ( $product->is_taxable() ) {

		if ( \WC()->cart->display_prices_including_tax() ) {
			$productSubtotal = \wc_get_price_including_tax( $product, [ 'qty' => $quantity ] );

		} else {
			$productSubtotal = \wc_get_price_excluding_tax( $product, [ 'qty' => $quantity ] );
		}
	} else {
		$productSubtotal = (float) $price * (float) $quantity;
	}

	return $productSubtotal;
}



/**
 * Get Subtotal by Order
 * @param  string    $taxDisplay
 * @param  \WC_Order $order     
 * @return float
 */
function getSubtotalByOrder( string $taxDisplay, \WC_Order $order ): float {
	$taxDisplay = $taxDisplay ? $taxDisplay : \get_option( 'woocommerce_tax_display_cart' );
	$subtotal    = (float) $order->get_subtotal();

	if ( 'incl' === $taxDisplay ) {
		$subtotalTaxes = 0;
		foreach ( $order->get_items() as $item ) {
			$subtotalTaxes += \wc_round_tax_total( (float) $item->get_subtotal_tax(), 0 );
		}
		$subtotal += \wc_round_tax_total( $subtotalTaxes );
	}

	return $subtotal;
}

function isCheckoutBlock() {
    return \WC_Blocks_Utils::has_block_in_page( \wc_get_page_id('cart'), 'woocommerce/cart' );
}

function isCartBlock() {
    return \WC_Blocks_Utils::has_block_in_page( \wc_get_page_id('checkout'), 'woocommerce/checkout' );
}


/**
 * Get WC Cart Page Content
 * @return string
 */
function getNoticeCheckoutContentDefault(): string {
	return \esc_html__( 'You are almost there for a [deal_number], add more products to the cart and get a surprise', 'wc-bogo-deals' );
}