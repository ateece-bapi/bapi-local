<?php 
/**
 * The file that defines the post types of this plugin
 *
 * A class definition that includes attributes and functions used across both the admin area.
 *
 * @link       https://www.thedotstore.com/
 * @since      1.0.0
 *
 * @package    Advanced_Extra_Fees_Woocommerce
 * @subpackage Advanced_Extra_Fees_Woocommerce/admin
 */

defined( 'ABSPATH' ) or exit;

/**
 * Advanced Extra Fees for WooCommerce post types.
 *
 * This class is responsible for registering custom post types used by the plugin.
 *
 * @since 1.0.0
 */
#[\AllowDynamicProperties]

class DSAEFW_Post_Types {

    /**
	 * Initialize and register custom post types.
	 *
	 * @since 1.0.0
	 */
	public static function init() {

		self::register_post_types();
		self::set_user_roles_and_capabilities();

		// handle custom post type admin messages
		add_filter( 'post_updated_messages',      array( __CLASS__, 'updated_messages' ) );
		add_filter( 'bulk_post_updated_messages', array( __CLASS__, 'bulk_updated_messages' ), 10, 2 );
	}

    /**
	 * Register custom post types.
	 *
	 * @since 1.0.0
	 */
	public static function register_post_types() {

		$dsaefw_post_type_labels = array(
			'name'               => __( 'Fees', 'advanced-extra-fees-woocommerce' ),
			'singular_name'      => __( 'Fee', 'advanced-extra-fees-woocommerce' ),
			'menu_name'          => _x( 'Fees', 'Admin menu name', 'advanced-extra-fees-woocommerce' ),
			'add_new'            => __( 'Add Fee', 'advanced-extra-fees-woocommerce' ),
			'add_new_item'       => __( 'Add New Fee', 'advanced-extra-fees-woocommerce' ),
			'edit'               => __( 'Edit', 'advanced-extra-fees-woocommerce' ),
			'edit_item'          => __( 'Edit Fee', 'advanced-extra-fees-woocommerce' ),
			'new_item'           => __( 'New Fee', 'advanced-extra-fees-woocommerce' ),
			'view'               => __( 'View Fees', 'advanced-extra-fees-woocommerce' ),
			'view_item'          => __( 'View Fee', 'advanced-extra-fees-woocommerce' ),
			'search_items'       => __( 'Search Fees', 'advanced-extra-fees-woocommerce' ),
			'not_found'          => __( 'No Fee found', 'advanced-extra-fees-woocommerce' ),
			'not_found_in_trash' => __( 'No Fees found in trash', 'advanced-extra-fees-woocommerce' ),
		);

		$dsaefw_post_type_args = array(
			'labels'              => $dsaefw_post_type_labels,
			'description'         => __( 'This is where you can add new Fees.', 'advanced-extra-fees-woocommerce' ),
			'public'              => false,
			'show_ui'             => true,
			'capability_type'     => DSAEFW_FEE_POST_TYPE,
			'map_meta_cap'        => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_in_menu'        => false,
			'hierarchical'        => false,
			'rewrite'             => false,
			'query_var'           => false,
			'supports'            => array(
				'title',
			),
			'show_in_nav_menus'   => false,
		);

		register_post_type( DSAEFW_FEE_POST_TYPE, $dsaefw_post_type_args );
	}

    /**
	 * Set up custom user roles and capabilities.
	 *
	 * @since 1.0.0
	 */
	private static function set_user_roles_and_capabilities() {

		$wp_roles = wp_roles();

		// allow shop managers and admins to manage fees
		if ( is_object( $wp_roles ) ) {

			$args = new \stdClass();
			$args->map_meta_cap = true;
			$args->capability_type = DSAEFW_FEE_POST_TYPE;
			$args->capabilities = array();

			foreach ( (array) get_post_type_capabilities( $args ) as $mapped ) {

				$wp_roles->add_cap( 'shop_manager', $mapped );
				$wp_roles->add_cap( 'administrator', $mapped );
			}

			$wp_roles->add_cap( 'shop_manager',  'manage_woocommerce_wc_conditional_fees' );
			$wp_roles->add_cap( 'administrator', 'manage_woocommerce_wc_conditional_fees' );
		}
	}

    /**
	 * Customize updated messages for custom post types.
	 *
	 * @since 1.0.0
	 *
	 * @param array $messages original messages
	 * @return array $messages modified messages
	 */
	public static function updated_messages( $messages ) {

		$messages[DSAEFW_FEE_POST_TYPE] = array(
			0  => '', // Unused (Messages start at index 1).
			1  => esc_html__( 'Fee has been updated.', 'advanced-extra-fees-woocommerce' ),
			2  => esc_html__( 'Custom field updated.', 'advanced-extra-fees-woocommerce' ),
			3  => esc_html__( 'Custom field deleted.', 'advanced-extra-fees-woocommerce' ),
			4  => esc_html__( 'Fee has been saved.', 'advanced-extra-fees-woocommerce' ),
			5  => '',
			6  => esc_html__( 'Fee has been saved.', 'advanced-extra-fees-woocommerce' ),
			7  => esc_html__( 'Fee has been saved.', 'advanced-extra-fees-woocommerce' ),
			8  => '',
			9  => '',
			10 => esc_html__( 'Fee draft updated.', 'advanced-extra-fees-woocommerce' ),
		);

		return $messages;
	}

    /**
	 * Customize bulk updated messages for custom post types.
	 *
	 * @since 1.0.0
	 *
	 * @param array $messages original messages
	 * @param array $bulk_counts bulk counts
	 * @return array $messages modified messages
	 */
	public static function bulk_updated_messages( $messages, $bulk_counts ) {

		$messages[DSAEFW_FEE_POST_TYPE] = array(
			'updated'   => _n( '%s fee updated.', '%s fees updated.', $bulk_counts['updated'], 'advanced-extra-fees-woocommerce' ),
			'locked'    => _n( '%s fee not updated, somebody is editing it.', '%s fees not updated, somebody is editing them.', $bulk_counts['locked'], 'advanced-extra-fees-woocommerce' ),
			'deleted'   => _n( '%s fee permanently deleted.', '%s fees permanently deleted.', $bulk_counts['deleted'], 'advanced-extra-fees-woocommerce' ),
			'trashed'   => _n( '%s fee moved to the Trash.', '%s fees moved to the Trash.', $bulk_counts['trashed'], 'advanced-extra-fees-woocommerce' ),
			'untrashed' => _n( '%s fee restored from the Trash.', '%s fees restored from the Trash.', $bulk_counts['untrashed'], 'advanced-extra-fees-woocommerce' ),
		);

		return $messages;
	}
}