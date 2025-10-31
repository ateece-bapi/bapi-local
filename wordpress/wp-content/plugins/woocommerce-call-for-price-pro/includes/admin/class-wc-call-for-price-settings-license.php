<?php
/**
 * WooCommerce Call for Price - License Settings Section
 *
 * @version 3.2.4
 * @since   3.2.4
 * @author  Tyche Softwares
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Call_For_Price_Settings_License' ) ) :

class Alg_WC_Call_For_Price_Settings_License {

	/**
	 * Constructor.
	 *
	 * @version 3.2.4
	 * @since   3.2.4
	 */
	function __construct() {
		$this->id   = 'license';
		$this->desc = __( 'License', 'woocommerce-call-for-price' );

		add_filter( 'woocommerce_get_sections_alg_call_for_price',              array( $this, 'settings_section' ) );
		add_filter( 'woocommerce_get_settings_alg_call_for_price_' . $this->id, array( $this, 'get_settings' ), PHP_INT_MAX );
	}

	/**
	 * settings_section.
	 *
	 * @version 3.2.4
	 * @since   3.2.4
	 */
	function settings_section( $sections ) {
		$sections[ $this->id ] = $this->desc;
		return $sections;
	}

	/**
	 * get_settings.
	 *
	 * @version 3.2.4
	 * @since   3.2.4
	 */
	function get_settings() {

		$license = get_option( 'edd_license_key_call_for_price' );
		$status  = get_option( 'edd_license_key_call_for_price_status' );
		$current_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

		$link = '';
		if ( false !== $license ) {
			if( $status !== false && $status == 'valid' ) {
				$link = '<span style="color:green;">active</span>' .
				wp_nonce_field( 'edd_sample_nonce' , 'edd_sample_nonce' ) . 
				'<a href="' . $current_link . '&license=deactivate" class="button-secondary" name="edd_cfp_license_deactivate">Deactivate<a/>';
			} else {
				$link = wp_nonce_field( 'edd_sample_nonce', 'edd_sample_nonce' ) . 
				'<a href="' . $current_link . '&license=activate" class="button-secondary" name="edd_cfp_license_activate">Activate<a/>';
			}
		}

		$license_settings = array(
			array(
				'title'     => __( 'Plugin License Options', 'woocommerce-call-for-price' ),
				'type'      => 'title',
				'id'        => 'alg_wc_call_for_price_license_options',
			),
			array(
				'title'     => __( 'License Key	', 'woocommerce-call-for-price' ),
				'desc'      => __( 'Enter your license key.', 'woocommerce-call-for-price' ),
				'id'        => 'edd_license_key_call_for_price',
				'default'   => '',
				'type'      => 'text',
			),
			array(
				'title'     => __( 'Activate License', 'woocommerce-call-for-price' ),
				'desc'      => __( $link, 'woocommerce-call-for-price' ),
				'id'        => 'edd_license_hidden_button',
				'default'   => '',
				'type'      => 'text',
				'css'       => 'display:none;',
			),
			array(
				'type'      => 'sectionend',
				'id'        => 'alg_wc_call_for_price_general_options',
			),
		);

		return $license_settings;
	}

}

endif;

return new Alg_WC_Call_For_Price_Settings_License();
