<?php

if ( !class_exists( 'Nf_Maintenance_Public' ) ) {

    class Nf_Maintenance_Public {

    	private $plugin_name;
    	private $version;

    	public function __construct( $plugin_name, $version ) {

    		$this->plugin_name = $plugin_name;
    		$this->version = $version;

    	}


    	public function enqueue_styles() {

    	    wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/nf-maintenance-public.css', array(), $this->version, 'all' );

    	}

        public function nf_woo_redirect() {

        	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

                $user = wp_get_current_user();
                $roles = array( 'shop_manager', 'administrator' );
                if( array_intersect( $roles, $user->roles ) ) {
                    return;
                }

                $options = get_option( 'nf_maintenance_cfg' );
                if ( !isset($options["active"]) ) { return; }

                if ( "1" === $options["active"] ) {

                    if (!isset($options['locked_out']) ){
                        if ( is_user_logged_in() ){
                            return;
                        }
                    }

            		if (  is_woocommerce() || is_cart() || is_checkout() )  {

                        if ( isset( $options["nf_postid"] ) and ('publish' === get_post_status( $options["nf_postid"]) ) ){
                            if ( function_exists( 'pll_the_languages' ) ) {
                                wp_safe_redirect( get_permalink( pll_get_post( $options["nf_postid"] ) ));
                                exit;
                            }
                		   	wp_safe_redirect( get_permalink( get_post( $options["nf_postid"] ) ) );
                			exit;
                        }

            		}
                }
        	}
        }

    }

}