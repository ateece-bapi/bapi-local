<?php
/**
 * Advanced Extra Fees for WooCommerce - Import class
 * 
 * @version 1.0.0
 */

defined( 'ABSPATH' ) or exit;

if ( class_exists( 'Advanced_Extra_Fees_Woocommerce_Import', false ) ) {
	return new Advanced_Extra_Fees_Woocommerce_Import();
}

/**
 * Fees Import class.
 *
 * @since 1.0.0
 */
class Advanced_Extra_Fees_Woocommerce_Import extends \Advanced_Extra_Fees_Woocommerce_Import_Export {

    /** @var bool whether to create new fees during an import process */
	private $create_new_fees = true;

	/** @var bool whether to merge (update) existing fees data during an import */
	private $merge_existing_fees = true;

    /**
    * Constructor.
    *
    * @since 1.0.0
    */
    public function __construct() {

        parent::__construct();
    }

    /**
	 * Get CSV file headers.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function get_csv_headers() {

		$headers = parent::get_csv_headers();

		/**
		 * Filter the Fees CSV import file row headers.
		 *
		 * @since 1.0.0
		 *
		 * @param array $csv_headers associative array
		 * @param \Advanced_Extra_Fees_Woocommerce_Import $import_instance instance of the import class
		 */
		return (array) apply_filters( 'dsaefw_csv_import_fees_headers', $headers, $this );
	}

    /**
	 * Process input form submission to import.
	 *
	 * @since 1.0.0
	 */
	public function process_import() {

        $file_import_file_args = array(
            'import_file' => array(
                'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
                'flags'  => FILTER_FORCE_ARRAY,
            ),
        );

        //We are using filter_var_array to get the file data because it is $_FILES array not $_GET or $_POST.
        $attached_import_files_arr = filter_var_array( $_FILES, $file_import_file_args ); // phpcs:ignore

		// bail out and return an error notice if no file was added for upload
		if ( empty( $attached_import_files_arr['import_file'] ) || empty( $attached_import_files_arr['import_file']['name'] ) ) {

            wp_send_json_error( 
                array( 
                    'message' => esc_html__( 'You must upload a file to import Fees.', 'advanced-extra-fees-woocommerce' ) 
                )  
            );
        // bail out if an upload file is not CSV format
        } elseif ( isset( $attached_import_files_arr['import_file'] ) && ! empty( $attached_import_files_arr['import_file']['type'] ) && 'text/csv' !== $attached_import_files_arr['import_file']['type'] ) {

            wp_send_json_error( 
                array( 
                    'message' => esc_html__( 'You must upload a CSV file to import Fees.', 'advanced-extra-fees-woocommerce' ) 
                )  
            );
		// bail out if an upload error occurred (most likely a server issue)
		} elseif ( isset( $attached_import_files_arr['import_file']['error'] ) && $attached_import_files_arr['import_file']['error'] > 0 ) {

            wp_send_json_error( 
                array( 
                    /* translators: Placeholders: %s - import file error while uploading */
                    'message' => sprintf( esc_html__( 'There was a problem uploading the file: %s', 'advanced-extra-fees-woocommerce' ), 
                        '<em>' . $this->get_file_upload_error( $attached_import_files_arr['import_file']['error'] ) . '</em>' )  
                )  
            );

		// process the file once uploaded
		} else {

			// get CSV data from file
			if ( isset( $attached_import_files_arr['import_file']['tmp_name'] ) ) {
				$csv_data = $this->parse_file_csv( $attached_import_files_arr['import_file']['tmp_name'] );
			}

			// bail out if the file can't be parsed or there are only headers
			if ( empty( $csv_data ) || count( $csv_data ) <= 1 ) {

                wp_send_json_error( 
                    array( 
                        'message' => esc_html__( 'Could not find Fees to import from uploaded file.', 'advanced-extra-fees-woocommerce' )  
                    )  
                );

			// proceed
			} else {
                
                // set importing options
				$this->create_new_fees     = apply_filters( 'dsaefw_csv_import_create_new_fees', true );
				$this->merge_existing_fees = apply_filters( 'dsaefw_csv_import_merge_existing_fees', true );

				// process rows to import
                $import_fees = $this->import_fees( $csv_data );

                if( 'error' === $import_fees['return_type'] ) {
                    wp_send_json_error( 
                        array( 
                            'message' => esc_html( $import_fees['return_message'] )
                        )  
                    );
                } else {
                    // Reset transient for updated fees
                    delete_transient( 'dsaefw_get_all_fees' );
                    wp_send_json_success( 
                        array( 
                            'message' => esc_html( $import_fees['return_message'] )
                        ) 
                    );
                }
			}
		}
	}

    /**
	 * Parse a file with CSV data into an array.
	 *
	 * @since 1.0.0
	 *
	 * @param resource $file_handle file to process as a resource
	 * @return null|array array data or null on read error
	 */
	private function parse_file_csv( $file_handle ) {

		if ( is_readable( $file_handle ) ) {

			$csv_data = array();

			// get the data from file
			$file_contents = fopen( $file_handle, 'r' ); // phpcs:ignore

			// handle character encoding
			if ( $enc = mb_detect_encoding( $file_handle, 'UTF-8, ISO-8859-1', true ) ) { // phpcs:ignore
				setlocale( LC_ALL, 'en_US.' . $enc );
			}

			$delimiter = $this->get_fields_delimiter();
			$enclosure = $this->get_enclosure();

			while ( ( $row = fgetcsv( $file_contents, 0, $delimiter, $enclosure ) ) !== false ) { // phpcs:ignore
				$csv_data[] = $row;
			}

			fclose( $file_contents ); // phpcs:ignore

			return $csv_data;
		}

		return null;
	}

    /**
	 * Import Fees from CSV data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $rows CSV import data parsed into an array format, with headers in the first key
	 */
	private function import_fees( array $rows ) {

		$created = 0;
		$merged  = 0;

		// get the column keys and remove them from the data set
		$columns = array_flip( $rows[0] );
		unset( $rows[0] );

		$total = count( $rows );

		if ( ! empty( $columns ) && ! empty( $rows ) ) {
            
			foreach ( $rows as $row ) {

				// try to get a Fee ID
				$fee_id         = isset( $columns['id'] ) && ! empty( $row[ $columns['id'] ] ) ? (int) $row[ $columns['id'] ] : null;
				$advance_fee    = get_post_status( (int) $fee_id ) && is_int( $fee_id ) ? new \Advanced_Extra_Fees_Woocommerce_Fee( $fee_id ) : null;

				if ( ! $advance_fee && false === $this->create_new_fees ) {
					// bail if no Fee is found to update and we can't create a new one by import setting
					continue;
				}

				if ( $advance_fee && false === $this->merge_existing_fees ) {
					// bail if there is already an existing Fee but we can't update it by import setting
					continue;
				}

				$import_data = array();
				$csv_headers = array_keys( $this->get_csv_headers() );

				// gather import data
				foreach ( $csv_headers as $column_key ) {
					$import_data[ $column_key ] = isset( $columns[ $column_key ] ) && ! empty( $row[ $columns[ $column_key ] ] ) ? $row[ $columns[ $column_key ] ] : null;
				}

				$import_data['id']  = $fee_id;
				$import_data['fee'] = $advance_fee;

				/**
				 * Filter Fee CSV import data before processing an import.
				 *
				 * @since 1.0.0
				 *
				 * @param array $import_data the imported data as associative array
				 * @param string $action either 'create' or 'merge' (update) a Fee
				 * @param array $columns CSV columns raw data
				 * @param array $row CSV row raw data
				 */
				$import_data = (array) apply_filters( 'dsaefw_csv_import_fee', $import_data, true === $this->create_new_fees ? 'create' : 'merge', $columns, $row );

				// create or update a Fee and bump counters
				if ( ! $advance_fee && true === $this->create_new_fees ) {
					$created += (int) $this->import_fee( 'create', $import_data );
				} elseif ( $advance_fee && true === $this->merge_existing_fees ) {
					$merged  += (int) $this->import_fee( 'merge', $import_data );
				}
			}
		}
		// output results as admin notice
		return $this->show_results_notice( $total, $created, $merged );
	}

    /**
	 * Creates or updates a Fee according to import data.
	 *
	 * @since 1.0.0
	 *
	 * @param string $action either 'create' or 'merge' (for updating)
	 * @param array $import_data fee import data
	 * @return null|bool
	 */
	private function import_fee( $action = '', $import_data = array() ) {

		$advance_fee    = null;
		$post_status    = isset( $import_data['status'] ) && in_array( $import_data['status'], array( 'draft', 'publish' ), true ) ? $import_data['status'] : 'publish';
        $fee_name       = is_string( $import_data['name'] ) ? sanitize_text_field( $this->unescape_value( $import_data['name'] ) ) : '';

		switch ( $action ) {

			case 'create' :

				$fee_id = wp_insert_post( array(
					'post_status'   => $post_status,
					'post_type'     => DSAEFW_FEE_POST_TYPE,
                    'post_title'    => $fee_name,
				) );

				if ( $advance_fee = new \Advanced_Extra_Fees_Woocommerce_Fee( $fee_id ) ) { // phpcs:ignore
					$advance_fee = $this->import_fee_data( $advance_fee, $import_data, 'create' );
				}

			break;

			case 'merge' :

				if ( isset( $import_data ) && $import_data['fee'] instanceof \Advanced_Extra_Fees_Woocommerce_Fee ) {

					if ( ! empty( $import_data['status'] ) ) {

						if ( $import_data['fee']->get_status() !== $post_status ) {

							wp_update_post( array(
								'ID'            => $import_data['fee']->get_id(),
								'post_status'   => $post_status,
                                'post_title'    => $fee_name,
							) );
						}
					}

					$advance_fee = $this->import_fee_data( $import_data['fee'], $import_data, 'merge' );
				}

			break;
		}

		if ( $advance_fee instanceof \Advanced_Extra_Fees_Woocommerce_Fee ) {

			/**
			 * Upon creating or updating a Fee via import.
			 *
			 * @since 1.0.0
			 *
			 * @param \Advanced_Extra_Fees_Woocommerce_Fee $advance_fee the imported fee
			 * @param string $action either 'create' or 'merge' (update) a fee
			 * @param array $data import data used in import process
			 */
			do_action( 'dsaefw_csv_import_fee', $advance_fee, $action, $import_data );

			return true;
		}

		return false;
	}

    /**
	 * Update fee data.
	 *
	 * @since 1.0.0
	 *
	 * @param \Advanced_Extra_Fees_Woocommerce_Fee $advance_fee the fee being updated
	 * @param array $import_data associative array with import data
	 * @param string $action type of import: 'create' or 'merge' a new or exiting entry
	 * @return null|\Advanced_Extra_Fees_Woocommerce_Fee
	 */
	private function import_fee_data( \Advanced_Extra_Fees_Woocommerce_Fee $advance_fee, array $import_data, $action ) {

        // bail out if there's nothing to import
		if ( empty( $import_data ) || ! in_array( $action, array( 'create', 'merge' ), true ) ) {
			return null;
		}
        
        if ( $advance_fee instanceof \Advanced_Extra_Fees_Woocommerce_Fee ) {

            /** Fee Configuration */

            // import fee type
			$fee_type = ! empty( $import_data['fee_type'] ) ? sanitize_text_field( $this->unescape_value( $import_data['fee_type'] ) ) : null;
			if ( is_string( $fee_type ) && '' !== $fee_type ) {
				$advance_fee->set_fee_type( $fee_type );
			}

            // import fee apply on cart total
			$fees_on_cart_total = ! empty( $import_data['fees_on_cart_total'] ) ? sanitize_text_field( $this->unescape_value( $import_data['fees_on_cart_total'] ) ) : null;
            $advance_fee->set_fees_on_cart_total( $fees_on_cart_total );

            // import fee cost
			$fee_settings_product_cost = ! empty( $import_data['fee_settings_product_cost'] ) ? $this->unescape_value( $import_data['fee_settings_product_cost'] ) : '';
            $advance_fee->set_fee_settings_product_cost( $fee_settings_product_cost );

            // import fee per quantity enable
			$fee_chk_qty_price = ! empty( $import_data['fee_chk_qty_price'] ) ? sanitize_text_field( $this->unescape_value( $import_data['fee_chk_qty_price'] ) ) : null;
            $advance_fee->set_fee_chk_qty_price( $fee_chk_qty_price );

            // import fee per quantity type
            $fee_per_qty = ! empty( $import_data['fee_per_qty'] ) ? sanitize_text_field( $this->unescape_value( $import_data['fee_per_qty'] ) ) : null;
            $advance_fee->set_fee_per_qty( $fee_per_qty );

            // import fee per quantity cost
            $extra_product_cost = ! empty( $import_data['extra_product_cost'] ) ? $this->unescape_value( $import_data['extra_product_cost'] ) : '';
            $advance_fee->set_extra_product_cost( $extra_product_cost );

            // import per weight cost enable
            $is_allow_custom_weight_base = ! empty( $import_data['is_allow_custom_weight_base'] ) ? sanitize_text_field( $this->unescape_value( $import_data['is_allow_custom_weight_base'] ) ) : null;
            $advance_fee->set_is_allow_custom_weight_base( $is_allow_custom_weight_base );

            // import per weight base cost
            $sm_custom_weight_base_cost = ! empty( $import_data['sm_custom_weight_base_cost'] ) ? $this->unescape_value( $import_data['sm_custom_weight_base_cost'] ) : '';
            $advance_fee->set_sm_custom_weight_base_cost( $sm_custom_weight_base_cost );

            // import per weight base each value
            $sm_custom_weight_base_per_each = ! empty( $import_data['sm_custom_weight_base_per_each'] ) ? $this->unescape_value( $import_data['sm_custom_weight_base_per_each'] ) : '';
            $advance_fee->set_sm_custom_weight_base_per_each( $sm_custom_weight_base_per_each );

            // import per weight over value
            $sm_custom_weight_base_over = ! empty( $import_data['sm_custom_weight_base_over'] ) ? $this->unescape_value( $import_data['sm_custom_weight_base_over'] ) : '';
            $advance_fee->set_sm_custom_weight_base_over( $sm_custom_weight_base_over );

            // import fee tooltip description
            $dsaefw_tooltip_description = ! empty( $import_data['dsaefw_tooltip_description'] ) ? sanitize_text_field( $this->unescape_value( $import_data['dsaefw_tooltip_description'] ) ) : null;
            $advance_fee->set_dsaefw_tooltip_description( $dsaefw_tooltip_description );

            // import fee taxable enable
            $fee_settings_select_taxable = ! empty( $import_data['fee_settings_select_taxable'] ) ? sanitize_text_field( $this->unescape_value( $import_data['fee_settings_select_taxable'] ) ) : null;
            $advance_fee->set_fee_settings_select_taxable( $fee_settings_select_taxable );
            

            /** Advanced Settings */

            // import first order user enable
            $first_order_for_user = ! empty( $import_data['first_order_for_user'] ) ? sanitize_text_field( $this->unescape_value( $import_data['first_order_for_user'] ) ) : null;
            $advance_fee->set_first_order_for_user( $first_order_for_user );

            // import recurring fee enable
            $fee_settings_recurring = ! empty( $import_data['fee_settings_recurring'] ) ? sanitize_text_field( $this->unescape_value( $import_data['fee_settings_recurring'] ) ) : null;
            $advance_fee->set_fee_settings_recurring( $fee_settings_recurring );

            // import fee on checkout enable
            $fee_show_on_checkout_only = ! empty( $import_data['fee_show_on_checkout_only'] ) ? sanitize_text_field( $this->unescape_value( $import_data['fee_show_on_checkout_only'] ) ) : null;
            $advance_fee->set_fee_show_on_checkout_only( $fee_show_on_checkout_only );

            // import days of week
            $ds_select_day_of_week = ! empty( $import_data['ds_select_day_of_week'] ) ? array_map( 'sanitize_text_field', json_decode( $import_data['ds_select_day_of_week'] ) ) : null;
            $advance_fee->set_ds_select_day_of_week( $ds_select_day_of_week );

            // import fee settings start date
            $fee_settings_start_date = ! empty( $import_data['fee_settings_start_date'] ) ? $this->unescape_value( $import_data['fee_settings_start_date'] ) : '';
            $advance_fee->set_fee_settings_start_date( $fee_settings_start_date );

            // import fee settings start date
            $fee_settings_end_date = ! empty( $import_data['fee_settings_end_date'] ) ? $this->unescape_value( $import_data['fee_settings_end_date'] ) : '';
            $advance_fee->set_fee_settings_end_date( $fee_settings_end_date );

            // import fee start time
            $ds_time_from = ! empty( $import_data['ds_time_from'] ) ? $this->unescape_value( $import_data['ds_time_from'] ) : '';
            $advance_fee->set_ds_time_from( $ds_time_from );

            // import fee end time
            $ds_time_to = ! empty( $import_data['ds_time_to'] ) ? $this->unescape_value( $import_data['ds_time_to'] ) : '';
            $advance_fee->set_ds_time_to( $ds_time_to );

            // import all conditional rules
            $product_fees_metabox = ! empty( $import_data['product_fees_metabox'] ) ? json_decode( $import_data['product_fees_metabox'], true ) : null;
            $advance_fee->set_product_fees_metabox( $product_fees_metabox );

            // import conditional rules match
            $cost_rule_match = ! empty( $import_data['cost_rule_match'] ) ? json_decode( $import_data['cost_rule_match'], true ) : null;
            $advance_fee->set_cost_rule_match( $cost_rule_match );
        }

        return $advance_fee;
    }

    /**
	 * Show a notice with import results
	 *
	 * @since 1.0.0
	 *
	 * @param int $total_rows total rows in CSV file
	 * @param int $created fees created
	 * @param int $merged fees merged/updated
	 */
	private function show_results_notice( $total_rows = 0, $created = 0, $merged = 0 ) {

		$rows_processed  = $created + $merged;
		$skipped_rows    = $total_rows - $rows_processed;

		if ( 0 === $total_rows ) {

			$notice_type = 'error';
			$message     = esc_html__( 'Could not find Fees to import from uploaded file.', 'advanced-extra-fees-woocommerce' );

		} else {

			/* translators: Placeholder: %s - Fees to import found in uploaded file */
			$message = sprintf( _n( '%s record found in file.', '%s records found in file.', $total_rows, 'advanced-extra-fees-woocommerce' ), $total_rows );

			if ( $rows_processed > 0 ) {

				$notice_type = 'message';

				/* translators: Placeholder: %s - Fees processed during import from file */
				$message .= ' ' . sprintf( _n( '%s row processed for import.', '%s rows processed for import.', $rows_processed, 'advanced-extra-fees-woocommerce' ), $rows_processed );

				if ( $created > 0 ) {
					/* translators: Placeholder: %s - Fees created in import */
					$message .= ' ' . sprintf( _n( '%s new Fee created.', '%s new Fees created.', $created, 'advanced-extra-fees-woocommerce' ), $created );
				}

				if ( $merged > 0 ) {
					/* translators: Placeholder: %s - Fees updated during import */
					$message .= ' ' . sprintf( _n( '%s existing Fee updated.', '%s existing Fees updated.', $merged, 'advanced-extra-fees-woocommerce' ), $merged );
				}

				if ( $skipped_rows > 0 ) {
					/* translators: Placeholder: %s - skipped Fees to import from file */
					$message .= ' ' . sprintf( _n( '%s row skipped.', '%s rows skipped.', $skipped_rows, 'advanced-extra-fees-woocommerce' ), $skipped_rows );
				}

			} else {

				$notice_type  = 'error';
				$message     .=  __( 'However, no Fees were created or updated with the given options.', 'advanced-extra-fees-woocommerce' );
			}
		}

        // add admin notice
        return array( 'return_type' => $notice_type, 'return_message' => $message );
	}

    /**
	 * Get an error message for file upload failure.
	 *
	 * @see http://php.net/manual/en/features.file-upload.errors.php
	 *
	 * @since 1.0.0
	 *
	 * @param int $error_code a PHP error code
	 * @return string error message
	 */
	private function get_file_upload_error( $error_code ) {

		switch ( $error_code ) {
			case 1 :
			case 2 :
				return __( 'The file uploaded exceeds the maximum file size allowed.', 'advanced-extra-fees-woocommerce' );
			case 3 :
				return __( 'The file was only partially uploaded. Please try again.', 'advanced-extra-fees-woocommerce' );
			case 4 :
				return __( 'No file was uploaded.', 'advanced-extra-fees-woocommerce' );
			case 6 :
				return __( 'Missing a temporary folder to store the file. Please contact your host.', 'advanced-extra-fees-woocommerce' );
			case 7 :
				return __( 'Failed to write file to disk. Perhaps a permissions error, please contact your host.', 'advanced-extra-fees-woocommerce' );
			case 8 :
				return __( 'A PHP Extension stopped the file upload. Please contact your host.', 'advanced-extra-fees-woocommerce' );
			default :
				return __( 'Unknown error.', 'advanced-extra-fees-woocommerce' );
		}
	}
}