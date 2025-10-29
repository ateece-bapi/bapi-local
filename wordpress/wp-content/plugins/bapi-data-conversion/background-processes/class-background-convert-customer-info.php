<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Async_Request', false ) ) {
	include_once plugin_dir_path( WC_PLUGIN_FILE ) . 'includes/libraries/wp-async-request.php';
}

if ( ! class_exists( 'WP_Background_Process', false ) ) {
	include_once plugin_dir_path( WC_PLUGIN_FILE ) . 'includes/libraries/wp-background-process.php';
}

class BAPI_Background_Convert_Customer_Info extends WP_Background_Process {

	protected $action = 'bapi_convert_customer_info';

	protected function task( $item ) {
		$cimy_fields = get_cimyFields();
		$user        = $item['user'];
		$logger      = wc_get_logger();

		if ( ! empty( $cimy_fields ) && $user ) {
			foreach ( $cimy_fields as $old_field ) {
				$cimy_value = get_cimyFieldValue( $user->ID, $old_field['NAME'] );

				if ( strpos( $old_field['NAME'], 'SUB_ADDRESS' ) !== false ) {
					$cimy_address = array( 'address' => json_decode( $cimy_value, true ) );

					$already_have_address = false;
					if ( have_rows( 'address_book', 'user_' . $user->ID ) ) {

						while ( have_rows( 'address_book', 'user_' . $user->ID ) ) {
							the_row();
							$tmp_address = get_sub_field( 'address' );
							if ( $tmp_address['address_1'] == $cimy_address['address']['address_1'] && $tmp_address['zipcode'] == $cimy_address['address']['zipcode'] && $tmp_address['company_name'] == $cimy_address['address']['company_name'] ) {
								$already_have_address = true;
							}
						}
					}

					if ( ! $already_have_address && ! empty( $cimy_address['address'] ) ) {
						$i = add_row( 'address_book', $cimy_address, 'user_' . $user->ID );
					}
				} else {
					if ( strpos( $old_field['NAME'], 'MAIN_' ) !== false ) {

						$tmp_name = str_replace( 'main_', '', str_replace( '-', '_', strtolower( $old_field['NAME'] ) ) );
						switch ( $tmp_name ) {
							case 'address':
								$acf_field_name = 'address_address_1';
								break;
							case 'address2':
								$acf_field_name = 'address_address_2';
								break;
							case 'postal_code':
								$acf_field_name = 'address_zipcode';
								break;
							default:
								$acf_field_name = 'address_' . $tmp_name;
								break;
						}
					} else {
						$acf_field_name = str_replace( '-', '_', strtolower( $old_field['NAME'] ) );

					}
					update_field( $acf_field_name, $cimy_value, 'user_' . $user->ID );

				}
			}
			$logger->info( sprintf( 'Customer Info updated for User ID %s', $user->ID ), array( 'source' => $this->action ) );
		}

		return false;
	}

	protected function complete() {

		parent::complete();

		WC_Admin_Notices::add_custom_notice(
			$this->action . '_complete',
			sprintf(
				__( 'The process that converts all user information has finished. <a href="%1$s">Click here to view the import log.</a>', 'woocommerce' ),
				admin_url( 'admin.php?page=wc-status&tab=logs&source=' . $this->action )
			)
		);
	}
}
