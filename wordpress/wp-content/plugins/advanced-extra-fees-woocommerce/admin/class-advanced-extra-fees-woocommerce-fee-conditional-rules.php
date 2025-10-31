<?php 
/**
 * WooCommerce Advanced Extra Fees lisings
 *
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'Advanced_Extra_Fees_Woocommerce_Admin_Fee_Conditional_Rules', false ) ) {
	return new Advanced_Extra_Fees_Woocommerce_Admin_Fee_Conditional_Rules();
}

/**
 * Advanced_Extra_Fees_Woocommerce_Admin_Fee_Conditional_Rules.
 */
#[\AllowDynamicProperties]
class Advanced_Extra_Fees_Woocommerce_Admin_Fee_Conditional_Rules {

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

        $this->id       = 'dsaefw-fee-conditional-rules';
		$this->priority = 'high';
		$this->screens  = array( DSAEFW_FEE_POST_TYPE );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        // add/edit screen hooks
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

        // update meta box data when saving post
        add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
    }


    public function enqueue_scripts(){

        global $wp_roles;

        wp_enqueue_script( 'dsaefw-acr', plugin_dir_url( __FILE__ ) . 'js/dsaefw-admin-conditional-rules.js', array( 'jquery', 'jquery-tiptip', 'jquery-ui-datepicker', 'advanced-extra-fees-woocommerce' ), false, false );
        wp_localize_script( 'dsaefw-acr', 'dsaefw_acr_vars', array(
                'ajax_url'                                  => admin_url( 'admin-ajax.php' ),
                'currency_symbol'	                        => esc_attr( get_woocommerce_currency_symbol() ),
                'dsaefw_filter_conditions'                  => $this->dsaefw_conditions_list_action(),
                'dsaefw_filter_action'                      => $this->dsaefw_operator_list_action('quantity'), // here we need to pass all values
                'dsaefw_country_data'                       => WC()->countries->get_allowed_countries(),
                'dsaefw_state_data'                         => WC()->countries->get_states(),
                'dsaefw_zone_data'                          => wp_list_pluck( WC_Shipping_Zones::get_zones(), 'zone_name', 'zone_id' ),
                'dsaefw_coupon_data'                        => wp_list_pluck( $this->dsaefw_coupon_data(), 'post_title', 'ID' ), 
                'dsaefw_shipping_class_data'                => wp_list_pluck( WC()->shipping->get_shipping_classes(), 'name', 'term_id' ), 
                'dsaefw_payment_data'                       => wp_list_pluck( WC()->payment_gateways->get_available_payment_gateways(), 'title', 'id' ), 
                'dsaefw_shipping_method_data'               => $this->dsaefw_shipping_method_data(),
                'dsaefw_user_role_data'                     => array_map( function($role) { return $role['name']; }, $wp_roles->roles ),
                'select2_country_placeholder'               => esc_html__( 'Select countries...', 'advanced-extra-fees-woocommerce' ),
                'select2_state_placeholder'                 => esc_html__( 'Select states...', 'advanced-extra-fees-woocommerce' ),
                'textarea_city_placeholder'                 => esc_html__( 'Write down each city name in a new line...', 'advanced-extra-fees-woocommerce' ),
                'textarea_postcode_placeholder'             => esc_html__( 'Write down each postcode/zip code in a new line...', 'advanced-extra-fees-woocommerce' ),
                'select2_zone_placeholder'                  => esc_html__( 'Select zones...', 'advanced-extra-fees-woocommerce' ),
                'select2_product_placeholder'               => esc_html__( 'Search for a product...', 'advanced-extra-fees-woocommerce' ),
                'select2_category_placeholder'              => esc_html__( 'Search for a category...', 'advanced-extra-fees-woocommerce' ),
                'select2_tag_placeholder'                   => esc_html__( 'Search for a tag...', 'advanced-extra-fees-woocommerce' ),
                'select2_user_placeholder'                  => esc_html__( 'Search for users...', 'advanced-extra-fees-woocommerce' ),
                'select2_user_role_placeholder'             => esc_html__( 'Select user roles...', 'advanced-extra-fees-woocommerce' ),
                'input_product_qty_placeholder'             => esc_html__( 'Enter product\'s quantity...', 'advanced-extra-fees-woocommerce' ),
                'input_cart_total_placeholder'              => esc_html__( 'Enter cart subtotal amount...', 'advanced-extra-fees-woocommerce' ),
                'input_cart_totalafter_placeholder'         => esc_html__( 'Enter after discount cart subtotal amount...', 'advanced-extra-fees-woocommerce' ),
                'input_cart_productspecific_placeholder'    => esc_html__( 'Enter product specific cart subtotal amount...', 'advanced-extra-fees-woocommerce' ),
                'input_quantity_placeholder'                => esc_html__( 'Enter quantity...', 'advanced-extra-fees-woocommerce' ),
                'input_weight_placeholder'                  => esc_html__( 'Enter total cart weight...', 'advanced-extra-fees-woocommerce' ),
                'select2_coupon_placeholder'                => esc_html__( 'Select coupons...', 'advanced-extra-fees-woocommerce' ),
                'select2_shipping_class_placeholder'        => esc_html__( 'Select shipping classs...', 'advanced-extra-fees-woocommerce' ),
                'select2_payment_placeholder'               => esc_html__( 'Select payment gateways...', 'advanced-extra-fees-woocommerce' ),
                'select2_shipping_method_placeholder'       => esc_html__( 'Select shipping methods...', 'advanced-extra-fees-woocommerce' ),
                'select2_product_attribute_placeholder'     => esc_html__( 'Select %s terms...', 'advanced-extra-fees-woocommerce' ),
                'dsaefw_pa_placeholder_labels'              => $this->dsaefw_product_attributes_labels_with_slugs(),
                'dsaefw_filter_delete_title'                => esc_html__( 'Delete', 'advanced-extra-fees-woocommerce' ), 
                'dsaefw_all_coupon_title'                   => esc_html__( 'All Coupons', 'advanced-extra-fees-woocommerce' ),
                'dsaefw_note_title'                         => esc_html__( 'Note', 'advanced-extra-fees-woocommerce' ),
                'dsaefw_note_link_title'                    => esc_html__( 'Click here', 'advanced-extra-fees-woocommerce' ),
                'dsaefw_note_product_qty'                   => esc_html__( 'This rule will only work if you have selected any one Product Specific option.', 'advanced-extra-fees-woocommerce' ),
                'dsaefw_note_product_qty_url'               => esc_url( 'https://docs.thedotstore.com/article/726-product-specific-fee-rules' ),
                'dsaefw_note_cart_totalafter'               => esc_html__( 'This rule will apply when you would apply coupon in front side.', 'advanced-extra-fees-woocommerce' ),
                'dsaefw_note_cart_totalafter_url'           => esc_url( 'https://docs.thedotstore.com/article/209-how-to-add-fee-based-on-after-discount-rule' ),
                'dsaefw_note_cart_productspecific'          => esc_html__( 'This rule will apply when you would add cart contain product.', 'advanced-extra-fees-woocommerce' ),
                'dsaefw_note_cart_productspecific_url'      => esc_url( 'https://docs.thedotstore.com/article/438-how-to-add-extra-fees-based-on-specific-product-subtotal-range' ),
            )
        );
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
			esc_html__( 'Conditional Fee Rules', 'advanced-extra-fees-woocommerce' ),
			array( $this, 'do_output' ),
			$current_screen->id,
			$this->context,
			$this->priority
		);

		add_filter( "postbox_classes_{$current_screen->id}_{$this->id}", array( $this, 'postbox_classes' ) );
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
	 * Output basic meta box contents.
	 *
	 * @since 1.0.0
	 */
	public function do_output() {
		global $post;

		$this->advance_fee = new \Advanced_Extra_Fees_Woocommerce_Fee( $post );

        $cost_rule_match             = $this->advance_fee->get_cost_rule_match( 'general_rule_match' );
        $dsaefw_product_fees_metabox = $this->advance_fee->get_product_fees_metabox();

        // add a nonce field
        wp_nonce_field( $this->get_nonce_action(), $this->get_nonce_name() );
        ?>
        <div id="dsaefw-condditional-rules" class="panel woocommerce_options_panel">
            <div class="dsaefw-condditional-rules-header">
                <a href="javascript:void(0);" id="dsaefw-add-rule" class="button add-button">+ <?php esc_html_e( 'Add Rule', 'advanced-extra-fees-woocommerce' ); ?></a> 
                <select
                    name="cost_rule_match[general_rule_match]"
                    id="general_rule_match">
                    <option value="any" <?php selected( 'any', $cost_rule_match, true ); ?>><?php esc_html_e( 'Any Rule match',  'advanced-extra-fees-woocommerce' ); ?></option>
                    <option value="all" <?php selected( 'all', $cost_rule_match, true ); ?>><?php esc_html_e( 'All Rule match', 'advanced-extra-fees-woocommerce' ); ?></option>
                </select>
            </div>
            <div class="dsaefw-condditional-rules-content">
                <?php $i = 0; ?>
                <table id="dsaefw-rules" class="table-outer">
                    <tbody>
                        <?php 
                        $empty_class = 'dsaefw-no-filter-tr-show';
                        if ( isset( $dsaefw_product_fees_metabox ) && ! empty( $dsaefw_product_fees_metabox ) ) { 
                            foreach ( $dsaefw_product_fees_metabox as $condition_value ) {
                                $fees_conditions = isset( $condition_value['product_fees_conditions_condition'] ) ? $condition_value['product_fees_conditions_condition'] : '';
                                $condition_is    = isset( $condition_value['product_fees_conditions_is'] ) ? $condition_value['product_fees_conditions_is'] : '';
                                $condtion_value  = isset( $condition_value['product_fees_conditions_values'] ) ? $condition_value['product_fees_conditions_values'] : array();
                                ?>
                                <tr id="row_<?php echo intval($i); ?>" valign="top">
                                    <td class="titledesc th_product_fees_conditions_condition" scope="row">
                                        <select rel-id="<?php echo intval($i); ?>" id="product_fees_conditions_condition_<?php echo intval($i); ?>" name="fees[product_fees_conditions_condition][]"  class="product_fees_conditions_condition">
                                            <?php
                                            $condition_spe = $this->dsaefw_conditions_list_action();
                                            foreach ( $condition_spe as $optg_key => $opt_data ) {
                                                ?>
                                                <optgroup label="<?php echo esc_attr( $optg_key ); ?>">
                                                    <?php
                                                    foreach ( $opt_data as $opt_key => $opt_value ) { ?>
                                                        <option value="<?php echo esc_attr( $opt_key ); ?>" <?php selected( $fees_conditions, $opt_key, true ); ?>><?php echo esc_html( $opt_value ); ?></option>
                                                        <?php
                                                    } ?>
                                                </optgroup>
                                                <?php 
                                            } ?>
                                        </select>
                                    </td>
                                    <td class="select_condition_for_in_notin">
                                        <?php 
                                        $opr_spe = $this->dsaefw_operator_list_action(); 
                                        if( isset($opr_spe[$fees_conditions]) ) { ?>
                                            <select name="fees[product_fees_conditions_is][]" class="product_fees_conditions_is_<?php echo intval($i); ?>">
                                                <?php foreach ( $opr_spe[$fees_conditions] as $opr_key => $opr_value ) { ?>
                                                    <option value="<?php echo esc_attr( $opr_key ); ?>" <?php selected( $condition_is, $opr_key, true ); ?>><?php echo esc_html( $opr_value ); ?></option>
                                                <?php } ?>
                                            </select>
                                        <?php } ?>
                                    </td>
                                    <td id="column_<?php echo intval($i); ?>" class="condition-value">
                                        <?php 
                                        $panel = "dsaefw_get_{$fees_conditions}_list";
                                        if ( method_exists( $this, $panel ) ) {
                                            $this->$panel( $i, $condtion_value );
                                        } else {
                                            $this->dsaefw_get_product_attributes_list( $i, $fees_conditions, $condtion_value );
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <a rel-id="<?php echo intval($i); ?>" class="dsaefw-delete-rule" href="javascript:void(0);" title="Delete">
                                            <i class="dashicons dashicons-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php
                                $i++;
                                $empty_class = 'dsaefw-no-filter-tr-hide';
                            }
                        } ?>
                        <tr class="dsaefw-no-filter-tr <?php echo esc_attr($empty_class); ?>" >
                            <td colspan="4">
                                <span class="dsaefw-no-filter-text"><?php esc_html_e( 'This fee will apply on all products.','advanced-extra-fees-woocommerce' ); ?></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <input type="hidden" name="total_row" id="total_row" value="<?php echo esc_attr( $i ); ?>">
            </div>
        </div>
        <?php
    }

    /**
	 * List of conditions
	 *
	 * @return array $final_data
	 *
	 * @since 1.0.0
	 */
	public function dsaefw_conditions_list_action() {
        $final_data = array(
			'Location Specific'  => $this->dsaefw_location_specific_action(),
			'Product Specific'   => $this->dsaefw_product_specific_action(),
			'Attribute Specific' => $this->dsaefw_attribute_specific_action(),
			'User Specific'      => $this->dsaefw_user_specific_action(),
			'Cart Specific'      => $this->dsaefw_cart_specific_action(),
			'Payment Specific'   => $this->dsaefw_payment_specific_action(),
			'Shipping Specific'  => $this->dsaefw_shipping_specific_action(),
		);
		
		return $final_data;
	}

    /**
	 * List of Location specific conditions.
	 *
	 * @return array $location_specific
	 *
	 * @since 1.0.0
     * 
	 */
	public function dsaefw_location_specific_action() {
        $location_specific = array(
            'country'  => esc_html__( 'Country', 'advanced-extra-fees-woocommerce' ),
            'state'    => esc_html__( 'State', 'advanced-extra-fees-woocommerce' ),
            'city'     => esc_html__( 'City', 'advanced-extra-fees-woocommerce' ),
            'postcode' => esc_html__( 'Postcode', 'advanced-extra-fees-woocommerce' ),
            'zone'     => esc_html__( 'Zone', 'advanced-extra-fees-woocommerce' ),
        );
        return $location_specific;
    }

    /**
	 * List of Product specific conditions.
	 *
	 * @return array $product_specific
	 *
	 * @since 1.0.0
     * 
	 */
	public function dsaefw_product_specific_action() {
		$product_specific = array(
			'product'       => esc_html__( 'Cart contains product', 'advanced-extra-fees-woocommerce' ),
			'category'      => esc_html__( 'Cart contains category\'s product', 'advanced-extra-fees-woocommerce' ),
            'tag'           => esc_html__( 'Cart contains tag\'s product', 'advanced-extra-fees-woocommerce' ),
            'product_qty'   => esc_html__( 'Cart contains product\'s quantity', 'advanced-extra-fees-woocommerce' ),
		);
        return $product_specific;
    }

    /**
	 * List of Product Attributes specific conditions.
	 *
	 * @return array $attribute_specific
	 *
	 * @since 1.0.0
     * 
	 */
    public function dsaefw_attribute_specific_action() {
        $attribute_taxonomies   = wc_get_attribute_taxonomies();
		$attribute_specific     = array();
		foreach ( $attribute_taxonomies as $attribute ) {
			$att_label  = $attribute->attribute_label;
			$att_name   = wc_attribute_taxonomy_name( $attribute->attribute_name );
            $attribute_specific[ $att_name ] = $att_label;
		};

		return $attribute_specific;
    }

    /**
	 * List of Product Attributes specific conditions.
	 *
	 * @return array $user_specific
	 *
	 * @since 1.0.0
     * 
	 */
    public function dsaefw_user_specific_action() {
        $user_specific = array(
			'user'      => esc_html__( 'User', 'advanced-extra-fees-woocommerce' ),
            'user_role' => esc_html__( 'User Role', 'advanced-extra-fees-woocommerce' ),
        );
		return $user_specific;
    }

    /**
	 * List of Cart specific conditions.
	 *
	 * @return array $cart_specific
	 *
	 * @since 1.0.0
     * 
	 */
    public function dsaefw_cart_specific_action() {
        $cart_specific = array(
			'cart_total'            => esc_html__( 'Cart Subtotal (Before Discount)', 'advanced-extra-fees-woocommerce' ),
            'cart_totalafter' 		=> esc_html__( 'Cart Subtotal (After Discount)', 'advanced-extra-fees-woocommerce' ),
            'cart_productspecific' 	=> esc_html__( 'Cart Subtotal (Product Specific)', 'advanced-extra-fees-woocommerce' ),
			'quantity'              => esc_html__( 'Quantity', 'advanced-extra-fees-woocommerce' ),
            'weight'          		=> esc_html__( 'Weight', 'advanced-extra-fees-woocommerce' ),
            'coupon'          		=> esc_html__( 'Coupon', 'advanced-extra-fees-woocommerce' ),
            'shipping_class'  		=> esc_html__( 'Shipping Class', 'advanced-extra-fees-woocommerce' ),
		);

        return $cart_specific;
    }

    /**
	 * List of Payment specific conditions.
	 *
	 * @return array $payment_specific
	 *
	 * @since 1.0.0
     * 
	 */
    public function dsaefw_payment_specific_action() {
        $payment_specific = array(
            'payment' => esc_html__( 'Payment Gateway', 'advanced-extra-fees-woocommerce' )
        );
        return $payment_specific;
    }

    /**
	 * List of Shipping specific conditions.
	 *
	 * @return array $shipping_specific
	 *
	 * @since 1.0.0
     * 
	 */
    public function dsaefw_shipping_specific_action() {
        $shipping_specific = array(
            'shipping_method' => esc_html__( 'Shipping Method', 'advanced-extra-fees-woocommerce' )
        );
        return $shipping_specific;
    }

    /**
	 * List of Operator
	 *
	 * @return array $final_data
	 *
	 * @since 1.0.0
	 */
	public function dsaefw_operator_list_action() {

        $operator_list = array();

        foreach ( $this->dsaefw_conditions_list_action() as $value ) {
            foreach ( array_keys($value) as $key ) {
                switch ( $key ) {
                    case 'product_qty':
                    case 'cart_total':
                    case 'cart_totalafter':
                    case 'cart_productspecific':
                    case 'quantity':
                    case 'weight':
                        $operator_list[$key] = array(
                            'is_equal_to'       => esc_html__( 'Equal to ( = )', 'advanced-extra-fees-woocommerce' ),
                            'not_in'            => esc_html__( 'Not Equal to ( != )', 'advanced-extra-fees-woocommerce' ),
                            'less_equal_to'     => esc_html__( 'Less or Equal to ( <= )', 'advanced-extra-fees-woocommerce' ),
                            'less_then'         => esc_html__( 'Less than ( < )', 'advanced-extra-fees-woocommerce' ),
                            'greater_equal_to'  => esc_html__( 'Greater or Equal to ( >= )', 'advanced-extra-fees-woocommerce' ),
                            'greater_then'      => esc_html__( 'Greater than ( > )', 'advanced-extra-fees-woocommerce' ),
                        );
                        break;
                    default:
                        $operator_list[$key] = array(
                            'is_equal_to'       => esc_html__( 'Equal to ( = )', 'advanced-extra-fees-woocommerce' ),
                            'not_in'            => esc_html__( 'Not Equal to ( != )', 'advanced-extra-fees-woocommerce' ),
                        );
                        break;
                }
            }
        }
       
		return $operator_list;
	}

    /**
	 * Get HTML for country list with select2 dropdown.
	 *
	 * @param string $count
	 * @param array  $selected
	 *
	 * @since 1.0.0
	 *
	 */
	public function dsaefw_get_country_list( $count = '', $selected = array() ) {
		$getCountries  = WC()->countries->get_allowed_countries();
        ?>
		<select 
            id="country-filter-<?php echo esc_attr( $count ); ?>"
            name="fees[product_fees_conditions_values][value_<?php echo esc_attr( $count ); ?>][]" 
            class="ds-select" 
            data-sortable="true" 
            data-placeholder="<?php esc_attr_e( 'Select country(ies)...', 'advanced-extra-fees-woocommerce' ); ?>" 
            multiple="multiple"
            data-allow_clear="true"
            data-width="100%">
		    <?php 
                if ( isset( $getCountries ) && !empty( $getCountries ) ) {
                    foreach ( $getCountries as $code => $country ) {
                        $selectedVal = is_array( $selected ) && ! empty( $selected ) && in_array( $code, $selected, true ) ? 'selected=selected' : '';
                        ?>
                        <option value="<?php echo esc_attr( $code ); ?>"<?php echo esc_attr( $selectedVal ); ?>><?php echo esc_html( $country ); ?></option>
                        <?php
                    }
                }
            ?>
        </select>
        <?php
	}

    /**
	 * Get HTML for state list with select2 dropdown.
	 *
	 * @param string $count
	 * @param array  $selected
	 *
	 * @since 1.0.0
	 *
	 */
	public function dsaefw_get_state_list( $count = '', $selected = array() ) {
        $getCountries   = WC()->countries->get_allowed_countries();
        ?>
		<select 
            id="state-filter-<?php echo esc_attr( $count ); ?>"
            name="fees[product_fees_conditions_values][value_<?php echo esc_attr( $count ); ?>][]" 
            class="ds-select" 
            data-sortable="true" 
            data-placeholder="<?php esc_attr_e( 'Select states...', 'advanced-extra-fees-woocommerce' ); ?>" 
            multiple="multiple"
            data-allow_clear="true"
            data-width="100%">
		    <?php 
            if ( isset( $getCountries ) && !empty( $getCountries ) ) {
                foreach ( $getCountries as $country_code => $country_name ) {
                    $getStates      = WC()->countries->get_states( $country_code );
                    if ( isset( $getStates ) && !empty( $getStates ) ) {
                        foreach ( $getStates as $state_code => $state_name ) {
                            $selectedVal = is_array( $selected ) && ! empty( $selected ) && in_array( esc_attr( $country_code . ':' . $state_code ), $selected, true ) ? 'selected=selected' : '';
                            ?>
                            <option value="<?php echo esc_attr( esc_attr( $country_code . ':' . $state_code ) ); ?>"<?php echo esc_attr( $selectedVal ); ?>><?php echo esc_html( $country_name . ' -> ' . $state_name ); ?></option>
                            <?php
                        }
                    }
                }
            }
            ?>
        </select>
        <?php
	}

    /**
	 * Get HTML for city field.
	 *
	 * @param string $count
	 * @param string $selected
	 *
	 * @since 1.0.0
	 *
	 */
	public function dsaefw_get_city_list( $count = '', $selected = null ) {
        // Convert single element from array to strings
        if( is_array( $selected ) ) {
            $selected = implode( '\n', $selected );
        }
        ?>
        <textarea 
            id="city-filter-<?php echo esc_attr( $count ); ?>" 
            name = "fees[product_fees_conditions_values][value_<?php echo esc_attr( $count ); ?>]"
            placeholder="<?php esc_attr_e( 'Write down each city name in a new line...', 'advanced-extra-fees-woocommerce' ); ?>" ><?php echo esc_textarea( $selected ); ?></textarea>
        <?php
	}

    /**
	 * Get HTML for postcode/zipcode field
	 *
	 * @param string $count
	 * @param string $selected
	 *
	 * @since 1.0.0
	 *
	 */
	public function dsaefw_get_postcode_list( $count = '', $selected = null ) {
        // Convert single element from array to strings
        if( is_array( $selected ) ) {
            $selected = implode( '\n', $selected );
        }
        ?>
        <textarea 
            id="postcode-filter-<?php echo esc_attr( $count ); ?>" 
            name = "fees[product_fees_conditions_values][value_<?php echo esc_attr( $count ); ?>]"
            placeholder="<?php esc_attr_e( 'Write down each postcode/zip code in a new line...', 'advanced-extra-fees-woocommerce' ); ?>" ><?php echo esc_textarea( $selected ); ?></textarea>
        <?php
	}

     /**
	 * Get HTML for zone list with select2 dropdown.
	 *
	 * @param string $count
	 * @param array  $selected
	 *
	 * @since 1.0.0
	 *
	 */
	public function dsaefw_get_zone_list( $count = '', $selected = array() ) {
		$getZones   = WC_Shipping_Zones::get_zones();
        $selected   = array_map( 'intval', $selected );
        ?>
		<select 
            id="zone-filter-<?php echo esc_attr( $count ); ?>"
            name="fees[product_fees_conditions_values][value_<?php echo esc_attr( $count ); ?>][]" 
            class="ds-select" 
            data-sortable="true" 
            data-placeholder="<?php esc_attr_e( 'Select zones...', 'advanced-extra-fees-woocommerce' ); ?>" 
            multiple="multiple"
            data-allow_clear="true"
            data-width="100%">
		    <?php 
                if ( isset( $getZones ) && !empty( $getZones ) ) {
                    foreach ( $getZones as $zone ) {
                        $zone_id = absint( $zone['zone_id'] );
                        $selectedVal = is_array( $selected ) && ! empty( $selected ) && in_array( $zone_id, $selected, true ) ? 'selected=selected' : '';
                        ?>
                        <option value="<?php echo esc_attr( $zone_id ); ?>"<?php echo esc_attr( $selectedVal ); ?>><?php echo esc_html( $zone['zone_name'] ); ?></option>
                        <?php
                    }
                }
            ?>
        </select>
        <?php
	}

    /**
	 * Get product HTML for filter use with select2 dropdown.
	 *
	 * @param string $count
	 * @param array  $selected
	 *
	 * @since 1.0.0
	 */
	public function dsaefw_get_product_list( $count = '', $selected = array() ) {
		?>
		<select 
            id="product-filter-<?php echo esc_attr( $count ); ?>" 
            name="fees[product_fees_conditions_values][value_<?php echo esc_attr( $count ); ?>][]" 
            class="ds-woo-search" 
            data-placeholder="<?php esc_attr_e( 'Search for a product...', 'advanced-extra-fees-woocommerce' ); ?>" 
            data-sortable="true" 
            multiple="multiple" 
            data-allow_clear="true"
            data-width="100%">
        <?php 
        if( !empty( $selected ) ) {
            $args = array( 'include' => $selected, 'type' => array( 'simple', 'variation') );
            $products = wc_get_products($args);
            if ( $products ) {
                foreach ( $products as $product ) { ?>
                    <option value="<?php echo intval($product->get_id()); ?>" selected="selected"><?php echo wp_kses_post( $product->get_formatted_name() ); ?></option>
                    <?php 
                }
            } 
        } ?>
		</select>
        <?php
	}

    /**
     * Get category HTML for filter use with select2 dropdown.
     * 
     * @param int $count
     * @param array $selected
     * 
     * @since 1.0.0
     */
    public function dsaefw_get_category_list( $count = '', $selected = array() ) {
        if( !empty( $selected ) ) {
            $args = array( 
                'taxonomy'  => 'product_cat',
                'include'   => $selected,
                'hide_empty' => false,
            );
            $categories = get_terms($args);
        }
        ?>
        <select 
            id="category-filter-<?php echo esc_attr( $count ); ?>"
            name="fees[product_fees_conditions_values][value_<?php echo esc_attr( $count ); ?>][]" 
            class="ds-woo-search" 
            data-placeholder="<?php esc_attr_e( 'Search for a category...', 'advanced-extra-fees-woocommerce' ); ?>" 
            data-sortable="true" 
            multiple="multiple" 
            data-allow_clear="true"
            data-width="100%" 
            data-action="dsaefw_json_search_categories">
        <?php 
        if ( $categories ) {
            foreach ( $categories as $category ) { 
                $show_cat_acenctors = implode( " > ", wp_list_pluck( array_reverse( dsaefw()->dsaefw_admin_object()->get_parent_terms( $category ) ), 'name' ) ); ?>
                <option value="<?php echo intval( $category->term_id ); ?>" selected="selected"><?php echo wp_kses_post( $show_cat_acenctors ); ?></option>
                <?php 
            }
        } ?>
        </select>
        <?php
    }

    /**
     * Get tag HTML for filter use with select2 dropdown.
     * 
     * @param int $count
     * @param array $selected
     * 
     * @since 1.0.0
     */
    public function dsaefw_get_tag_list( $count = '', $selected = array() ) {
        ?>
        <select 
            id="tag-filter-<?php echo esc_attr( $count ); ?>"
            name="fees[product_fees_conditions_values][value_<?php echo esc_attr( $count ); ?>][]" 
            class="ds-woo-search" 
            data-placeholder="<?php esc_attr_e( 'Search for a tag...', 'advanced-extra-fees-woocommerce' ); ?>" 
            data-sortable="true" 
            multiple="multiple" 
            data-allow_clear="true"
            data-width="100%" 
            data-action="dsaefw_json_search_tags">
        <?php 
        if( !empty( $selected ) ) {
            $args = array( 
                'taxonomy'  => 'product_tag',
                'include'   => $selected,
                'hide_empty' => false,
            );
            $tags = get_terms($args);
            if ( $tags ) {
                foreach ( $tags as $tag ) { 
                    $show_tag_acenctors = implode( " > ", wp_list_pluck( array_reverse( dsaefw()->dsaefw_admin_object()->get_parent_terms( $tag ) ), 'name' ) ); ?>
                    <option value="<?php echo intval( $tag->term_id ); ?>" selected="selected"><?php echo wp_kses_post( $show_tag_acenctors ); ?></option>
                    <?php 
                }
            } 
        } ?>
        </select>
        <?php
    }

    /**
	 * Get HTML for product quantity field.
	 *
	 * @param string    $count
	 * @param int       $selected
	 *
	 * @since 1.0.0
	 *
	 */
	public function dsaefw_get_product_qty_list( $count, $selected ) {

        // Convert single element from array to number
        if( is_array( $selected ) ) {
            $selected = absint( implode( ',', $selected ) );
        } else {
            $selected = absint( $selected );
        }
        ?>
        <input 
            type="number" 
            name="fees[product_fees_conditions_values][value_<?php echo esc_attr( $count ); ?>]" 
            id="product_qty-filter-<?php echo esc_attr( $count ); ?>" 
            class="qty-class"
            placeholder="<?php esc_attr_e( 'Enter product\'s quantity...', 'advanced-extra-fees-woocommerce' ); ?>"
            value="<?php echo esc_attr( $selected ); ?>" />
        <?php echo wp_kses_post( sprintf( '<span class="dsaefw-condition-note"><strong>%s:</strong> %s<a href="%s" target="_blank">%s</a></span>',
            esc_html__( 'Note', 'advanced-extra-fees-woocommerce' ),
            esc_html__( 'This rule will only work if you have selected any one Product Specific option.', 'advanced-extra-fees-woocommerce' ),
            esc_url( 'https://docs.thedotstore.com/article/726-product-specific-fee-rules' ),
            esc_html__( 'Click here', 'advanced-extra-fees-woocommerce' )
        ) );
    }

    /**
	 * Get HTML for cart subtotal (before discount) field.
	 *
	 * @param string    $count
	 * @param int       $selected
	 *
	 * @since 1.0.0
	 *
	 */
	public function dsaefw_get_cart_total_list( $count, $selected ) {

        // Convert single element from array to number
        if( is_array( $selected ) ) {
            $selected = floatval( implode( ',', $selected ) );
        } else {
            $selected = floatval( $selected );
        }
        ?>
        <input 
            type="number" 
            name="fees[product_fees_conditions_values][value_<?php echo esc_attr( $count ); ?>]" 
            id="cart_total-filter-<?php echo esc_attr( $count ); ?>" 
            placeholder="<?php esc_attr_e( 'Enter cart subtotal amount...', 'advanced-extra-fees-woocommerce' ); ?>"
            value="<?php echo esc_attr( $selected ); ?>" 
            step="0.01" />
        <?php
    }
    
    /**
	 * Get HTML for cart subtotal (After discount) field.
	 *
	 * @param string    $count
	 * @param int       $selected
	 *
	 * @since 1.0.0
	 *
	 */
	public function dsaefw_get_cart_totalafter_list( $count, $selected ) {

        // Convert single element from array to number
        if( is_array( $selected ) ) {
            $selected = floatval( implode( ',', $selected ) );
        } else {
            $selected = floatval( $selected );
        }
        ?>
        <input 
            type="number" 
            name="fees[product_fees_conditions_values][value_<?php echo esc_attr( $count ); ?>]" 
            id="cart_totalafter-filter-<?php echo esc_attr( $count ); ?>" 
            placeholder="<?php esc_attr_e( 'Enter after discount cart subtotal amount...', 'advanced-extra-fees-woocommerce' ); ?>"
            value="<?php echo esc_attr( $selected ); ?>" 
            step="0.01" />
        <?php echo wp_kses_post( sprintf( '<span class="dsaefw-condition-note"><strong>%s:</strong> %s<a href="%s" target="_blank">%s</a></span>',
            esc_html__( 'Note', 'advanced-extra-fees-woocommerce' ),
            esc_html__( 'This rule will apply when you would apply coupon in front side.', 'advanced-extra-fees-woocommerce' ),
            esc_url( 'https://docs.thedotstore.com/article/209-how-to-add-fee-based-on-after-discount-rule' ),
            esc_html__( 'Click here', 'advanced-extra-fees-woocommerce' )
        ) );
    }

    /**
	 * Get HTML for cart subtotal (Product specific) field.
	 *
	 * @param string    $count
	 * @param int       $selected
	 *
	 * @since 1.0.0
	 *
	 */
	public function dsaefw_get_cart_productspecific_list( $count, $selected ) {

        // Convert single element from array to number
        if( is_array( $selected ) ) {
            $selected = floatval( implode( ',', $selected ) );
        } else {
            $selected = floatval( $selected );
        }
        ?>
        <input 
            type="number" 
            name="fees[product_fees_conditions_values][value_<?php echo esc_attr( $count ); ?>]" 
            id="cart_productspecific-filter-<?php echo esc_attr( $count ); ?>" 
            placeholder="<?php esc_attr_e( 'Enter product specific cart subtotal amount...', 'advanced-extra-fees-woocommerce' ); ?>"
            value="<?php echo esc_attr( $selected ); ?>" />
        <?php echo wp_kses_post( sprintf( '<span class="dsaefw-condition-note"><strong>%s:</strong> %s<a href="%s" target="_blank">%s</a></span>',
            esc_html__( 'Note', 'advanced-extra-fees-woocommerce' ),
            esc_html__( 'This rule will apply when you would add cart contain product.', 'advanced-extra-fees-woocommerce' ),
            esc_url( 'https://docs.thedotstore.com/article/438-how-to-add-extra-fees-based-on-specific-product-subtotal-range' ),
            esc_html__( 'Click here', 'advanced-extra-fees-woocommerce' )
        ) );
    }

    /**
	 * Get HTML for cart quantity field.
	 *
	 * @param string    $count
	 * @param int       $selected
	 *
	 * @since 1.0.0
	 *
	 */
	public function dsaefw_get_quantity_list( $count, $selected ) {

        // Convert single element from array to number
        if( is_array( $selected ) ) {
            $selected = absint( implode( ',', $selected ) );
        } else {
            $selected = absint( $selected );
        }
        ?>
        <input 
            type="number" 
            name="fees[product_fees_conditions_values][value_<?php echo esc_attr( $count ); ?>]" 
            id="quantity-filter-<?php echo esc_attr( $count ); ?>" 
            class="qty-class"
            placeholder="<?php esc_attr_e( 'Enter quantity...', 'advanced-extra-fees-woocommerce' ); ?>"
            value="<?php echo esc_attr( $selected ); ?>" />
        <?php
    }

    /**
	 * Get HTML for cart weight field.
	 *
	 * @param string    $count
	 * @param int       $selected
	 *
	 * @since 1.0.0
	 *
	 */
	public function dsaefw_get_weight_list( $count = '', $selected = 1 ) {

        // Convert single element from array to number
        if( is_array( $selected ) ) {
            $selected = floatval( implode( ',', $selected ) );
        } else {
            $selected = floatval( $selected );
        }
        ?>
        <input 
            type="number" 
            name="fees[product_fees_conditions_values][value_<?php echo esc_attr( $count ); ?>]" 
            id="weight-filter-<?php echo esc_attr( $count ); ?>" 
            placeholder="<?php esc_attr_e( 'Enter total cart weight...', 'advanced-extra-fees-woocommerce' ); ?>"
            value="<?php echo esc_attr( $selected ); ?>"
            step="0.01" />
        <?php 
    }

    /**
	 * Get HTML for cart weight field.
	 *
	 * @param string    $count
	 * @param int       $selected
	 *
	 * @since 1.0.0
	 *
	 */
	public function dsaefw_get_coupon_list( $count = '', $selected = array() ) {
        $getCoupons = $this->dsaefw_coupon_data();
        $selected   = array_map( 'intval', $selected );
        if ( ! empty( $getCoupons ) ) {
            ?>
            <select 
                id="coupon-filter-<?php echo esc_attr( $count ); ?>"
                name="fees[product_fees_conditions_values][value_<?php echo esc_attr( $count ); ?>][]" 
                class="ds-select" 
                data-sortable="true" 
                data-placeholder="<?php esc_attr_e( 'Select coupons...', 'advanced-extra-fees-woocommerce' ); ?>" 
                multiple="multiple"
                data-allow_clear="true"
                data-width="100%">
                <?php 
                    $selectedVal = is_array( $selected ) && ! empty( $selected ) && in_array( -1, $selected, true ) ? 'selected=selected' : '';
                    ?>
                    <option value="-1" <?php echo esc_attr( $selectedVal ); ?>><?php esc_html_e( 'All Coupons', 'advanced-extra-fees-woocommerce' ); ?></option>
                    <?php
                    if ( isset( $getCoupons ) && !empty( $getCoupons ) ) {
                        foreach ( $getCoupons as $coupon ) {
                            $coupon_id = !empty( $coupon->ID ) ? absint( $coupon->ID ) : 0;
                            $selectedVal = is_array( $selected ) && ! empty( $selected ) && in_array( $coupon_id, $selected, true ) ? 'selected=selected' : '';
                            ?>
                            <option value="<?php echo esc_attr( $coupon_id ); ?>"<?php echo esc_attr( $selectedVal ); ?>><?php echo esc_html( $coupon->post_title ); ?></option>
                            <?php
                        }
                    }
                ?>
            </select>
            <?php
        }
    }

    /**
	 * Get HTML for shipping class field.
	 *
	 * @param string    $count
	 * @param int       $selected
	 *
	 * @since 1.0.0
	 *
	 */
	public function dsaefw_get_shipping_class_list( $count = '', $selected = array() ) {
        $getShippingClasses = WC()->shipping->get_shipping_classes();
        $selected   = array_map( 'intval', $selected );
        ?>
        <select 
            id="shipping_class-filter-<?php echo esc_attr( $count ); ?>"
            name="fees[product_fees_conditions_values][value_<?php echo esc_attr( $count ); ?>][]" 
            class="ds-select" 
            data-sortable="true" 
            data-placeholder="<?php esc_attr_e( 'Select shipping classs...', 'advanced-extra-fees-woocommerce' ); ?>" 
            multiple="multiple"
            data-allow_clear="true"
            data-width="100%">
            <?php 
            if ( ! empty( $getShippingClasses ) ) {
                foreach ( $getShippingClasses as $shipping_class ) {
                    $shipping_class_id = !empty( $shipping_class->term_id ) ? absint( $shipping_class->term_id ) : 0;
                    $selectedVal = is_array( $selected ) && ! empty( $selected ) && in_array( $shipping_class_id, $selected, true ) ? 'selected=selected' : '';
                    ?>
                    <option value="<?php echo esc_attr( $shipping_class_id ); ?>"<?php echo esc_attr( $selectedVal ); ?>><?php echo esc_html( $shipping_class->name ); ?></option>
                    <?php
                }
            }
            ?>
        </select>
        <?php
    }

    /**
	 * Get HTML for payment methods field.
	 *
	 * @param string    $count
	 * @param array     $selected
	 *
	 * @since 1.0.0
	 *
	 */
	public function dsaefw_get_payment_list( $count = '', $selected = array() ) {
        $getPaymentGateways = WC()->payment_gateways->get_available_payment_gateways();
        $selected   = array_map( 'sanitize_text_field', $selected );
        ?>
        <select 
            id="payment-filter-<?php echo esc_attr( $count ); ?>"
            name="fees[product_fees_conditions_values][value_<?php echo esc_attr( $count ); ?>][]" 
            class="ds-select" 
            data-sortable="true" 
            data-placeholder="<?php esc_attr_e( 'Select payment gateways...', 'advanced-extra-fees-woocommerce' ); ?>" 
            multiple="multiple"
            data-allow_clear="true"
            data-width="100%">
            <?php 
            if ( !empty( $getPaymentGateways ) ) {
                
                foreach ( $getPaymentGateways as $payment_gateway ) {
                    $payment_gateway_id = !empty( $payment_gateway->id ) ? sanitize_text_field( $payment_gateway->id ) : '';
                    $selectedVal = is_array( $selected ) && ! empty( $selected ) && in_array( $payment_gateway_id, $selected, true ) ? 'selected=selected' : '';
                    ?>
                    <option value="<?php echo esc_attr( $payment_gateway_id ); ?>"<?php echo esc_attr( $selectedVal ); ?>><?php echo esc_html( $payment_gateway->title ); ?></option>
                    <?php
                }
            }
            ?>
        </select>
        <?php
    }

    /**
	 * Get HTML for shipping methods field.
	 *
	 * @param string    $count
	 * @param array     $selected
	 *
	 * @since 1.0.0
	 *
	 */
	public function dsaefw_get_shipping_method_list( $count = '', $selected = array() ) {
        $getShippingMethods = $this->dsaefw_shipping_method_data();
        $selected   = array_map( 'sanitize_text_field', $selected );
        ?>
        <select 
            id="shipping_method-filter-<?php echo esc_attr( $count ); ?>"
            name="fees[product_fees_conditions_values][value_<?php echo esc_attr( $count ); ?>][]" 
            class="ds-select" 
            data-sortable="true" 
            data-placeholder="<?php esc_attr_e( 'Select shipping methods...', 'advanced-extra-fees-woocommerce' ); ?>" 
            multiple="multiple"
            data-allow_clear="true"
            data-width="100%">
            <?php 
            if ( !empty( $getShippingMethods ) ) {
                foreach ( $getShippingMethods as $shipping_key => $shipping_method ) {
                    $shipping_method_id = !empty( $shipping_key ) ? sanitize_text_field( $shipping_key ) : '';
                    $selectedVal = is_array( $selected ) && ! empty( $selected ) && in_array( $shipping_method_id, $selected, true ) ? 'selected=selected' : '';
                    ?>
                    <option value="<?php echo esc_attr( $shipping_method_id ); ?>"<?php echo esc_attr( $selectedVal ); ?>><?php echo esc_html( $shipping_method['full_title'] ); ?></option>
                    <?php
                }
            }
            ?>
        </select>
        <?php
    }

    /**
	 * Get user list HTML for filter use with select2 dropdown.
	 *
	 * @param string $count
	 * @param array  $selected
	 *
	 * @since 1.0.0
	 */
	public function dsaefw_get_user_list( $count = '', $selected = array() ) {
        $selected    = array_map( 'intval', $selected );
        $display_id = true;
		?>
		<select 
            id="user-filter-<?php echo esc_attr( $count ); ?>" 
            name="fees[product_fees_conditions_values][value_<?php echo esc_attr( $count ); ?>][]" 
            class="ds-woo-search" 
            data-placeholder="<?php esc_attr_e( 'Search for users...', 'advanced-extra-fees-woocommerce' ); ?>" 
            data-sortable="true" 
            multiple="multiple" 
            data-allow_clear="true"
            data-width="100%"
            data-action="dsaefw_json_search_users"
            data-display_id="<?php echo esc_attr($display_id); ?>">
        <?php 
        if( !empty( $selected ) ) {
            $args = array( 'include' => $selected, 'number' => -1 );
            $users = get_users($args);
            if ( $users ) {
                foreach ( $users as $user ) { ?>
                    <option value="<?php echo intval($user->ID); ?>" selected="selected"><?php echo $display_id 
                    /* translators: %s is replaced with "string" which show searched user name and %d is replaced with "number" which show searched user ID */
                    ? sprintf( esc_html__( '%3$s (#%2$d - %1$s)', 'advanced-extra-fees-woocommerce' ), esc_html( sanitize_email( $user->data->user_email ) ), absint( $user->ID ), esc_html( $user->data->display_name ) )
                    : esc_html( sanitize_email( $user->data->user_email ) ); ?></option>
                    <?php 
                }
            } 
        } ?>
		</select>
        <?php
	}

    /**
	 * Get HTML for user roles field.
	 *
	 * @param string    $count
	 * @param array     $selected
	 *
	 * @since 1.0.0
	 *
	 */
	public function dsaefw_get_user_role_list( $count = '', $selected = array() ) {

        global $wp_roles;

        $getUserRoles = $wp_roles->roles;
        $selected   = array_map( 'sanitize_text_field', $selected );
        ?>
        <select 
            id="user_role-filter-<?php echo esc_attr( $count ); ?>"
            name="fees[product_fees_conditions_values][value_<?php echo esc_attr( $count ); ?>][]" 
            class="ds-select" 
            data-sortable="true" 
            data-placeholder="<?php esc_attr_e( 'Select user roles...', 'advanced-extra-fees-woocommerce' ); ?>" 
            multiple="multiple"
            data-allow_clear="true"
            data-width="100%">
            <?php 
            if ( !empty( $getUserRoles ) ) {
                
                foreach ( $getUserRoles as $user_role_slug => $user_role ) {
                    $user_role_slug = !empty( $user_role_slug ) ? sanitize_text_field( $user_role_slug ) : '';
                    $selectedVal = is_array( $selected ) && ! empty( $selected ) && in_array( $user_role_slug, $selected, true ) ? 'selected=selected' : '';
                    ?>
                    <option value="<?php echo esc_attr( $user_role_slug ); ?>"<?php echo esc_attr( $selectedVal ); ?>><?php echo esc_html( $user_role['name'] ); ?></option>
                    <?php
                }
            }
            ?>
        </select>
        <?php
    }

    /**
	 * Get HTML for user roles field.
	 *
	 * @param string    $count
	 * @param array     $selected
	 *
	 * @since 1.0.0
	 *
	 */
	public function dsaefw_get_product_attributes_list( $count = '', $product_attribute = '', $selected = array() ) {

        if( empty( $product_attribute ) ) {
            return;
        }

        // For placeholder purpose
        $pa_label = wc_attribute_label($product_attribute) ?: $product_attribute;

        // For selecting data
        $getPATerms = get_terms( array(
			'taxonomy'      => $product_attribute,
			'parent'        => 0,
            'slug'          => $selected,
			'hide_empty'    => false,
		) ); 
        ?>
        <select 
            id="<?php echo esc_attr( $product_attribute ); ?>-filter-<?php echo esc_attr( $count ); ?>"
            name="fees[product_fees_conditions_values][value_<?php echo esc_attr( $count ); ?>][]" 
            class="ds-woo-search" 
            data-sortable="true" 
            data-placeholder="<?php echo sprintf( esc_html__( 'Select %s terms...', 'advanced-extra-fees-woocommerce' ), esc_html( $pa_label ) ); ?>" 
            multiple="multiple"
            data-allow_clear="true"
            data-width="100%"
            data-action="dsaefw_json_search_pa_terms"
            data-product_attribute="<?php echo esc_attr( $product_attribute ); ?>">
            <?php 
            if ( !empty( $getPATerms ) ) {
                
                foreach ( $getPATerms as $PA_term ) {
                    $PA_term_slug = !empty( $PA_term->slug ) ? sanitize_text_field( $PA_term->slug ) : '';
                    $selectedVal = is_array( $selected ) && ! empty( $selected ) && in_array( $PA_term_slug, $selected, true ) ? 'selected=selected' : '';
                    ?>
                    <option value="<?php echo esc_attr( $PA_term_slug ); ?>"<?php echo esc_attr( $selectedVal ); ?>><?php echo esc_html( $PA_term->name ); ?></option>
                    <?php
                }
            }
            ?>
        </select>
        <?php
    }
    
    /**
     * Preare array of product attributes slugs with labels
     * 
     * @return array Product attributes slugs with labels
     * 
     * @since 1.0.0
     */
    public function dsaefw_product_attributes_labels_with_slugs() {
        $taxonomy_names = array();
        $attribute_taxonomies = wc_get_attribute_taxonomies();
        if ( ! empty( $attribute_taxonomies ) ) {
            foreach ( $attribute_taxonomies as $tax ) {
                $taxonomy_names[wc_attribute_taxonomy_name( $tax->attribute_name )] = $tax->attribute_label;
            }
        }
        return $taxonomy_names;
    }


    /**
     * Prepare coupon data
     * 
     * @return array Coupon object array
     * 
     * @since 1.0.0 
     */
    public function dsaefw_coupon_data() {
        $coupons = array();
        $coupons = get_posts( array(
            'post_type' => 'shop_coupon',
            'numberposts' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC',
        ) );
        return $coupons;
    }


    /**
     * Prepare shipping method data
     * 
     * @return array Shipping method object array
     * 
     * @since 1.0.0
     */
    public function dsaefw_shipping_method_data() {
        
        $active_methods   = array();

        /**
         * Shipping Zone free shipping methods - shipping methods which not require shipping zone
         */
        //Tree Table Rate Shipping global setting plugin
		if ( class_exists('TrsVendors_DgmWpPluginBootstrapGuard') ){
			$unique_name = new Trs\Woocommerce\ShippingMethod(); // @phpstan-ignore-line
			$ttr_config  = get_option( 'woocommerce_'.$unique_name->id.'_settings' );
			if ( isset( $ttr_config ) && is_array( $ttr_config ) ) {    
				if ( 'yes' === $ttr_config['enabled'] ) {
					$default_ttr_title = $unique_name->title;
					$ttr_method_rule = json_decode($ttr_config['rule']);
					if ( isset($ttr_method_rule) && !empty($ttr_method_rule) ) {
						if ( count($ttr_method_rule->children) > 0 ){
							$wcRateIdsCounters = array();
							foreach( $ttr_method_rule->children as $ttr_method_child ){
								
								$ttr_method_child_title = $ttr_method_child->meta->title ? $ttr_method_child->meta->title : ($ttr_method_child->meta->label ? $ttr_method_child->meta->label : $default_ttr_title);
								$method_name = $default_ttr_title . ' > ' . $ttr_method_child_title;

								$ttr_method_hash_title = $ttr_method_child->meta->title ? $ttr_method_child->meta->title : $default_ttr_title;
				
								$idParts = array();

								$hash = substr(md5($ttr_method_hash_title), 0, 8);
								$idParts[] = $hash;

								$slug = strtolower($ttr_method_hash_title);
								$slug = preg_replace('/[^a-z0-9]+/', '_', $slug);
								$slug = preg_replace('/_+/', '_', $slug);
								$slug = trim($slug, '_');
								if ($slug !== '') {
									$idParts[] = $slug;
								}

								$id = join('_', $idParts);

								$ttr_count = isset($wcRateIdsCounters[$id]) ? $wcRateIdsCounters[$id]++ : ($wcRateIdsCounters[$id]=0);
								if ( $ttr_count > 0 ) {
									$id .= '_'.($ttr_count+1);
								}

								$method_id = $unique_name->id . ':' . $id;

								$method_args           = array(
									'id'           => $unique_name->id,
									'method_title' => $ttr_method_hash_title,
									'title'        => $ttr_method_hash_title,
									'tax_status'   => ('yes' === $ttr_config['enabled']) ? 'taxable' : '',
									'full_title'   => esc_html( $method_name ),
								);
								
								$active_methods[ $method_id ] = $method_args;
							}
						}	
					}
				}
			}
		}

        //Weight Based Shipping global setting plugin
		if ( class_exists( 'WbsVendors_DgmWpPluginBootstrapGuard' ) ) {
			$unique_name = new \Wbs\Plugin( wp_normalize_path(WP_PLUGIN_DIR.'/weight-based-shipping-for-woocommerce/plugin.php') ); // @phpstan-ignore-line
			$wbs_config  = get_option( 'wbs_config' );
			if ( isset( $wbs_config ) && is_array( $wbs_config ) ) {
				if ( true === $wbs_config['enabled'] ) {
					foreach ( $wbs_config['rules'] as $wbs_value ) {
						if ( ! empty( $wbs_value ) ) {
							foreach ( $wbs_value as $wbs_meta_value ) {
								if ( ! empty( $wbs_meta_value['title'] ) ) {
									$idParts   = array();
									$hash      = substr( md5( $wbs_meta_value['title'] ), 0, 8 );
									$idParts[] = $hash;
									$slug      = strtolower( $wbs_meta_value['title'] );
									$slug      = preg_replace( '/[^a-z0-9]+/', '_', $slug );
									$slug      = preg_replace( '/_+/', '_', $slug );
									$slug      = trim( $slug, '_' );
									if ( $slug !== '' ) {
										$idParts[] = $slug;
									}
									$id                                      = implode( '_', $idParts );
									$unique_shipping_id                      = $unique_name::ID . ':' . $id;
									$method_args           = array(
										'id'           => $unique_name::ID,
										'method_title' => $wbs_meta_value['title'],
										'title'        => $wbs_meta_value['title'],
										'tax_status'   => ($wbs_meta_value['taxable']) ? 'taxable' : '',
										'full_title'   => esc_html( $wbs_meta_value['title'] ),
									);
									$active_methods[ $unique_shipping_id ] = $method_args;
								}
							}
						}
					}
				}
			}
		}

        //Advanced Flat Rate plugin by thedotstore
        if ( class_exists( 'Advanced_Flat_Rate_Shipping_For_WooCommerce_Pro_Admin' ) ) {
            $adrsfwp          = new Advanced_Flat_Rate_Shipping_For_WooCommerce_Pro_Admin( '', '' );
            $get_all_shipping = $adrsfwp::afrsm_pro_get_shipping_method( 'not_list' );
            $plugins_unique_id = 'advanced_flat_rate_shipping';
            if ( isset( $get_all_shipping ) && ! empty( $get_all_shipping ) ) {
                foreach ( $get_all_shipping as $get_all_shipping_data ) {
                    $unique_shipping_id = $plugins_unique_id . ':' . $get_all_shipping_data->ID;
                    $sm_cost            = get_post_meta( $get_all_shipping_data->ID, 'sm_product_cost', true );
                    if ( ! empty( $sm_cost ) || '' !== $sm_cost ) {
                        $method_args           = array(
                            'id'           => $plugins_unique_id,
                            'method_title' => $get_all_shipping_data->post_title,
                            'title'        => $get_all_shipping_data->post_title,
                            'tax_status'   => ('yes' === get_post_meta( $get_all_shipping_data->ID, 'sm_select_taxable', true )) ? 'taxable' : '',
                            'full_title'   => esc_html( $get_all_shipping_data->post_title ),
                        );
                        $active_methods[ $unique_shipping_id ] = $method_args;
                    }
                }
            }
        }

        /**
         * Shipping Zone based shipping methods - shipping methods which must required shipping zone
         */
        
        $zones = [];

        // Rest of the world zone shipping methods
        $data_store = WC_Data_Store::load( 'shipping-zone' );
        $zones[] = new WC_Shipping_Zone( 0 );
        
        // Other named zones
        $raw_zones = $data_store->get_zones();
        foreach ( $raw_zones as $raw_zone ) {
            $zones[] = new WC_Shipping_Zone( $raw_zone );
        }
        if( ! empty( $zones ) ){ 
            foreach ( $zones as $zone ) {

                $zone_name = ( 0 === $zone->get_id() ) ? esc_html__( 'Rest of the world', 'advanced-extra-fees-woocommerce' ) : $zone->get_zone_name();

                $zone_shipping_methods = $zone->get_shipping_methods();
                
                if( !empty( $zone_shipping_methods ) ) {
                    foreach ( $zone_shipping_methods as $zone_shipping_method ) {
                        
                        // If shipping method is not enabled then skip it
                        if( ! $zone_shipping_method->is_enabled() ){
                            continue;
                        }
                        
                        if ( 'jem_table_rate' !== $zone_shipping_method->id && 'tree_table_rate' !== $zone_shipping_method->id && 'wbs' !== $zone_shipping_method->id ) {
                            $method_args           = array(
                                'id'           => $zone_shipping_method->id,
                                'method_title' => $zone_shipping_method->get_method_title(),
                                'title'        => $zone_shipping_method->get_title(),
                                'tax_status'   => ( $zone_shipping_method->is_taxable() ) ? 'taxable' : 'none',
                                'full_title'   => sprintf( '%1$s - %2$s', esc_html( $zone_name ),esc_html( $zone_shipping_method->get_title() ) ),
                            );
                            $active_methods[ $zone_shipping_method->get_rate_id() ] = $method_args;
                        }

                        //Table Rate Shipping for WooCommerce by JEM plugins
                        if ( class_exists('JEMTR_Table_Rate_Shipping_Method') && 'jem_table_rate' === $zone_shipping_method->id ) {
                            $jemtr_methods = get_option( $zone_shipping_method->id.'_shipping_methods_' . $zone_shipping_method->get_instance_id() );
                            if( ! empty( $jemtr_methods ) ){	
                                foreach( $jemtr_methods as $jemtr_method ){
                                    if( 'yes' === $jemtr_method['method_enabled'] ) {
                                        $zone_shipping_method->get_rate_id();
                                        $method_name = sprintf( esc_html__( '%1$s - %2$s > %3$s', 'advanced-extra-fees-woocommerce' ), esc_html( $zone_name ), esc_html( $zone_shipping_method->get_method_title() ), esc_html( $jemtr_method['method_title'] ) );
                                        $method_id = $zone_shipping_method->id . '_' . $zone_shipping_method->get_instance_id() . '_' . sanitize_title($jemtr_method['method_title']);
                                        $method_args           = array(
                                            'id'           => $zone_shipping_method->id,
                                            'method_title' => $jemtr_method['method_title'],
                                            'title'        => $jemtr_method['method_title'],
                                            'tax_status'   => $jemtr_method['method_tax_status'],
                                            'full_title'   => esc_html( $method_name ),
                                        );
                                        $active_methods[ $method_id ] = $method_args;
                                    }
                                }
                            }
                        }

                        //Tree Table Rate Shipping method-wise setting
                        if ( class_exists('TrsVendors_DgmWpPluginBootstrapGuard') && 'tree_table_rate' === $zone_shipping_method->id ) {
                            $ttr_method = get_option( 'woocommerce_' . $zone_shipping_method->id . '_' . $zone_shipping_method->get_instance_id() . '_settings' );
        
                            $default_ttr_title = $zone_shipping_method->get_title();
                            
                            $ttr_method_rule = json_decode($ttr_method['rule']);
                            if ( isset($ttr_method_rule) && !empty($ttr_method_rule) ) {
                                if ( count($ttr_method_rule->children) > 0 ){
                                    $wcRateIdsCounters = array();
                                    foreach( $ttr_method_rule->children as $ttr_method_child ){
                                        
                                        $ttr_method_child_title = $ttr_method_child->meta->title ? $ttr_method_child->meta->title : ($ttr_method_child->meta->label ? $ttr_method_child->meta->label : $default_ttr_title);
                                        $method_name = sprintf( esc_html__( '%1$s - %2$s > %3$s', 'advanced-extra-fees-woocommerce' ), esc_html( $zone_name), esc_html( $ttr_method['label'] ? $ttr_method['label'] : $default_ttr_title ), esc_html( $ttr_method_child_title ) );
        
                                        $ttr_method_hash_title = $ttr_method_child->meta->title ? $ttr_method_child->meta->title : ($ttr_method['label'] ? $ttr_method['label'] : $default_ttr_title);
                        
                                        $idParts = array();
        
                                        $hash = substr(md5($ttr_method_hash_title), 0, 8);
                                        $idParts[] = $hash;
        
                                        $slug = strtolower($ttr_method_hash_title);
                                        $slug = preg_replace('/[^a-z0-9]+/', '_', $slug);
                                        $slug = preg_replace('/_+/', '_', $slug);
                                        $slug = trim($slug, '_');
                                        if ($slug !== '') {
                                            $idParts[] = $slug;
                                        }
        
                                        $id = join('_', $idParts);
        
                                        $ttr_count = isset($wcRateIdsCounters[$id]) ? $wcRateIdsCounters[$id]++ : ($wcRateIdsCounters[$id]=0);
                                        if ( $ttr_count > 0 ) {
                                            $id .= '_'.($ttr_count+1);
                                        }
        
                                        $method_id = $zone_shipping_method->id . ':' . $zone_shipping_method->get_instance_id() . ':' . $id;
        
                                        $method_args           = array(
                                            'id'           => $zone_shipping_method->id,
                                            'method_title' => $ttr_method['label'],
                                            'title'        => $ttr_method['label'],
                                            'tax_status'   => $ttr_method['tax_status'],
                                            'full_title'   => esc_html( $method_name ),
                                        );
                                        
                                        $active_methods[ $method_id ] = $method_args;
                                    }
                                }	
                            }
                        }

                        //Weight Based Shipping method-wise setting
                        if ( class_exists( 'WbsVendors_DgmWpPluginBootstrapGuard' ) && 'wbs' === $zone_shipping_method->id ) {
                            $wbs_method  = get_option( $zone_shipping_method->id . '_' . $zone_shipping_method->get_instance_id() . '_config' );
        
                            $default_wbs_title = $zone_shipping_method->get_title();
        
                            $wbs_method_rules = $wbs_method['rules'];
                            if ( isset($wbs_method_rules) && !empty($wbs_method_rules) ) {
                                if ( count( $wbs_method_rules ) > 0 && $wbs_method['enabled']) {
                                    $wcRateIdsCounters = array();
                                    $wbs_count = 0;
                                    foreach ( $wbs_method_rules as $wbs_value ) {
                                        $wbs_method_child_title = $wbs_value['meta']['title'] ? $wbs_value['meta']['title'] : $default_wbs_title;
                                        if ( $wbs_value['meta']['enabled'] ) {
                                            $idParts   = array();
                                            $hash      = substr( md5( $wbs_method_child_title ), 0, 8 );
                                            $idParts[] = $hash;
                                            $slug      = strtolower( $wbs_method_child_title );
                                            $slug      = preg_replace( '/[^a-z0-9]+/', '_', $slug );
                                            $slug      = preg_replace( '/_+/', '_', $slug );
                                            $slug      = trim( $slug, '_' );
                                            if ( $slug !== '' ) {
                                                $idParts[] = $slug;
                                            }
                                            $id = implode( '_', $idParts );
        
                                            $wbs_count = isset($wcRateIdsCounters[$id]) ? $wcRateIdsCounters[$id]++ : ($wcRateIdsCounters[$id]=0);
                                            if ( $wbs_count > 0) {
                                                $id .= '_'.($wbs_count+1);
                                            }
        
                                            $unique_shipping_id = $zone_shipping_method->id . ':' . $zone_shipping_method->get_instance_id() . ':' . $id;
        
                                            $method_args           = array(
                                                'id'           => $zone_shipping_method->id,
                                                'method_title' => $wbs_method_child_title,
                                                'title'        => $wbs_method_child_title,
                                                'tax_status'   => ($wbs_value['meta']['taxable']) ? 'taxable' : 'none',
                                                'full_title'   => esc_html( $wbs_method_child_title ),
                                            );
                                            $active_methods[ $unique_shipping_id ] = $method_args;
                                        }
                                    }
                                }
                            }
                        }


                    }
                }
            }
        } 
        
        return $active_methods;
    }

    /**
	 * Process and save conditional rules meta box data.
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

        $advance_fee = new \Advanced_Extra_Fees_Woocommerce_Fee( $post );

        $get_cost_rule_match        = filter_input( INPUT_POST, 'cost_rule_match', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY );
        $get_dsaefw_fee             = filter_input( INPUT_POST, 'fees', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY );
        
        $cost_rule_match    = isset( $get_cost_rule_match ) ? array_map( 'sanitize_text_field', $get_cost_rule_match ) : array();
        $dsaefw_fee         = $this->dsaefw_prepare_rule_filter_array($get_dsaefw_fee);
        
        $advance_fee->set_cost_rule_match($cost_rule_match);
        $advance_fee->set_product_fees_metabox($dsaefw_fee);
    }

    /**
     * Prepare rule filter array
     * 
     * @param array $post_data_array
     * 
     * @return array
     * 
     * @since 1.0.0
     */
    public function dsaefw_prepare_rule_filter_array( $post_data_array ) {
        $product_fees_conditions_conditions = array();
        if( !empty($post_data_array) ) {
            $product_fees_conditions_conditions_values_array = array();
            $size = isset($post_data_array['product_fees_conditions_condition']) && !empty($post_data_array['product_fees_conditions_condition']) ? count($post_data_array['product_fees_conditions_condition']) : 0;
            if( !empty( $post_data_array['product_fees_conditions_values'] ) ) {
                foreach ( $post_data_array['product_fees_conditions_values'] as $v ) {
                    $product_fees_conditions_conditions_values_array[] = $v;
                }
            }
            for ( $i = 0; $i < $size; $i ++ ) {
                if( !empty( $product_fees_conditions_conditions_values_array[ $i ] ) ) {
                    $product_fees_conditions_conditions[] = array(
                        'product_fees_conditions_condition' => $post_data_array['product_fees_conditions_condition'][ $i ],
                        'product_fees_conditions_is'        => $post_data_array['product_fees_conditions_is'][ $i ],
                        'product_fees_conditions_values'    => $product_fees_conditions_conditions_values_array[ $i ],
                    );
                }
            }
        }
        return $product_fees_conditions_conditions;
    }
}
