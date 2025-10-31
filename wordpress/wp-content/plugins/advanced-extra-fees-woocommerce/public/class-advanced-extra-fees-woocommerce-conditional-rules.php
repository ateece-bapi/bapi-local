<?php 
/**
 * WooCommerce Advanced Extra Fees Conditional rule validation and checks for frontend side.
 *
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'Advanced_Extra_Fees_Woocommerce_Public_Fee_Conditional_Rules', false ) ) {
	return new Advanced_Extra_Fees_Woocommerce_Public_Fee_Conditional_Rules(0);
}

/**
 * Advanced_Extra_Fees_Woocommerce_Public_Fee_Conditional_Rules.
 */
#[\AllowDynamicProperties]
class Advanced_Extra_Fees_Woocommerce_Public_Fee_Conditional_Rules {

    /** @var int ID of the corresponding fee */
	private $advance_fee;

    /** @var int advance fee (post) unique ID */
	protected $id = 0;

	/** @var string cost_rule_match */
	private $cost_rule_match = '';

	/** @var array product_fees_metabox */
	private $product_fees_metabox = array();

    /** @var array county specific variable */
    private $country_array = array();

    /** @var array state specific variable */
    private $state_array = array();

    /** @var array city specific variable */
    private $city_array = array();

    /** @var array postcode specific variable */
    private $postcode_array = array();

    /** @var array zone specific variable */
    private $zone_array = array();

    /** @var array product specific variable */
    private $product_array = array();

    /** @var array category specific variable */
    private $category_array = array();

    /** @var array tag specific variable */
    private $tag_array = array();

    /** @var array product_qty specific variable */
    private $product_qty_array = array();

    /** @var array user specific variable */
    private $user_array = array();

    /** @var array user_role specific variable */
    private $user_role_array = array();

    /** @var array cart_total specific variable */
    private $cart_total_array = array();

    /** @var array cart_totalafter specific variable */
    private $cart_totalafter_array = array();

    /** @var array cart_productspecific specific variable */
    private $cart_productspecific_array = array();

    /** @var array quantity specific variable */
    private $quantity_array = array();

    /** @var array weight specific variable */
    private $weight_array = array();

    /** @var array coupon specific variable */
    private $coupon_array = array();

    /** @var array shipping_class specific variable */
    private $shipping_class_array = array();
    
    /** @var array payment specific variable */
    private $payment_array = array();

    /** @var array shipping_method specific variable */
    private $shipping_method_array = array();

    /** @var array product_attribute specific variable */
    private $product_attribute_array = array();
    
    /**
	 * Fee constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param int|\WP_Post|\Advanced_Extra_Fees_Woocommerce_Fee $data the post or advance fee ID, object
	 */
	public function __construct( $data ) {
        
        // Check for admin panel (We are using this because is_admin() is not working for block editor)
        $current_url = '';
        if( isset($_SERVER['HTTP_REFERER']) ) {
            $current_url = filter_input( INPUT_SERVER, 'HTTP_REFERER', FILTER_SANITIZE_URL, FILTER_VALIDATE_URL );
        }
        $referrer = home_url( $current_url );
        if( $referrer && strpos( $referrer, admin_url() ) !== false ) {
            return;
        }

        if( empty( $data ) ) {
            return;
        }

		$this->advance_fee = new \Advanced_Extra_Fees_Woocommerce_Fee( $data );

		if ( $this->advance_fee instanceof \Advanced_Extra_Fees_Woocommerce_Fee ) {

			// set post type data
			$this->id               = (int) $this->advance_fee->get_id();
            $this->cost_rule_match  = $this->advance_fee->get_cost_rule_match( 'general_rule_match' );
            $product_fees_metabox   = $this->advance_fee->get_product_fees_metabox();

            // parse the product fees metabox
            $this->parse_product_fees_metabox( $product_fees_metabox );
		}
	}

    /**
     * Parse the product fees metabox.
     * 
     * @param array $product_fees_metabox
     * 
     * @since 1.0.0
     */
    public function parse_product_fees_metabox( $product_fees_metabox ) {

        $this->product_fees_metabox = $product_fees_metabox;

        if( !empty( $product_fees_metabox ) ) {
            foreach ( $product_fees_metabox as $key => $value ) {
                
                // Location specific
                if ( array_search( 'country', $value, true ) ) {
                    $this->country_array[ $key ] = $value;
                }
                if ( array_search( 'state', $value, true ) ) {
                    $this->state_array[ $key ] = $value;
                }
                if ( array_search( 'city', $value, true ) ) {
                    $this->city_array[ $key ] = $value;
                }
                if( array_search( 'postcode', $value, true ) ) {
                    $this->postcode_array[ $key ] = $value;
                }
                if( array_search( 'zone', $value, true ) ) {
                    $this->zone_array[ $key ] = $value;
                }

                // Product specific
                if( array_search( 'product', $value, true ) ) {
                    $this->product_array[ $key ] = $value;
                }
                if( array_search( 'category', $value, true ) ) {
                    $this->category_array[ $key ] = $value;
                }
                if( array_search( 'tag', $value, true ) ) {
                    $this->tag_array[ $key ] = $value;
                }
                if( array_search( 'product_qty', $value, true ) ) {
                    $this->product_qty_array[ $key ] = $value;
                }

                // User specific
                if( array_search( 'user', $value, true ) ) {
                    $this->user_array[ $key ] = $value;
                }
                if( array_search( 'user_role', $value, true ) ) {
                    $this->user_role_array[ $key ] = $value;
                }

                // Cart specific
                if( array_search( 'cart_total', $value, true ) ) {
                    $this->cart_total_array[ $key ] = $value;
                }
                if( array_search( 'cart_totalafter', $value, true ) ) {
                    $this->cart_totalafter_array[ $key ] = $value;
                }
                if( array_search( 'cart_productspecific', $value, true ) ) {
                    $this->cart_productspecific_array[ $key ] = $value;
                }
                if( array_search( 'quantity', $value, true ) ) {
                    $this->quantity_array[ $key ] = $value;
                }
                if( array_search( 'weight', $value, true ) ) {
                    $this->weight_array[ $key ] = $value;
                }
                if( array_search( 'coupon', $value, true ) ) {
                    $this->coupon_array[ $key ] = $value;
                }
                if( array_search( 'shipping_class', $value, true ) ) {
                    $this->shipping_class_array[ $key ] = $value;
                }

                // Payment specific
                if( array_search( 'payment', $value, true ) ) {
                    $this->payment_array[ $key ] = $value;
                }

                // Shipping specific
                if( array_search( 'shipping_method', $value, true ) ) {
                    $this->shipping_method_array[ $key ] = $value;
                }

                // Product attribute specific
                foreach ( wc_get_attribute_taxonomies() as $attribute ) {
                    $att_name = wc_attribute_taxonomy_name( $attribute->attribute_name );
                    if ( array_search( $att_name, $value, true ) ) {
                        // If user add same attribute multiple time then merge the values
                        if( array_key_exists( $att_name, $this->product_attribute_array ) ) {
                            $this->product_attribute_array[ $att_name ]['product_fees_conditions_values'] = array_merge( $this->product_attribute_array[ $att_name ]['product_fees_conditions_values'], $value['product_fees_conditions_values'] );
                        } else {
                            $this->product_attribute_array[ $att_name ] = $value;
                        }
                    }
                }
            }
        }
    }

    /**
     * Get the fee rule match value.
     * 
     * @since 1.0.0
     */
    public function get_rule_match() {
        return $this->cost_rule_match;
    }

    /**
     * Check all conditional rule validation and return the result.
     * 
     * @return boolen
     * 
     * @since 1.0.0
     */
    public function is_fee_passed_conditional_rule_validation() {

        if( is_admin() ) {
            return;
        }

        // Check if product fees metabox is empty then fee will apply to all products
        if( empty( $this->product_fees_metabox ) ) {
            return true;
        }

        $is_passed = array();

        //Check if is country exist
        if ( isset( $this->country_array ) && ! empty( $this->country_array ) && is_array( $this->country_array ) ) {

            $country_passed = $this->dsaefw_match_country_rules( $this->country_array );

            if ( $country_passed ) {
                $is_passed['dsaefw_has_fee_based_on_country'] = true;
            } else {
                $is_passed['dsaefw_has_fee_based_on_country'] = false;
            }
        }

        // Check if is state exist
        if ( isset( $this->state_array ) && ! empty( $this->state_array ) && is_array( $this->state_array ) ) {

            $state_passed = $this->dsaefw_match_state_rules( $this->state_array );

            if ( $state_passed ) {
                $is_passed['dsaefw_has_fee_based_on_state'] = true;
            } else {
                $is_passed['dsaefw_has_fee_based_on_state'] = false;
            }
        }

        // Check if is city exist
        if ( isset( $this->city_array ) && ! empty( $this->city_array ) && is_array( $this->city_array ) ) {
            
            $city_passed = $this->dsaefw_match_city_rules( $this->city_array );

            if ( $city_passed ) {
                $is_passed['dsaefw_has_fee_based_on_city'] = true;
            } else {
                $is_passed['dsaefw_has_fee_based_on_city'] = false;
            }
        }

        // Check if is postcode exist
        if ( isset( $this->postcode_array ) && ! empty( $this->postcode_array ) && is_array( $this->postcode_array ) ) {

            $postcode_passed = $this->dsaefw_match_postcode_rules( $this->postcode_array );

            if ( $postcode_passed ) {
                $is_passed['dsaefw_has_fee_based_on_postcode'] = true;
            } else {
                $is_passed['dsaefw_has_fee_based_on_postcode'] = false;
            }
        }

        // Check if is zone exist
        if ( isset( $this->zone_array ) && ! empty( $this->zone_array ) && is_array( $this->zone_array ) ) {

            $zone_passed = $this->dsaefw_match_zone_rules( $this->zone_array );

            if ( $zone_passed ) {
                $is_passed['dsaefw_has_fee_based_on_zone'] = true;
            } else {
                $is_passed['dsaefw_has_fee_based_on_zone'] = false;
            }
        }
        
        // Check if is product exist
        if ( isset( $this->product_array ) && ! empty( $this->product_array ) && is_array( $this->product_array ) ) {

            $product_passed = $this->dsaefw_match_product_rules( $this->product_array );

            if ( $product_passed ) {
                $is_passed['dsaefw_has_fee_based_on_product'] = true;
            } else {
                $is_passed['dsaefw_has_fee_based_on_product'] = false;
            }
        }

        // Check if is category exist
        if ( isset( $this->category_array ) && ! empty( $this->category_array ) && is_array( $this->category_array ) ) {

            $category_passed = $this->dsaefw_match_category_rules( $this->category_array );

            if ( $category_passed ) {
                $is_passed['dsaefw_has_fee_based_on_category'] = true;
            } else {
                $is_passed['dsaefw_has_fee_based_on_category'] = false;
            }
        }

        // Check if is tag exist
        if ( isset( $this->tag_array ) && ! empty( $this->tag_array ) && is_array( $this->tag_array ) ) {

            $tag_passed = $this->dsaefw_match_tag_rules( $this->tag_array );

            if ( $tag_passed ) {
                $is_passed['dsaefw_has_fee_based_on_tag'] = true;
            } else {
                $is_passed['dsaefw_has_fee_based_on_tag'] = false;
            }
        }

        // Check if is product qty exist
        if ( isset( $this->product_qty_array ) && ! empty( $this->product_qty_array ) && is_array( $this->product_qty_array ) ) {

            $product_qty_passed = $this->dsaefw_match_product_qty_rules( $this->product_qty_array );

            if ( $product_qty_passed ) {
                $is_passed['dsaefw_has_fee_based_on_product_qty'] = true;
            } else {
                $is_passed['dsaefw_has_fee_based_on_product_qty'] = false;
            }
        }

        // Check if is user exist
        if ( isset( $this->user_array ) && ! empty( $this->user_array ) && is_array( $this->user_array ) ) {

            $user_passed = $this->dsaefw_match_user_rules( $this->user_array );

            if ( $user_passed ) {
                $is_passed['dsaefw_has_fee_based_on_user'] = true;
            } else {
                $is_passed['dsaefw_has_fee_based_on_user'] = false;
            }
        }

        // Check if is user role exist
        if ( isset( $this->user_role_array ) && ! empty( $this->user_role_array ) && is_array( $this->user_role_array ) ) {

            $user_role_passed = $this->dsaefw_match_user_role_rules( $this->user_role_array );

            if ( $user_role_passed ) {
                $is_passed['dsaefw_has_fee_based_on_user_role'] = true;
            } else {
                $is_passed['dsaefw_has_fee_based_on_user_role'] = false;
            }
        }

        // Check if is cart total (Before Discount) exist
        if ( isset( $this->cart_total_array ) && ! empty( $this->cart_total_array ) && is_array( $this->cart_total_array ) ) {

            $cart_total_passed = $this->dsaefw_match_cart_total_rules( $this->cart_total_array );

            if ( $cart_total_passed ) {
                $is_passed['dsaefw_has_fee_based_on_cart_total'] = true;
            } else {
                $is_passed['dsaefw_has_fee_based_on_cart_total'] = false;
            }
        }

        // Check if is cart total (After Discount) exist
        if ( isset( $this->cart_totalafter_array ) && ! empty( $this->cart_totalafter_array ) && is_array( $this->cart_totalafter_array ) ) {

            $cart_totalafter_passed = $this->dsaefw_match_cart_totalafter_rules( $this->cart_totalafter_array );

            if ( $cart_totalafter_passed ) {
                $is_passed['dsaefw_has_fee_based_on_cart_totalafter'] = true;
            } else {
                $is_passed['dsaefw_has_fee_based_on_cart_totalafter'] = false;
            }
        }

        // Check if is cart product specific subtotal exist
        if ( isset( $this->cart_productspecific_array ) && ! empty( $this->cart_productspecific_array ) && is_array( $this->cart_productspecific_array ) ) {

            $cart_productspecific_passed = $this->dsaefw_match_cart_productspecific_rules( $this->cart_productspecific_array );

            if ( $cart_productspecific_passed ) {
                $is_passed['dsaefw_has_fee_based_on_cart_productspecific'] = true;
            } else {
                $is_passed['dsaefw_has_fee_based_on_cart_productspecific'] = false;
            }
        }

        // Check if is quantity exist
        if ( isset( $this->quantity_array ) && ! empty( $this->quantity_array ) && is_array( $this->quantity_array ) ) {

            $quantity_passed = $this->dsaefw_match_quantity_rules( $this->quantity_array );

            if ( $quantity_passed ) {
                $is_passed['dsaefw_has_fee_based_on_quantity'] = true;
            } else {
                $is_passed['dsaefw_has_fee_based_on_quantity'] = false;
            }
        }

        // Check if is weight exist
        if ( isset( $this->weight_array ) && ! empty( $this->weight_array ) && is_array( $this->weight_array ) ) {

            $weight_passed = $this->dsaefw_match_weight_rules( $this->weight_array );

            if ( $weight_passed ) {
                $is_passed['dsaefw_has_fee_based_on_weight'] = true;
            } else {
                $is_passed['dsaefw_has_fee_based_on_weight'] = false;
            }
        }

        // Check if is coupon exist
        if ( isset( $this->coupon_array ) && ! empty( $this->coupon_array ) && is_array( $this->coupon_array ) ) {

            $coupon_passed = $this->dsaefw_match_coupon_rules( $this->coupon_array );

            if ( $coupon_passed ) {
                $is_passed['dsaefw_has_fee_based_on_coupon'] = true;
            } else {
                $is_passed['dsaefw_has_fee_based_on_coupon'] = false;
            }
        }

        // Check if is shipping class exist
        if ( isset( $this->shipping_class_array ) && ! empty( $this->shipping_class_array ) && is_array( $this->shipping_class_array ) ) {

            $shipping_class_passed = $this->dsaefw_match_shipping_class_rules( $this->shipping_class_array );

            if ( $shipping_class_passed ) {
                $is_passed['dsaefw_has_fee_based_on_shipping_class'] = true;
            } else {
                $is_passed['dsaefw_has_fee_based_on_shipping_class'] = false;
            }
        }

        // Check if is payment exist
        if ( isset( $this->payment_array ) && ! empty( $this->payment_array ) && is_array( $this->payment_array ) ) {

            $payment_passed = $this->dsaefw_match_payment_rules( $this->payment_array );

            if ( $payment_passed ) {
                $is_passed['dsaefw_has_fee_based_on_payment'] = true;
            } else {
                $is_passed['dsaefw_has_fee_based_on_payment'] = false;
            }
        }

        // Check if is shipping method exist
        if ( isset( $this->shipping_method_array ) && ! empty( $this->shipping_method_array ) && is_array( $this->shipping_method_array ) ) {

            $shipping_method_passed = $this->dsaefw_match_shipping_method_rules( $this->shipping_method_array );

            if ( $shipping_method_passed ) {
                $is_passed['dsaefw_has_fee_based_on_shipping_method'] = true;
            } else {
                $is_passed['dsaefw_has_fee_based_on_shipping_method'] = false;
            }
        }

        // Check if is product attribute exist
        if ( isset( $this->product_attribute_array ) && ! empty( $this->product_attribute_array ) && is_array( $this->product_attribute_array ) ) {

            $product_attribute_passed = $this->dsaefw_match_product_attribute_rules( $this->product_attribute_array );

            if ( $product_attribute_passed ) {
                $is_passed['dsaefw_has_fee_based_on_product_attribute'] = true;
            } else {
                $is_passed['dsaefw_has_fee_based_on_product_attribute'] = false;
            }
        }
        
        if ( ! empty( $is_passed ) && is_array( $is_passed ) ) {
            return ( 'any' === $this->get_rule_match() ) ? in_array( true, $is_passed, true ) : !in_array( false, $is_passed, true );
        }

        return true;
    }

    /**
	 * Find unique id based on given array
	 *
	 * @param array  $is_passed
	 * @param string $has_fee_based
	 * @param string $general_rule_match
	 *
	 * @return string $main_is_passed
     * 
	 * @since 1.0.0
     * 
     * @internal
	 */
	public function dsaefw_check_all_passed_general_rule( $is_passed, $has_fee_based, $general_rule_match ) {
		
        $main_is_passed = false;
		$flag           = array();

		if ( ! empty( $is_passed ) ) {

			foreach ( $is_passed as $key => $is_passed_value ) {
				if ( true === $is_passed_value[ $has_fee_based ] ) {
					$flag[ $key ] = true;
				} else {
					$flag[ $key ] = false;
				}
			}

            $main_is_passed = ('any' === $general_rule_match) ? in_array(true, $flag, true) : !in_array(false, $flag, true);
		}

		return $main_is_passed;
	}

    /**
     * Get cart product attributes
     * 
     * @return array $cart_product_attributes
     * 
     * @since 1.0.0
     * 
     * @internal
     * 
     * @uses     WC()->cart->get_cart()
     * @uses     wc_get_product()
     * @uses     WC_Product::get_attributes()
     * @uses     WC_Product_Attribute::get_slugs()
     */
    public function dsaefw_get_cart_product_attributes() {

        $cart_array = WC()->cart->get_cart();

        $cart_product_attributes = array();
		
        foreach ( $cart_array as $cart_item ) {
            
            $product_id = !empty( $cart_item['variation_id'] ) && $cart_item['variation_id'] > 0 ? $cart_item['variation_id'] : $cart_item['product_id'];
            $product = wc_get_product( $product_id );

            if ( ! $product->is_virtual( 'yes' ) ) {
                foreach( $product->get_attributes() as $pa_key => $pa_value ) {

                    if( $product->is_type( 'variation' ) ) {
                        
                        // For Variation product
                        if( empty( $pa_value ) ) {
                            
                            // For 'Any' attribute value
                            $variation_data = $cart_item['variation'];
                            $selected_value = $variation_data["attribute_$pa_key"] ?? '';
    
                            $terms = wc_get_product_terms($product->get_parent_id(), $pa_key, ['fields' => 'slugs']);
                            if ( !empty($terms) ) {
                                // If the selected value is one of the valid terms, it's from "Any" options
                                if ( in_array( $selected_value, $terms, true ) ) {
                                    $cart_product_attributes[] = $selected_value;
                                }
                            }
                        } else {
    
                            // For 'Specific' attribute value
                            $cart_product_attributes[] = $pa_value;
                        }
                    } else {
    
                        // For Simple product
                        foreach( $pa_value->get_slugs() as $sj_slugs ){
                            $cart_product_attributes[] = $sj_slugs;
                        }
                    }
                }
            }
		}

		return array_unique( $cart_product_attributes );
    }

    /**
	 * Find a matching zone for a given package.
	 *
	 * @param array $available_zone_id_array
	 *
	 * @return int $return_zone_id
	 *
	 * @since 1.0.0
     * 
     * @internal
	 *
	 * @uses   WC_Customer::get_shipping_country()
     * @uses   WC_Customer::get_shipping_state()
	 * @uses   WC_Customer::get_shipping_postcode()
	 * @uses   wc_postcode_location_matcher()
	 */
	public function dsaefw_check_zone_available( $available_zone_id_array ) {

        if( empty( WC()->customer ) ) {
            return;
        }

		$return_zone_id     = '';
		$country            = strtoupper( wc_clean( WC()->customer->get_shipping_country() ) );
		$state              = strtoupper( wc_clean( WC()->customer->get_shipping_state() ) );
		$postcode           = wc_normalize_postcode( wc_clean( WC()->customer->get_shipping_postcode() ) );
		$state_flag         = false;
		$flag               = false;
		$postcode_locations = array();
		$zone_array         = array();
		foreach ( $available_zone_id_array as $zone_id ) {
			$zone_by_id = WC_Shipping_Zones::get_zone_by( 'zone_id', $zone_id );
			$zones = array();
			if ( isset($zone_by_id) && !empty($zone_by_id) ) {
				$zones = $zone_by_id->get_zone_locations();	
			}
			if ( ! empty( $zones ) ) {
				foreach ( $zones as $zone_location ) {
					if ( 'country' === $zone_location->type || 'state' === $zone_location->type ) {
						$zone_array[ $zone_id ][ $zone_location->type ][] = $zone_location->code;
					}
					$location = new stdClass();
					if ( 'postcode' === $zone_location->type ) {
						$location->zone_id       = $zone_id;
						$location->location_code = $zone_location->code;
						if ( false !== strpos( $location->location_code, '...' ) ) {
							$postcode_locations_ex = explode( '...', $location->location_code );
							$start_index           = $postcode_locations_ex[0];
							$end_index             = $postcode_locations_ex[1];
							if ( $start_index < $end_index ) {
								$total_count = $end_index - $start_index;
								$new_index   = $start_index;
								for ( $i = 0; $i <= $total_count; $i ++ ) {
									$desh_location = new stdClass();
									if ( 0 === $i ) {
										$new_index = $start_index;
									} elseif ( $total_count === $i ) {
										$new_index = $end_index;
									} else {
										$new_index += 1;
									}
									$desh_location->zone_id = $zone_id;
									settype( $new_index, 'string' );
									$desh_location->location_code         = $new_index;
									$postcode_locations[ $zone_id ][ $i ] = $desh_location;
								}
							}
						} else {
							$postcode_locations[ $zone_id ][] = $location;
						}
					}
				}
			}
		}
		if ( ! empty( $zone_array ) ) {
			foreach ( $zone_array as $zone_id => $zone_location_detail ) {
				foreach ( $zone_location_detail as $zone_location_type => $zone_location_code ) {
					if ( 'country' === $zone_location_type ) {
						if ( $postcode_locations ) {
							foreach ( $postcode_locations as $post_zone_id => $postcode_location_detail ) {
								if ( $zone_id === $post_zone_id ) {
									if ( in_array( $country, $zone_location_code, true ) ) {
										$flag = 1;
									}
								} else {
									if ( in_array( $country, $zone_location_code, true ) ) {
										$return_zone_id = $zone_id;
									}
								}
							}
						} else {
							if ( in_array( $country, $zone_location_code, true ) ) {
								$return_zone_id = $zone_id;
							}
						}
					}
					if ( 'state' === $zone_location_type ) {
						$state_array = array();
						foreach ( $zone_location_code as $subzone_location_code ) {
							if ( false !== strpos( $subzone_location_code, ':' ) ) {
								$sub_zone_location_code_explode = explode( ':', $subzone_location_code );
							}
							$state_array[] = $sub_zone_location_code_explode[1];
							if ( ! $postcode_locations ) {
								if ( in_array( $state, $state_array, true ) ) {
									$return_zone_id = $zone_id;
									$state_flag     = true;
								}
							} else {
								if ( in_array( $state, $state_array, true ) ) {
									$flag = 1;
								}
							}
						}
					}
				}
			}
		} else {
			if ( $postcode_locations ) {
				$flag = 1;
			}
		}

		if ( true === $state_flag || 1 === $flag ) {
			if ( $postcode_locations ) {
				foreach ( $postcode_locations as $post_zone_id => $postcode_location_detail ) {
					$matches       = wc_postcode_location_matcher( $postcode, $postcode_location_detail, 'zone_id', 'location_code', $country );
					$matches_count = count( $matches );
					if ( 0 !== $matches_count ) {
						$matches_array_key = array_keys( $matches );
						$return_zone_id    = $matches_array_key[0];
					} else {
						$return_zone_id = '';
					}
				}
			}
		}

		return $return_zone_id;
	}

    /**
     * Check if product is already in cart
     * 
     * @param int $product_id
     * @param boolean $return_cart_item
     * 
     * @return boolean|array
     * 
     * @since 1.0.0
     * 
     * @internal
     */
    public function dsaefw_is_product_in_cart( $product_id, $return_cart_item = false ) {

        if ( empty( $product_id ) ) {
			return false;
		}

        //Sometime change type to string so we need it.
        $product_id = absint( $product_id );

        $product_obj = wc_get_product( $product_id );

		if ( ! $product_obj ) {
			return false;
		}

        if ( ! WC()->cart ) {
			return;
		}

        $exist_in_cart = false;

        $cart = WC()->cart->get_cart();

        foreach ( $cart as $cart_item ) {
            
            //Product ID
            $cart_product_id   = $cart_item['product_id'];

            //Variation ID
			$cart_variation_id = ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : 0;

            //Variable ID
			$cart_parent_id    = $cart_variation_id ? wp_get_post_parent_id( $cart_variation_id ) : 0;

            if( $product_obj->is_type( 'variable' ) && $cart_parent_id === $product_id ) {

                //Variable Product check
                $exist_in_cart = true;
            } else if( $product_obj->is_type( 'variation' ) && $cart_variation_id === $product_id ) {

                //Variation Product check
                $exist_in_cart = true;
            } else if( $cart_product_id === $product_id ) {

                //Simple and other product type check
                $exist_in_cart = true;
            }
            if( $exist_in_cart ) {
                if( $return_cart_item ) {
                    return $cart_item;
                } else {
                    return $exist_in_cart;
                }
            }
        }

        return $exist_in_cart;
    }

    /**
     * Prepare data for product specific cart data
     * 
     * @param array $return_type
     * 
     * @return array
     * 
     * @since 1.0.0
     * 
     * @internal
     * 
     * @uses     Advanced_Extra_Fees_Woocommerce_Public::dsaefw_cart_line_specific_data()
     * @uses     Advanced_Extra_Fees_Woocommerce::dsaefw_remove_currency_symbol()
     * @uses     wc_prices_include_tax()
     */
    public function dsaefw_get_product_specific_cart_data( $return_type = '' ) {

        $return_data = array(
            'quantity' => dsaefw()->dsaefw_public_object()->dsaefw_cart_line_specific_data(),
            'subtotal' => floatval( wc_prices_include_tax() ? WC()->cart->subtotal : WC()->cart->subtotal_ex_tax )
        );

        // Check if product fees metabox is empty then return total cart data
        if( empty( $this->product_fees_metabox ) ) {

            if ( array_key_exists( $return_type, $return_data ) ) {
                return $return_data[$return_type];
            }

            return $return_data;
        }

        $prepare_array = array();

        foreach ( $this->product_fees_metabox as $condition ) {
            
            $condition_value = !empty( $condition['product_fees_conditions_values'] ) && is_array( $condition['product_fees_conditions_values'] ) ? array_map( 'intval', $condition['product_fees_conditions_values'] ) : array();
            
            // For Product condition
            if( array_search( 'product', $condition, true ) ) {
                    
                foreach ( WC()->cart->get_cart() as $cart_item ) {

                    $product = $cart_item['data'];
                    
                    // Check if product is not a product object then skip the loop
                    if ( ! is_a( $product, 'WC_Product' ) ) {
                        continue;
                    }   
                    
                    $product_id = $product->get_id();
                    
                    $flag = false;

                    if ( 
                        ( 'is_equal_to' === $condition['product_fees_conditions_is'] && in_array( $product_id, $condition_value, true ) )
                        || ( 'not_in' === $condition['product_fees_conditions_is'] && ! in_array( $product_id, $condition_value, true ) ) 
                    ) {
                        $flag = true;
                    }

                    if( $flag ) {
                        $prepare_array[$product_id]['quantity'] = absint( $cart_item['quantity'] );
                        $prepare_array[$product_id]['subtotal'] = floatval( dsaefw()->dsaefw_remove_currency_symbol(WC()->cart->get_product_subtotal( $product, $cart_item['quantity'] ) ) );
                    }
                }
            }
            // For Category and Tag condition
            if( array_search( 'category', $condition, true ) 
            || array_search( 'tag', $condition, true ) ) {
                foreach ( WC()->cart->get_cart() as $cart_item ) {
                    
                    //This is main object we will use it for prepare array
                    $cart_product = $cart_item['data'];

                    //Get product id from product type from cart
                    $product_id = 'variation' === $cart_product->get_type() ? $cart_product->get_parent_id() : $cart_product->get_id();

                    // This object for checking purpose
                    $product = wc_get_product( $product_id );

                    // Check if product is not a product object then skip the loop
                    if ( ! is_a( $product, 'WC_Product' ) ) {
                        continue;
                    }
                    
                    // Category check
                    if( array_search( 'category', $condition, true ) ) {
                        $cart_item_data = $product->get_category_ids();
                    }

                    // Tag check
                    if( array_search( 'tag', $condition, true ) ) {
                        $cart_item_data = $product->get_tag_ids();
                    }
                    
                    $flag = false;

                    if ( 
                        ( 'is_equal_to' === $condition['product_fees_conditions_is'] && array_intersect( $cart_item_data, $condition_value ) )
                        || ( 'not_in' === $condition['product_fees_conditions_is'] && ! array_intersect( $cart_item_data, $condition_value ) ) 
                    ) {
                        $flag = true;
                    }

                    if( $flag ) {
                        $prepare_array[$cart_product->get_id()]['quantity'] = absint( $cart_item['quantity'] );
                        $prepare_array[$cart_product->get_id()]['subtotal'] = floatval( dsaefw()->dsaefw_remove_currency_symbol(WC()->cart->get_product_subtotal( wc_get_product( $cart_product->get_id() ), $cart_item['quantity'] ) ) );
                    }
                }
            }
        }
        
        $return_data = array(
            'quantity' => array_sum(array_column($prepare_array, 'quantity')),
            'subtotal' => array_sum(array_column($prepare_array, 'subtotal'))
        );

        if (array_key_exists( $return_type, $return_data ) ) {
            return $return_data[$return_type];
        }

        return $return_data;
    }

    /**
	 * Match country rules
	 *
	 * @param array  $country_array
	 *
	 * @return string $main_is_passed
	 *
	 * @since 1.0.0
	 *
	 * @uses     WC_Customer::get_shipping_country()
	 */
	public function dsaefw_match_country_rules( $country_array ) {
		
        if( empty( WC()->customer ) ) {
            return;
        }

        // Cart country data
        $selected_country = WC()->customer->get_shipping_country();

		$is_passed        = array();

		foreach ( $country_array as $key => $country ) {
			if ( 'is_equal_to' === $country['product_fees_conditions_is'] ) {
				if ( ! empty( $country['product_fees_conditions_values'] ) ) {
					if ( in_array( $selected_country, $country['product_fees_conditions_values'], true ) ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_country'] = true;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_country'] = false;
					}
				}
				if ( empty( $country['product_fees_conditions_values'] ) ) {
					$is_passed[ $key ]['dsaefw_has_fee_based_on_country'] = true;
				}
			}
			if ( 'not_in' === $country['product_fees_conditions_is'] ) {
				if ( ! empty( $country['product_fees_conditions_values'] ) ) {
					if ( in_array( $selected_country, $country['product_fees_conditions_values'], true ) || in_array( 'all', $country['product_fees_conditions_values'], true ) ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_country'] = false;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_country'] = true;
					}
				}
			}
		}
		$main_is_passed = $this->dsaefw_check_all_passed_general_rule( $is_passed, 'dsaefw_has_fee_based_on_country', $this->get_rule_match() );

		return $main_is_passed;
	}

    /**
	 * Match state rules
	 *
	 * @param array  $state_array
	 *
	 * @return string $main_is_passed
	 *
	 * @since 1.0.0
     * 
     * @uses     WC_Customer::get_shipping_state()
     * @uses     WC_Customer::get_shipping_country()
	 */
	public function dsaefw_match_state_rules( $state_array ) {

        if( empty( WC()->customer ) ) {
            return;
        }

        // Cart country and state data
		$country        = WC()->customer->get_shipping_country();
		$state          = WC()->customer->get_shipping_state();
		$selected_state = $country . ':' . $state;

		$is_passed      = array();

		foreach ( $state_array as $key => $get_state ) {
			if ( 'is_equal_to' === $get_state['product_fees_conditions_is'] ) {
				if ( ! empty( $get_state['product_fees_conditions_values'] ) ) {
					if ( in_array( $selected_state, $get_state['product_fees_conditions_values'], true ) ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_state'] = true;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_state'] = false;
					}
				}
			}
			if ( 'not_in' === $get_state['product_fees_conditions_is'] ) {
				if ( ! empty( $get_state['product_fees_conditions_values'] ) ) {
					if ( in_array( $selected_state, $get_state['product_fees_conditions_values'], true ) ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_state'] = false;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_state'] = true;
					}
				}
			}
		}
		$main_is_passed = $this->dsaefw_check_all_passed_general_rule( $is_passed, 'dsaefw_has_fee_based_on_state', $this->get_rule_match() );

		return $main_is_passed;
	}

    /**
	 * Match city rules
	 *
	 * @param array  $city_array
	 *
	 * @return string $main_is_passed
	 *
	 * @since 1.0.0
	 *
	 * @uses     WC_Customer::get_shipping_city()
	 */
	public function dsaefw_match_city_rules( $city_array ) {
        
        if( empty( WC()->customer ) ) {
            return;
        }

        // Cart city data
		$selected_city = WC()->customer->get_shipping_city() ? strtolower( WC()->customer->get_shipping_city() ) : '';

		$is_passed        = array();

		foreach ( $city_array as $key => $city ) {
			if ( ! empty( $city['product_fees_conditions_values'] ) ) {

                if( is_string( $city['product_fees_conditions_values'] ) ) {
                    $citystr        = str_replace( PHP_EOL, "<br/>", $city['product_fees_conditions_values'] );
                    $city_val_array = explode( '<br/>', $citystr );
                } else {
                    $city_val_array = $city['product_fees_conditions_values'];
                }
                $city_val_array = array_map(function($value) {
                    return strtolower(trim($value));
                }, $city_val_array);
				
				if ( 'is_equal_to' === $city['product_fees_conditions_is'] ) {
					if ( in_array( $selected_city, $city_val_array, true ) ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_city'] = true;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_city'] = false;
					}
				}
				if ( 'not_in' === $city['product_fees_conditions_is'] ) {
					if ( in_array( $selected_city, $city_val_array, true ) ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_city'] = false;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_city'] = true;
					}
				}
			}
		}
		$main_is_passed = $this->dsaefw_check_all_passed_general_rule( $is_passed, 'dsaefw_has_fee_based_on_city', $this->get_rule_match() );

		return $main_is_passed;
	}

    /**
	 * Match postcode rules
	 *
	 * @param array  $postcode_array
	 *
	 * @return string $main_is_passed
	 *
	 * @since 1.0.0
	 *
	 * @uses     WC_Customer::get_shipping_postcode()
	 */
	public function dsaefw_match_postcode_rules( $postcode_array ) {

        if( empty( WC()->customer ) ) {
            return;
        }
		
        // Cart postcode data
        $selected_postcode = WC()->customer->get_shipping_postcode();

		$is_passed          = array();
        $postcode_val_array = array();

		foreach ( $postcode_array as $key => $postcode ) {

            if( is_string( $postcode['product_fees_conditions_values'] ) ) {
                $postcodestr        = str_replace( PHP_EOL, "<br/>", $postcode['product_fees_conditions_values'] );
                $postcode_val_array = explode( '<br/>', $postcodestr );
            } else {
                $postcode_val_array = $postcode['product_fees_conditions_values'];
            }
            $postcode_val_array = array_map( 'trim', $postcode_val_array );

            if ( ! empty( $postcode_val_array ) ) {
			    if ( 'is_equal_to' === $postcode['product_fees_conditions_is'] ) {
					if ( in_array( $selected_postcode, $postcode_val_array, true ) ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_postcode'] = true;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_postcode'] = false;

					}
				}
			    if ( 'not_in' === $postcode['product_fees_conditions_is'] ) {
					if ( in_array( $selected_postcode, $postcode_val_array, true ) ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_postcode'] = false;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_postcode'] = true;
					}
				}
            }
		}
        
		$main_is_passed = $this->dsaefw_check_all_passed_general_rule( $is_passed, 'dsaefw_has_fee_based_on_postcode', $this->get_rule_match() );
        
		return $main_is_passed;
	}

    /**
	 * Match zone rules
	 *
	 * @param array  $zone_array
	 *
	 * @return string $main_is_passed
	 *
	 * @since 1.0.0
	 *
	 * @uses     dsaefw_check_zone_available()
	 */
	public function dsaefw_match_zone_rules( $zone_array ) {

		$is_passed = array();
		foreach ( $zone_array as $key => $zone ) {

            if ( ! empty( $zone['product_fees_conditions_values'] ) ) {
                
                $get_zonelist                           = $this->dsaefw_check_zone_available( $zone['product_fees_conditions_values'] );
                $zone['product_fees_conditions_values'] = array_map( 'intval', $zone['product_fees_conditions_values'] );

			    if ( 'is_equal_to' === $zone['product_fees_conditions_is'] ) {
					if ( in_array( $get_zonelist, $zone['product_fees_conditions_values'], true ) ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_zone'] = true;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_zone'] = false;
					}
				}
			    if ( 'not_in' === $zone['product_fees_conditions_is'] ) {
					if ( in_array( $get_zonelist, $zone['product_fees_conditions_values'], true ) ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_zone'] = false;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_zone'] = true;
					}
				}
			}
		}
		$main_is_passed = $this->dsaefw_check_all_passed_general_rule( $is_passed, 'dsaefw_has_fee_based_on_zone', $this->get_rule_match() );

		return $main_is_passed;
	}

    /**
	 * Match simple products rules
	 *
	 * @param array  $product_array
	 *
	 * @return string $main_is_passed
	 *
	 * @since 1.0.0
	 */
	public function dsaefw_match_product_rules( $product_array ) {
		
        $is_passed = array();

        foreach( $product_array as $key => $product ) {
            if ( ! empty( $product['product_fees_conditions_values'] ) ) {

                if ( 'is_equal_to' === $product['product_fees_conditions_is'] ) {

                    foreach ( $product['product_fees_conditions_values'] as $product_id ) {
                        if( $this->dsaefw_is_product_in_cart( $product_id ) ) {
                            $is_passed[ $key ]['dsaefw_has_fee_based_on_product'] = true;
                            break;
                        } else {
                            $is_passed[ $key ]['dsaefw_has_fee_based_on_product'] = false;
                        }
                    }
                }

                if ( 'not_in' === $product['product_fees_conditions_is'] ) {

                    foreach ( $product['product_fees_conditions_values'] as $product_id ) {
                        if( $this->dsaefw_is_product_in_cart( $product_id ) ) {
                            $is_passed[ $key ]['dsaefw_has_fee_based_on_product'] = false;
                            break;
                        } else {
                            $is_passed[ $key ]['dsaefw_has_fee_based_on_product'] = true;
                        }
                    }
                }
            }
        }

		$main_is_passed = $this->dsaefw_check_all_passed_general_rule( $is_passed, 'dsaefw_has_fee_based_on_product', $this->get_rule_match() );

		return $main_is_passed;
	}

    /**
	 * Match category rules
	 *
	 * @param array  $category_array
	 *
	 * @return string $main_is_passed
	 *
	 * @since 1.0.0
	 */
	public function dsaefw_match_category_rules( $category_array ) {
        $is_passed = array();
        $main_is_passed = false;
        $cart_category_id_array = array();
        
        foreach (WC()->cart->get_cart() as $value ) {
            $cart_product_id = ( ! empty( $value['product_id'] ) && 0 !== $value['product_id'] ) ? $value['product_id'] : 0;
            $prod_obj = wc_get_product( $cart_product_id );
            $cart_category_id_array = array_map( 'absint', array_unique( array_merge( $cart_category_id_array, $prod_obj->get_category_ids() ) ) );
        }

        foreach( $category_array as $key => $category ) {
            if ( ! empty( $category['product_fees_conditions_values'] ) ) {

                $condition_category = array_map( 'absint', $category['product_fees_conditions_values'] );

                if ( 'is_equal_to' === $category['product_fees_conditions_is'] ) {

                    foreach ( $condition_category as $category_id ) {
                        if( in_array( $category_id, $cart_category_id_array, true ) ) {
                            $is_passed[ $key ]['dsaefw_has_fee_based_on_category'] = true;
                            break;
                        } else {
                            $is_passed[ $key ]['dsaefw_has_fee_based_on_category'] = false;
                        }
                    }
                }

                if ( 'not_in' === $category['product_fees_conditions_is'] ) {

                    foreach ( $condition_category as $category_id ) {
                        if( in_array( $category_id, $cart_category_id_array, true ) ) {
                            $is_passed[ $key ]['dsaefw_has_fee_based_on_category'] = false;
                            break;
                        } else {
                            $is_passed[ $key ]['dsaefw_has_fee_based_on_category'] = true;
                        }
                    }
                }
            }
        }
        $main_is_passed = $this->dsaefw_check_all_passed_general_rule( $is_passed, 'dsaefw_has_fee_based_on_category', $this->get_rule_match() );

		return $main_is_passed;
    }

    /**
	 * Match tag rules
	 *
	 * @param array  $tag_array
	 *
	 * @return string $main_is_passed
	 *
	 * @since 1.0.0
	 */
	public function dsaefw_match_tag_rules( $tag_array ) {
        $is_passed = array();
        $main_is_passed = false;
        $cart_tag_id_array = array();
        
        foreach (WC()->cart->get_cart() as $value ) {
            $cart_product_id = ( ! empty( $value['product_id'] ) && 0 !== $value['product_id'] ) ? $value['product_id'] : 0;
            $prod_obj = wc_get_product( $cart_product_id );
            $cart_tag_id_array = array_map( 'absint', array_unique( array_merge( $cart_tag_id_array, $prod_obj->get_tag_ids() ) ) );
        }

        foreach( $tag_array as $key => $tag ) {
            if ( ! empty( $tag['product_fees_conditions_values'] ) ) {

                $condition_tag = array_map( 'absint', $tag['product_fees_conditions_values'] );

                if ( 'is_equal_to' === $tag['product_fees_conditions_is'] ) {

                    foreach ( $condition_tag as $tag_id ) {
                        if( in_array( $tag_id, $cart_tag_id_array, true ) ) {
                            $is_passed[ $key ]['dsaefw_has_fee_based_on_tag'] = true;
                            break;
                        } else {
                            $is_passed[ $key ]['dsaefw_has_fee_based_on_tag'] = false;
                        }
                    }
                }

                if ( 'not_in' === $tag['product_fees_conditions_is'] ) {

                    foreach ( $condition_tag as $tag_id ) {
                        if( in_array( $tag_id, $cart_tag_id_array, true ) ) {
                            $is_passed[ $key ]['dsaefw_has_fee_based_on_tag'] = false;
                            break;
                        } else {
                            $is_passed[ $key ]['dsaefw_has_fee_based_on_tag'] = true;
                        }
                    }
                }
            }
        }
        $main_is_passed = $this->dsaefw_check_all_passed_general_rule( $is_passed, 'dsaefw_has_fee_based_on_tag', $this->get_rule_match() );

		return $main_is_passed;
    }

    /**
	 * Match specific product quantity rules
	 *
	 * @param array  $product_qty_array
	 *
	 * @return string $main_is_passed
     * 
	 * @since 1.0.0
	 *
     * @uses    dsaefw_get_product_specific_cart_data()
	 */
	public function dsaefw_match_product_qty_rules( $product_qty_array ) {

        // Cart product quantity data
        $product_qty = $this->dsaefw_get_product_specific_cart_data( 'quantity' );

        $is_passed = array();

        foreach ( $product_qty_array as $key => $quantity ) {
            
			settype( $quantity['product_fees_conditions_values'], 'integer' );
			if ( 'is_equal_to' === $quantity['product_fees_conditions_is'] ) {
				if ( ! empty( $quantity['product_fees_conditions_values'] ) ) {
					if ( $product_qty === $quantity['product_fees_conditions_values'] ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_product_qty'] = true;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_product_qty'] = false;
					}
				}
			}
			if ( 'less_equal_to' === $quantity['product_fees_conditions_is'] ) {
				if ( ! empty( $quantity['product_fees_conditions_values'] ) ) {
					if ( $quantity['product_fees_conditions_values'] >= $product_qty ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_product_qty'] = true;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_product_qty'] = false;
					}
				}
			}
			if ( 'less_then' === $quantity['product_fees_conditions_is'] ) {
				if ( ! empty( $quantity['product_fees_conditions_values'] ) ) {
					if ( $quantity['product_fees_conditions_values'] > $product_qty ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_product_qty'] = true;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_product_qty'] = false;
					}
				}
			}
			if ( 'greater_equal_to' === $quantity['product_fees_conditions_is'] ) {
				if ( ! empty( $quantity['product_fees_conditions_values'] ) ) {
					if ( $quantity['product_fees_conditions_values'] <= $product_qty ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_product_qty'] = true;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_product_qty'] = false;
					}
				}
			}
			if ( 'greater_then' === $quantity['product_fees_conditions_is'] ) {
				if ( ! empty( $quantity['product_fees_conditions_values'] ) ) {
					if ( $quantity['product_fees_conditions_values'] < $product_qty ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_product_qty'] = true;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_product_qty'] = false;
					}
				}
			}
			if ( 'not_in' === $quantity['product_fees_conditions_is'] ) {
				if ( ! empty( $quantity['product_fees_conditions_values'] ) ) {
					if ( $product_qty === $quantity['product_fees_conditions_values'] ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_product_qty'] = false;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_product_qty'] = true;
					}
				}
			}
		}

        $main_is_passed = $this->dsaefw_check_all_passed_general_rule( $is_passed, 'dsaefw_has_fee_based_on_product_qty', $this->get_rule_match() );

		return $main_is_passed;
    }

    /**
	 * Match user rules
	 *
	 * @param array  $user_array
	 *
	 * @return string $main_is_passed
	 *
     * @since 1.0.0
	 *
	 * @uses     get_current_user_id()
	 */
	public function dsaefw_match_user_rules( $user_array ) {

        // Current login user id
		$current_user_id = get_current_user_id();

		$is_passed       = array();

		foreach ( $user_array as $key => $user ) {

			$user['product_fees_conditions_values'] = array_map( 'intval', $user['product_fees_conditions_values'] );

			if ( 'is_equal_to' === $user['product_fees_conditions_is'] ) {
				if ( in_array( $current_user_id, $user['product_fees_conditions_values'], true ) ) {
					$is_passed[ $key ]['dsaefw_has_fee_based_on_user'] = true;
				} else {
					$is_passed[ $key ]['dsaefw_has_fee_based_on_user'] = false;
				}
			}
			if ( 'not_in' === $user['product_fees_conditions_is'] ) {
				if ( in_array( $current_user_id, $user['product_fees_conditions_values'], true ) ) {
					$is_passed[ $key ]['dsaefw_has_fee_based_on_user'] = false;
				} else {
					$is_passed[ $key ]['dsaefw_has_fee_based_on_user'] = true;
				}
			}
		}
		$main_is_passed = $this->dsaefw_check_all_passed_general_rule( $is_passed, 'dsaefw_has_fee_based_on_user', $this->get_rule_match() );

		return $main_is_passed;
	}

    /**
	 * Match user role rules
	 *
	 * @param array  $user_role_array
	 *
	 * @return string $main_is_passed
	 *
	 * @since 1.0.0
	 *
	 * @uses     is_user_logged_in()
	 */
	public function dsaefw_match_user_role_rules( $user_role_array ) {
		
		// Check user loggedin or not
		global $current_user;
		$current_user_role = is_user_logged_in() ? $current_user->roles : [ 'guest' ];
        
		$is_passed = array();

		foreach ( $user_role_array as $key => $user_role ) {
            if ( 'is_equal_to' === $user_role['product_fees_conditions_is'] ) {
                if ( array_intersect( $current_user_role, $user_role['product_fees_conditions_values'] ) ) {
                    $is_passed[ $key ]['dsaefw_has_fee_based_on_user_role'] = true;
                } else {
                    $is_passed[ $key ]['dsaefw_has_fee_based_on_user_role'] = false;
                }
            }
            if ( 'not_in' === $user_role['product_fees_conditions_is'] ) {
                if ( array_intersect( $current_user_role, $user_role['product_fees_conditions_values'] ) ) {
                    $is_passed[ $key ]['dsaefw_has_fee_based_on_user_role'] = false;
                } else {
                    $is_passed[ $key ]['dsaefw_has_fee_based_on_user_role'] = true;
                }
            }
		}
		$main_is_passed = $this->dsaefw_check_all_passed_general_rule( $is_passed, 'dsaefw_has_fee_based_on_user_role', $this->get_rule_match() );

		return $main_is_passed;
	}

    /**
	 * Match rule based on cart subtotal before discount
	 *
	 * @param array  $cart_total_array
	 *
	 * @return string $main_is_passed
	 *
	 * @uses     Advanced_Extra_Fees_Woocommerce_Public::dsaefw_cart_line_specific_data()
	 *
	 * @since 1.0.0
	 *
	 */
	public function dsaefw_match_cart_total_rules( $cart_total_array ) {

        // Cart subtotal data
        $cart_subtotal = dsaefw()->dsaefw_public_object()->dsaefw_cart_line_specific_data('subtotal');

        $is_passed = array();

        foreach ( $cart_total_array as $key => $cart_total ) {

			settype( $cart_total['product_fees_conditions_values'], 'float' );

            if ( $cart_total['product_fees_conditions_values'] >= 0 && ! empty( $cart_total['product_fees_conditions_values'] ) ) {
			    if ( 'is_equal_to' === $cart_total['product_fees_conditions_is'] ) {
					if ( $cart_total['product_fees_conditions_values'] === $cart_subtotal ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_cart_total'] = true;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_cart_total'] = false;
					}
				}
			    if ( 'less_equal_to' === $cart_total['product_fees_conditions_is'] ) {
					if ( $cart_total['product_fees_conditions_values'] >= $cart_subtotal ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_cart_total'] = true;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_cart_total'] = false;
					}
				}
			    if ( 'less_then' === $cart_total['product_fees_conditions_is'] ) {
					if ( $cart_total['product_fees_conditions_values'] > $cart_subtotal ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_cart_total'] = true;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_cart_total'] = false;
					}
				}
			    if ( 'greater_equal_to' === $cart_total['product_fees_conditions_is'] ) {
					if ( $cart_total['product_fees_conditions_values'] <= $cart_subtotal ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_cart_total'] = true;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_cart_total'] = false;
					}
				}
			    if ( 'greater_then' === $cart_total['product_fees_conditions_is'] ) {
					if ( $cart_total['product_fees_conditions_values'] < $cart_subtotal ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_cart_total'] = true;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_cart_total'] = false;
					}
				}
			    if ( 'not_in' === $cart_total['product_fees_conditions_is'] ) {
					if ( $cart_total['product_fees_conditions_values'] === $cart_subtotal ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_cart_total'] = false;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_cart_total'] = true;
					}
				}
			}
		}

        $main_is_passed = $this->dsaefw_check_all_passed_general_rule( $is_passed, 'dsaefw_has_fee_based_on_cart_total', $this->get_rule_match() );

		return $main_is_passed;
    }

    /**
	 * Match rule based on cart subtotal after discount
	 *
	 * @param array  $cart_totalafter_array
	 *
	 * @return array $is_passed
	 * @uses     WC_Cart::get_total_discount()
	 *
	 * @since 1.0.0
	 *
	 * @uses     Advanced_Extra_Fees_Woocommerce_Public::dsaefw_cart_line_specific_data()
	 */
	public function dsaefw_match_cart_totalafter_rules( $cart_totalafter_array ) {

        // Cart subtotal after discount applied data
        $cart_subtotal_with_discount = dsaefw()->dsaefw_public_object()->dsaefw_cart_line_specific_data('subtotal_with_discount');

        $is_passed = array();

        foreach ( $cart_totalafter_array as $key => $cart_totalafter ) {

            settype( $cart_totalafter['product_fees_conditions_values'], 'float' );

            if ( $cart_totalafter['product_fees_conditions_values'] >= 0 || ! empty( $cart_totalafter['product_fees_conditions_values'] ) ) {
                if ( 'is_equal_to' === $cart_totalafter['product_fees_conditions_is'] ) {
                    if ( $cart_totalafter['product_fees_conditions_values'] === $cart_subtotal_with_discount ) {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_cart_totalafter'] = true;
                    } else {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_cart_totalafter'] = false;
                    }
                }
                if ( 'less_equal_to' === $cart_totalafter['product_fees_conditions_is'] ) {
                    if ( $cart_totalafter['product_fees_conditions_values'] >= $cart_subtotal_with_discount ) {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_cart_totalafter'] = true;
                    } else {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_cart_totalafter'] = false;
                    }
                }
                if ( 'less_then' === $cart_totalafter['product_fees_conditions_is'] ) {
                    if ( $cart_totalafter['product_fees_conditions_values'] > $cart_subtotal_with_discount ) {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_cart_totalafter'] = true;
                    } else {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_cart_totalafter'] = false;
                    }
                }
                if ( 'greater_equal_to' === $cart_totalafter['product_fees_conditions_is'] ) {
                    if ( $cart_totalafter['product_fees_conditions_values'] <= $cart_subtotal_with_discount ) {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_cart_totalafter'] = true;
                    } else {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_cart_totalafter'] = false;
                    }
                }
                if ( 'greater_then' === $cart_totalafter['product_fees_conditions_is'] ) {
                    if ( $cart_totalafter['product_fees_conditions_values'] < $cart_subtotal_with_discount ) {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_cart_totalafter'] = true;
                    } else {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_cart_totalafter'] = false;
                    }
                }
                if ( 'not_in' === $cart_totalafter['product_fees_conditions_is'] ) {
                    if ( $cart_totalafter['product_fees_conditions_values'] === $cart_subtotal_with_discount ) {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_cart_totalafter'] = false;
                    } else {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_cart_totalafter'] = true;
                    }
                }
            }
        }
        $main_is_passed = $this->dsaefw_check_all_passed_general_rule( $is_passed, 'dsaefw_has_fee_based_on_cart_totalafter', $this->get_rule_match() );

		return $main_is_passed;
    }

    /**
	 * Match rule based on cart subtotal for specific products
	 *
	 * @param array  $cart_specificproduct_array
	 *
	 * @return array $is_passed
	 *
	 * @since 1.0.0
     * 
     * @uses    dsaefw_get_product_specific_cart_data()
	 */
	public function dsaefw_match_cart_productspecific_rules( $cart_specificproduct_array ) {

        // Cart product subtotal data
        $product_subtotal = $this->dsaefw_get_product_specific_cart_data( 'subtotal' );

        $is_passed = array();

        foreach ( $cart_specificproduct_array as $key => $cart_specificproduct ) {

            settype( $cart_specificproduct['product_fees_conditions_values'], 'float' );

            if ( $cart_specificproduct['product_fees_conditions_values'] >= 0 || ! empty( $cart_specificproduct['product_fees_conditions_values'] ) ) {

                if ( 'is_equal_to' === $cart_specificproduct['product_fees_conditions_is'] ) {
                    if ( $cart_specificproduct['product_fees_conditions_values'] === $product_subtotal ) {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_cart_productspecific'] = true;
                    } else {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_cart_productspecific'] = false;
                    }
                }
                if ( 'less_equal_to' === $cart_specificproduct['product_fees_conditions_is'] ) {
                    if ( $cart_specificproduct['product_fees_conditions_values'] >= $product_subtotal ) {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_cart_productspecific'] = true;
                    } else {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_cart_productspecific'] = false;
                    }
                }
                if ( 'less_then' === $cart_specificproduct['product_fees_conditions_is'] ) {
                    if ( $cart_specificproduct['product_fees_conditions_values'] > $product_subtotal ) {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_cart_productspecific'] = true;
                    } else {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_cart_productspecific'] = false;
                    }
                }
                if ( 'greater_equal_to' === $cart_specificproduct['product_fees_conditions_is'] ) {
                    if ( $cart_specificproduct['product_fees_conditions_values'] <= $product_subtotal ) {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_cart_productspecific'] = true;
                    } else {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_cart_productspecific'] = false;
                    }
                }
                if ( 'greater_then' === $cart_specificproduct['product_fees_conditions_is'] ) {
                    if ( $cart_specificproduct['product_fees_conditions_values'] < $product_subtotal ) {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_cart_productspecific'] = true;
                    } else {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_cart_productspecific'] = false;
                    }
                }            
                if ( 'not_in' === $cart_specificproduct['product_fees_conditions_is'] ) {
                    if ( $cart_specificproduct['product_fees_conditions_values'] === $product_subtotal ) {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_cart_productspecific'] = false;
                    } else {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_cart_productspecific'] = true;
                    }
                }
            }
        }
        $main_is_passed = $this->dsaefw_check_all_passed_general_rule( $is_passed, 'dsaefw_has_fee_based_on_cart_productspecific', $this->get_rule_match() );

		return $main_is_passed;
    }

    /**
	 * Match rule based on total cart quantity
	 *
	 * @param array $quantity_array
	 *
	 * @return array $is_passed
     * 
	 * @since 1.0.0
     * 
     * @uses     Advanced_Extra_Fees_Woocommerce_Public::dsaefw_cart_line_specific_data()
	 */
	public function dsaefw_match_quantity_rules( $quantity_array ) {

        // Cart total quantity data
        $cart_quantity = dsaefw()->dsaefw_public_object()->dsaefw_cart_line_specific_data();

        $is_passed = array();
        
        foreach ( $quantity_array as $key => $quantity ) {
            
			settype( $quantity['product_fees_conditions_values'], 'integer' );

            if ( ! empty( $quantity['product_fees_conditions_values'] ) ) {

			    if ( 'is_equal_to' === $quantity['product_fees_conditions_is'] ) {
					if ( $quantity['product_fees_conditions_values'] === $cart_quantity ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_quantity'] = true;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_quantity'] = false;
					}
				}
			    if ( 'less_equal_to' === $quantity['product_fees_conditions_is'] ) {
					if ( $quantity['product_fees_conditions_values'] >= $cart_quantity ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_quantity'] = true;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_quantity'] = false;
					}
			    }
			    if ( 'less_then' === $quantity['product_fees_conditions_is'] ) {
					if ( $quantity['product_fees_conditions_values'] > $cart_quantity ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_quantity'] = true;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_quantity'] = false;
					}
				}
			    if ( 'greater_equal_to' === $quantity['product_fees_conditions_is'] ) {
					if ( $quantity['product_fees_conditions_values'] <= $cart_quantity ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_quantity'] = true;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_quantity'] = false;
					}
				}
			    if ( 'greater_then' === $quantity['product_fees_conditions_is'] ) {
					if ( $quantity['product_fees_conditions_values'] < $cart_quantity ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_quantity'] = true;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_quantity'] = false;
					}
				}
			    if ( 'not_in' === $quantity['product_fees_conditions_is'] ) {
					if ( $quantity['product_fees_conditions_values'] === $cart_quantity ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_quantity'] = false;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_quantity'] = true;
					}
				}
			}
		}
		$main_is_passed = $this->dsaefw_check_all_passed_general_rule( $is_passed, 'dsaefw_has_fee_based_on_quantity', $this->get_rule_match() );

		return $main_is_passed;

    }

    /**
	 * Match rule based on total cart weight
	 *
	 * @param array $weight_array
	 *
	 * @return array $is_passed
     * 
	 * @since 1.0.0
	 *
     * @uses     Advanced_Extra_Fees_Woocommerce_Public::dsaefw_cart_line_specific_data()
	 */
	public function dsaefw_match_weight_rules( $weight_array ) {

        // Cart total weight data
        $cart_weight = dsaefw()->dsaefw_public_object()->dsaefw_cart_line_specific_data( 'weight' );

        $is_passed = array();

        foreach ( $weight_array as $key => $weight ) {

			settype( $weight['product_fees_conditions_values'], 'float' );

            if ( ! empty( $weight['product_fees_conditions_values'] ) ) {
			    if ( 'is_equal_to' === $weight['product_fees_conditions_is'] ) {
					if ( $cart_weight === $weight['product_fees_conditions_values'] ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_weight'] = true;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_weight'] = false;
					}
				}
			    if ( 'less_equal_to' === $weight['product_fees_conditions_is'] ) {
					if ( $weight['product_fees_conditions_values'] >= $cart_weight ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_weight'] = true;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_weight'] = false;
					}
				}
			    if ( 'less_then' === $weight['product_fees_conditions_is'] ) {
					if ( $weight['product_fees_conditions_values'] > $cart_weight ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_weight'] = true;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_weight'] = false;
					}
				}
			    if ( 'greater_equal_to' === $weight['product_fees_conditions_is'] ) {
					if ( $weight['product_fees_conditions_values'] <= $cart_weight ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_weight'] = true;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_weight'] = false;
					}
				}
			    if ( 'greater_then' === $weight['product_fees_conditions_is'] ) {
					if ( $weight['product_fees_conditions_values'] < $cart_weight ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_weight'] = true;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_weight'] = false;
					}
				}
			    if ( 'not_in' === $weight['product_fees_conditions_is'] ) {
					if ( $weight['product_fees_conditions_values'] === $cart_weight ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_weight'] = false;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_weight'] = true;
					}
				}
			}
		}
		$main_is_passed = $this->dsaefw_check_all_passed_general_rule( $is_passed, 'dsaefw_has_fee_based_on_weight', $this->get_rule_match() );

		return $main_is_passed;
    }

    /**
	 * Match coupon role rules
     * 
	 * @param array  $coupon_array
	 *
	 * @return string $main_is_passed
	 *
	 * @since 1.0.0
	 *
	 * @uses     WC_Cart::get_coupons()
	 * @uses     WC_Coupon::is_valid()
	 */
	public function dsaefw_match_coupon_rules( $coupon_array ) {

        // Cart coupon data
        $cart_coupons = WC()->cart->get_coupons();
        $cart_coupon_ids = array_values( array_map( function( $cart_coupon ){
            if( $cart_coupon->is_valid() ){
                return $cart_coupon->get_id();
            }
        }, $cart_coupons ) );

        $is_passed = array();

        foreach ( $coupon_array as $key => $coupon ) {

			if ( ! empty( $coupon['product_fees_conditions_values'] ) ) {

				$product_fees_conditions_values = array_map( 'intval', $coupon['product_fees_conditions_values'] );

				if ( 'is_equal_to' === $coupon['product_fees_conditions_is'] ) {
					if( in_array( -1, $product_fees_conditions_values, true ) && !empty( $cart_coupon_ids ) ){
						$is_passed[ $key ]['dsaefw_has_fee_based_on_coupon'] = true;
						break;
					}
					foreach ( $product_fees_conditions_values as $coupon_id ) {
						settype( $coupon_id, 'integer' );
						if ( in_array( $coupon_id, $cart_coupon_ids, true ) ) {
							$is_passed[ $key ]['dsaefw_has_fee_based_on_coupon'] = true;
							break;
						} else {
							$is_passed[ $key ]['dsaefw_has_fee_based_on_coupon'] = false;
						}
					}
				}
				if ( 'not_in' === $coupon['product_fees_conditions_is'] ) {
					if( in_array( -1, $product_fees_conditions_values, true ) && !empty( $cart_coupon_ids ) ){
						$is_passed[ $key ]['dsaefw_has_fee_based_on_coupon'] = false;
						break;
					} else {
                        foreach ( $product_fees_conditions_values as $coupon_id ) {
                            settype( $coupon_id, 'integer' );
                            if ( in_array( $coupon_id, $cart_coupon_ids, true ) ) {
                                $is_passed[ $key ]['dsaefw_has_fee_based_on_coupon'] = false;
                                break;
                            } else {
                                $is_passed[ $key ]['dsaefw_has_fee_based_on_coupon'] = true;
                            }
                        }
                    }
				}
			}
		}
        $main_is_passed = $this->dsaefw_check_all_passed_general_rule( $is_passed, 'dsaefw_has_fee_based_on_coupon', $this->get_rule_match() );

		return $main_is_passed;
    }

    /**
	 * Match rule based on shipping class
	 *
	 * @param array $shipping_class_array
	 *
	 * @return array $is_passed
     * 
	 * @since 1.0.0
     * 
     * @uses     WC_Product::get_shipping_class_id()
	 */
	public function dsaefw_match_shipping_class_rules( $shipping_class_array ) {
        
        // Cart shipping class data
        $cart_shipping_classes = array();

        $cart_array     = WC()->cart->get_cart();
        foreach ( $cart_array as $cart_item ) {
            if( $cart_item['data']->get_shipping_class_id() > 0 ) {
                $cart_shipping_classes[] = $cart_item['data']->get_shipping_class_id();
            }
        }
        
        $is_passed = array();

        foreach ( $shipping_class_array as $key => $shipping_class ) {
            if ( ! empty( $shipping_class['product_fees_conditions_values'] ) ) {

                $shipping_class['product_fees_conditions_values'] = array_map( 'absint', $shipping_class['product_fees_conditions_values'] );
                
			    if ( 'is_equal_to' === $shipping_class['product_fees_conditions_is'] ) {
                    if ( array_intersect( $cart_shipping_classes, $shipping_class['product_fees_conditions_values'] ) ) {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_shipping_class'] = true;
                    } else {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_shipping_class'] = false;
                    }
				}
			    if ( 'not_in' === $shipping_class['product_fees_conditions_is'] ) {
                    if ( array_intersect( $cart_shipping_classes, $shipping_class['product_fees_conditions_values'] ) ) {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_shipping_class'] = false;
                    } else {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_shipping_class'] = true;
                    }
				}
			}
		}

        $main_is_passed = $this->dsaefw_check_all_passed_general_rule( $is_passed, 'dsaefw_has_fee_based_on_shipping_class', $this->get_rule_match() );

		return $main_is_passed;
    }

    /**
	 * Match rule based on payment gateway
	 *
	 * @param array $payment_gateway
	 *
	 * @return array $is_passed
     * 
	 * @since 1.0.0
     * 
     * @uses     WC()->session->get( 'chosen_payment_method' )
	 */
	public function dsaefw_match_payment_rules( $payment_methods_array ) {

        // Cart payment method data
		$chosen_payment_method = WC()->session->get( 'chosen_payment_method' );

        $is_passed             = array();

		if ( ! empty( $payment_methods_array ) ) {
			foreach ( $payment_methods_array as $key => $payment ) {
				if ( $payment['product_fees_conditions_is'] === 'is_equal_to' ) {
					if ( in_array( $chosen_payment_method, $payment['product_fees_conditions_values'], true ) ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_payment'] = true;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_payment'] = false;
					}
				}
				if ( $payment['product_fees_conditions_is'] === 'not_in' ) {
					if ( in_array( $chosen_payment_method, $payment['product_fees_conditions_values'], true ) ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_payment'] = false;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_payment'] = true;
					}
				}
			}
		}

        $main_is_passed = $this->dsaefw_check_all_passed_general_rule( $is_passed, 'dsaefw_has_fee_based_on_payment', $this->get_rule_match() );

		return $main_is_passed;
    }
    
    /**
	 * Match rule based on shipping method
     * 
	 * @param array $shipping_methods
	 *
	 * @return array $is_passed
     * 
	 * @since 1.0.0
	 */
	public function dsaefw_match_shipping_method_rules( $shipping_methods ) {

        // Cart shipping method data
        $chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

        $is_passed = array();

        if ( ! empty( $chosen_shipping_methods ) ) {
			// Make plugin compatibility with "UPS Live Rates and Access Points" plugin.
			if ( false !== strpos( $chosen_shipping_methods[0], 'flexible_shipping_ups' ) ) {
				// Split the string based on ":"
				$chosen_shipping_method = explode(":", $chosen_shipping_methods[0]);

				// Take the first and second part of the array
				$chosen_shipping_methods = array( $chosen_shipping_method[0] . ":" . $chosen_shipping_method[1] );
			}
			
			// Check shipping methods to add fee
			foreach ( $shipping_methods as $key => $method ) {
				if ( 'is_equal_to' === $method['product_fees_conditions_is'] ) {
					if ( in_array( $chosen_shipping_methods[0], $method['product_fees_conditions_values'], true ) ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_shipping_method'] = true;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_shipping_method'] = false;
					}
				}
				if ( 'not_in' === $method['product_fees_conditions_is'] ) {
					if ( in_array( $chosen_shipping_methods[0], $method['product_fees_conditions_values'], true ) ) {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_shipping_method'] = false;
					} else {
						$is_passed[ $key ]['dsaefw_has_fee_based_on_shipping_method'] = true;
					}
				}
			}
		}
        $main_is_passed = $this->dsaefw_check_all_passed_general_rule( $is_passed, 'dsaefw_has_fee_based_on_shipping_method', $this->get_rule_match() );

		return $main_is_passed;
    }

    /**
	 * Match attribute rules
	 *
	 * @param array $product_attribute_array
	 *
	 * @return string $main_is_passed
     * 
	 * @since 1.0.0
	 */
	public function dsaefw_match_product_attribute_rules( $product_attribute_array ) {
     
        // Cart product attribute data
        $cart_product_attributes = $this->dsaefw_get_cart_product_attributes();
        
        $is_passed = array();

        foreach ( $product_attribute_array as $key => $product_attribute ) {
            if ( ! empty( $product_attribute['product_fees_conditions_values'] ) ) {
			    if ( $product_attribute['product_fees_conditions_is'] === 'is_equal_to' ) {
                    if ( array_intersect( $cart_product_attributes, $product_attribute['product_fees_conditions_values'] ) ) {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_product_attribute'] = true;
                    } else {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_product_attribute'] = false;
                    }
				}
			    if ( $product_attribute['product_fees_conditions_is'] === 'not_in' ) {
                    if ( array_intersect( $cart_product_attributes, $product_attribute['product_fees_conditions_values'] ) ) {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_product_attribute'] = false;
                    } else {
                        $is_passed[ $key ]['dsaefw_has_fee_based_on_product_attribute'] = true;
                    }
				}
			}
		}

        $main_is_passed = $this->dsaefw_check_all_passed_general_rule( $is_passed, 'dsaefw_has_fee_based_on_product_attribute', $this->get_rule_match() );

		return $main_is_passed;
    }
}