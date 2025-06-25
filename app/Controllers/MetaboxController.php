<?php
Namespace BogoDeals\Controllers;

use BogoDeals\Traits\Singleton;

use function BogoDeals\getProductCategories;
use function BogoDeals\getNoticeCheckoutContentDefault;

class MetaboxController {

	use Singleton;

	/**
	 * Construct
	 */
	public function __construct() {

		// Register CPT
		\add_action( 'init', [ $this, 'registerCPT' ] );

		// Register new fields
		\add_action( 'wp_loaded', [ $this, 'registerFields' ] );

		// Column Name
		\add_filter( 'manage_bogo_deals_posts_columns', [ $this, 'columnName' ] );

		// Column Value
		\add_action( 'manage_bogo_deals_posts_custom_column', [ $this, 'columnValue' ], 10, 2);

		// List Admin
		\add_action( 'admin_enqueue_scripts', [ $this, 'enqueueScriptsAdminList' ] );

		// Editor Admin
		\add_action( 'acf/input/admin_enqueue_scripts', [ $this, 'enqueueScriptsAdminEditor' ] );
	}


	/**
	 * Enqueue Script in Admin List
	 * @return void
	 */
	public function enqueueScriptsAdminList(): void {
		global $pagenow, $post_type, $wp_version;

		if ( $post_type !== 'bogo_deals' ) {
			return;
		}

		if ( $pagenow === 'edit.php' ) {
			\wp_enqueue_style(
				'wc-bogo-deals-list',
				BOGO_DEALS_URL . 'resources/assets/styles/list-admin.css',
				[],
				$wp_version
			);
		}

		if ( $pagenow === 'post-new.php' || $pagenow === 'post.php' ) {
			\wp_enqueue_style(
				'wc-bogo-deals-post',
				BOGO_DEALS_URL . 'resources/assets/styles/post-admin.css',
				[],
				$wp_version
			);
		}
	}


	/**
	 * Enqueue Script in Admin Editor
	 * @return void
	 */
	public function enqueueScriptsAdminEditor(): void {
		global $pagenow, $post_type, $wp_version;

		if( ! \in_array( $pagenow, [ 'post.php', 'post-new.php' ] ) || $post_type !== 'bogo_deals' ) {
			return;
		}

		\wp_enqueue_script(
			'wc-bogo-deals-editor',
			BOGO_DEALS_URL . 'resources/assets/scripts/acf-editor.js',
			false,
			$wp_version,
			[ 'in_footer' => true ]
		);
	}



	/**
	 * Register CPT
	 * @return void
	 */
	public function registerCPT(): void {

		$labels = [
			'name'               => \esc_html__( 'Bogo Deals', 'wc-bogo-deals' ),
			'menu_name'          => \esc_html__( 'Bogo Deals', 'wc-bogo-deals' ),
			'name_admin_bar'     => \esc_html__( 'Bogo Deals', 'wc-bogo-deals' ),
			'all_items'          => \esc_html__( 'Bogo Deals', 'wc-bogo-deals'),
			'singular_name'      => \esc_html__( 'Bogo Deals', 'wc-bogo-deals' ),
			'add_new'            => \esc_html__( 'Add New Bogo Deal', 'wc-bogo-deals' ),
			'add_new_item'       => \esc_html__( 'Add New Bogo Deal','wc-bogo-deals' ),
			'edit_item'          => \esc_html__( 'Edit Bogo Deal', 'wc-bogo-deals' ),
			'new_item'           => \esc_html__( 'New Bogo Deal', 'wc-bogo-deals' ),
			'view_item'          => \esc_html__( 'View Bogo Deal', 'wc-bogo-deals' ),
			'search_items'       => \esc_html__( 'Search Bogo Deals', 'wc-bogo-deals' ),
			'not_found'          => \esc_html__( 'Nothing found', 'wc-bogo-deals' ),
			'not_found_in_trash' => \esc_html__( 'Nothing found in Trash', 'wc-bogo-deals' ),
			'parent_item_colon'  => ''	
		];
		 
		$args = \apply_filters( 'bogodeals/cpt/args', [
			'labels'				=> $labels,
			'public'				=> false,
			'show_in_menu'			=> \current_user_can( 'edit_others_bogo_deals' ) ? 'woocommerce' : true,
			'publicly_queryable'	=> false,
			'show_ui'				=> true,
			'query_var'				=> false,
			'rewrite'				=> false,
			'hierarchical'			=> false,
			'supports'				=> [ 'title' ],
			'exclude_from_search'	=> true,
			'show_in_nav_menus'		=> false,
			'map_meta_cap'			=> true,
			'has_archive'           => false,
			'capability_type'		=> [ 'bogo_deal', 'bogo_deals' ],
			/*'capabilities'			=> [
				'edit_post'              => 'edit_bogo_deal',
				'read_post'              => 'read_bogo_deal',
				'delete_post'            => 'delete_bogo_deal',

				'edit_posts'			 => 'edit_bogo_deals',
				'edit_others_posts'		 => 'edit_others_bogo_deals',
				'publish_posts'			 => 'publish_bogo_deals',
				'read_private_posts'	 => 'read_private_bogo_deals',

				'read'                   => 'read_bogo_deals',
				'delete_posts'           => 'delete_bogo_deals',
				'delete_private_posts'   => 'delete_private_bogo_deals',
				'delete_published_posts' => 'delete_published_bogo_deals',
				'delete_others_posts'    => 'delete_others_bogo_deals',
				'edit_private_posts'     => 'edit_private_bogo_deals',
				'edit_published_posts'   => 'edit_published_bogo_deals',
				'create_posts'           => 'edit_bogo_deals',
			]*/
		] );

		// Charges CPT
		\register_post_type( 'bogo_deals',  $args );
	}


	/**
	 * Register Fields
	 * @return void
	 */
	public function registerFields(): void {

		if ( \has_action( 'bogodeals/register_fields' ) ) {
			\do_action( 'bogodeals/register_fields' );
			return;
		}

		// phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage

		\acf_add_local_field_group( [
			'key'    => 'bogo_deals_conditions',
			'title'  => \esc_html__( 'Deal Conditions', 'wc-bogo-deals' ),
			'fields' => [],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'bogo_deals',
					],
				],
			],
			'position' => 'normal',
		]);

		\acf_add_local_field( [
			'parent' => 'bogo_deals_conditions',
			'key'    => 'bogo_deals_enable',
			'name'   => 'bogo_deals_enable',
			'label'  => \esc_html__( 'Enable Bogo', 'wc-bogo-deals' ),
			'type'   => 'true_false',
			'ui'     => true,
		]);

		\acf_add_local_field( [
			'parent' => 'bogo_deals_conditions',
			'key'    => 'bogo_deals_number',
			'name'   => 'bogo_deals_number',
			'label'  => \esc_html__( 'Deal', 'wc-bogo-deals' ),
			'type'   => 'button_group',
			'choices' => [
				'2x1'     => '2x1',
				'3x2'     => '3x2',
				'4x3'     => '4x3',
				'5x4'     => '5x4',
				'custom'  => \esc_html__( 'Custom', 'wc-bogo-deals' ),
			],
			'wrapper' => [
				'width' => '40',
			],
		]);


		\acf_add_local_field( [
			'parent'        => 'bogo_deals_conditions',
			'key'           => 'bogo_deals_number_custom',
			'name'          => 'bogo_deals_number_custom',
			'label'         => \esc_html__( 'Deal Custom', 'wc-bogo-deals' ),
			'type'          => 'text',
			'placeholder'   => \esc_html__( 'Example: 2x1', 'wc-bogo-deals' ),
			'wrapper'       => [
				'width' => '60',
			],
			'instructions' => \esc_html__( 'Custom deal : Pro version required', 'wc-bogo-deals' ),
		]);


		\acf_add_local_field( [
			'parent' => 'bogo_deals_conditions',
			'key'    => 'bogo_deals_type',
			'name'   => 'bogo_deals_type',
			'label'  => \esc_html__( 'Apply discount to...', 'wc-bogo-deals' ),
			'type'   => 'radio',
			'choices' => [
				'same'     => \esc_html__( 'the same product', 'wc-bogo-deals' ),
				'multiple' => \esc_html__( 'multiple products', 'wc-bogo-deals' ),
			],
			'wrapper' => [
				'width' => '40',
			],
		]);


		\acf_add_local_field( [
			'parent' => 'bogo_deals_conditions',
			'key'    => 'bogo_deals_multiple',
			'name'   => 'bogo_deals_multiple',
			'label'  => '',
			'type'   => 'radio',
			'choices' => [
				'most' => \esc_html__( 'Give most expensive products for free', 'wc-bogo-deals' ),
				'less' => \esc_html__( 'Give less expensive products for free', 'wc-bogo-deals' ),
			],
			'wrapper' => [
				'width' => '60',
			],
		]);


		\acf_add_local_field( [
			'parent' => 'bogo_deals_conditions',
			'key'    => 'bogo_deals_ulogged',
			'name'   => 'bogo_deals_ulogged',
			'label'  => \esc_html__( 'Apply only to user logged', 'wc-bogo-deals' ),
			'type'   => 'true_false',
			'ui'     => true,
			'wrapper' => [
				'width' => '40',
			],
		]);

		global $wp_roles;

		\acf_add_local_field( [
			'parent'   => 'bogo_deals_conditions',
			'key'      => 'bogo_deals_urole',
			'name'     => 'bogo_deals_urole',
			'label'    => \esc_html__( 'Choose user roles ( empty for all roles )', 'wc-bogo-deals' ),
			'type'     => 'select',
			'choices'  => $wp_roles->get_names(),
			'multiple' => true,
			'ui'       => true,
			'wrapper'  => [
				'width' => '60',
			],
		]);


		\acf_add_local_field( [
			'parent'  => 'bogo_deals_conditions',
			'key'     => 'bogo_deals_link',
			'name'    => 'bogo_deals_link',
			'label'   => \esc_html__( 'Apply the deal only with a link', 'wc-bogo-deals' ),
			'message' => \sprintf(
					'<p class="description">%s</p><img src="%s" alt="" />',
					\esc_html__( 'Pro version required', 'wc-bogo-deals' ),
					BOGO_DEALS_URL . 'resources/assets/images/true_false.jpg'
				),
			'type'    => 'message',
			'wrapper' => [
				'width' => '40'
			],
		]);

		$uniqid = \get_field( 'bogo_deals_uniqid' ) ?: \md5( \uniqid() );

		\acf_add_local_field( [
			'parent'        => 'bogo_deals_conditions',
			'key'           => 'bogo_deals_link_uniqid',
			'name'          => 'bogo_deals_link_uniqid',
			'type'          => 'text',
			'readonly'      => true,
			'instructions'  => \esc_html__( 'Share this link with users who must access this deal', 'wc-bogo-deals' ),
			'default_value' => \site_url( \sprintf( '?bogo_deals=%s', $uniqid ) ),
			'wrapper'       => [
				'width' => '60',
			],
		]);


		\acf_add_local_field( [
			'parent'        => 'bogo_deals_conditions',
			'key'           => 'bogo_deals_label',
			'name'          => 'bogo_deals_label',
			'label'         => \esc_html__( 'Deal Message', 'wc-bogo-deals' ),
			'instructions'  => \esc_html__( 'This message will appear on the checkout page', 'wc-bogo-deals' ),
			'type'          => 'text',
			'default_value' => \esc_html__( 'Deal 2x1', 'wc-bogo-deals' ),
			'wrapper' => [
				'width' => '80',
			],
		]);


		\acf_add_local_field( [
			'parent'        => 'bogo_deals_conditions',
			'key'           => 'bogo_deals_priority',
			'name'          => 'bogo_deals_priority',
			'label'         => \esc_html__( 'Deal Priority', 'wc-bogo-deals' ),
			'instructions'  => \esc_html__( '1 is less', 'wc-bogo-deals' ),
			'type'          => 'number',
			'step'          => 1,
			'default_value' => 1,
			'wrapper' => [
				'width' => '20',
			],
		]);


		\acf_add_local_field_group( [
			'key'    => 'bogo_deals_location',
			'title'  => \esc_html__( 'Deal location', 'wc-bogo-deals' ),
			'fields' => [],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'bogo_deals',
					],
				],
			],
			'position' => 'normal',
		]);


		\acf_add_local_field( [
			'parent'       => 'bogo_deals_location',
			'key'          => 'bogo_deals_categories',
			'name'         => 'bogo_deals_categories',
			'label'        => \esc_html__( 'Product Categories', 'wc-bogo-deals' ),
			'instructions' => \esc_html__( 'Select the categories where to apply the deal', 'wc-bogo-deals' ),
			'type'         => 'select',
			'choices'      => getProductCategories(),
			'multiple'     => true,
			'ui'           => true,
		]);

		
		\acf_add_local_field( [
			'parent'       => 'bogo_deals_location',
			'key'          => 'bogo_deals_attributes',
			'name'         => 'bogo_deals_attributes',
			'label'        => \esc_html__( 'Product Attributes', 'wc-bogo-deals' ),
			'instructions' => \esc_html__( 'Custom deal : Pro version required', 'wc-bogo-deals' ),
			'type'         => 'select',
			'choices'      => [],
			'multiple'     => true,
			'ui'           => true,
		]);


		\acf_add_local_field( [
			'parent'       => 'bogo_deals_location',
			'key'          => 'bogo_deals_iproducts',
			'name'         => 'bogo_deals_iproducts',
			'label'        => \esc_html__( 'Include Products', 'wc-bogo-deals' ),
			'instructions' => \esc_html__( 'Custom deal : Pro version required', 'wc-bogo-deals' ),
			'type'         => 'select',
			'choices'      => [],
			'multiple'     => true,
			'ui'           => true,
			'ajax'         => true,
		]);

		\acf_add_local_field( [
			'parent'       => 'bogo_deals_location',
			'key'          => 'bogo_deals_eproducts',
			'name'         => 'bogo_deals_eproducts',
			'label'        => \esc_html__( 'Exclude Products', 'wc-bogo-deals' ),
			'instructions' => \esc_html__( 'Custom deal : Pro version required', 'wc-bogo-deals' ),
			'type'         => 'select',
			'choices'      => [],
			'multiple'     => true,
			'ui'           => true,
			'ajax'         => true,
		]);



		\acf_add_local_field_group( [
			'key'    => 'bogo_deals_notices',
			'title'  => \esc_html__( 'Deal notices', 'wc-bogo-deals' ),
			'fields' => [],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'bogo_deals',
					],
				],
			],
			'position' => 'normal',
		]);


		\acf_add_local_field( [
			'parent' => 'bogo_deals_notices',
			'key'    => 'bogo_deals_notices_product',
			'name'   => 'bogo_deals_notices_product',
			'label'  => \sprintf(
				'<img src="%s" alt="" class="responsive" /><br />%s',
				BOGO_DEALS_URL . 'resources/assets/images/bogo_product.jpg',
				\esc_html__( 'Notice to product page', 'wc-bogo-deals' ),
			),
			'type'   => 'true_false',
			'ui'     => true,
			'instructions' => \esc_html__( 'Enable notice if the product has a deal in the product page (OnSale format)', 'wc-bogo-deals' ),
			'wrapper' => [
				'width' => '50',
			],
		]);

		\acf_add_local_field( [
			'parent' => 'bogo_deals_notices',
			'key'    => 'bogo_deals_notices_shop',
			'name'   => 'bogo_deals_notices_shop',
			'label'  => \sprintf(
				'<img src="%s" alt="" class="responsive" /><br />%s',
				BOGO_DEALS_URL . 'resources/assets/images/bogo_shop.jpg',
				\esc_html__( 'Notice to shop page', 'wc-bogo-deals' ),
			),
			'type'   => 'true_false',
			'ui'     => true,
			'instructions' => \esc_html__( 'Enable notice if there is a product with a deal in the shop page (OnSale format)', 'wc-bogo-deals' ),
			'wrapper' => [
				'width' => '50',
			],
		]);


		\acf_add_local_field( [
			'parent'  => 'bogo_deals_notices',
			'key'     => 'bogo_deals_notices_checkout',
			'name'    => 'bogo_deals_notices_checkout',
			'label'   => \sprintf(
				'<img src="%s" alt="" class="responsive" /><br />%s',
				BOGO_DEALS_URL . 'resources/assets/images/bogo_checkout.jpg',
				\esc_html__( 'Notice to checkout page', 'wc-bogo-deals' )
			),
			'message' => \sprintf(
					'<p class="description">%s</p><img src="%s" alt="" />',
					\esc_html__( 'Pro version required', 'wc-bogo-deals' ),
					BOGO_DEALS_URL . 'resources/assets/images/true_false.jpg'
				),
			'type'    => 'message',
			'wrapper' => [
				'width' => '50'
			],
		]);
		
		\acf_add_local_field( [
			'parent'        => 'bogo_deals_notices',
			'key'           => 'bogo_deals_notices_checkout_message',
			'name'          => 'bogo_deals_notices_checkout_message',
			'label'         => \esc_html__( 'Notice Message to checkout page', 'wc-bogo-deals' ),
			'type'          => 'textarea',
			'wrapper'       => [
				'width' => '50',
			],
			'default_value' => getNoticeCheckoutContentDefault(),
			'instructions' => \esc_html__( 'Pro version required', 'wc-bogo-deals' ),
		]);
	}


	/**
	 * Table List column
	 * @param  array  $cols
	 * @return array
	 */
	public function columnName( array $cols ): array {

		$newCols = [];

		unset( $cols['date'] );

		foreach ( $cols as $colKey => $colValue ) {	
			$newCols[ $colKey ] = $colValue;

			if ( $colKey === 'title' ) {
				$newCols[ 'status' ]    = \esc_html__( 'Status', 'wc-bogo-deals' );
				$newCols[ 'number' ]    = \esc_html__( 'Deal', 'wc-bogo-deals' );
				$newCols[ 'type' ]      = \esc_html__( 'Type', 'wc-bogo-deals' );
				$newCols[ 'priority' ]  = \esc_html__( 'Priority', 'wc-bogo-deals' );
			}
		}

		return $newCols;
	}


	/**
	 * Table list values
	 * @param  string $colKey
	 * @param  int    $postID
	 * @return void
	 */
	public function columnValue( string $colKey, int $postID ): void {

		switch( $colKey ) {
			case 'status' :
				$statusKey   = \get_field( 'bogo_deals_enable', $postID ) ? 'enable' : 'disable';
				$statusLabel = \get_field( 'bogo_deals_enable', $postID ) ? \esc_html__( 'Enable', 'wc-bogo-deals' ) : \esc_html__( 'Disable', 'wc-bogo-deals' );

				\printf( '<mark class="badge badge-%s"><span>%s</span></mark>', \esc_attr( $statusKey ), \esc_html( $statusLabel ) );
				break;
			case 'number' :
				echo \esc_html( \apply_filters(
					'bogodeals/column/number',
					\get_field( 'bogo_deals_number', $postID ),
					$postID
				) );
				break;
			case 'type' :
				echo \esc_html( \get_field( 'bogo_deals_type', $postID ) );
				break;
			case 'priority' :
				echo \esc_html( \get_field( 'bogo_deals_priority', $postID ) );
				break;
		}
	}
}