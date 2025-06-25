<?php
Namespace BogoDeals\Controllers;

use function BogoDeals\getProductSubtotal;

use BogoDeals\Traits\Singleton;

class CheckoutController {
	use Singleton;

	/**
	 * Products with some deal 
	 * @var array
	 */
	public array $dealProducts = [];

	
	/**
	 * Construct
	 */
	public function __construct() {

		// Calculate discounts
		\add_action( 'woocommerce_before_calculate_totals', [ $this, 'calculateDiscount' ] );

		// Apply discounts to total cart
		\add_action( 'woocommerce_cart_calculate_fees', [ $this, 'applyDiscountMultiple' ] );
		\add_action( 'woocommerce_after_calculate_totals', [ $this, 'applyDiscountSame' ] );

		// Item cart on cart page or checkout page
		\add_filter( 'woocommerce_cart_item_subtotal', [ $this, 'itemCartSubtotal' ], 10, 2 );

		// Create line totals in Cart
		\add_action( 'woocommerce_calculate_totals', [ $this, 'setLineTotals' ] );

	}


	/**
	 * Calculate discounts
	 * @return void
	 */
	public function calculateDiscount(): void {

		if ( \has_action( 'bogodeals/calculate_discount' ) ) {
			\do_action( 'bogodeals/calculate_discount' );
			return;
		}

		$deals = DealController::getInstance()->getAllDeals();

		foreach ( $deals as $deal ) {

			$multipleProductsQty = 0;
			$multipleProductsIDs = [];

			foreach ( \WC()->cart->get_cart() as $itemCart ) {
				$product     = $itemCart['data'];
				$qtyItemCart = \absint( $itemCart['quantity'] );

				// If this product has an deal applied
				if ( \in_array( $product->get_id(), $this->dealProducts ) ) {
					continue;
				}

				// Check if the deal applies to this product
				if ( ! DealController::getInstance()->isLocation( $product->get_id(), $deal ) ) {
					continue;
				}

				// Same Type
				if ( $deal['bogo_deals_type'] === 'same' ) { 

					if ( $qtyItemCart >= 2 ) {
						$this->dealProducts[] = $product->get_id();
						$this->calculateDiscountSame( $deal, $itemCart );
						continue;
					
					}
				}

				// Multiple Type
				if ( $deal['bogo_deals_type'] === 'multiple' ) {

					// Counter multiple products
					$multipleProductsQty += $qtyItemCart;

					// IDs multiple products
					$multipleProductsIDs[] = $product->get_id();

					for ( $i=0; $i<$qtyItemCart; $i++ ) {
						$priceMultipleProducts[] = $product->get_price();
					}
				}
			}


			if ( ! empty( $priceMultipleProducts ) ) {

				if ( $multipleProductsQty >= 2 ) {
					$this->dealProducts = \array_merge( $this->dealProducts, $multipleProductsIDs );
					$this->calculateDiscountMultiple( $deal, $priceMultipleProducts );
				
				}
			}
		}
	}


	/**
	 * Calculate discount to "same" type
	 * @param  array  $deal     
	 * @param  array  $itemCart
	 * @return void
	 */
	public function calculateDiscountSame( array $deal, array $itemCart ): void {
		
		$product        = $itemCart['data'];
		$groupQty       = \floor( $itemCart['quantity'] / 2 );
		$noGroupQty     = $itemCart['quantity'] % 2;
		$realQtyToNoPay = $groupQty;
		$realQtyToPay   = $groupQty + $noGroupQty;

		$discountToSame[ $product->get_id() ] = [
			'deal_id'         => $deal['ID'],
			'deal_label'      => $deal['bogo_deals_label'],
			'subtotal_no_pay' => getProductSubtotal( $product, $realQtyToNoPay ),
			'subtotal_pay'    => getProductSubtotal( $product, $realQtyToPay ),
			'subtotal_all'    => getProductSubtotal( $product, $itemCart['quantity'] ),
		];

		DiscountController::getInstance()->setDiscountToSame( $discountToSame );
	}


	/**
	 * Calculate discount Multiple
	 * @param  array  $deal
	 * @param  array  $priceMultipleProducts
	 * @return void
	 */
	public function calculateDiscountMultiple( array $deal, array $priceMultipleProducts ): void {

		if ( $deal['bogo_deals_multiple'] === 'less' ) {
			\sort( $priceMultipleProducts, SORT_NUMERIC );
		} else {
			\rsort( $priceMultipleProducts, SORT_NUMERIC );
		}

		$groupPrice       = \floor( \count( $priceMultipleProducts ) / 2 );
		$counterGroup     = 0;
		$discountSubtotal = 0;

		foreach ( $priceMultipleProducts as $key => $price ) {

			if ( $key % $groupPrice === 0  ) {
				$counterGroup++;
				$counterDiscount = 1;
			}

			if ( $counterGroup > $groupPrice ) {
				break;
			}

			if ( $counterDiscount > 1 ) {
				continue;
			}

			$discountSubtotal += $price;

			$counterDiscount++;
		}

		if ( $discountSubtotal === 0 ) {
			return;
		}

		$discountToMultiple[] = [
			'deal_id'         => $deal['ID'],
			'deal_label'      => $deal['bogo_deals_label'],
			'subtotal_no_pay' => $discountSubtotal,
		];

		DiscountController::getInstance()->setDiscountToMultiple( $discountToMultiple );
	}


	/**
	 * Apply discount to "same" type
	 * @param  \WC_Cart $cart
	 * @return void
	 */
	public function applyDiscountSame( \WC_Cart $cart ): void {
		$discountToSame = DiscountController::getInstance()->getDiscountToSame();

		if ( empty ( $discountToSame ) ) {
			return;
		}

		$totalDiscount = \array_sum( \wp_list_pluck( $discountToSame, 'subtotal_no_pay' ) );

		$cart->set_subtotal( $cart->get_subtotal() - $totalDiscount );
		$cart->set_total( $cart->get_total('edit') - $totalDiscount );
	}


	/**
	 * Apply discount to "multiple" type
	 * @return void
	 */
	public function applyDiscountMultiple(): void {
		$discountToMultiple = DiscountController::getInstance()->getDiscountToMultiple();

		if ( empty( $discountToMultiple ) ) {
			return;
		}

		foreach ( $discountToMultiple as $key => $totalDiscount ) {
			\WC()->cart->add_fee(
				$totalDiscount['deal_label'], $totalDiscount['subtotal_no_pay'] * (-1), false
			);
		}
	}


	/**
	 * Subtotal in cart page and checkout page
	 * @param  string $subtotal
	 * @param  array  $itemCart
	 * @param  string $itemCartkey
	 * @return string
	 */
	public function itemCartSubtotal( string $subtotal, array $itemCart ): string {
		$discountToSame = DiscountController::getInstance()->getDiscountToSame();

		if ( empty( $discountToSame ) ) {
			return $subtotal;
		}

		$product = $itemCart['data'];

		if ( ! isset( $discountToSame[ $product->get_id() ] ) ) {
			return $subtotal;
		}

		
		// is cart page
		if ( \is_cart() ) {
			$subtotal = \wc_format_sale_price(
				$discountToSame[ $product->get_id() ]['subtotal_all'],
				$discountToSame[ $product->get_id() ]['subtotal_pay'],
			);
		} else {
			$subtotal = \wc_price( $discountToSame[ $product->get_id() ]['subtotal_pay'] );
		}

		$subtotalLabel = \sprintf(
			'%s <small>(%s)</small>', $subtotal, $discountToSame[ $product->get_id() ]['deal_label']
		);

		return \apply_filters( 'bogodeals/checkout/item_subtotal', $subtotalLabel, $itemCart, $this );
	}


	/**
	 * Create LineTotals in cat contents. This information is passed to the order.
	 * @param \WC_Cart $cart
	 */
	public function setLineTotals( \WC_Cart $cart ): void {

		$discountToSame = DiscountController::getInstance()->getDiscountToSame();

		foreach( $cart->get_cart() as $itemCartKey => $itemCart ) {
			$product = $itemCart['data'];

			if ( ! isset( $discountToSame[ $product->get_id() ] ) ) {
				continue;
			}
				
			$cart->cart_contents[ $itemCartKey ]['line_total'] = $discountToSame[ $product->get_id() ]['subtotal_pay'];
		}
	}

}