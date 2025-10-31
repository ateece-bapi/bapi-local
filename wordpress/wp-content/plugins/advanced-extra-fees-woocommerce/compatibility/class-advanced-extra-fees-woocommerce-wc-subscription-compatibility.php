<?php
/**
 * WooCommerce subscription compatibility class.
 *
 * @link       https://www.thedotstore.com/
 * @since      1.0.0
 *
 * @package    Advanced_Extra_Fees_Woocommerce
 * @subpackage Advanced_Extra_Fees_Woocommerce/compatibility
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Advanced_Extra_Fees_Woocommerce_WC_Subscription_Compatibility {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        
        // WooCommerce Subscriptions Compatibility
        add_filter( 'woocommerce_subscriptions_is_recurring_fee', array( $this, 'dsaefw_apply_recurring_fees' ), 10, 2 );
    }

    /**
     * Whether fee apply recurring product or not
     * 
     * @param boolean $return
     * @param object $fee
     * 
     * @return boolean $return
     * 
     * @since 1.0.0
     */
    public function dsaefw_apply_recurring_fees( $return, $fee ){

        if( empty( $fee ) ){
            return false;
        }

        $fee_id = dsaefw()->dsaefw_public_object()->dsaefw_fee_id_from_name( $fee->name );

        if( empty( $fee_id ) ){
            return false;
        }

        $advance_fee = new \Advanced_Extra_Fees_Woocommerce_Fee( $fee_id );

        if( empty( $advance_fee ) ){
            return false;
        }
        
        if( $fee_id > 0 ) {

            $fee_is_recurring = $advance_fee->get_fee_settings_recurring();

            if( 'yes' === $fee_is_recurring ) {
                $return = true;
            } else {
                $return = false;
            }
        }

        return $return;
    }

}