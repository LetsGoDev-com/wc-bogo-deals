<?php
Namespace BogoDeals\Controllers;

use BogoDeals\Traits\Singleton;


class ActivatorController {
	use Singleton;

	
	public function activate(): void {
		$this->initCapabilities();
	}

	
	/**
	 * Initialize capabilities to admin
	 * @return void
	 */
	public function initCapabilities(): void {
		$admin = \get_role( 'administrator' );
	
		$caps = [
			'delete_bogo_deals',
			'delete_others_bogo_deals',
			'delete_private_bogo_deals',
			'delete_published_bogo_deals',
			'edit_bogo_deals',
			'edit_others_bogo_deals',
			'edit_private_bogo_deals',
			'edit_published_bogo_deals',
			'publish_bogo_deals',
			'read_private_bogo_deals',
			'read_bogo_deals',
		];
	
		foreach ( $caps as $cap ) {
			$admin->add_cap( $cap );
		}
	}
}