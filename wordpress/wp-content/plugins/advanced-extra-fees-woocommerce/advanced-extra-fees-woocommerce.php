<?php
/**
 * Plugin Name:             Advanced Extra Fees for WooCommerce
 * Plugin URI:              https://www.thedotstore.com/woocommerce-extra-fees-plugin
 * Description:             Provides options to add additional fees based on cart contents, user roles, or specific products.
 * Version:                 1.0.3
 * Author:                  theDotstore
 * Author URI:              https://www.thedotstore.com/
 * Developer:               Sagar Jariwala
 * Developer URI:           https://www.thedotstore.com/author/mdsagarjariwala/
 * Text Domain:             advanced-extra-fees-woocommerce
 * Domain Path:             /languages
 * 
 * Woo: 18734004262834:e8112827e12266653f23025f69784330
 * Requires PHP:            7.4
 * Requires at least:       6.0
 * tested up to:            6.8.3
 * WC requires at least:    9.0.0
 * WC tested up to:         10.2.2
 * Requires Plugins:        woocommerce
 * 
 * License:                 GPL-2.0+
 * License URI:             http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
if( ! defined( 'DSAEFW_PLUGIN_VERSION' ) ) {
    define( 'DSAEFW_PLUGIN_VERSION', '1.0.3' );
}

/**
 * Minimum PHP version required
 */
if( ! defined( 'MINIMUM_PHP_VERSION' ) ) {
    define( 'MINIMUM_PHP_VERSION', '7.4' );
}

/**
 * Minimum WordPress version required
 */
if( ! defined( 'MINIMUM_WP_VERSION' ) ) {
    define( 'MINIMUM_WP_VERSION', '6.0' );
}

/**
 * Minimum WooCommerce version required
 */
if( ! defined( 'MINIMUM_WC_VERSION' ) ) {
    define( 'MINIMUM_WC_VERSION', '9.0.0' );
}

/**
 * Define the plugin's name if not already defined.
 */
if ( ! defined( 'DSAEFW_PLUGIN_NAME' ) ) {
    define( 'DSAEFW_PLUGIN_NAME', 'Advanced Extra Fees for WooCommerce' );
}

/**
 * Define the post type name for listing rule use.
 */
if ( ! defined( 'DSAEFW_DOC_LINK' ) ) {
    define( 'DSAEFW_DOC_LINK', 'https://docs.thedotstore.com/article/922-comming-soon' );
}

/**
 * Define the post type name for listing rule use.
 */
if ( ! defined( 'DSAEFW__DEV_MODE' ) ) {
    define( 'DSAEFW__DEV_MODE', true );
}

/**
 * Retrieve the basename of the main plugin file. 
 * This ensures that the constant always holds the accurate basename, even if the plugin file is renamed or moved.
 */
if ( !defined( 'DSAEFW_PLUGIN_BASENAME' ) ) {
    define( 'DSAEFW_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

/**
 * Define the post type name for fee listing rule use.
 */
if ( !defined( 'DSAEFW_FEE_POST_TYPE' ) ) {
    define( 'DSAEFW_FEE_POST_TYPE', 'wc_conditional_fee' );
}

/**
 * Define the post type name for fee listing rule use.
 */
if ( !defined( 'DSAEFW_TOOLTIP_LENGTH' ) ) {
    define( 'DSAEFW_TOOLTIP_LENGTH', 50 );
}

/**
 * The function is used to dynamically generate the base path of the directory containing the main plugin file.
 */
if ( ! defined( 'DSAEFW_PLUGIN_BASE_DIR' ) ) {
    define( 'DSAEFW_PLUGIN_BASE_DIR', plugin_dir_path( __FILE__ ) );
}

/**
 * Activates the Advanced Extra Fees for WooCommerce plugin.
 * 
 * This code is responsible for activating the plugin and checking the compatibility of the environment, including the PHP version, WordPress version, and WooCommerce version.
 * 
 * @since 1.0.0
 * @package Advanced_Extra_Fees_WooCommerce
 */
require plugin_dir_path( __FILE__ ) . 'advanced-extra-fees-woocommerce-security-checks.php';
