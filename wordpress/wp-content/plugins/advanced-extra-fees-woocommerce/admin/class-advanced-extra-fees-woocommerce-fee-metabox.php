<?php 
/**
 * WooCommerce Advanced Extra Fees lisings
 *
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'Advanced_Extra_Fees_Woocommerce_Admin_Fee_Metabox', false ) ) {
	return new Advanced_Extra_Fees_Woocommerce_Admin_Fee_Metabox();
}

/**
 * Advanced_Extra_Fees_Woocommerce_Admin_Fee_Metabox.
 */
#[\AllowDynamicProperties]
class Advanced_Extra_Fees_Woocommerce_Admin_Fee_Metabox {

    /** @var string meta box ID **/
	protected $id;

	/** @var string meta box context **/
	protected $context = 'normal';

	/** @var string meta box priority **/
	protected $priority = 'default';

	/** @var array list of supported screen IDs **/
	protected $screens = array();

    /** @var array list of additional postbox classes for this meta box **/
	protected $postbox_classes = array( 'woocommerce', 'advanced-extra-fees-woocommerce' );

    /** @var \Advanced_Extra_Fees_Woocommerce_Fee the advance fee where this meta box appears */
	private $advance_fee;

    /**
	 * Meta box constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

        $this->id       = 'dsaefw-fee-data';
		$this->priority = 'high';
		$this->screens  = array( DSAEFW_FEE_POST_TYPE );

        // add/edit screen hooks
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

        // update meta box data when saving post
        add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
    }

    /**
	 * Get the meta box ID, with underscores instead of dashes.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_id_underscored() {
		return str_replace( '-', '_', $this->id );
	}


	/**
	 * Get the nonce name for this meta box.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_nonce_name() {
		return '_' . $this->get_id_underscored() . '_nonce';
	}


	/**
	 * Get the nonce action for this meta box.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_nonce_action() {
		return 'update-' . $this->id;
	}

    /**
	 * Add meta box to the supported screen(s).
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function add_meta_box() {
		global $post, $current_screen;

		// sanity checks
		if (    ! $post instanceof \WP_Post
		     || ! $current_screen
		     || ! in_array( $current_screen->id, $this->screens, true )
		     || ! current_user_can( 'manage_woocommerce_wc_conditional_fees' ) ) {
			return;
		}

		add_meta_box(
			$this->id,
			esc_html__( 'Fee Configuration', 'advanced-extra-fees-woocommerce' ),
			array( $this, 'do_output' ),
			$current_screen->id,
			$this->context,
			$this->priority
		);

		add_filter( "postbox_classes_{$current_screen->id}_{$this->id}", array( $this, 'postbox_classes' ) );
	}

    /**
	 * Get the meta box tabs.
	 *
	 * @since 1.0.0
	 *
	 * @return array associative array of tab keys and properties
	 */
	public function get_tabs() {

		$tabs = array(

			'fee_setting' => array(
				'label'  => __( 'Fee configuration', 'advanced-extra-fees-woocommerce' ),
				'target' => 'dsaefw-fee-setting',
				'class'  => array( 'active' ),
			),

			'advance_setting' => array(
				'label'  => __( 'Advance Setting', 'advanced-extra-fees-woocommerce' ),
				'target' => 'dsaefw-advance-setting',
			),

		);

		return $tabs;
	}

    /**
	 * Output basic meta box contents.
	 *
	 * @since 1.0.0
	 */
	public function do_output() {
		global $post;

		$this->advance_fee = new \Advanced_Extra_Fees_Woocommerce_Fee( $post );

        // Get data from the fee object
        $fee_type = $this->advance_fee->get_fee_type();
        $fees_on_cart_total = $this->advance_fee->get_fees_on_cart_total();
        $fee_settings_product_cost = $this->advance_fee->get_fee_settings_product_cost();
        $fee_chk_qty_price = $this->advance_fee->get_fee_chk_qty_price();
        $fee_per_qty = $this->advance_fee->get_fee_per_qty();
        $extra_product_cost = $this->advance_fee->get_extra_product_cost();

        $is_allow_custom_weight_base = $this->advance_fee->get_is_allow_custom_weight_base();
        $sm_custom_weight_base_cost = $this->advance_fee->get_sm_custom_weight_base_cost();
        $sm_custom_weight_base_per_each = $this->advance_fee->get_sm_custom_weight_base_per_each();
        $sm_custom_weight_base_over = $this->advance_fee->get_sm_custom_weight_base_over();

        $dsaefw_tooltip_description = $this->advance_fee->get_dsaefw_tooltip_description();
        $fee_settings_select_taxable = $this->advance_fee->get_fee_settings_select_taxable();

        //Advanced Section
        $first_order_for_user = $this->advance_fee->get_first_order_for_user();
        $fee_settings_recurring = $this->advance_fee->get_fee_settings_recurring();
        $fee_show_on_checkout_only = $this->advance_fee->get_fee_show_on_checkout_only();
        $ds_select_day_of_week = $this->advance_fee->get_ds_select_day_of_week();
        $fee_settings_start_date = $this->advance_fee->get_fee_settings_start_date(true);
        $fee_settings_end_date = $this->advance_fee->get_fee_settings_end_date(true);
        $ds_time_from = $this->advance_fee->get_ds_time_from();
        $ds_time_to = $this->advance_fee->get_ds_time_to();
        
        // annotation for weight unit
        $get_weight_unit = get_option( 'woocommerce_weight_unit' );
        $get_weight_unit = isset( $get_weight_unit ) && !empty( $get_weight_unit ) ? $get_weight_unit : 'kg';
        ?>
        
        <?php
		// add a nonce field
        wp_nonce_field( $this->get_nonce_action(), $this->get_nonce_name() );

		// output the child meta box HTML ?>
		<div class="dsaefw-meta-box <?php echo esc_attr($this->id); ?>">
            <div class="panel-wrap data">
            <?php $tabs   = $this->get_tabs(); ?>
                <ul class="dsaefw_data_tabs wc-tabs">
                    <?php foreach ( $tabs as $key => $tab ) : ?>

                        <?php $class = isset( $tab['class'] ) ? $tab['class'] : array(); ?>
                        <li class="<?php echo sanitize_html_class( $key ); ?>_options <?php echo sanitize_html_class( $key ); ?>_tab <?php echo implode( ' ' , array_map( 'sanitize_html_class', $class ) ); ?>">
                            <a href="#<?php echo esc_attr( $tab['target'] ); ?>"><span><?php echo esc_html( $tab['label'] ); ?></span></a>
                        </li>

                    <?php endforeach; ?>
                </ul>

                <div id="dsaefw-fee-setting" class="panel woocommerce_options_panel">
                    <div class="options_group">
                        <h4><?php esc_html_e( 'Configuration', 'advanced-extra-fees-woocommerce' ); ?></h4>
                        <p class="form-field">
                            <label for="fee_settings_select_fee_type"><?php esc_html_e( 'Fee type', 'advanced-extra-fees-woocommerce' ); ?>
                            <?php echo wp_kses_post( wc_help_tip( __( 'You can apply extra fees as a fixed or percentage or both price.', 'advanced-extra-fees-woocommerce' ) ) ); ?></label>
                            <select
                                name="fee_settings_select_fee_type"
                                id="fee_settings_select_fee_type">
                                <option value="fixed"  <?php selected( 'fixed',  $fee_type, true ); ?>><?php esc_html_e( 'Fixed',  'advanced-extra-fees-woocommerce' ); ?></option>
                                <option value="percentage" <?php selected( 'percentage', $fee_type, true ); ?>><?php esc_html_e( 'Percentage', 'advanced-extra-fees-woocommerce' ); ?></option>
                                <option value="both" <?php selected( 'both', $fee_type, true ); ?>><?php esc_html_e( 'Percentage + Fixed', 'advanced-extra-fees-woocommerce' ); ?></option>
                            </select>
                        </p>
                        <p class="form-field js-hide-if-fee_type js-hide-if-fee_type-fixed" <?php if ( 'fixed' === $fee_type || !$fee_type ) { echo 'style="display:none;"'; } ?>>
                            <label><?php esc_html_e( 'Enable', 'advanced-extra-fees-woocommerce' ); ?><?php echo wp_kses_post( wc_help_tip( __( 'By enabling a apply on cart total, fee will apply on cart total instead of cart subtotal', 'advanced-extra-fees-woocommerce' ) ) ); ?></label>
                            <input
                                type="checkbox"
                                name="fees_on_cart_total"
                                id="fees_on_cart_total"
                                value="yes"
                                <?php checked( 'yes' === $fees_on_cart_total, true, true ); ?>
                            />
                            <label class="label-checkbox" for="fees_on_cart_total"><?php esc_html_e( 'Apply fee on cart total', 'advanced-extra-fees-woocommerce' ); ?></label>
                        </p>
                        <p class="form-field">
                            <label for="fee_settings_product_cost"><?php esc_html_e( 'Fee amount', 'advanced-extra-fees-woocommerce' ); ?> *
                            <?php echo wp_kses_post( wc_help_tip( __( 'You can add a fixed/percentage fee based on the selection above.', 'advanced-extra-fees-woocommerce' ) ) ); ?></label>
                            <?php if( $fee_type === 'percentage' ) { ?>
                            <input
                                type="number"
                                id="fee_settings_product_cost"
                                name="fee_settings_product_cost"
                                value="<?php echo esc_attr( $fee_settings_product_cost ); ?>"
                                placeholder="0"
                                min="0"
                                step="0.01"
                                required
                            />
                            <?php } else if( $fee_type === 'both' ) { ?>
                                <input
                                    type="text"
                                    id="fee_settings_product_cost"
                                    name="fee_settings_product_cost"
                                    value="<?php echo esc_attr( $fee_settings_product_cost ); ?>"
                                    placeholder="% + <?php echo esc_attr( get_woocommerce_currency_symbol() );?>"
                                    required
                                />
                            <?php } else { ?>
                                <input
                                    type="text"
                                    id="fee_settings_product_cost"
                                    name="fee_settings_product_cost"
                                    value="<?php echo esc_attr( $fee_settings_product_cost ); ?>"
                                    placeholder="<?php echo esc_attr( get_woocommerce_currency_symbol() );?>"
                                    required
                                />
                            <?php } ?>
                        </p>
                        <div class="dsaefw-description-wrap">
                            <p><strong><?php echo esc_html( 'Dynamic Charges:', 'advanced-extra-fees-woocommerce'); ?></strong> <?php echo esc_html( 'You can enter fixed amount or make it dynamic using the below short code parameters:', 'advanced-extra-fees-woocommerce'); ?></p>
                            <div class="dsaefw-description">
                                <ul>
                                    <li><code><?php esc_html_e( '[qty]', 'advanced-extra-fees-woocommerce'); ?></code> - <?php esc_html_e( 'total number of items in cart', 'advanced-extra-fees-woocommerce'); ?></li>
                                    <li><code><?php esc_html_e( '[cost]', 'advanced-extra-fees-woocommerce'); ?></code> - <?php esc_html_e( 'cost of items', 'advanced-extra-fees-woocommerce'); ?></li>
                                    <li><code><?php esc_html_e( '[weight]', 'advanced-extra-fees-woocommerce'); ?></code> - <?php esc_html_e( 'weight of items', 'advanced-extra-fees-woocommerce'); ?></li>
                                    <li><code><?php esc_html_e( '[fee min_fee=20]', 'advanced-extra-fees-woocommerce'); ?></code> - <?php esc_html_e( 'Minimum fee to apply', 'advanced-extra-fees-woocommerce'); ?></li>
                                    <li><code><?php esc_html_e( '[fee max_fee=20]', 'advanced-extra-fees-woocommerce'); ?></code> - <?php esc_html_e( 'Maximum fee to apply', 'advanced-extra-fees-woocommerce'); ?></li>
                                </ul>
                                <p><?php esc_html_e( 'Below are some examples', 'advanced-extra-fees-woocommerce'); ?>:</p>
                                <ol>
                                    <li><?php esc_html_e( '10.00 -> To add flat 10.00 fee charge', 'advanced-extra-fees-woocommerce'); ?></li>
                                    <li>10.00 * <code>[qty]</code> - <?php esc_html_e( 'To charge 10.00 per quantity in the cart. It will be 50.00 if the cart has 5 quantity', 'advanced-extra-fees-woocommerce'); ?></li>
                                    <li><code>[fee min_fee=20]</code> - <?php esc_html_e( 'This means minimum 20 fee charge will be applicable', 'advanced-extra-fees-woocommerce'); ?></li>
                                    <li><code>[fee max_fee=20]</code> - <?php esc_html_e( 'This means cart subtotal charge greater than max_fee then maximum 20 charge will be applicable', 'advanced-extra-fees-woocommerce'); ?></li>
                                </ol>
                            </div>
                        </div>
                        <p class="form-field">
                            <label><?php esc_html_e( 'Enable', 'advanced-extra-fees-woocommerce' ); ?><?php echo wp_kses_post( wc_help_tip( __( 'This will apply fees as per the quantity of products.', 'advanced-extra-fees-woocommerce' ) ) ); ?></label>
                            <input
                                type="checkbox"
                                name="fee_chk_qty_price"
                                id="fee_chk_qty_price"
                                value="yes"
                                <?php checked( 'yes' === $fee_chk_qty_price, true, true ); ?>
                            />
                            <label class="label-checkbox" for="fee_chk_qty_price"><?php esc_html_e( 'Apply Per Additional Quantity', 'advanced-extra-fees-woocommerce' ); ?></label>
                        </p>
                        <p class="form-field js-hide-if-fee_per_quantity js-hide-if-fee_per_quantity-disabled" <?php if ( 'yes' !== $fee_chk_qty_price || !$fee_chk_qty_price ) { echo 'style="display:none;"'; } ?>>
                            <label for="fee_per_qty"><?php esc_html_e( 'Based on', 'advanced-extra-fees-woocommerce' ); ?>
                            <?php echo wp_kses_post( wc_help_tip( __( 'Cart based will apply to the total product\'s quantity in the cart and Product based will apply to the specific product\'s quantity in the cart.', 'advanced-extra-fees-woocommerce' ) ) ); ?></label>
                            <select
                                name="fee_per_qty"
                                id="fee_per_qty">
                                <option value="qty_cart_based"  <?php selected( 'qty_cart_based',  $fee_per_qty, true ); ?>><?php esc_html_e( 'Cart',  'advanced-extra-fees-woocommerce' ); ?></option>
                                <option value="qty_product_based" <?php selected( 'qty_product_based', $fee_per_qty, true ); ?>><?php esc_html_e( 'Product', 'advanced-extra-fees-woocommerce' ); ?></option>
                            </select>
                        </p>
                        <p class="form-field js-hide-if-fee_per_quantity js-hide-if-fee_per_quantity-disabled" <?php if ( 'yes' !== $fee_chk_qty_price || !$fee_chk_qty_price ) { echo 'style="display:none;"'; } ?>>
                            <label for="extra_product_cost"><?php esc_html_e( 'Fee Per Quantity', 'advanced-extra-fees-woocommerce' ); ?>
                            <?php echo wp_kses_post( wc_help_tip( __( 'You can add a fixed/percentage fee based on the selection above.', 'advanced-extra-fees-woocommerce' ) ) ); ?></label>
                            <input
                                type="number"
                                id="extra_product_cost"
                                name="extra_product_cost"
                                value="<?php echo esc_attr( $extra_product_cost ); ?>"
                                placeholder="0"
                                step="0.01"
                                min="0"
                            />
                        </p>
                        <p class="form-field">
                            <label><?php esc_html_e( 'Enable', 'advanced-extra-fees-woocommerce' ); ?><?php echo wp_kses_post( wc_help_tip( __( 'Enable/Disable additional rules per weight on the cart page.', 'advanced-extra-fees-woocommerce' ) ) ); ?></label>
                            <input
                                type="checkbox"
                                name="is_allow_custom_weight_base"
                                id="is_allow_custom_weight_base"
                                value="yes"
                                <?php checked( 'yes' === $is_allow_custom_weight_base, true, true ); ?>
                            />
                            <label class="label-checkbox" for="is_allow_custom_weight_base"><?php esc_html_e( 'Each Weight Rule', 'advanced-extra-fees-woocommerce' ); ?></label>
                        </p>
                        <p class="form-field js-hide-if-weight_base-disabled" <?php if ( 'yes' !== $is_allow_custom_weight_base || !$is_allow_custom_weight_base ) { echo 'style="display:none;"'; } ?>>
                            <label for="sm_custom_weight_base_cost"><?php echo sprintf( esc_html__( 'Fee Per Weight (%s)', 'advanced-extra-fees-woocommerce' ), esc_attr( get_woocommerce_currency_symbol() ) ); ?>
                            <?php echo wp_kses_post( wc_help_tip( __( 'You can add a fixed/percentage fee based on the selection above.', 'advanced-extra-fees-woocommerce' ) ) ); ?></label>
                            <input
                                type="number"
                                id="sm_custom_weight_base_cost"
                                name="sm_custom_weight_base_cost"
                                value="<?php echo floatval( $sm_custom_weight_base_cost ); ?>"
                                placeholder="<?php echo esc_attr( get_woocommerce_currency_symbol() );?>"
                                step="0.01"
                                min="0"
                            />
                        </p>
                        <p class="form-field js-hide-if-weight_base-disabled" <?php if ( 'yes' !== $is_allow_custom_weight_base || !$is_allow_custom_weight_base ) { echo 'style="display:none;"'; } ?>>
                            <label for="sm_custom_weight_base_per_each"><?php echo sprintf( esc_html__( 'Fee per each weight (%s)', 'advanced-extra-fees-woocommerce' ), esc_attr($get_weight_unit) ); ?>
                            <?php echo wp_kses_post( wc_help_tip( __( 'You can add a fixed/percentage fee based on the selection above.', 'advanced-extra-fees-woocommerce' ) ) ); ?></label>
                            <input
                                type="number"
                                id="sm_custom_weight_base_per_each"
                                name="sm_custom_weight_base_per_each"
                                value="<?php echo esc_attr( $sm_custom_weight_base_per_each ); ?>"
                                placeholder="<?php echo esc_attr($get_weight_unit); ?>"
                                step="0.01"
                                min="0"
                            />
                        </p>
                        <p class="form-field js-hide-if-weight_base-disabled" <?php if ( 'yes' !== $is_allow_custom_weight_base || !$is_allow_custom_weight_base ) { echo 'style="display:none;"'; } ?>>
                            <label for="sm_custom_weight_base_over"><?php echo sprintf( esc_html__( 'Fee over weight (%s)', 'advanced-extra-fees-woocommerce' ), esc_attr($get_weight_unit) ); ?>
                            <?php echo wp_kses_post( wc_help_tip( __( 'You can add a fixed/percentage fee based on the selection above.', 'advanced-extra-fees-woocommerce' ) ) ); ?></label>
                            <input
                                type="number"
                                id="sm_custom_weight_base_over"
                                name="sm_custom_weight_base_over"
                                value="<?php echo esc_attr( $sm_custom_weight_base_over ); ?>"
                                placeholder="<?php echo esc_attr($get_weight_unit); ?>"
                                step="0.01"
                                min="0"
                            />
                        </p>
                        <p class="form-field">
                            <label for="dsaefw_tooltip_description"><?php esc_html_e( 'Tooltip Description', 'advanced-extra-fees-woocommerce' ); ?><?php echo wp_kses_post( wc_help_tip( __( 'As a tooltip, provide short information for this fee to your customers.', 'advanced-extra-fees-woocommerce' ) ) ); ?></label>
                            <textarea
                                type="test"
                                name="dsaefw_tooltip_description"
                                id="dsaefw_tooltip_description"
                                maxlength="<?php echo esc_attr( apply_filters( 'dsaefw_set_fee_tooltip_maxlength', DSAEFW_TOOLTIP_LENGTH ) ); ?>"
                            ><?php echo esc_textarea( $dsaefw_tooltip_description ); ?></textarea>
                        </p>
                        <p class="form-field">
                            <label><?php esc_html_e( 'Is Amount Taxable ?', 'advanced-extra-fees-woocommerce' ); ?><?php echo wp_kses_post( wc_help_tip( __( 'Enable this to calculate fee as taxable.', 'advanced-extra-fees-woocommerce' ) ) ); ?></label>
                            <input
                                type="checkbox"
                                name="fee_settings_select_taxable"
                                id="fee_settings_select_taxable"
                                value="yes"
                                <?php checked( 'yes' === $fee_settings_select_taxable, true, true ); ?>
                            />
                            <label class="label-checkbox" for="fee_settings_select_taxable"><?php esc_html_e( 'Show as taxable fee', 'advanced-extra-fees-woocommerce' ); ?></label>
                        </p>
                    </div>
                </div>
                <div id="dsaefw-advance-setting" class="panel woocommerce_options_panel">
                    <div class="options_group">
                        <h4><?php esc_html_e( 'Advanced Setting', 'advanced-extra-fees-woocommerce' ); ?></h4>
                        <p class="form-field">
                            <label><?php esc_html_e( 'Current Local Time', 'advanced-extra-fees-woocommerce' ); ?><?php echo wp_kses_post( wc_help_tip( __( 'User can take reference to configure setting.', 'advanced-extra-fees-woocommerce' ) ) ); ?></label>
                            <code>
                                <?php 
                                $timezone_format = get_option( 'date_format' ) .' '. get_option( 'time_format' );
                                echo esc_html( date_i18n( $timezone_format ) );
                                ?>
                            </code>
                        </p>
                        <p class="form-field">
                            <label><?php esc_html_e( 'Start Date', 'advanced-extra-fees-woocommerce' ); ?><?php echo wp_kses_post( wc_help_tip( __( 'After this date fee will display and apply on frontend side', 'advanced-extra-fees-woocommerce' ) ) ); ?></label>
                            <input 
                                type="text" 
                                name="fee_settings_start_date"
                                id="fee_settings_start_date" 
                                class="datepicker"
                                value="<?php echo esc_attr($fee_settings_start_date); ?>" 
                                autocomplete="off" />
                        </p>
                        <p class="form-field">
                            <label><?php esc_html_e( 'End Date', 'advanced-extra-fees-woocommerce' ); ?><?php echo wp_kses_post( wc_help_tip( __( 'Till this date fee will display and apply on frontend side.', 'advanced-extra-fees-woocommerce' ) ) ); ?></label>
                            <input 
                                type="text" 
                                name="fee_settings_end_date"
                                id="fee_settings_end_date" 
                                class="datepicker" 
                                value="<?php echo esc_attr($fee_settings_end_date); ?>"
                                autocomplete="off" />
                        </p>
                        <p class="form-field">
                            <label><?php esc_html_e( 'Days of week', 'advanced-extra-fees-woocommerce' ); ?><?php echo wp_kses_post( wc_help_tip( __( 'Select the days on which you want to enable fees on your website. This rule will match with the current day which is set by WordPress Timezone', 'advanced-extra-fees-woocommerce' ) ) ); ?></label>
                            <?php
								$select_day_week_array = array(
									'sun' => esc_html__( 'Sunday', 'advanced-extra-fees-woocommerce' ),
									'mon' => esc_html__( 'Monday', 'advanced-extra-fees-woocommerce' ),
									'tue' => esc_html__( 'Tuesday', 'advanced-extra-fees-woocommerce' ),
									'wed' => esc_html__( 'Wednesday', 'advanced-extra-fees-woocommerce' ),
									'thu' => esc_html__( 'Thursday', 'advanced-extra-fees-woocommerce' ),
									'fri' => esc_html__( 'Friday', 'advanced-extra-fees-woocommerce' ),
									'sat' => esc_html__( 'Saturday', 'advanced-extra-fees-woocommerce' ),
								);
								?>
                            <select 
                                name="ds_select_day_of_week[]" 
                                id="ds_select_day_of_week" 
                                class="ds_select_day_of_week dsaefw_select" 
                                multiple="multiple" 
                                placeholder='<?php echo esc_attr( 'Select day of the Week',  'advanced-extra-fees-woocommerce' ); ?>'
                                data-width="50%">
                                <?php foreach ( $select_day_week_array as $value => $name ) { ?>
                                    <option value="<?php echo esc_attr( $value ); ?>" <?php echo ! empty( $ds_select_day_of_week ) && in_array( $value, $ds_select_day_of_week, true ) ? 'selected="selected"' : '' ?>><?php echo esc_html( $name ); ?></option>
                                <?php } ?>
                            </select>
                        </p>
                        <p class="form-field">
                            <label><?php esc_html_e( 'Start Time', 'advanced-extra-fees-woocommerce' ); ?><?php echo wp_kses_post( wc_help_tip( __( 'Select the days on which you want to enable fees on your website. This rule will match with the current day which is set by WordPress Timezone', 'advanced-extra-fees-woocommerce' ) ) ); ?></label>
                            <?php echo wp_kses( $this->generateTimeDropdown( 'ds_time_from', 'ds_time_from', esc_html( $ds_time_from ) ), dsaefw()->dsaefw_allowed_html_tags() ); ?>
                        </p>
                        <p class="form-field">
                            <label><?php esc_html_e( 'End Time', 'advanced-extra-fees-woocommerce' ); ?><?php echo wp_kses_post( wc_help_tip( __( 'Select the days on which you want to enable fees on your website. This rule will match with the current day which is set by WordPress Timezone', 'advanced-extra-fees-woocommerce' ) ) ); ?></label>
                            <?php echo wp_kses( $this->generateTimeDropdown( 'ds_time_to', 'ds_time_to', esc_html( $ds_time_to ) ), dsaefw()->dsaefw_allowed_html_tags() ); ?>
                        </p>
                        <p class="form-field">
                            <label><?php esc_html_e( 'User\'s First Order', 'advanced-extra-fees-woocommerce' ); ?><?php echo wp_kses_post( wc_help_tip( __( 'Apply the fee for the user\'s first order only.', 'advanced-extra-fees-woocommerce' ) ) ); ?></label>
                            <input
                                type="checkbox"
                                name="first_order_for_user"
                                id="first_order_for_user"
                                value="yes"
                                <?php checked( 'yes' === $first_order_for_user, true, true ); ?>
                            />
                        </p>
                        <?php if ( is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) { ?>
                        <p class="form-field">
                            <label><?php esc_html_e( 'Recurring Fee', 'advanced-extra-fees-woocommerce' ); ?><?php echo wp_kses_post( wc_help_tip( __( 'Once selected it will allow fees on recurring payments as well.(This option only works with subscription products.)', 'advanced-extra-fees-woocommerce' ) ) ); ?></label>
                            <input
                                type="checkbox"
                                name="fee_settings_recurring"
                                id="fee_settings_recurring"
                                value="yes"
                                <?php checked( 'yes' === $fee_settings_recurring, true, true ); ?>
                            />
                        </p>
                        <?php } ?>
                        <p class="form-field">
                            <label><?php esc_html_e( 'Showcase Fee', 'advanced-extra-fees-woocommerce' ); ?><?php echo wp_kses_post( wc_help_tip( __( 'Once you enabled this option, It will only show fee on checkout page', 'advanced-extra-fees-woocommerce' ) ) ); ?></label>
                            <input
                                type="checkbox"
                                name="fee_show_on_checkout_only"
                                id="fee_show_on_checkout_only"
                                value="yes"
                                <?php checked( 'yes' === $fee_show_on_checkout_only, true, true ); ?>
                            />
                            <label class="label-checkbox" for="fee_show_on_checkout_only"><?php esc_html_e( 'On Checkout Only', 'advanced-extra-fees-woocommerce' ); ?></label>
                        </p>
                    </div>
                </div>
            </div>
		</div>
		<?php
	}

    /**
	 * Add meta box classes.
	 *
	 * @since 1.0.0
	 *
	 * @param string[] $classes
	 * @return string[]
	 */
	public function postbox_classes( $classes ) {
		return array_merge( $classes, $this->postbox_classes );
	}

    /**
	 * Prepare time dropdown HTML.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name
	 * @param string $id
	 * @return html
	 */
    public function generateTimeDropdown( $name, $id, $selected ) {
        $interval = 30; // Interval in minutes
        $start = new DateTime('00:00');
        $end = new DateTime('24:00');
        $end = $end->modify('+1 second'); // to include the end time
    
        $times = [];
        while ($start < $end) {
            $times[$start->getTimestamp()] = $start->format( get_option( 'time_format' ) );
            $start->add(new DateInterval('PT' . $interval . 'M'));
        }
        ob_start();
        ?>
            <select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>">
                <option value="">Select Time</option>
                <?php foreach ($times as $timestamp => $time) { ?>
                    <option value="<?php echo esc_attr( $timestamp ); ?>" <?php selected( $timestamp, $selected, true ); ?>><?php echo esc_html( $time ); ?></option>
                <?php } ?>
            </select>
        <?php
        return ob_get_clean();
    }

    /**
	 * Process and save meta box data.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id the post ID
	 * @param \WP_Post $post the post object
	 */
	public function save_post( $post_id, \WP_Post $post ) {

        $get_nonce = filter_input( INPUT_POST, $this->get_nonce_name(), FILTER_SANITIZE_FULL_SPECIAL_CHARS );

        // check nonce
		if ( ! isset( $get_nonce ) || ! wp_verify_nonce( $get_nonce, $this->get_nonce_action() ) ) {
			return;
		}

		// if this is an autosave, our form has not been submitted, so we don't want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
        
		// bail out if not a supported post type
		if ( ! in_array( $post->post_type, $this->screens, true ) ) {
			return;
		}

        if ( ! current_user_can( 'manage_woocommerce_wc_conditional_fees' ) ) {
			return;
		}

        // Avoid infinite loop
        remove_action( 'save_post', 'save_post', 10 );

        // Check if the fee title is exist with same name because fee title must be unique to apply on frontend side.
        if( dsaefw()->dsaefw_admin_object()->dsaefw_check_duplicate_fee_title( $post_id, $post ) ) {

            // Delete the post to prevent saving
            wp_delete_post( $post_id, true );

            // Modify the redirect URL to show up listing page
            add_filter('redirect_post_location', function($location) {
                $location = add_query_arg('post_type', DSAEFW_FEE_POST_TYPE, admin_url( 'edit.php' ) );
                return $location;
            });
            
            // tell the user there were no Fees to export matching the criteria
            dsaefw()->add_admin_notice( 'fee_export_error', 'error', sprintf( esc_html__( 'A post with "%s" already exists. Please choose a different title.', 'advanced-extra-fees-woocommerce' ), $post->post_title ) );

            return;
        }

        // Re-attach the action for future saves
        add_action( 'save_post', 'save_post', 10, 2 );

        $advance_fee = new \Advanced_Extra_Fees_Woocommerce_Fee( $post );

        $get_fee_settings_select_fee_type       = filter_input( INPUT_POST, 'fee_settings_select_fee_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $get_fees_on_cart_total                 = filter_input( INPUT_POST, 'fees_on_cart_total', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $get_fee_settings_product_cost          = filter_input( INPUT_POST, 'fee_settings_product_cost', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $get_fee_chk_qty_price                  = filter_input( INPUT_POST, 'fee_chk_qty_price', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $get_fee_per_qty                        = filter_input( INPUT_POST, 'fee_per_qty', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $get_extra_product_cost                 = filter_input( INPUT_POST, 'extra_product_cost', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

        $get_is_allow_custom_weight_base        = filter_input( INPUT_POST, 'is_allow_custom_weight_base', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $get_sm_custom_weight_base_cost         = filter_input( INPUT_POST, 'sm_custom_weight_base_cost', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $get_sm_custom_weight_base_per_each     = filter_input( INPUT_POST, 'sm_custom_weight_base_per_each', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $get_sm_custom_weight_base_over         = filter_input( INPUT_POST, 'sm_custom_weight_base_over', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

        $get_dsaefw_tooltip_description         = filter_input( INPUT_POST, 'dsaefw_tooltip_description', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $get_fee_settings_select_taxable        = filter_input( INPUT_POST, 'fee_settings_select_taxable', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        
        //Advanced Section
        $get_first_order_for_user               = filter_input( INPUT_POST, 'first_order_for_user', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $get_fee_settings_recurring             = filter_input( INPUT_POST, 'fee_settings_recurring', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $get_fee_show_on_checkout_only          = filter_input( INPUT_POST, 'fee_show_on_checkout_only', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $get_ds_select_day_of_week              = filter_input( INPUT_POST, 'ds_select_day_of_week', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY );
        $get_fee_settings_start_date            = filter_input( INPUT_POST, 'fee_settings_start_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $get_fee_settings_end_date              = filter_input( INPUT_POST, 'fee_settings_end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $get_ds_time_from                       = filter_input( INPUT_POST, 'ds_time_from', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $get_ds_time_to                         = filter_input( INPUT_POST, 'ds_time_to', FILTER_SANITIZE_FULL_SPECIAL_CHARS );


        $fee_settings_select_fee_type       = !empty( $get_fee_settings_select_fee_type ) ? sanitize_text_field( $get_fee_settings_select_fee_type ) : '';
        $fees_on_cart_total                 = !empty( $get_fees_on_cart_total ) ? sanitize_text_field( $get_fees_on_cart_total ) : 'no';
        $fee_settings_product_cost          = !empty( $get_fee_settings_product_cost ) ? sanitize_text_field( $get_fee_settings_product_cost ) : 0;
        $fee_chk_qty_price                  = !empty( $get_fee_chk_qty_price ) ? sanitize_text_field( $get_fee_chk_qty_price ) : 'no';
        $fee_per_qty                        = !empty( $get_fee_per_qty ) ? sanitize_text_field( $get_fee_per_qty ) : 'qty_cart_based';
        $extra_product_cost                 = !empty( $get_extra_product_cost ) ? floatval( $get_extra_product_cost ) : 0;

        $is_allow_custom_weight_base        = !empty( $get_is_allow_custom_weight_base ) ? sanitize_text_field( $get_is_allow_custom_weight_base ) : 'no';
        $sm_custom_weight_base_cost         = !empty( $get_sm_custom_weight_base_cost ) ? sanitize_text_field( $get_sm_custom_weight_base_cost ) : '';
        $sm_custom_weight_base_per_each     = !empty( $get_sm_custom_weight_base_per_each ) ? sanitize_text_field( $get_sm_custom_weight_base_per_each ) : '';
        $sm_custom_weight_base_over         = !empty( $get_sm_custom_weight_base_over ) ? sanitize_text_field( $get_sm_custom_weight_base_over ) : '';

        $dsaefw_tooltip_description         = !empty( $get_dsaefw_tooltip_description ) ? substr( htmlspecialchars_decode( $get_dsaefw_tooltip_description, ENT_QUOTES ), 0, apply_filters( 'dsaefw_set_fee_tooltip_maxlength', DSAEFW_TOOLTIP_LENGTH ) ) : '';
        $fee_settings_select_taxable        = !empty( $get_fee_settings_select_taxable ) ? sanitize_text_field( $get_fee_settings_select_taxable ) : 'no';

        //Advanced Section
        $first_order_for_user               = isset( $get_first_order_for_user ) ? sanitize_text_field( $get_first_order_for_user ) : 'no';
        $fee_settings_recurring             = isset( $get_fee_settings_recurring ) ? sanitize_text_field( $get_fee_settings_recurring ) : 'no';
        $fee_show_on_checkout_only          = isset( $get_fee_show_on_checkout_only ) ? sanitize_text_field( $get_fee_show_on_checkout_only ) : 'no';
        $ds_select_day_of_week              = isset( $get_ds_select_day_of_week ) ? array_map( 'sanitize_text_field', $get_ds_select_day_of_week ) : array();
        $fee_settings_start_date            = isset( $get_fee_settings_start_date ) ? sanitize_text_field( $get_fee_settings_start_date ) : '';
        $fee_settings_end_date              = isset( $get_fee_settings_end_date ) ? sanitize_text_field( $get_fee_settings_end_date ) : '';
        $ds_time_from                       = isset( $get_ds_time_from ) ? sanitize_text_field( $get_ds_time_from ) : '';
        $ds_time_to                         = isset( $get_ds_time_to ) ? sanitize_text_field( $get_ds_time_to ) : '';


        $advance_fee->set_fee_type( $fee_settings_select_fee_type );
        $advance_fee->set_fees_on_cart_total( $fees_on_cart_total );
        $advance_fee->set_fee_settings_product_cost( $fee_settings_product_cost );
        $advance_fee->set_fee_chk_qty_price( $fee_chk_qty_price );
        $advance_fee->set_fee_per_qty( $fee_per_qty );
        $advance_fee->set_extra_product_cost( $extra_product_cost );

        $advance_fee->set_is_allow_custom_weight_base( $is_allow_custom_weight_base );
        $advance_fee->set_sm_custom_weight_base_cost( $sm_custom_weight_base_cost );
        $advance_fee->set_sm_custom_weight_base_per_each( $sm_custom_weight_base_per_each );
        $advance_fee->set_sm_custom_weight_base_over( $sm_custom_weight_base_over );

        $advance_fee->set_dsaefw_tooltip_description( $dsaefw_tooltip_description );
        $advance_fee->set_fee_settings_select_taxable( $fee_settings_select_taxable );
        
        //Advanced Section
        $advance_fee->set_first_order_for_user( $first_order_for_user );
        $advance_fee->set_fee_settings_recurring( $fee_settings_recurring );
        $advance_fee->set_fee_show_on_checkout_only( $fee_show_on_checkout_only );
        $advance_fee->set_ds_select_day_of_week( $ds_select_day_of_week );
        $advance_fee->set_fee_settings_start_date( $fee_settings_start_date );
        $advance_fee->set_fee_settings_end_date( $fee_settings_end_date );
        $advance_fee->set_ds_time_from( $ds_time_from );
        $advance_fee->set_ds_time_to( $ds_time_to );
        
        // Reset transient for updated fees
        delete_transient( 'dsaefw_get_all_fees' );
    }
}