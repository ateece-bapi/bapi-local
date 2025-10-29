<?php
use WP_CLI\Iterators\Query as QueryIterator;

class Bapi_Data_Conversion_CLI extends WP_CLI_Command {

	function testing( $args = null, $ass_args = [] ) {
		WP_CLI::success( 'in testing' );
	}

	function convert_cimy_to_acf( $args = null, $ass_args = [] ) {
		$user_args = array(
			'number' => ( isset( $ass_args['limit'] ) ) ? $ass_args['limit'] : 10,
			'offset' => ( isset( $ass_args['page'] ) ) ? ( $ass_args['page'] - 1 ) * $ass_args['limit'] : 0,
		);
		if ( isset( $ass_args['include'] ) ) {
			$user_args['include'] = explode( ',', $ass_args['include'] );
		}
		$users = new WP_User_Query( $user_args );
		if ( $users->get_results() ) {
			$progress = \WP_CLI\Utils\make_progress_bar( sprintf( 'Checking %s users: ', count( $users->get_results() ) ), count( $users->get_results() ) );
			foreach ( $users->get_results() as $user ) {
				// Delete existing address book items
				update_field( 'primary_address', array(), 'user_' . $user->ID );
				update_field( 'address_book', array(), 'user_' . $user->ID );
				$cimy_fields = get_cimyFields();
				$logger      = wc_get_logger();

				WP_CLI::warning( sprintf( 'Looking at user: %s', $user->ID ) );

				if ( ! empty( $cimy_fields ) && $user ) {
					foreach ( $cimy_fields as $old_field ) {
						$cimy_value = get_cimyFieldValue( $user->ID, $old_field['NAME'] );

						if ( strpos( $old_field['NAME'], 'SUB_ADDRESS' ) !== false ) {
							$cimy_address = array( 'address' => json_decode( $cimy_value, true ) );
							if ( ! empty( $cimy_address['address'] ) ) {
								$cimy_address['address']['country'] = $this->get_country_code( $cimy_address['address']['country'] );

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
							}
						} else {
							if ( strpos( $old_field['NAME'], 'MAIN_' ) !== false ) {
								$current_primary_address = ( get_field( 'primary_address', 'user_' . $user->ID ) ) ? get_field( 'primary_address', 'user_' . $user->ID ) : array( 'address' => array() );
								$tmp_name                = str_replace( 'main_', '', str_replace( '-', '_', strtolower( $old_field['NAME'] ) ) );
								switch ( $tmp_name ) {
									case 'address':
										$tmp_name = 'address_1';
										break;
									case 'address2':
										$tmp_name = 'address_2';
										break;
									case 'postal_code':
										$tmp_name = 'zipcode';
										break;
									case 'country':
										$cimy_value = $this->get_country_code( $cimy_value );

										break;

								}
								$current_primary_address['address'][ $tmp_name ] = $cimy_value;

								update_field( 'primary_address', $current_primary_address, 'user_' . $user->ID );
							} elseif ( $old_field['NAME'] === 'PAYMENT-TERMS' ) {
								$acf_field_name = str_replace( '-', '_', strtolower( $old_field['NAME'] ) );
								$cimy_value     = str_replace( '-', '_', sanitize_title( $cimy_value ) );
								WP_CLI::warning( sprintf( '%s | %s', $acf_field_name, $cimy_value ) );
								update_field( $acf_field_name, $cimy_value, 'user_' . $user->ID );
							} else {
								$acf_field_name = str_replace( '-', '_', strtolower( $old_field['NAME'] ) );
								// WP_CLI::warning( sprintf( 'ACF Field Name: %s | Cimy Value %s', $acf_field_name, $cimy_value ) );
								update_field( $acf_field_name, $cimy_value, 'user_' . $user->ID );
							}
						}
					}
					$logger->info( sprintf( 'Customer Info updated for User ID %s', $user->ID ), array( 'source' => $this->action ) );
				}
				$progress->tick();
			}
			$progress->finish();
		}
	}

	function get_country_code( $country ) {
		$country_code = $country;
		foreach ( WC()->countries->get_countries() as $woo_code => $woo_country ) {
			if ( $country === $woo_country ) {
				$country_code = $woo_code;
			}
		}

		if ( $country_code === 'USA' ) {
			$country_code = 'US';
		}
		return $country_code;
	}

}
