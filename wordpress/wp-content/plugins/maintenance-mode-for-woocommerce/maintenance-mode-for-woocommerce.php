<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Maintenance mode for WooCommerce
 * Description:       Put WooCommerce in maintenance mode, without affecting other parts of a website.
 * Version:           1.2.2
 * Author:            netfett
 * Author URI:        https://netfett.de
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       maintenance-mode-for-woocommerce
 * Domain Path:       /languages
 * Tested up to: 6.8.1
 * Stable tag: 1.2.2
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 9.8.5
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

const NF_MAINTENANCE_VERSION = '1.2.2';


if ( !function_exists( 'activate_nf_maintenance' ) ) {
    function activate_nf_maintenance() {
    	require_once plugin_dir_path( __FILE__ ) . 'includes/class-nf-maintenance-activator.php';
    	Nf_Maintenance_Activator::activate();
    }
}

if ( !function_exists( 'deactivate_nf_maintenance' ) ) {
    function deactivate_nf_maintenance() {
    	require_once plugin_dir_path( __FILE__ ) . 'includes/class-nf-maintenance-deactivator.php';
    	Nf_Maintenance_Deactivator::deactivate();
    }
}

add_action( 'before_woocommerce_init', function() {
        if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        }
} );

register_activation_hook( __FILE__, 'activate_nf_maintenance' );
register_deactivation_hook( __FILE__, 'deactivate_nf_maintenance' );

require plugin_dir_path( __FILE__ ) . 'includes/class-nf-maintenance.php';

if ( !function_exists( 'run_nf_maintenance' ) ) {
    function run_nf_maintenance() {

    	$plugin = new Nf_Maintenance();
    	$plugin->run();

    }
}
run_nf_maintenance();

