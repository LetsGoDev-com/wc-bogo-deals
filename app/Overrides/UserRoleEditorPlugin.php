<?php
Namespace BogoDeals\Overrides;

use BogoDeals\Traits\Singleton;


/**
 * Compatibility with the User Role plugin
 */
class UserRoleEditorPlugin {

	use Singleton;

	/**
	 * Construct
	 */
	public function __construct() {

		// Add Bogo Deals to group in WooCommerce
		\add_filter( 'ure_capabilities_groups_tree', [ $this, 'groupsTree' ], 1 );

		// Add Capability to BogoDeals
		\add_filter( 'ure_custom_capability_groups', [ $this, 'groupsCapability' ], 1, 2 );
	}


	/**
	 * BogoDeals in group of WC
	 * @param  array  $groups
	 * @return array
	 */
	public function groupsTree( array $groups ): array {

		$groups[ 'woocommerce_bogo_deals' ] = [
			'caption' => \esc_html__( 'Bogo Deals', 'wc-bogo-deals' ), 
			'parent'  => 'woocommerce', 
			'level'   => 3,
		];

		return $groups;
	}


	/**
	 * Group Capability
	 * @param  array  $groups
	 * @param  string $capID
	 * @return array
	 */
	public function groupsCapability( array $groups, string $capID ): array {

		if ( \strpos( $capID, 'bogo_deal' ) !== false && \in_array( 'bogo_deals', $groups ) ) {
			$groups[] = 'woocommerce';
			$groups[] = 'woocommerce_bogo_deals';
		}

		return $groups;
	}
}