<?php
Namespace BogoDeals\Controllers;

use BogoDeals\Traits\Singleton;


class DiscountController {
	use Singleton;

	/**
	 * Discount with deals type multiple
	 * @var array
	 */
	protected array $discountToMultiple = [];

	/**
	 * Discount with deals type multiple
	 * @var array
	 */
	protected array $discountToSame = [];


	/**
	 * Set Discount to Same
	 * @param array $discountToSame
	 * @return void
	 */
	public function setDiscountToSame( array $discountToSame ): void {
		$this->discountToSame = $discountToSame;
	}

	/**
	 * Set Discount to Multiple
	 * @param array $discountToMultiple
	 * @return void
	 */
	public function setDiscountToMultiple( array $discountToMultiple ): void {
		$this->discountToMultiple = $discountToMultiple;
	}

	/**
	 * Get Discount to Same
	 * @return array
	 */
	public function getDiscountToSame(): array {
		return $this->discountToSame;
	}

	/**
	 * Get Discount to Multiple
	 * @return array
	 */
	public function getDiscountToMultiple(): array {
		return $this->discountToMultiple;
	}
}