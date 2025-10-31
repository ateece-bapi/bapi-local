<?php
/**
 * Advanced Extra Fees for WooCommerce - Export class
 * 
 * @version 1.0.0
 */

defined( 'ABSPATH' ) or exit;

if ( class_exists( 'Advanced_Extra_Fees_Woocommerce_Export', false ) ) {
	return new Advanced_Extra_Fees_Woocommerce_Export();
}

/**
 * Fees Export class.
 *
 * @since 1.0.0
 */
class Advanced_Extra_Fees_Woocommerce_Export extends \Advanced_Extra_Fees_Woocommerce_Import_Export {
    
    /** @var resource output stream containing CSV data */
	private $stream;

    /**
    * Constructor.
    *
    * @since 1.0.0
    */
    public function __construct() {

        parent::__construct();

        // process exports from Fees edit screen bulk action
		add_action( 'load-edit.php', array( $this, 'process_bulk_export' ) );
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
		 * Filter the Fees CSV export file row headers.
		 *
		 * @since 1.0.0
		 *
		 * @param array $headers Associative array
		 * @param \Advanced_Extra_Fees_Woocommerce_Export $export_instance Instance of the export class
		 */
		return (array) apply_filters( 'dsaefw_csv_export_fees_headers', $headers, $this );
	}

    /**
	 * Get the export CSV file name.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private function get_file_name() {

		// file name default: blog_name_fees_YYYY_MM_DD.csv
		$file_name = str_replace( '-', '_', sanitize_file_name( strtolower( get_bloginfo( 'name' ) . '_fees_' . date_i18n( 'Y_m_d', time() ) . '.csv' ) ) );

		/**
		 * Filter the fees CSV export file name.
		 *
		 * @since 1.0.0
		 *
		 * @param string $file_name the CSV file name, should have .csv extension
		 */
		return apply_filters( 'dsaefw_csv_export_fees_file_name', $file_name );
	}

    /**
	 * Process input form submission to export a CSV file.
	 *
	 * @since 1.0.0
	 *
	 * @param int[] $fee_ids array of fee IDs
	 * @param array $args optional array of arguments to pass to get_posts() to fetch fees
	 */
	public function process_export( $fee_ids = array(), $args = array() ) {

		if ( empty( $fee_ids ) ) {
			$fee_ids = dsaefw()->dsaefw_public_object()->dsaefw_get_all_fees( wp_parse_args( array(
				'post_status'    => 'any',
				'fields'         => 'ids',
				'posts_per_page' => -1,
				'offset'         => 0,
			), $args ) );
		}
        if( wp_doing_ajax() ) {
            // This will call on ajax action from export page
            if ( ! empty( $fee_ids ) ) {
                //File path where our csv file will be saved
                $file_path = $this->get_file_path();

                // File name
                $file_name = $this->get_file_name();

                //Remove all previous CSV files
                $files = glob("$file_path/*.csv");
                foreach ($files as $csv_file) {
                    wp_delete_file($csv_file);
                }

                // Open the CSV file for writing
                $csv_file = fopen($file_path.$file_name, 'w'); // phpcs:ignore

                // write the generated CSV to the output buffer
                fwrite( $csv_file, $this->get_csv( $fee_ids ) );

                // close the output buffer
                fclose( $csv_file ); // phpcs:ignore
                
                // For ajax call we need to return file path
                wp_send_json_success( 
                    array( 
                        'message' => esc_html__( 'Fees has been exported in CSV!', 'advanced-extra-fees-woocommerce' ), 
                        'download_path' => $this->get_file_path('download') . $file_name 
                    ) 
                );
            } else {
                wp_send_json_error( 
                    array( 
                        'message' => esc_html__( 'No Fees found matching the criteria to export.', 'advanced-extra-fees-woocommerce' )
                    ) 
                );
            }
        } else {
            // This will call on bulk action from fee listing page
            if ( ! empty( $fee_ids ) ) {

                // try to set unlimited script timeout and generate file for download
                @set_time_limit( 0 ); // phpcs:ignore
                
                $this->download( $this->get_file_name(), $this->get_csv( $fee_ids ) );
            } else {
                
                // tell the user there were no Fees to export matching the criteria
                dsaefw()->add_admin_notice( 'fee_export_error', 'error', esc_html__( 'No Fees found matching the criteria to export.', 'advanced-extra-fees-woocommerce' ) );
            }
        }
	}

    /**
	 * Downloads the CSV via the browser.
	 *
	 * @since 1.0.0
	 *
	 * @param string $filename the file name
	 * @param string $csv the CSV data to download as a file
	 */
	protected function download( $filename, $csv ) {

		// set headers for download
		header( 'Content-Type: text/csv; charset=' . get_option( 'blog_charset' ) );
		header( sprintf( 'Content-Disposition: attachment; filename="%s"', $filename ) );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

        // phpcs:disable
		// clear the output buffer
		@ini_set( 'zlib.output_compression', 'Off' );
		@ini_set( 'output_buffering', 'Off' );
		@ini_set( 'output_handler', '' );
        // phpcs:enable

        // clean output buffer which contain HTML from get_csv() function
        ob_end_clean();

		// open the output buffer for writing
		$file = fopen( 'php://output', 'w' );
        
		// write the generated CSV to the output buffer
		fwrite( $file, $csv );

		// close the output buffer
		fclose( $file ); // phpcs:ignore
		exit;
	}

    /**
	 * Write the given row to the CSV
	 *
	 * @since 1.0.0
	 *
	 * @param array $headers row headers
	 * @param array $row row data to write
	 */
	private function write( $headers, $row ) {

		$data = array();

		foreach ( $headers as $header_key ) {

			if ( ! isset( $row[ $header_key ] ) ) {
				$row[ $header_key ] = '';
			}

			$value = '';

			// strict string comparison, as values like '0' are valid
			if ( '' !== $row[ $header_key ]  ) {
				$value = $row[ $header_key ];
			}

			// escape spreadsheet sensitive characters with a single quote to prevent CSV injections, by prepending a single quote `'`
			$first_char = isset( $value[0] ) ? $value[0] : '';

			if ( in_array( $first_char, array( '=', '+', '-', '@' ), false ) ) { // phpcs:ignore
				$value = "'" . $value;
			}

			$data[] = $value;
		}

		fputcsv( $this->stream, $data, $this->get_fields_delimiter(), $this->get_enclosure() );
	}

    /**
	 * Get the CSV data
	 *
	 * @since 1.0.0
	 *
	 * @param int[] $fee_ids array of \Advanced_Extra_Fees_Woocommerce_Fee IDs
	 * @return string
	 */
	private function get_csv( array $fee_ids ) {

		// open output buffer to write CSV to
		$this->stream = fopen( 'php://output', 'w' );

		ob_start();

		/**
		 * CSV BOM (Byte order mark).
		 *
		 * Enable adding a BOM to the exported CSV.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $enable_bom true to add the BOM, false otherwise (default value)
		 * @param \Advanced_Extra_Fees_Woocommerce_Export instance of this class
		 */
		if ( true === apply_filters( 'dsaefw_csv_export_fees_enable_bom', false, $this ) ) { // @phpstan-ignore-line

			// prepends the BOM at the top of the file, before the CSV headers
			fwrite( $this->stream, chr(0xEF) . chr(0xBB) . chr(0xBF) );
		}

		$headers = $this->get_csv_headers();

		// add CSV headers
		$this->write( $headers, $headers );

		foreach ( $fee_ids as $fee_id ) {

            $advance_fee = new \Advanced_Extra_Fees_Woocommerce_Fee( $fee_id );

			if ( $advance_fee->get_id() > 0 ) {

				$row = $this->get_csv_row( $headers, $advance_fee );
                
				if ( ! empty ( $row ) ) {

					$data = array();

					foreach ( $headers as $header_key ) {

						if ( ! isset( $row[ $header_key ] ) ) {
							$row[ $header_key ] = '';
						}

						$value = '';

						// strict string comparison, as values like '0' are valid
						if ( '' !== $row[ $header_key ]  ) {
							$value = $row[ $header_key ];
						}

						// escape spreadsheet sensitive characters with a single quote, to prevent CSV injections, by prepending a single quote `'`.
						$data[] = $this->escape_value( $value );
					}

					fputcsv( $this->stream, $data, $this->get_fields_delimiter(), $this->get_enclosure() );
				}
			}
		}

		$csv = ob_get_clean();

		fclose( $this->stream ); // phpcs:ignore

		return $csv;
	}


	/**
	 * Get an individual Fee CSV row data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $headers CSV headers
	 * @param \Advanced_Extra_Fees_Woocommerce_Fee $advance_fee the fee
	 * @return array
	 */
	private function get_csv_row( $headers, $advance_fee ) {

		$row     = array();
		$columns = array_keys( $headers );

		if ( ! empty( $columns ) ) {

			foreach ( $columns as $column_name ) {

                $method = "get_{$column_name}";
                $value  = method_exists( $advance_fee, $method ) ? $advance_fee->$method() : '';

				$row[ $column_name ] = is_array($value) ? wp_json_encode( $value ) : $value;
			}
		}

		/**
		 * Filter Fee CSV row data.
		 *
		 * @since 1.0.0
		 *
		 * @param array $row fee data in associative array format for CSV output
		 * @param \Advanced_Extra_Fees_Woocommerce_Fee $advance_fee fee object
		 * @param \Advanced_Extra_Fees_Woocommerce_Export $export_instance instance of the export class
		 */
		return (array) apply_filters( 'dsaefw_csv_export_fee_row', $row, $advance_fee, $this );
	}

    /**
	 * Process an export from bulk action request.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function process_bulk_export( ) {

        $nonce      = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $post_type  = filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

        $wp_list_table  = _get_list_table( 'WP_Posts_List_Table' );
        $action_nonce   = 'bulk-' . $wp_list_table->_args['plural'];

        if ( ! wp_verify_nonce( $nonce, $action_nonce ) ) {
            return;
        }

        if ( !empty( $post_type ) && DSAEFW_FEE_POST_TYPE === $post_type ) {

            $action = $wp_list_table->current_action();

            if ( 'export' === $action ) {

                if ( ! current_user_can( 'manage_woocommerce' ) ) {

                    wp_die( esc_html__( 'You are not allowed to perform this action.', 'advanced-extra-fees-woocommerce' ) );
                } else {

                    $get_post_ids   = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY );
                    $post_ids       = !empty( $get_post_ids ) ? array_map( 'absint', $get_post_ids ) : array();

                    $this->process_export( $post_ids );
                }
            }
        }
	}

    /**
     * Get file path.
	 *
	 * @since 1.0.0
	 *
     * @param string $type save|download
     * 
	 * @return string
     */
    private function get_file_path( $type = 'save' ) {

        $return_path = '';

        //File path details
        $path_data = wp_get_upload_dir();

        if( 'save' === $type ) {
            
            $return_path = $path_data['basedir'].'/dsaefw_export_data/';

            // Create directory if not exists
            if( !file_exists( $return_path ) ) {
                mkdir( $return_path, 0777, true ); //phpcs:ignore
            }
        } else {

            $return_path = $path_data['baseurl'].'/dsaefw_export_data/';
        }

        return $return_path;
    }
}