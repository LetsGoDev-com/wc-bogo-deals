<?php
Namespace BogoDeals\Controllers;

use BogoDeals\Traits\Singleton;


class DealController {
	use Singleton;


	/**
	 * Get Deal By Product
	 * @param  int    $productID
	 * @param  string $page
	 * @return array
	 */
	public function getDealsByProduct( int $productID, string $page = 'product' ): array {

		$keyByProduct   = \sprintf( 'bogo_deals_by_product_%d', $productID );
		$dealsByProduct = \wp_cache_get( $keyByProduct, 'bogo_deals' );

		if ( ! empty( $dealsByProduct ) ) {
			return $dealsByProduct;
		}

		// Initialize deals
		$dealsByProduct = [];

		// Get all bogo deals
		foreach ( $this->getAllDeals() as $bogoDeal ) {

			if ( ! $this->isLocation( $productID, $bogoDeal ) ) {
				continue;
			}

			if ( ! $this->hasNotices( $page, $bogoDeal ) ) {
				continue;
			}

			$dealsByProduct[] = $bogoDeal;
		}

		\wp_cache_set( $keyByProduct, $dealsByProduct, 'bogo_deals' );

		return $dealsByProduct;
	}



	/**
	 * Get All Bogo Deals By ID
	 * @return array
	 */
	public function getAllDeals(): array {

		$allDeals = \wp_cache_get( 'bogo_deals_all' ) ?: [];

		if ( ! empty( $allDeals ) ) {
			return $allDeals;
		}

		global $wpdb;

		$bogoPostType   = 'bogo_deals';
		$bogoPostStatus = 'publish';

		$bogoDealIDs = $wpdb->get_col( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			'SELECT ID FROM '.$wpdb->posts.' WHERE post_type=%s AND post_status=%s',
			$bogoPostType,
			$bogoPostStatus
		) );

		foreach ( $bogoDealIDs as $bogoDealID ) {
			$allDeals[ $bogoDealID ]          = \get_fields( $bogoDealID );
			$allDeals[ $bogoDealID ]['ID']    = $bogoDealID;
			$allDeals[ $bogoDealID ]['title'] = \get_the_title( $bogoDealID );

			// If is no enable
			if ( empty( $allDeals[ $bogoDealID ]['bogo_deals_enable'] ) ) {
				unset( $allDeals[ $bogoDealID ] );
			}
		}

		// Order by priority field
		\uasort( $allDeals, function( $a, $b ) {
			return $b['bogo_deals_priority'] - $a['bogo_deals_priority'];
		} );

		\wp_cache_set( 'bogo_deals_all', $allDeals );

		return $allDeals;
	}


	/**
	 * Has Notices
	 * @param  string $page
	 * @param  array  $fields
	 * @return boolean
	 */
	public function hasNotices( string $page, array $fields ): bool {
		
		$key = \sprintf( 'bogo_deals_notices_%s', $page );
		
		if ( ! empty( $fields[ $key ] ) ) {
			return true;
		}

		return false;
	}


	/**
	 * Check if the productt has a deal
	 * @param  int     $productID
	 * @param  array   $fields
	 * @return boolean
	 */
	public function isLocation( int $productID, array $fields ) : bool {

		// is user logged
		if ( ! empty( $fields['bogo_deals_ulogged'] ) ) {

			if ( ! \is_user_logged_in() ) {
				return false;
			}


			// has user role
			if ( ! empty( $fields['bogo_deals_urole'] ) ) {
				$currentUser = \wp_get_current_user();
				$iRoles      = \array_intersect( $currentUser->roles, $fields['bogo_deals_urole'] );

				return ! empty( $iRoles );
			}
		}


		// If has categories
		if ( ! empty( $fields['bogo_deals_categories'] ) ) {
			$productCategories = \wc_get_product_cat_ids( $productID ) ?: [];

			$matchCategories = \array_intersect(
				$fields['bogo_deals_categories'], $productCategories
			);

			if ( empty( $matchCategories ) ) {
				return false;
			}
		}

		return \apply_filters( 'bogodeals/is_location', true, $productID, $fields );
	}
}