<?php

Namespace BogoDeals\Core;

use LetsGoDev\Core\License;
use BogoDeals\Controllers\I18nController;
use BogoDeals\Controllers\ACFController;
use BogoDeals\Controllers\DealController;
use BogoDeals\Controllers\MetaboxController;
use BogoDeals\Controllers\NoticeController;
use BogoDeals\Controllers\DiscountController;
use BogoDeals\Controllers\CheckoutController;
use BogoDeals\Controllers\OrderController;
use BogoDeals\Controllers\HPOSController;
use BogoDeals\Overrides\UserRoleEditorPlugin;
use BogoDeals\Controllers\LinksController;

/**
 * Class BogoDeals
 * @package Bogodeals\Core
 * @since 1.0.0
 */
class BogoDeals {

	/**
	 * Instance
	 * @var BogoDeals
	 */
	public static ?BogoDeals $instance = null;


	/**
	 * Instance license
	 * @var null
	 */
	public ?License $license = null;

	/**
	 * Construct
	 */
	public function __construct() {
		$this->initControllers();
	}


	/**
	 * Init controllers
	 * @return void
	 */
	public function initControllers(): void {
		I18nController::getInstance();
		ACFController::getInstance();
		MetaboxController::getInstance();
		DealController::getInstance();
		DiscountController::getInstance();
		CheckoutController::getInstance();
		OrderController::getInstance();
		NoticeController::getInstance();
		HPOSController::getInstance();
		UserRoleEditorPlugin::getInstance();
		LinksController::getInstance();
	}


	/**
	 * Getinstance method
	 * @return mixed
	 */
	public static function getInstance(): ?BogoDeals {
		
		if ( empty( self::$instance ) ) {
			$className      = \get_called_class();
			self::$instance = new $className();
		}

		return self::$instance;

	}

}