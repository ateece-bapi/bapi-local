<?php
/*
Plugin Name: Call for Price for WooCommerce Pro
Plugin URI: https://www.tychesoftwares.com/store/premium-plugins/woocommerce-call-for-price-plugin/
Description: Plugin extends WooCommerce by outputting "Call for Price" when price field for product is left empty.
Version: 3.2.6
Author: Tyche Softwares
Author URI: https://www.tychesoftwares.com/
Text Domain: woocommerce-call-for-price
Domain Path: /langs
Copyright: � 2018 Tyche Softwares.
WC tested up to: 3.9
Requires PHP: 5.6
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Check if WooCommerce is active
$plugin = 'woocommerce/woocommerce.php';
if (
	! in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ) ) &&
	! ( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) )
) {
	return;
}

if ( 'woocommerce-call-for-price.php' === basename( __FILE__ ) ) {
	// Check if Pro is active, if so then return
	$plugin = 'woocommerce-call-for-price-pro/woocommerce-call-for-price-pro.php';
	if (
		in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ) ) ||
		( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) )
	) {
		return;
	}
}

if ( ! class_exists( 'Alg_Woocommerce_Call_For_Price' ) ) :

/**
 * Main Alg_Woocommerce_Call_For_Price Class
 *
 * @class   Alg_Woocommerce_Call_For_Price
 * @version 3.2.2
 */
final class Alg_Woocommerce_Call_For_Price {

	/**
	 * Plugin version.
	 *
	 * @var   string
	 * @since 3.0.0
	 */
	public $version = '3.2.6';

	/**
	 * @var Alg_Woocommerce_Call_For_Price The single instance of the class
	 */
	protected static $_instance = null;

	/**
	 * Main Alg_Woocommerce_Call_For_Price Instance
	 *
	 * Ensures only one instance of Alg_Woocommerce_Call_For_Price is loaded or can be loaded.
	 *
	 * @static
	 * @return Alg_Woocommerce_Call_For_Price - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Alg_Woocommerce_Call_For_Price Constructor.
	 *
	 * @access  public
	 * @version 3.0.0
	 */
	function __construct() {

		// Set up localisation
		load_plugin_textdomain( 'woocommerce-call-for-price', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );

		add_action( 'alg_get_plugins_list', array( $this, 'cfp_remove_plugin_name' ), PHP_INT_MAX );

		// The Filter
		add_filter( 'alg_call_for_price', array( $this, 'alg_call_for_price' ), PHP_INT_MAX, 5 );

		// Include required files
		$this->includes();

		// Settings
		if ( is_admin() ) {
			add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );

			$this->define_constants();

			if ( isset( $_GET['license'] ) ) {
				add_action( 'admin_init', array( $this, 'cfp_edd_handle_license' ) );
			}

			$this->check_for_updates();
		}
	}

	/**
	 * alg_call_for_price.
	 *
	 * @version 3.2.0
	 * @since   3.0.0
	 */
	function alg_call_for_price( $value, $type, $product_type = '', $view = '', $args = array() ) {
		switch ( $type ) {
			case 'settings':
				return '';
			case 'value':
				return ( 'per_product' != $product_type ?
					get_option( 'alg_wc_call_for_price_text' . '_' . $product_type . '_' . $view,
						'<strong>' . __( 'Call for Price', 'woocommerce-call-for-price' ) . '</strong>' ) :
					get_post_meta( $args['product_id'], '_' . 'alg_wc_call_for_price_text' . '_' . $view,
						'<strong>' . __( 'Call for Price', 'woocommerce-call-for-price' ) . '</strong>' )
				);
			case 'per_product':
				return get_option( 'alg_wc_call_for_price_per_product_enabled', 'no' );
			case 'button_text':
				return get_option( 'alg_call_for_price_button_text', __( 'Call for Price', 'woocommerce-call-for-price' ) );
			case 'out_of_stock':
				return get_option( 'alg_call_for_price_make_out_of_stock_empty_price', 'no' );
		}
	}

	/**
	 * Show action links on the plugin screen
	 *
	 * @param   mixed $links
	 * @return  array
	 * @version 3.1.1
	 */
	function action_links( $links ) {
		$custom_links = array();
		$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_call_for_price' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>';
		if ( 'woocommerce-call-for-price.php' === basename( __FILE__ ) ) {
			$custom_links[] = '<a target="_blank" href="https://www.tychesoftwares.com/store/premium-plugins/woocommerce-call-for-price-plugin/">' . __( 'Unlock All', 'woocommerce-call-for-price' ) . '</a>';
		}
		return array_merge( $custom_links, $links );
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @version 3.2.2
	 */
	private function includes() {
		$this->settings = array();
		$this->settings['general']       = require_once( 'includes/admin/class-wc-call-for-price-settings-general.php' );
		$this->settings['product-types'] = require_once( 'includes/admin/class-wc-call-for-price-settings-product-types.php' );
		if ( is_admin() && get_option( 'alg_wc_call_for_price_version', '' ) !== $this->version ) {
			foreach ( $this->settings as $section ) {
				foreach ( $section->get_settings() as $value ) {
					if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
						$autoload = isset( $value['autoload'] ) ? ( bool ) $value['autoload'] : true;
						add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
					}
				}
			}
			$this->handle_deprecated_options();
			update_option( 'alg_wc_call_for_price_version', $this->version );
		}
		$this->settings['license'] = require_once( 'includes/admin/class-wc-call-for-price-settings-license.php' );
		require_once( 'includes/class-wc-call-for-price.php' );
		require_once( 'includes/license/class-wc-call-for-price-license-page.php' );
	}

	/**
	 * handle_deprecated_options.
	 *
	 * @version 3.0.2
	 * @since   3.0.0
	 */
	function handle_deprecated_options() {
		$deprecated_settings = array(
			// v3.0.0
			'woocommerce_call_for_price_enabled'         => 'alg_wc_call_for_price_enabled',
			'woocommerce_call_for_price_text'            => 'alg_wc_call_for_price_text_simple_single',
			'woocommerce_call_for_price_text_on_archive' => 'alg_wc_call_for_price_text_simple_archive',
			'woocommerce_call_for_price_text_on_home'    => 'alg_wc_call_for_price_text_simple_home',
			'woocommerce_call_for_price_text_on_related' => 'alg_wc_call_for_price_text_simple_related',
			'woocommerce_call_for_price_hide_sale_sign'  => 'alg_wc_call_for_price_hide_sale_sign',
		);
		foreach ( $deprecated_settings as $old => $new ) {
			if ( false !== ( $old_value = get_option( $old ) ) ) {
				update_option( $new, $old_value );
				delete_option( $old );
			}
		}
	}

	/**
	 * Add Woocommerce settings tab to WooCommerce settings.
	 *
	 * @version 3.0.0
	 */
	function add_woocommerce_settings_tab( $settings ) {
		$settings[] = include( 'includes/admin/class-wc-settings-call-for-price.php' );
		return $settings;
	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	function plugin_url() {
		return untrailingslashit( plugin_dir_url( __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	private function define_constants() {
		define( 'EDD_CFP_STORE_URL', 'https://www.tychesoftwares.com/' );
		define( 'EDD_CFP_ITEM_NAME', 'Call for Price for WooCommerce' );
	}

	function cfp_edd_handle_license(){

		if ( isset( $_GET['license'] ) && $_GET['license'] === 'activate' ) {
			self::cfp_activate_license();
		}elseif ( isset( $_GET['license'] ) && $_GET['license'] === 'deactivate' ) {
			self::cfp_deactivate_license();
		}
	}

	static function cfp_activate_license() {
		// run a quick security check
		/*if ( ! check_admin_referer( 'edd_sample_nonce', 'edd_sample_nonce' ) )
			return; // get out if we didn't click the Activate button*/
		// retrieve the license from the database
		$license = trim( get_option( 'edd_license_key_call_for_price' ) );
		// data to send in our API request
		$api_params = array(
				'edd_action'=> 'activate_license',
				'license'   => $license,
				'item_name' => urlencode( EDD_CFP_ITEM_NAME ) // the name of our product in EDD
		);
		// Call the custom API.
		$response = wp_remote_get( add_query_arg( $api_params, EDD_CFP_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );
		// make sure the response came back okay
		if ( is_wp_error( $response ) )
			return false;
		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "active" or "inactive"
		update_option( 'edd_license_key_call_for_price_status', $license_data->license );
	}

	static function cfp_deactivate_license() {
		// run a quick security check
		/*if ( ! check_admin_referer( 'edd_sample_nonce', 'edd_sample_nonce' ) )
			return; // get out if we didn't click the Activate button*/
		// retrieve the license from the database
		$license = trim( get_option( 'edd_license_key_call_for_price' ) );
		// data to send in our API request
		$api_params = array(
				'edd_action'=> 'deactivate_license',
				'license'   => $license,
				'item_name' => urlencode( EDD_CFP_ITEM_NAME ) // the name of our product in EDD
		);
		// Call the custom API.
		$response = wp_remote_get( add_query_arg( $api_params, EDD_CFP_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );
		// make sure the response came back okay
		if ( is_wp_error( $response ) )
			return false;
		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		// $license_data->license will be either "deactivated" or "failed"
		if ( $license_data->license == 'deactivated' )
			delete_option( 'edd_license_key_call_for_price_status' );
	}

	private function check_for_updates() {

		if( ! class_exists( 'EDD_CFP_Plugin_Updater' ) ) {
			// load our custom updater if it doesn't already exist
			include( dirname( __FILE__ ) . '/plugin_updates/EDD_CFP_Plugin_Updater.php' );
		}
		/**
		 * Retrieve our license key from the DB
		 */ 
		$license_key = trim( get_option( 'edd_license_key_call_for_price' ) );
		/**
		 * Setup the updater
		 */
		$edd_updater = new EDD_CFP_Plugin_Updater( EDD_CFP_STORE_URL, __FILE__, array(
			'version'   => $this->version,    // current version number
			'license'   => $license_key,      // license key (used get_option above to retrieve from DB)
			'item_name' => EDD_CFP_ITEM_NAME, // name of this plugin
			'author'    => 'Ashok Rane'       // author of this plugin
			)
		);
	}

	public function cfp_remove_plugin_name(){

		$plugin_list = get_option( 'alg_wpcodefactory_helper_plugins' );

		if ( $plugin_list != '' ) {
			$plugin_list = array_diff( $plugin_list, array( 'woocommerce-call-for-price-pro' ) );
			update_option( 'alg_wpcodefactory_helper_plugins', $plugin_list );
		}
	}
}

endif;

if ( ! function_exists( 'alg_woocommerce_call_for_price' ) ) {
	/**
	 * Returns the main instance of Alg_Woocommerce_Call_For_Price to prevent the need to use globals.
	 *
	 * @return  Alg_Woocommerce_Call_For_Price
	 * @version 3.0.0
	 */
	function alg_woocommerce_call_for_price() {
		return Alg_Woocommerce_Call_For_Price::instance();
	}
}

alg_woocommerce_call_for_price();
