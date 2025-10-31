<?php 
/**
 * WooCommerce Advanced Extra Fees Settings
 *
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'Advanced_Extra_Fees_Woocommerce_Settings', false ) ) {
	return new Advanced_Extra_Fees_Woocommerce_Settings();
}


/**
 * Advanced_Extra_Fees_Woocommerce_Settings.
 */
class Advanced_Extra_Fees_Woocommerce_Settings extends WC_Settings_Page {

    /**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'advanced_extra_fees';
		$this->label = __( 'Advanced Extra Fees', 'advanced-extra-fees-woocommerce' );

		parent::__construct();

        add_action( 'woocommerce_sections_' . $this->id, array( $this, 'load_fees_edit_screen' ), 10 );

        // Custom export button
        add_action('woocommerce_admin_field_export_button', array( $this, 'output_import_export_button') );

        // Custom import button
        add_action('woocommerce_admin_field_import_button', array( $this, 'output_import_export_button') );
        
        // Custom file upload field
        add_action('woocommerce_admin_field_file', array( $this, 'render_file_upload_field') );
	}

    public function get_tab_id() {
        return $this->id;
    }

    /**
	 * Get own sections.
	 *
	 * @return array
	 */
	protected function get_own_sections() {
		return array(
			'manage_fees'     => __( 'Manage Fees', 'advanced-extra-fees-woocommerce' ),
			'global_settings' => __( 'Global Settings', 'advanced-extra-fees-woocommerce' ),
			'import_fees'     => __( 'Import Fees', 'advanced-extra-fees-woocommerce' ),
			'export_fees'     => __( 'Export Fees', 'advanced-extra-fees-woocommerce' ),
		);
	}

    /**
	 * Load the Fees edit screen from the corresponding WooCommerce section.
	 *
	 * @since 1.0.0
	 */
    public function load_fees_edit_screen(){
        global $current_screen;
        
        // phpcs:ignore WordPress.Security.NonceVerification

        $get_tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $current_tab = isset( $get_tab ) ? sanitize_text_field( wp_unslash( $get_tab ) ) : '';

        $get_section = filter_input( INPUT_GET, 'section', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $current_section = isset( $get_section ) ? sanitize_text_field( wp_unslash( $get_section ) ) : '';
        
        if ( isset( $current_screen->id, $current_tab, $current_section )
		     && 'woocommerce_page_wc-settings' === $current_screen->id
		     && ( 'manage_fees' === $current_section || empty( $current_section ) ) ){

			wp_safe_redirect( admin_url( 'edit.php?post_type=' . DSAEFW_FEE_POST_TYPE ) );
			exit;
		}
    }

    /**
	 * Get settings for the default section.
	 *
	 * @return array
	 */
	protected function get_settings_for_global_settings_section() {

        $settings = 
        array(
            array(
                'title'         => __( 'Global Configuration', 'advanced-extra-fees-woocommerce' ),
                'type'          => 'title',
                'desc'          => '',
                'id'            => 'dsaefw_global_settings',
            ),
            array(
                'title'         => __( 'Remove fees', 'advanced-extra-fees-woocommerce' ),
                'desc'          => __( 'When this option is enabled, the fee will be removed once a 100% discount applies to the cart.', 'advanced-extra-fees-woocommerce' ),
                'id'            => 'dsaefw_remove_fee_on_full_discount',
                'default'       => 'no',
                'type'          => 'checkbox',
                'desc_tip'      => __('Remove fees once a 100% discount applies.', 'advanced-extra-fees-woocommerce'),
            ),
            array(
                'title'         => __( 'Combine Fees', 'advanced-extra-fees-woocommerce' ),
                'desc'          => __( 'When this option is enabled, all fees will be combined into a single fee.', 'advanced-extra-fees-woocommerce' ),
                'id'            => 'dsaefw_combine_fees',
                'default'       => 'no',
                'type'          => 'checkbox',
            ),
            array(
                'title'         => __( 'Combine Fees Taxable', 'advanced-extra-fees-woocommerce' ),
                'desc'          => __( 'When applying multiple coupons, apply the first coupon to the full price and the second coupon to the discounted price and so on.', 'advanced-extra-fees-woocommerce' ),
                'id'            => 'dsaefw_combine_fees_taxable',
                'default'       => 'no',
                'type'          => 'checkbox',
                'autoload'      => false,
                'class'         => 'dsaefw_combine_fees_field',
            ),
            array(
                'title'         => __( 'Combine Fees Tooltip', 'advanced-extra-fees-woocommerce' ),
                'desc'          => __( 'Enable this if you want to add a tooltip to the combined fee label.', 'advanced-extra-fees-woocommerce' ),
                'id'            => 'dsaefw_combine_fees_tooltip',
                'default'       => 'no',
                'type'          => 'checkbox',
                'autoload'      => false,
                'class'         => 'dsaefw_combine_fees_field',
            ),
            array(
                'title'         => __( 'Combine Fees Tooltip Text', 'advanced-extra-fees-woocommerce' ),
                'id'            => 'dsaefw_combine_fees_tooltip_text',
                'type'          => 'text',
                'default'       => '',
                'css'           => '',
                'placeholder'   => __( 'Enter tooltip text...', 'advanced-extra-fees-woocommerce' ),
                'desc_tip'      => __( 'Add your own tooltip text that will apply to the combined fee label.', 'advanced-extra-fees-woocommerce' ),
                'class'         => 'dsaefw_combine_fees_field',
            ),
            array(
                'type'          => 'sectionend',
                'id'            => 'dsaefw_global_settings',
            ),
        );

        return $settings;
    }

    protected function get_settings_for_import_fees_section () {
        
        // Hide the default save button.
		$GLOBALS['hide_save_button'] = true;

        $max_upload_size   = size_format( wp_max_upload_size() );

        $settings = 
        array(
            array(
                'title' => __( 'Import Fees', 'advanced-extra-fees-woocommerce' ),
                'type'  => 'title',
                'desc'  => '',
                'id'    => 'dsaefw_import_fees',
            ),
            //We have done this element's HTML in Import Class
            array(
                'title'         => __( 'Import Fees', 'advanced-extra-fees-woocommerce' ),
                'desc'          => __( 'Import fees from a CSV file.', 'advanced-extra-fees-woocommerce' ),
                'id'            => 'dsaefw_import_fees_file',
                'default'       => '',
                'type'          => 'file',
                'desc_tip'      => sprintf( __( 'Acceptable file types: CSV or tab-delimited text files. Maximum file size: %s', 'advanced-extra-fees-woocommerce' ), empty( $max_upload_size ) ? '<em>' . __( 'Undetermined', 'advanced-extra-fees-woocommerce' ) . '</em>' : $max_upload_size ),
            ),
            array(
                'title'         => __( 'Import Fees', 'advanced-extra-fees-woocommerce' ),
                'desc'          => __( 'Import fees to a CSV file.', 'advanced-extra-fees-woocommerce' ),
                'id'            => 'dsaefw_import_fees',
                'default'       => '',
                'type'          => 'import_button',
            ),
            array(
                'type' => 'sectionend',
                'id'   => 'dsaefw_import_fees',
            ),
        );

        return $settings;
    }

    protected function get_settings_for_export_fees_section () {
        
        // Hide the default save button.
		$GLOBALS['hide_save_button'] = true;

        $documentation_url = 'https://docs.thedotstore.com/article/194-how-to-export-and-import-conditional-fees';

        $settings = 
        array(
            array(
                'title'     => __( 'Export Fees', 'advanced-extra-fees-woocommerce' ),
                'type'      => 'title',
                /* translators: Placeholders: %1$s - opening <a> link HTML tag, $2$s - closing </a> link HTML tag */
                'desc'      =>  sprintf( esc_html__( 'Your CSV file must be formatted with the correct column names and cell data. Please %1$ssee the documentation%2$s for more information and a sample CSV file.', 'advanced-extra-fees-woocommerce' ), '<a href="' . esc_url( $documentation_url ) . '" target="_blank">', '</a>' ),
                'id'        => 'dsaefw_export_fees',
            ),
            array(
                'title'     => __( 'Export Fees', 'advanced-extra-fees-woocommerce' ),
                'desc'      => __( 'Export fees to a CSV file.', 'advanced-extra-fees-woocommerce' ),
                'id'        => 'dsaefw_export_fees',
                'default'   => '',
                'type'      => 'export_button',
            ),
            array(
                'type'      => 'sectionend',
                'id'        => 'dsaefw_export_fees',
            ),
        );
        return $settings;
       
    }

    /**
     * Output the import/export button
     * 
     * @param array $field
     * 
     * @since 1.0.0
     * 
     * @internal
     */
    public function output_import_export_button( $field ) {
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <?php wp_nonce_field( esc_attr($field['id']).'-action-nonce', esc_attr($field['id']).'_action_nonce' ); ?>
                <input type="hidden" name="dsaefw_action" value="<?php echo esc_attr($field['id']); ?>" />
                <button type="submit" class="woocommerce-save-button components-button is-primary" id="<?php echo esc_attr($field['id']); ?>">
                    <?php echo esc_html($field['title']); ?>
                </button>
            </th>
        </tr>
        <?php
    }

    /**
	 * Output a file input field
	 *
     * @param array $field field settings
	 *
	 * @since 1.0.0
     * 
     * @internal
	 */
	public function render_file_upload_field( $field ) {

		$field = wp_parse_args( $field, array(
			'id'       => '',
			'title'    => __( 'Choose a file from your computer', 'advanced-extra-fees-woocommerce' ),
			'desc'     => '',
			'desc_tip' => '',
			'type'     => 'dsaefw-file',
			'class'    => '',
			'css'      => '',
			'value'    => '',
            'accept'   => array( 'csv' ),
		) );

		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?><span class="woocommerce-help-tip" data-tip="<?php echo esc_attr( $field['desc'] ); ?>"></span></label>
			</th>
			<td class="forminp forminp-<?php echo sanitize_html_class( $field['type'] ) ?>">
				<input
					type="hidden"
					name="MAX_FILE_SIZE"
					value="<?php echo esc_attr( wp_max_upload_size() ); ?>"
				/>
				<input
					name="<?php echo esc_attr( $field['id'] ); ?>"
					id="<?php echo esc_attr( $field['id'] ); ?>"
					type="file"
					style="<?php echo esc_attr( $field['css'] ); ?>"
					value="<?php echo esc_attr( $field['value'] ); ?>"
					class="<?php echo esc_attr( $field['class'] ); ?>"
                    accept="<?php echo esc_attr( '.' . implode(', .', $field['accept']) ); ?>"
				/><br><span class="description"><?php echo esc_html( $field['desc_tip'] ); ?></span>
			</td>
		</tr>
		<?php
	}

    /**
	 * Save settings and trigger the 'woocommerce_update_options_'.id action.
	 */
	public function save() {
        
		$this->save_settings_for_current_section();

		$this->do_update_options_action();
	}
}

return new Advanced_Extra_Fees_Woocommerce_Settings();
?>