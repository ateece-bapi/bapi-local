<?php 
/**
 * WooCommerce Advanced Extra Fees lisings
 *
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'Advanced_Extra_Fees_Woocommerce_Admin_Fees', false ) ) {
	return new Advanced_Extra_Fees_Woocommerce_Admin_Fees();
}

/**
 * Advanced_Extra_Fees_Woocommerce_Admin_Fees.
 */
#[\AllowDynamicProperties]
class Advanced_Extra_Fees_Woocommerce_Admin_Fees {

    /**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

        // fee admin screen columns
        add_filter( 'manage_edit-' . DSAEFW_FEE_POST_TYPE . '_columns', array( $this, 'customize_columns' ) );
		add_action( 'manage_' . DSAEFW_FEE_POST_TYPE . '_posts_custom_column', array( $this, 'custom_column_content' ), 10, 2 );

        // customize Advance Fees admin screen row actions
		add_filter( 'post_row_actions', array( $this, 'customize_row_actions' ), 10, 2 );
        // customize Advance Fees bulk actions
		add_filter( 'bulk_actions-edit-' . DSAEFW_FEE_POST_TYPE, array( $this, 'customize_bulk_actions' ) );

        // add a Advance Fee permanent delete action link that replaces the send to trash link
		add_action( 'post_submitbox_misc_actions', array( $this, 'add_delete_advance_fee_action_link' ) );

        // filter the "Enter title here" post title placeholder
		add_filter( 'enter_title_here', array( $this, 'dsaefw_fee_title_placeholder' ) );

        // process actions from Fees edit screen bulk action
		add_action( 'load-edit.php', array( $this, 'process_bulk_actions' ) );

        // Advance Fees admin edit screens
        require_once( plugin_dir_path( dirname( __FILE__ ) ) . '/admin/class-advanced-extra-fees-woocommerce-fee.php' );
        require_once( plugin_dir_path( dirname( __FILE__ ) ) . '/admin/class-advanced-extra-fees-woocommerce-fee-metabox.php' );
        require_once( plugin_dir_path( dirname( __FILE__ ) ) . '/admin/class-advanced-extra-fees-woocommerce-fee-conditional-rules.php' );

    }

    /**
	 * Filter the fee admin column keys.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns array of admin columns
	 * @return array
	 */
	public function customize_columns( array $columns ) {

        $new_columns = array();

        foreach ( $columns as $key => $value ) {

			$new_columns[ $key ] = $value;

			if ( 'title' === $key ) {
				$new_columns['amount']     = esc_html__( 'Fee', 'advanced-extra-fees-woocommerce' );
                $new_columns['start_date'] = esc_html__( 'Start Date', 'advanced-extra-fees-woocommerce' );
                $new_columns['end_date']   = esc_html__( 'End Date', 'advanced-extra-fees-woocommerce' );   
                $new_columns['status']     = esc_html__( 'Status', 'advanced-extra-fees-woocommerce' );
			}
		}

		return $new_columns;
	}

    /**
	 * Filter the fee admin column content.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @param string $column the column being displayed
	 * @param int $post_id the \WP_Post ID
	 */
	public function custom_column_content( $column, $post_id ) {

		$advance_fee = new \Advanced_Extra_Fees_Woocommerce_Fee( $post_id );

		switch ( $column ) {

			case 'amount':

				$amount = $advance_fee->get_fee_settings_product_cost();

				if ( ! empty( $amount ) ) {
                    $fee_type = $advance_fee->get_fee_type();
                    if( 'percentage' === $fee_type ) {
                        printf( esc_html('%d%%'), floatval( $amount ) );
                    } elseif( 'both' === $fee_type ) {
                        echo esc_html( $amount );
                    } else {
                        printf( esc_html('%s%s'), esc_html( get_woocommerce_currency_symbol() ), esc_html( $amount ) );
                    }
				} else {
					echo '&ndash;';
				}

			break;

			case 'start_date':

				$start_date = $advance_fee->get_fee_settings_start_date(true);

				echo empty( $start_date ) ? '&ndash;' : $start_date; // phpcs:ignore

			break;

			case 'end_date':

				$end_date = $advance_fee->get_fee_settings_end_date(true);

				echo empty( $end_date ) ? '&ndash;' : $end_date; // phpcs:ignore

			break;

			case 'status' :
                
				if ( 'publish' === get_post_status( $post_id ) ) {
                    printf( '<mark class="order-status status-processing"><span>%s</span></mark>', esc_html__( 'Enabled', 'advanced-extra-fees-woocommerce' ) );
				} else {
                    printf( '<mark class="order-status status-failed"><span>%s</span></mark>', esc_html__( 'Disabled', 'advanced-extra-fees-woocommerce' ) );
				}

			break;

		}
	}

    /**
	 * Filter fees row actions.
	 *
	 * @since 1.0.0
	 *
	 * @param array $actions associative array of actions and labels
	 * @param \WP_Post $post the post object
	 * @return array
	 */
	public function customize_row_actions( $actions, $post ) {

		if ( DSAEFW_FEE_POST_TYPE === get_post_type( $post ) ) {

			unset( $actions['inline hide-if-no-js'], $actions['trash'] );

			if ( current_user_can( 'delete_post', $post->ID ) ) {
				$actions['delete'] = "<a class='submitdelete delete-advance-fee' title='" . esc_attr__( 'Delete this fee permanently', 'advanced-extra-fees-woocommerce' ) . "' href='" . esc_url( get_delete_post_link( $post->ID, '', true ) ) . "'>" . esc_html__( 'Delete', 'advanced-extra-fees-woocommerce' ) . "</a>";
			}
		}

		return $actions;
	}

    /**
	 * Add a permanent delete action link in the fee publish box.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Post $post the current fee post
	 */
	public function add_delete_advance_fee_action_link( $post ) {

		if ( DSAEFW_FEE_POST_TYPE === get_post_type( $post ) && current_user_can( 'delete_post', $post->ID ) ) {

            $show_confirmation = ! MEDIA_TRASH ? " onclick='return showNotice.warn();'" : '';

			printf(
				'<div id="delete-advance-fee" class="submitdelete misc-pub-section"><a class="submitdelete deletion"%1$s href="%2$s">%3$s</a></div>',
				$show_confirmation, // phpcs:ignore
				get_delete_post_link( $post->ID, '', true ),
				esc_html__( 'Delete permanently', 'advanced-extra-fees-woocommerce' )
			);
		}
	}

    /**
	 * Filter fees bulk actions.
	 *
	 * @since 1.0.0
	 *
	 * @param array $actions associative array
	 * @return array
	 */
	public function customize_bulk_actions( $actions ) {

		unset( $actions['trash'], $actions['edit'] );

        // Export selected fees (This operation covered in Export class file)
		$actions['export']      = __( 'Export', 'advanced-extra-fees-woocommerce' );

        // Parmanently delete
		$actions['delete']      = __( 'Delete', 'advanced-extra-fees-woocommerce' );

        // Enable selected fees
		$actions['enable']      = __( 'Enable', 'advanced-extra-fees-woocommerce' );

        // Disable selected fees
		$actions['disable']      = __( 'Disable', 'advanced-extra-fees-woocommerce' );

		return $actions;
	}

    /**
	 * Filter fees bulk actions.
	 *
	 * @since 1.0.0
	 */
	public function process_bulk_actions() {

        $nonce      = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $post_type  = filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

        $wp_list_table  = _get_list_table( 'WP_Posts_List_Table' );
        $action_nonce   = 'bulk-' . $wp_list_table->_args['plural'];

        if ( ! wp_verify_nonce( $nonce, $action_nonce ) ) {
            return;
        }

		if ( !empty( $post_type ) && DSAEFW_FEE_POST_TYPE === $post_type ) {

            $action = $wp_list_table->current_action();

            if ( ! current_user_can( 'manage_woocommerce' ) ) {

                wp_die( esc_html__( 'You are not allowed to perform this action.', 'advanced-extra-fees-woocommerce' ) );
            }

            $get_post_ids   = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY );
            $post_ids       = !empty( $get_post_ids ) ? array_map( 'absint', $get_post_ids ) : array();
            
            if( empty( $post_ids ) ) {
                return;
            }
            
            if ( 'enable' === $action ) {
                
                foreach ( $post_ids as $post_id ) {
                    $update_post = array(
                        'post_type'     => DSAEFW_FEE_POST_TYPE,
                        'ID'            => $post_id,
                        'post_status'   => 'publish'
                    );
                    wp_update_post($update_post);
                }

                // Reset transient for updated fees
                delete_transient( 'dsaefw_get_all_fees' );
            }

            if ( 'disable' === $action ) {

                foreach ( $post_ids as $post_id ) {
                    $update_post = array(
                        'post_type'     => DSAEFW_FEE_POST_TYPE,
                        'ID'            => $post_id,
                        'post_status'   => 'draft'
                    );
                    
                    wp_update_post($update_post);
                }

                // Reset transient for updated fees
                delete_transient( 'dsaefw_get_all_fees' );
            }

            if ( 'delete' === $action ) {

                // Reset transient for updated fees
                delete_transient( 'dsaefw_get_all_fees' );
            }
        }
	}

    /**
	 * Changes the default text of the "Enter title here" for the Fee post type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $enter_title_here default text
	 * @return string
	 */
	public function dsaefw_fee_title_placeholder( $enter_title_here ) {
		global $post_type;

		if ( DSAEFW_FEE_POST_TYPE === $post_type ) {
			return esc_html__( 'Enter fee title here', 'advanced-extra-fees-woocommerce' );
		}

		return $enter_title_here;
	}
}