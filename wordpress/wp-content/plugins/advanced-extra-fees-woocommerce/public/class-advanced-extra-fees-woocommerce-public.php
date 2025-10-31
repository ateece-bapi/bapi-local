<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.thedotstore.com/
 * @since      1.0.0
 *
 * @package    Advanced_Extra_Fees_Woocommerce
 * @subpackage Advanced_Extra_Fees_Woocommerce/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Advanced_Extra_Fees_Woocommerce
 * @subpackage Advanced_Extra_Fees_Woocommerce/public
 * @author     theDotstore <support@thedotstore.com>
 */
#[\AllowDynamicProperties]
class Advanced_Extra_Fees_Woocommerce_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      float    $advance_fee_cost    this will use for fee shortcode use.
	 */
    private $advance_fee_cost;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

        $this->include_modules();
	}

    /**
	 * Include public classes and objects.
	 *
	 * @since 1.0.0
	 */
	private function include_modules() {

        // Fees public edit screens
        require_once( plugin_dir_path( dirname( __FILE__ ) ) . '/public/class-advanced-extra-fees-woocommerce-conditional-rules.php' );        
    }

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Advanced_Extra_Fees_Woocommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Advanced_Extra_Fees_Woocommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/advanced-extra-fees-woocommerce-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Advanced_Extra_Fees_Woocommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Advanced_Extra_Fees_Woocommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

        wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.min.js', [ 'jquery' ], WC_VERSION, true );

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/advanced-extra-fees-woocommerce-public.js', [ 'jquery', 'jquery-tiptip' ], $this->version, true );
        
        wp_localize_script( $this->plugin_name, 'dasefw_public_vars', array(
                'fee_tooltip_data' => $this->dsaefw_all_fee_tooltip_data(),
                'is_checkout' => is_checkout(),
            ) 
        );
	}

    /**
     * List all fees with tooltip data (For Block Cart/Checkout Use)
     * 
     * @return array $fee_tooltip_data
     * 
     * @since 1.0.0
     */
    public function dsaefw_all_fee_tooltip_data() {

        $all_fees = $this->dsaefw_get_all_fees();

        $fee_tooltip_data = array();

        if( !empty( $all_fees ) ) {
            
            $combine_fees_status = get_option( 'dsaefw_combine_fees', 'no' );
            if( 'yes' === $combine_fees_status ) {
                
                $combine_fees_tooltip = get_option( 'dsaefw_combine_fees_tooltip', 'no' );
                if( 'yes' === $combine_fees_tooltip ) {
                    $fee_tooltip = get_option( 'dsaefw_combine_fees_tooltip_text', '' );
                    if( !empty( $fee_tooltip ) ) {
                        $combine_fee_title = apply_filters('dsaefw_combine_fee_title', __( 'Combine Fees', 'advanced-extra-fees-woocommerce' ) );
                        $fee_tooltip_data[ sanitize_title( $combine_fee_title ) ] = esc_html( $fee_tooltip );
                    }
                }
            } else {
                
                foreach( $all_fees as $fee_id ) {
                    $advance_fee = new \Advanced_Extra_Fees_Woocommerce_Fee( $fee_id );
                    if( $advance_fee->has_dsaefw_tooltip_description() ) {
                        $fee_tooltip_data[ sanitize_title( $advance_fee->get_name() ) ] = $advance_fee->get_dsaefw_tooltip_description();
                    }
                }
            }

        }

        return $fee_tooltip_data;
    }

    /**
     * Retrive fee ID from fee name
     * 
     * @param string $fee_name
     * 
     * @return int $fee_id
     * 
     * @since 1.0.0
     */
    public function dsaefw_fee_id_from_name( $fee_name ) {

        if( empty( $fee_name ) ) {
            return 0;
        }

        // This will return latest fee if fond same fee name found
        $fee_args = new WP_Query(
            array(
                'post_type'              => DSAEFW_FEE_POST_TYPE,
                'title'                  => $fee_name,
                'post_status'            => 'publish',
                'posts_per_page'         => 1,
                'no_found_rows'          => true,
                'ignore_sticky_posts'    => true,
                'update_post_term_cache' => false,
                'update_post_meta_cache' => false,          
                'orderby'                => 'post_date',
                'order'                  => 'DESC',
            )
        );
        
        $fee_object = null;

        if ( ! empty( $fee_args->post ) ) {
            $fee_object = $fee_args->post;
        }

        $fee_id = (int) isset($fee_object->ID) && !empty($fee_object->ID) ? $fee_object->ID : 0;

        return $fee_id;
    }

    /**
     * Display fee tooltip on cart and checkout page
     * 
     * @param string $fee_html
     * @param object $fee
     * 
     * @return string $fee_html
     * 
     * @since 1.0.0
     */
    public function dsaefw_fee_tooltip( $fee_html, $fee ) {

        $fee_id = $this->dsaefw_fee_id_from_name( $fee->name );
        $fee_tooltip = '';

        if( !empty( $fee_id ) ) {

            $advance_fee = new \Advanced_Extra_Fees_Woocommerce_Fee( $fee_id );
            $fee_tooltip = $advance_fee->get_dsaefw_tooltip_description();
        } else {
            
            $combine_fees_status = get_option( 'dsaefw_combine_fees', 'no' );
            $combine_fees_tooltip = get_option( 'dsaefw_combine_fees_tooltip', 'no' );
            if( 'yes' === $combine_fees_tooltip && 'yes' === $combine_fees_status ) {
                $fee_tooltip = get_option( 'dsaefw_combine_fees_tooltip_text', '' );
            }
        }
        
        if( !empty( $fee_tooltip ) ) {
            $fee_html .= sprintf( '<span class="wc-dsaefw-help-tip" data-tip="%s"></span>', esc_attr( $fee_tooltip ) );
        }
        
        return $fee_html;
    }

    /**
     * Get all fees
     * 
     * @param array $args
     * @param boolean $reset
     * 
     * @return array $dsaefw_get_all_fees
     * 
     * @since 1.0.0
     */
    public function dsaefw_get_all_fees( $args = array(), $reset = false ) {
        
        // First delete transient then fetch all fees
        if( $reset ) {
            delete_transient( 'dsaefw_get_all_fees' );
        }

        // Get all fees
        $dsaefw_get_all_fees = get_transient( 'dsaefw_get_all_fees' );

		if ( false === $dsaefw_get_all_fees ) {

			$fees_args    = wp_parse_args( $args, array(
				'post_type'        	=> DSAEFW_FEE_POST_TYPE,
				'post_status'      	=> 'publish',
				'posts_per_page'   	=> -1,
				'suppress_filters' 	=> false,
				'fields'        	=> 'ids',
				'order'          	=> 'DESC',
				'orderby'        	=> 'ID',
			) );

			$dsaefw_get_all_fees_query = new WP_Query( $fees_args );
			$dsaefw_get_all_fees       = $dsaefw_get_all_fees_query->get_posts();

            // Set transient for fees
			set_transient( 'dsaefw_get_all_fees', $dsaefw_get_all_fees );
		}

        return $dsaefw_get_all_fees;
    }

    /**
	 * Add fees in cart based on rule
	 *
	 * @since    1.0.0
     */
    public function dsaefw_conditional_fee_add_to_cart() {
        
        // We are checking block on checkout here as this fee will only show on checkout page
        $is_checkout_has_block = dsaefw()->dsaefw_is_wc_has_block( 'checkout' );

        // If customer switch block to classic checkout and cart then we will unset session
        if( ! $is_checkout_has_block ) {
            WC()->session->__unset('dsaefw_is_checkout');
        }
        
        // For block we will use session and for classic our traditional way
        $is_checkout_page = WC()->session->__isset('dsaefw_is_checkout') ? WC()->session->get('dsaefw_is_checkout') : is_checkout();

        // Get all fees
        $dsaefw_get_all_fees = $this->dsaefw_get_all_fees();

        // If merge all fee into one fee enabled then this @var will use
        $total_fee = 0;
        $combine_fees_status = get_option( 'dsaefw_combine_fees', 'no' );

        if ( isset( $dsaefw_get_all_fees ) && ! empty( $dsaefw_get_all_fees ) ) {

			foreach ( $dsaefw_get_all_fees as $fee_id ) {

                // Check for user first order or not
                $check_for_user = $this->dsaefw_apply_fee_for_first_order( $fee_id );
                if( ! $check_for_user ) {
                    continue;
                }

                if( ! $this->dsaefw_apply_fee_based_on_date_and_time( $fee_id ) ){
                    continue;
                }

                $fee_cost                   = 0;  
                $advance_fee                = new \Advanced_Extra_Fees_Woocommerce_Fee( $fee_id );
                $fee_title                  = $advance_fee->get_name();
                $title                      = !empty( $fee_title ) ? __( $fee_title, 'advanced-extra-fees-woocommerce' ) : __( 'Fee', 'advanced-extra-fees-woocommerce' );
                $fees_on_cart_total         = $advance_fee->get_fees_on_cart_total();
                $getFeeType                 = $advance_fee->get_fee_type();
                $getFeesCost                = $advance_fee->get_fee_settings_product_cost();
                $final_item_tax_class       = '';
                $getFeetaxable   		    = $advance_fee->get_fee_settings_select_taxable();
                $texable      			    = ( !empty( $getFeetaxable ) && 'yes' === $getFeetaxable ) ? true : false;
                
                if( 'yes' === $fees_on_cart_total ) {
                    
                    $cart_total = $this->dsaefw_cart_total();
                    
                    // Apply basic configuration fee on cart total
                    $fee_cost = $this->dsaefw_calculate_amount( $getFeeType, $getFeesCost, $cart_total );
                } else {

                    $cart_subtotal = floatval( wc_prices_include_tax() ? WC()->cart->subtotal : WC()->cart->subtotal_ex_tax );
                    
                    // Apply basic configuration fee on cart subtotal
                    $fee_cost = $this->dsaefw_calculate_amount( $getFeeType, $getFeesCost, $cart_subtotal );
                }
                
                // Per Quantity based calculation
                $fee_cost += $this->dsaefw_calculate_quantity_based_fee( $fee_id );
                
                // Weight based calculation
                $fee_cost += $this->dsaefw_calculate_weight_based_fee( $fee_id );
                
                // Check for conditional rule validation
                $conditional_rule = new \Advanced_Extra_Fees_Woocommerce_Public_Fee_Conditional_Rules( $fee_id );
                if( ! $conditional_rule->is_fee_passed_conditional_rule_validation() ){
                    continue;
                }
                
                // Only apply on checkout page validation
                if( !$this->dsaefw_is_fee_available_on_checkout_only( $fee_id, $is_checkout_page ) ) {
                    continue;
                }
                
                /** 
                 * Check with Global setting things
                 */
                // Remove fee on 100% discount applied
                if( $this->dsaefw_remove_fee_on_full_discount() ){
                    continue;
                }

                // Remove all fees if merge fee global option is enable, we will combine them and add as one fee
                if( 'yes' === $combine_fees_status ) {
                    $total_fee += $fee_cost;
                    continue;
                }
                
                // This will go last as our all calculation on cart will above this
                WC()->cart->add_fee( 
                    $title, 
                    $fee_cost, 
                    $texable, 
                    apply_filters('dsaefw_tax_class', $final_item_tax_class, $fee_id ) 
                );
            }

            // Apply combined fee
			if ( ( ! empty( $combine_fees_status ) && 'yes' === $combine_fees_status ) ) {
				if ( isset( $total_fee ) && 0 < $total_fee ) {
					$combine_fees_taxable   = ( 'yes' === get_option( 'dsaefw_combine_fees_taxable', 'no' ) ) ? true : false;
					$fee_title              = sanitize_text_field( apply_filters('dsaefw_combine_fee_title', esc_html__( 'Combine Fees', 'advanced-extra-fees-woocommerce' ) ) );

					WC()->cart->add_fee( $fee_title, $total_fee, $combine_fees_taxable, apply_filters('dsaefw_tax_class', $final_item_tax_class, -1)); //-1 for combined fees id
				}
			}
        }
    }

    /**
     * Check provided fee is available on checkout only
     * 
     * @param int $fee_id
     * 
     * @return boolean
     * 
     * @since 1.0.0
     */
    public function dsaefw_is_fee_available_on_checkout_only( $fee_id, $is_checkout_page = false ) {

        if( empty( $fee_id ) ){
            return false;
        }

        $advance_fee = new \Advanced_Extra_Fees_Woocommerce_Fee( $fee_id );

        if( empty( $advance_fee ) ){
            return false;
        }

        $fee_show_on_checkout_only = 'yes' === $advance_fee->get_fee_show_on_checkout_only() ? true : false;

        // Scenario 1: If flag is on and is checkout page, return true
        if ( $fee_show_on_checkout_only && $is_checkout_page ) {
            return true;
        }

        // Scenario 2: If flag is on and is not checkout page, return false
        if ( $fee_show_on_checkout_only && !$is_checkout_page ) {
            return false;
        }

        // Scenario 3: If flag is off and is checkout page, return true
        if ( !$fee_show_on_checkout_only && $is_checkout_page ) {
            return true;
        }

        // Scenario 4: If flag is off and is not checkout page, return true
        if ( !$fee_show_on_checkout_only && !$is_checkout_page ) {
            return true;
        }

        return false;
    }

    /**
	 * Calculate amount based on fee type
	 *
     * @param    string $fee_type.
     * @param    number $fee_cost.
     * @param    number $cart_amount.
     * 
     * @return   float $return_cost.
     * 
	 * @since    1.0.0
     */
    public function dsaefw_calculate_amount( $fee_type, $fee_cost, $cart_amount ){

        $return_cost = 0;

        switch ( $fee_type ) {

            case 'percentage':
                $return_cost = $cart_amount * ( $fee_cost / 100);
                break;

            case 'both':
                $total = 0;
                // Split the input into parts using '+'
                $parts = explode('+', $fee_cost);
                foreach ( $parts as $part ) {

                    $part = trim($part); // Trim any whitespace

                    if ( strpos( $part, '%' ) !== false ) {

                        // Calculate percentage
                        $percentage = floatval($part) / 100;
                        $total += $cart_amount * $percentage;
                    } elseif ( is_numeric( $part ) ) {

                        // Add fixed value
                        $total += floatval( $part );
                    }
                }
                $return_cost = $total;
                break;
                
            default:
                // For fixed amount we are evaluate cost
                $return_cost = $this->dsaefw_evaluate_cost( 
                    $fee_cost, 
                    array( 
                        $this->dsaefw_cart_line_specific_data(), 
                        $this->dsaefw_cart_line_specific_data('subtotal'), 
                        $this->dsaefw_cart_line_specific_data('weight') 
                    ) 
                );
                break;
        }

        return (float) $return_cost;
    }

    /**
	 * Evaluate a cost from a sum/string.
	 *
	 * @param string $fee_cost_sum
	 * @param array  $args
	 *
	 * @return string $fee_cost_sum if fee cost is empty then it will return 0
	 * @since 1.0.0
	 *
	 * @uses  wc_get_price_decimal_separator()
	 * @uses  WC_Eval_Math::evaluate()
	 */
	public function dsaefw_evaluate_cost( $fee_cost_sum, $args = array() ){
        global $woocommerce;
        include_once $woocommerce->plugin_path() . '/includes/libraries/class-wc-eval-math.php';

		$locale         = localeconv();
		$decimals       = array( wc_get_price_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point'], ',' );
		
		if ( isset( $args) && ! empty( $args ) ) {
			
            $this->advance_fee_cost = $args[1];
			
            // Expand shortcodes.
			add_shortcode( 'fee', array( $this, 'dsaefw_fee' ) );
			
			$fee_cost_sum = do_shortcode( str_replace( array( '[qty]', '[cost]', '[weight]' ), array(
				$args[0],
				$args[1],
				$args[2] 
			), $fee_cost_sum ) );
			
			remove_shortcode( 'fee', array( $this, 'dsaefw_fee' ) );	
		}
		
		// Remove whitespace from string
		$fee_cost_sum = preg_replace( '/\s+/', '', $fee_cost_sum );
		
		// Remove locale from string
		$fee_cost_sum = str_replace( $decimals, '.', $fee_cost_sum );
		
		// Trim invalid start/end characters
		$fee_cost_sum = rtrim( ltrim( $fee_cost_sum, "\t\n\r\0\x0B+*/" ), "\t\n\r\0\x0B+-*/" );
		
		// Do the math
		return $fee_cost_sum ? WC_Eval_Math::evaluate( $fee_cost_sum ) : 0;
	}

    /**
	 * Work out fee ( shortcode ).
	 *
	 * @param array $atts
	 *
	 * @return string $calculated_fee
	 * @since 1.0.0
	 *
	 * @uses  dsaefw_fee_string_sanitize
	 * 
	 */
	public function dsaefw_fee( $atts ) {
		
        $atts            = shortcode_atts( array( 'min_fee' => '', 'max_fee' => '' ), $atts );
		$atts['min_fee'] = $this->dsaefw_fee_string_sanitize( $atts['min_fee'] );
		$atts['max_fee'] = $this->dsaefw_fee_string_sanitize( $atts['max_fee'] );

		$calculated_fee  = $this->advance_fee_cost ? $this->advance_fee_cost : 0;
		
        if ( $atts['min_fee'] && $calculated_fee < $atts['min_fee'] ) {
			$calculated_fee = $atts['min_fee'];
		}

		if ( $atts['max_fee'] && $calculated_fee > $atts['max_fee'] ) {
			$calculated_fee = $atts['max_fee'];
		}
		
		return $calculated_fee; // nosemgrep
	}

    /**
	 * Sanitize string
	 *
	 * @param mixed $string
	 *
	 * @return string $result
	 * @since 1.0.0
	 *
	 */
	public function dsaefw_fee_string_sanitize( $string ) {
		$result = preg_replace( "/[^ A-Za-z0-9_=.*()+\-\[\]\/]+/", '', html_entity_decode( $string, ENT_QUOTES ) );
		return $result;
	}

    /**
	 * Cart total with tax and shipping cost
	 *
	 * @return number $cart_final_total.
	 *
	 * @since  1.0.0
	 */
	public function dsaefw_cart_total(){
		$cart_final_total = 0;
		$total_tax = 0;
		$total_shipping = 0;

		$cart_subtotal = WC()->cart->get_cart_contents_total();

		foreach(WC()->cart->get_tax_totals() as $taxy){
			$total_tax += $taxy->amount;
		}
		
		// Loop through shipping packages from WC_Session (They can be multiple in some cases)
		foreach ( WC()->cart->get_shipping_packages() as $package_id => $package ) {

			// Check if a shipping for the current package exist
			if ( WC()->session->__isset( 'shipping_for_package_'.$package_id ) ) {
				// Loop through shipping rates for the current package
				foreach ( WC()->session->get( 'shipping_for_package_'.$package_id )['rates'] as $shipping_rate_id => $shipping_rate ) {
					if( in_array( $shipping_rate_id, WC()->session->get( 'chosen_shipping_methods' ), true ) ){
						$shipping_rate = WC()->session->get( 'shipping_for_package_'.$package_id )['rates'][$shipping_rate_id];
						$total_shipping += $shipping_rate->get_cost(); // The cost without tax
					}
				}
			}
		}
		$cart_final_total = $cart_subtotal + $total_tax + $total_shipping;
		return $cart_final_total;
	}

    /**
     * Get cart line specific data
     * 
     * @param string $element_count
     * 
     * @return float $return_count
     * 
     * @since 1.0.0
     */
    public function dsaefw_cart_line_specific_data( $element_count = 'quantity' ) {

        $return_count   = 0;

        if( empty( WC()->cart ) ){
            return $return_count;
        }

        $cart_array     = WC()->cart->get_cart();

        if( 'subtotal' === $element_count ) {
            return floatval( wc_prices_include_tax() ? WC()->cart->subtotal : WC()->cart->subtotal_ex_tax );
        }

        if( 'subtotal_with_discount' === $element_count ) {
            
            $cart_subtotal = floatval( wc_prices_include_tax() ? WC()->cart->subtotal : WC()->cart->subtotal_ex_tax );
            $discount_amount =  wc_prices_include_tax() ? round(WC()->cart->get_discount_total(), 2) + round(WC()->cart->get_discount_tax(), 2) : round(WC()->cart->get_discount_total(), 2);

            return floatval( $cart_subtotal - $discount_amount );
        }

        if( 'weight' === $element_count ){
            return WC()->cart->get_cart_contents_weight();
        }

        if ( !empty( $cart_array ) ) {
            foreach ( $cart_array as $cart_item ) {

                $product_obj = $cart_item['data'];
                $product_type = $product_obj->get_type();

                // If bundle product then skip from count
                if( "bundle" === $product_type ){
                    continue;
                }

                if( 'quantity' === $element_count ) {
                    $return_count += $cart_item['quantity'];
                }
            }	
        }

        return $return_count;
    }

    /**
     * Calculate quantity based fee
     * 
     * @param int $fee_id
     * 
     * @return float $fee_cost
     * 
     * @since 1.0.0
     */
    public function dsaefw_calculate_quantity_based_fee( $fee_id ) {

        $fee_cost = 0;

        if( empty( $fee_id ) ){
            return $fee_cost;
        }

        $advance_fee = new \Advanced_Extra_Fees_Woocommerce_Fee( $fee_id );

        if( empty( $advance_fee ) ){
            return $fee_cost;
        }

        $getFeesPerQtyFlag          = $advance_fee->get_fee_chk_qty_price();
        $getFeesPerQty              = $advance_fee->get_fee_per_qty();
        $extraProductCostOriginal 	= $advance_fee->get_extra_product_cost();

        $extraProductCostOriginal   = $this->dsaefw_evaluate_cost( $extraProductCostOriginal );

        if( 'yes' === $getFeesPerQtyFlag ){
            if ( 'qty_cart_based' === $getFeesPerQty ) {
                
                $cart_based_qty = $this->dsaefw_cart_line_specific_data();
                $fee_cost += ( ( $cart_based_qty - 1 ) * $extraProductCostOriginal );
            } else if ( 'qty_product_based' === $getFeesPerQty ) {
                
                $conditional_rule = new \Advanced_Extra_Fees_Woocommerce_Public_Fee_Conditional_Rules( $fee_id );
                $products_based_qty = $conditional_rule->dsaefw_get_product_specific_cart_data('quantity');

                $fee_cost += ( ( $products_based_qty - 1 ) * $extraProductCostOriginal );
            }
        }
        
        return $fee_cost;
    }

    /**
     * Calculate weight based fee
     * 
     * @param int $fee_id
     * 
     * @return float $fee_cost
     * 
     * @since 1.0.0
     */
    public function dsaefw_calculate_weight_based_fee( $fee_id ) {

        $fee_cost = 0;

        if( empty( $fee_id ) || empty( WC()->cart ) ){
            return $fee_cost;
        }

        $advance_fee = new \Advanced_Extra_Fees_Woocommerce_Fee( $fee_id );

        if( empty( $advance_fee ) ){
            return $fee_cost;
        }

        $is_allow_custom_weight_base = $advance_fee->get_is_allow_custom_weight_base();

        if( "yes" === $is_allow_custom_weight_base ) {

            $total_cart_weights = WC()->cart->get_cart_contents_weight();

            $sm_custom_weight_base_cost = $advance_fee->get_sm_custom_weight_base_cost();
            $sm_custom_weight_base_per_each = $advance_fee->get_sm_custom_weight_base_per_each();
            $sm_custom_weight_base_over = $advance_fee->get_sm_custom_weight_base_over();
            $sm_custom_weight_base_cost_shipping = 0;

            if( $total_cart_weights > 0 && $sm_custom_weight_base_per_each > 0 && $sm_custom_weight_base_cost > 0 && $total_cart_weights >= $sm_custom_weight_base_per_each ){
                if( $sm_custom_weight_base_over > 0 ){
                    if( $total_cart_weights >= $sm_custom_weight_base_over ){
                        $total_cart_weights = ($total_cart_weights - $sm_custom_weight_base_over);
                        $sm_custom_weight_base_cost_part = (float)( $total_cart_weights / $sm_custom_weight_base_per_each );
                        $sm_custom_weight_base_cost_shipping = (float)( $sm_custom_weight_base_cost * $sm_custom_weight_base_cost_part );
                    }
                } else {
                    $sm_custom_weight_base_cost_part = (float)( $total_cart_weights / $sm_custom_weight_base_per_each );
                    $sm_custom_weight_base_cost_shipping = (float)( $sm_custom_weight_base_cost * $sm_custom_weight_base_cost_part );
                }
                $fee_cost += $sm_custom_weight_base_cost_shipping;
            }
        }

        return $fee_cost;
    }

    /**
     * Apply fee for first order
     * 
     * @param int $fee_id
     * 
     * @since 1.0.0
     */
    public function dsaefw_apply_fee_for_first_order( $fee_id ) {
        
        if( empty( $fee_id ) ){
            return false;
        }

        $advance_fee = new \Advanced_Extra_Fees_Woocommerce_Fee( $fee_id );

        if( empty( $advance_fee ) ){
            return false;
        }

        $getFirstOrderForUser   	= $advance_fee->get_first_order_for_user();
        $firstOrderForUser   		= ( ! empty( $getFirstOrderForUser ) && 'yes' === $getFirstOrderForUser ) ? true : false;

        if( $firstOrderForUser && is_user_logged_in() ) {

            $current_user_id = get_current_user_id();
            $check_for_user = $this->dsaefw_check_first_order_for_user( $current_user_id );

            if( !$check_for_user ){
                return false;
            } else {
                return true;
            }
        }

        return true;
    }

    /**
	 * Check user's have first order or not
	 *
     * @param int $user_id
     * 
	 * @return boolean $order_check
     * 
	 * @since 1.0.0
	 */
	public function dsaefw_check_first_order_for_user( $user_id ) {

		$user_id = !empty($user_id) ? $user_id : get_current_user_id();

        $args = array(
            'customer_id'   => $user_id,
            'limit'         => 1, 
            'status'        => array( 'wc-completed', 'wc-processing' ), 
            'return'        => 'ids',
        );
        $customer_orders = wc_get_orders($args);
        
		// return "true" when customer has already at least one order (false if not)
	   return count($customer_orders) > 0 ? false : true; 
	}

    /**
     * Whether fee apply based on date, time and days or not
     * 
     * @param int $fee_id
     * 
     * @return boolean
     * 
     * @since 1.0.0
     */
    public function dsaefw_apply_fee_based_on_date_and_time( $fee_id ) {
        
        if( empty( $fee_id ) ){
            return false;
        }

        $advance_fee = new \Advanced_Extra_Fees_Woocommerce_Fee( $fee_id );

        if( empty( $advance_fee ) ){
            return false;
        }

        $date_check = $time_check = $day_check = false;

        // Date validation check
        $currentDate    = strtotime( gmdate( 'd-m-Y' ) );
        $feeStartDate   = $advance_fee->get_fee_settings_start_date();
        $feeEndDate     = $advance_fee->get_fee_settings_end_date();

        if( ( $currentDate >= $feeStartDate || '' === $feeStartDate ) && ( $currentDate <= $feeEndDate || '' === $feeEndDate ) ) {
            $date_check = true;
        }

        // Time validation check
        $currentTime    = current_time( 'timestamp' );
        $feeStartTime   = $advance_fee->get_ds_time_from();
        $feeEndTime     = $advance_fee->get_ds_time_to();

        if( ( $currentTime >= $feeStartTime || '' === $feeStartTime ) && ( $currentTime <= $feeEndTime || '' === $feeEndTime ) ) {
            $time_check = true;
        }

        // Days validation check
        $today =  strtolower( gmdate( "D" ) );
        $ds_select_day_of_week = $advance_fee->get_ds_select_day_of_week();

        if( in_array( $today, $ds_select_day_of_week, true ) || empty( $ds_select_day_of_week ) ) {
            $day_check = true;
        }

        if( $date_check && $time_check && $day_check ) {
            return true;
        }

        return false;
    }

    /**
     * Check for applying full discount on cart/checkout page
     * 
     * @return boolean
     * 
     * @since 1.0.0
     */
    public function dsaefw_remove_fee_on_full_discount() {

        if( empty( WC()->cart ) ){
            return false;
        }

        $cart_subtotal = floatval( wc_prices_include_tax() ? WC()->cart->subtotal : WC()->cart->subtotal_ex_tax );

        $remove_fee_on_full_disacount = get_option( 'dsaefw_remove_fee_on_full_discount', 'no' );

        if( 'yes' === $remove_fee_on_full_disacount ) {

            $discount_excl_tax_total = WC()->cart->get_cart_discount_total();
            $discount_tax_total = WC()->cart->get_cart_discount_tax_total();

            $discount_total = round( $discount_excl_tax_total + $discount_tax_total, 2);

            if( $discount_total >= $cart_subtotal ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add/Remove fee at Block Checkout, here we are use session to transfer data to hook
     * it will use in `woocommerce_cart_calculate_fees` hook
     * 
     * @since 1.0.0
     */
    public function dsaefw_block_checokut_data_register() {
        woocommerce_store_api_register_update_callback(
			array(
				'namespace' => 'dotstore-advance-extra-checkout-data',
                'callback'  => function( $data ) {
                    
                    //Check is checkout page active
                    if( isset( $data['isCheckout'] ) && !empty( $data['isCheckout'] ) ) {
                        WC()->session->set( 'dsaefw_is_checkout', $data['isCheckout'] );
                    } else {
                        WC()->session->set( 'dsaefw_is_checkout', false );
                    }

                    // Check for payment method
                    if( isset( $data['payment_method'] ) && !empty( $data['payment_method'] ) ) {
                        WC()->session->set( 'chosen_payment_method', $data['payment_method'] );
                    } else {
                        WC()->session->set( 'chosen_payment_method', '' );
                    }
				},
			)
		);
    }

    /**
	 * Filter data
	 *
	 * @param string $string
	 *
	 * @since 1.0.0
	 */
	public function dsaefw_filter_sanitize_string( $string ) {
	    $str = preg_replace('/\x00|<[^>]*>?/', '', $string);
	    return str_replace(["'", '"'], ['&#39;', '&#34;'], $str);
	}
}
