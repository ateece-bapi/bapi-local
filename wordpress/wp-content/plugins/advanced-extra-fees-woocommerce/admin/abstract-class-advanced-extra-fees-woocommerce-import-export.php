<?php
/**
 * Advanced Extra Fees for WooCommerce - Import/Export abstract class
 * 
 * @version 1.0.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Advance Extra Fees abstract class for importing and exporting.
 *
 * @since 1.0.0
 */
#[\AllowDynamicProperties]
abstract class Advanced_Extra_Fees_Woocommerce_Import_Export {

    /**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
    }

    /**
	 * Get default CSV headers expected by import and export processes.
	 *
	 * @see \Advanced_Extra_Fees_Woocommerce_Export::get_csv_headers()
	 * @see \Advanced_Extra_Fees_Woocommerce_Import::get_csv_headers()
	 *
	 * @since 1.0.0
	 *
	 * @return array associative array
	 */
	protected function get_csv_headers() {

		// these will be filtered in the respective import/export classes:
		return array(
			'id'                                => 'id',
			'status'                            => 'status',
			'name'                              => 'name',
			'fee_type'                          => 'fee_type',
            'fees_on_cart_total'                => 'fees_on_cart_total',
            'fee_settings_product_cost'         => 'fee_settings_product_cost',
            'fee_chk_qty_price'                 => 'fee_chk_qty_price',
            'fee_per_qty'                       => 'fee_per_qty',
            'extra_product_cost'                => 'extra_product_cost',
            'is_allow_custom_weight_base'       => 'is_allow_custom_weight_base',
            'sm_custom_weight_base_cost'        => 'sm_custom_weight_base_cost',
            'sm_custom_weight_base_per_each'    => 'sm_custom_weight_base_per_each',
            'sm_custom_weight_base_over'        => 'sm_custom_weight_base_over',
            'dsaefw_tooltip_description'        => 'dsaefw_tooltip_description',
            'fee_settings_select_taxable'       => 'fee_settings_select_taxable',
            'first_order_for_user'              => 'first_order_for_user',
            'fee_settings_recurring'            => 'fee_settings_recurring',
            'fee_show_on_checkout_only'         => 'fee_show_on_checkout_only',
            'ds_select_day_of_week'             => 'ds_select_day_of_week',
            'fee_settings_start_date'           => 'fee_settings_start_date',
            'fee_settings_end_date'             => 'fee_settings_end_date',
            'ds_time_from'                      => 'ds_time_from',
            'ds_time_to'                        => 'ds_time_to',
            'product_fees_metabox'              => 'product_fees_metabox',
            'cost_rule_match'                   => 'cost_rule_match',
		);
	}


	/**
	 * Get fields delimiter for CSV import or export file.
	 *
	 * @since 1.0.0
	 *
	 * @return string tab space or comma (default)
	 */
	protected function get_fields_delimiter() {

        return ',';
	}


	/**
	 * Get the CSV enclosure.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_enclosure() {

		/**
		 * Filter the CSV enclosure.
		 *
		 * @since 1.0.0
		 *
		 * @param string $enclosure default double quote `"`
		 * @param \Advanced_Extra_Fees_Woocommerce_Export $export_instance instance of the export class
		 */
		return apply_filters( 'dsaefw_csv_export_fees_enclosure', '"', $this );  // @phpstan-ignore-line
	}


	/**
	 * Escape sensitive characters with a single quote, to prevent CSV injections.
	 *
	 * @link http://www.contextis.com/resources/blog/comma-separated-vulnerabilities/
	 *
	 * @since 1.0.0
	 *
	 * @param string|mixed $value
	 * @return string|mixed
	 */
	protected function escape_value( $value ) {

		if ( is_string( $value ) ) {

			$first_char = isset( $value[0] ) ? $value[0] : '';

			if ( '' !== $first_char && in_array( $first_char, array( '=', '+', '-', '@' ), true ) ) {
				$value = "'{$value}";
			}
		}

		return $value;
	}


	/**
	 * Unescape a string that may have been escaped with slashes, a single quote or back tick.
	 *
	 * @since 1.0.0
	 *
	 * @param string|mixed $value
	 * @return string|mixed
	 */
	protected function unescape_value( $value ) {

		$first_char = is_string( $value ) && isset( $value[0] ) ? $value[0] : '';

		if ( '' !== $first_char && in_array( $first_char, array( "'", '`', "\'" ), true ) ) {
			$value = substr( $value, 1 );
		}

		return is_string( $value ) ? trim( stripslashes( $value ) ) : $value;
	}
}