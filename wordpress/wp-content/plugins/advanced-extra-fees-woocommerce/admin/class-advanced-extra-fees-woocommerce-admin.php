<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.thedotstore.com/
 * @since      1.0.0
 *
 * @package    Advanced_Extra_Fees_Woocommerce
 * @subpackage Advanced_Extra_Fees_Woocommerce/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Advanced_Extra_Fees_Woocommerce
 * @subpackage Advanced_Extra_Fees_Woocommerce/admin
 * @author     theDotstore <support@thedotstore.com>
 */

 #[\AllowDynamicProperties]
class Advanced_Extra_Fees_Woocommerce_Admin {

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

        $this->include_modules();

	}

    /**
	 * Include admin classes and objects.
	 *
	 * @since 1.0.0
	 */
	private function include_modules() {

        // Fees admin edit screens
        require_once( plugin_dir_path( dirname( __FILE__ ) ) . '/admin/class-advanced-extra-fees-woocommerce-admin-fees.php' );  
        
        // Fees Import and Export handlers
		require_once( plugin_dir_path( dirname( __FILE__ ) ) . '/admin/abstract-class-advanced-extra-fees-woocommerce-import-export.php' );

        // Fees admin import operations
        require_once( plugin_dir_path( dirname( __FILE__ ) ) . '/admin/class-advanced-extra-fees-woocommerce-import.php' );  

        // Fees admin export operations
        require_once( plugin_dir_path( dirname( __FILE__ ) ) . '/admin/class-advanced-extra-fees-woocommerce-export.php' );  
    }

    /**
	 * Get the export class instance.
	 *
	 * @since 1.0.0
	 *
	 * @return \Advanced_Extra_Fees_Woocommerce_Export instance
	 */
	public function get_export_instance() {
		return new \Advanced_Extra_Fees_Woocommerce_Export();
	}

    /**
	 * Get the import class instance.
	 *
	 * @since 1.0.0
	 *
	 * @return \Advanced_Extra_Fees_Woocommerce_Import instance
	 */
	public function get_import_instance() {
		return new \Advanced_Extra_Fees_Woocommerce_Import();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

        wp_enqueue_style( 'woocommerce_admin_styles');

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/advanced-extra-fees-woocommerce-admin.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'jquery-ui',  plugin_dir_url( __FILE__ ) . 'css/jquery-ui.css', array(), $this->version, 'all'); 
        wp_enqueue_style( $this->plugin_name . '-datepicker', plugin_dir_url( __FILE__ ) . 'css/advanced-extra-fees-woocommerce-datepicker.css', array( $this->plugin_name, 'jquery-ui' ), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {
        global $typenow, $pagenow;

        $get_page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        if( $typenow !== DSAEFW_FEE_POST_TYPE && 'post-new.php' !== $pagenow && $get_page !== 'wc-orders' ) {
            return;
        }

        wp_enqueue_script( 'jquery-blockui');
        wp_enqueue_script( 'jquery-tiptip');
        wp_enqueue_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full.min.js', array( 'jquery' ), '1.0.0'  );

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/advanced-extra-fees-woocommerce-admin.js', array( 'jquery', 'jquery-tiptip', 'jquery-ui-datepicker' ), $this->version, false );
        wp_localize_script( $this->plugin_name, 'coditional_vars', array(
                'ajax_url'                  => admin_url( 'admin-ajax.php' ),
                'currency_symbol'	        => esc_attr( get_woocommerce_currency_symbol() ),
                'dsaefw_woo_search_nonce'   => wp_create_nonce( 'dsaefw-woo-search' ),
                'select2_per_data_ajax'     => absint( apply_filters( 'dsaefw_json_data_search_limit', 10 ) ),
            )
        );

        wp_enqueue_script( $this->plugin_name . '-import-export', plugin_dir_url( __FILE__ ) . 'js/dsaefw-import-export.js', array( 'jquery' ), $this->version, false );
        wp_localize_script( $this->plugin_name, 'dasefw_import_export_vars', array(
                'ajax_url'          => admin_url( 'admin-ajax.php' ),
                'file_upload_msg'   => esc_html__( "Please upload CSV file", 'advanced-extra-fees-woocommerce' ),
            )
        );
	}

    /**
     * Add settings page
     *
     * @param $settings
     * 
     * @return array
     * 
     * @since 1.0.0
     */
    public function dsaefw_add_settings_page( $settings ) {

        $settings[] = include __DIR__ . '/class-advanced-extra-fees-woocommerce-settings.php';
        
        return $settings;
    }

    /**
	 * Render WooCommerce core settings tabs while in Fees edit screens.
	 *
	 *
	 * @since 1.0.0
	 */
	public function dsaefw_output_woocommerce_settings_tabs_html() {
		global $typenow;
        
		if ( DSAEFW_FEE_POST_TYPE === $typenow ) {
            

            WC_Admin_Settings::get_settings_pages();

            // get tabs for the settings page.
            $tabs = apply_filters( 'woocommerce_settings_tabs_array', array() );

            // Get our settings object
            $advanced_extra_fees   = new \Advanced_Extra_Fees_Woocommerce_Settings();
            ?>
            <div class="wrap woocommerce">
                <form method="<?php echo esc_attr( apply_filters( 'woocommerce_settings_form_method_tab_shipping', 'post' ) ); ?>" id="mainform" action="" enctype="multipart/form-data">

                    <nav class="nav-tab-wrapper woo-nav-tab-wrapper">
                        <?php foreach ( $tabs as $name => $label ) : ?>
                            <a href="<?php echo esc_url( admin_url( "admin.php?page=wc-settings&tab={$name}" ) ); ?>" class="nav-tab <?php if ( $advanced_extra_fees->get_tab_id() === $name ) { echo 'nav-tab-active'; } ?>"><?php echo esc_html( $label ); ?></a>
                        <?php endforeach; ?>
                    </nav>

                    <ul class="subsubsub">
                        <?php

                        $sections   = $advanced_extra_fees->get_sections();
                        $array_keys = array_keys( $sections );

                        foreach ( $sections as $id => $label ) { 
                            $menu_link = add_query_arg( array(
                                'page' => 'wc-settings',
                                'tab' => sanitize_title( $advanced_extra_fees->get_tab_id() ),
                                'section' => sanitize_title( $id ),
                            ), admin_url( 'admin.php' ) );
                            $menu_class = 'manage_fees' === $id ? 'current' : '';
                            ?>
                            <li>
                                <a href="<?php echo esc_url($menu_link); ?>" class="<?php echo esc_attr($menu_class); ?>"><?php echo esc_html( $label ); ?></a> 
                                <?php echo end( $array_keys ) === $id ? '' : '|'; ?>
                            </li>
                        <?php
                        }
                        ?>
                    </ul>
                    <br class="clear" />
                </form>
            </div>
            <?php
		}
	}

    /**
     * AJAX callback function for return searched products
     * 
     * @since 1.0.0
     */
    public function dsaefw_json_search_products_callback() {

        check_ajax_referer( 'dsaefw-woo-search', 'security' );

        $search = filter_input( INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $search = ! empty( $search ) ? sanitize_text_field( wc_clean( wp_unslash( $search ) ) ) : '';

        if ( empty( $search ) ) {
		    wp_send_json_error( esc_html__( 'No search term provided.', 'advanced-extra-fees-woocommerce' ) );
        }

        $posts_per_page = filter_input( INPUT_GET, 'posts_per_page', FILTER_VALIDATE_INT );
		$posts_per_page = ! empty( $posts_per_page ) ? intval( $posts_per_page ) : absint( apply_filters( 'woocommerce_json_search_limit', 30 ) );
		
        $offset         = filter_input( INPUT_GET, 'offset', FILTER_VALIDATE_INT );
		$offset         = ! empty( $offset ) ? intval( $offset ) : 1;

        $display_pid    = filter_input( INPUT_GET, 'display_pid', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$display_pid    = ! empty( $display_pid ) && 'true' === $display_pid  ? true : false;

        $args = array(
            'post_type' => array( 'product', 'product_variation' ),
            's'         => $search,
            'number'    => $posts_per_page,
            'offset'    => $posts_per_page * ( $offset - 1 ),
            'orderby'   => 'title',
			'order'     => 'ASC',
            'fields'    => 'ids'
        );

        add_filter( 'posts_where', array( $this, 'dsaefw_posts_where'), 10, 2 );
		$products = new WP_Query( $args );
		remove_filter( 'posts_where', array($this, 'dsaefw_posts_where'), 10, 2 );

        $results = array();

        if( isset( $products->posts ) && !empty( $products->posts ) && count( $products->posts ) > 0 ) {
            foreach ( $products->posts as $id ) {

                $product_object = wc_get_product( $id );

                if ( ! wc_products_array_filter_readable( $product_object ) ) {
                    continue;
                }

                // product validation
                if( ! $product_object->is_in_stock() || ! $product_object->is_purchasable() || ! $product_object->is_visible() ) {
                    continue;
                }

                $formatted_name = $product_object->get_formatted_name();

                /* translators: %1$s is replaced with "string" which show searched product name and %2$d is replaced with "number" which show searched product ID */
                $results[ $product_object->get_id() ] = $display_pid ? sprintf( __( '#%2$d - %1$s', 'advanced-extra-fees-woocommerce' ), html_entity_decode( wp_strip_all_tags( $formatted_name ), ENT_QUOTES ), $product_object->get_id() ) : html_entity_decode( wp_strip_all_tags( $formatted_name ), ENT_QUOTES );
            }
        }

        wp_send_json( $results );
    }

    /**
     * Search product by title in admin
     * 
     * @since    1.0.0
     */
    public function dsaefw_posts_where( $where, $wp_query ) {
        global $wpdb;
        $search_term = $wp_query->get( 'search_pro_title' );
        if ( ! empty( $search_term ) ) {
            $search_term_like = $wpdb->esc_like( $search_term );
            $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( $search_term_like ) . '%\'';
        }
        return $where;
    }

    /**
     * AJAX callback function for return searched categories
     * 
     * @since 1.0.0
     */
    public function dsaefw_json_search_categories_callback() {

        check_ajax_referer( 'dsaefw-woo-search', 'security' );

        $search = filter_input( INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $search = ! empty( $search ) ? sanitize_text_field( wc_clean( wp_unslash( $search ) ) ) : '';

        if ( empty( $search ) ) {
		    wp_send_json_error( esc_html__( 'No search term provided.', 'advanced-extra-fees-woocommerce' ) );
        }

        $posts_per_page = filter_input( INPUT_GET, 'posts_per_page', FILTER_VALIDATE_INT );
		$posts_per_page = ! empty( $posts_per_page ) ? intval( $posts_per_page ) : 0;
		
        $offset         = filter_input( INPUT_GET, 'offset', FILTER_VALIDATE_INT );
		$offset         = ! empty( $offset ) ? intval( $offset ) : 1;

        $display_pid    = filter_input( INPUT_GET, 'display_pid', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$display_pid    = ! empty( $display_pid ) && 'true' === $display_pid  ? true : false;

        $args               = array(
			'post_type'     => 'product',
			'post_status'   => 'publish',
			'taxonomy'      => 'product_cat',
			'orderby'       => 'name',
			'hierarchical'  => true,
			'hide_empty'    => false,
            'search'        => $search,
			'number'        => $posts_per_page,
            'offset'        => $posts_per_page * ( $offset - 1 ),
		);
		$get_all_categories = get_terms( $args );

        $results = array();
        if(  !empty( $get_all_categories ) && count( $get_all_categories ) > 0 ) {
            foreach ( $get_all_categories as $category ) {
                $show_cat_acenctors = implode( " > ", wp_list_pluck( array_reverse( $this->get_parent_terms( $category ) ), 'name' ) );
                $results[$category->term_id] = $display_pid 
                /* translators: %s is replaced with "string" which show searched category name and %d is replaced with "number" which show searched category ID */
                ? sprintf( __( '#%2$d - %1$s', 'advanced-extra-fees-woocommerce' ), html_entity_decode( wp_strip_all_tags( $show_cat_acenctors ), ENT_QUOTES ), $category->term_id )
                : html_entity_decode( wp_strip_all_tags( $show_cat_acenctors ), ENT_QUOTES );
            }
        }

        wp_send_json( $results );
    }

    /**
     * AJAX callback function for return searched tags
     * 
     * @since 1.0.0
     */
    public function dsaefw_json_search_tags_callback() {

        check_ajax_referer( 'dsaefw-woo-search', 'security' );

        $search = filter_input( INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $search = ! empty( $search ) ? sanitize_text_field( wc_clean( wp_unslash( $search ) ) ) : '';

        if ( empty( $search ) ) {
		    wp_send_json_error( esc_html__( 'No search term provided.', 'advanced-extra-fees-woocommerce' ) );
        }

        $posts_per_page = filter_input( INPUT_GET, 'posts_per_page', FILTER_VALIDATE_INT );
		$posts_per_page = ! empty( $posts_per_page ) ? intval( $posts_per_page ) : 0;
		
        $offset         = filter_input( INPUT_GET, 'offset', FILTER_VALIDATE_INT );
		$offset         = ! empty( $offset ) ? intval( $offset ) : 1;

        $display_pid    = filter_input( INPUT_GET, 'display_pid', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$display_pid    = ! empty( $display_pid ) && 'true' === $display_pid  ? true : false;

        $args               = array(
			'post_type'     => 'product',
			'post_status'   => 'publish',
			'taxonomy'      => 'product_tag',
			'orderby'       => 'name',
			'hierarchical'  => true,
			'hide_empty'    => false,
            'search'        => $search,
			'number'        => $posts_per_page,
            'offset'        => $posts_per_page * ( $offset - 1 ),
		);
		$get_all_tags = get_terms( $args );

        $results = array();
        if(  !empty( $get_all_tags ) && count( $get_all_tags ) > 0 ) {
            foreach ( $get_all_tags as $tag ) {
                $show_tag_acenctors = implode( " > ", wp_list_pluck( array_reverse( $this->get_parent_terms( $tag ) ), 'name' ) );
                $results[$tag->term_id] = $display_pid 
                /* translators: %s is replaced with "string" which show searched tag name and %d is replaced with "number" which show searched tag ID */
                ? sprintf( __( '#%2$d - %1$s', 'advanced-extra-fees-woocommerce' ), html_entity_decode( wp_strip_all_tags( $show_tag_acenctors ), ENT_QUOTES ), $tag->term_id )
                : html_entity_decode( wp_strip_all_tags( $show_tag_acenctors ), ENT_QUOTES );
            }
        }

        wp_send_json( $results );
    }

    /**
     * AJAX callback function for return searched users data
     * 
     * @since 1.0.0
     */
    public function dsaefw_json_search_users_callback() {

        check_ajax_referer( 'dsaefw-woo-search', 'security' );

        $search = filter_input( INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $search = ! empty( $search ) ? sanitize_text_field( wc_clean( wp_unslash( $search ) ) ) : '';

        if ( empty( $search ) ) {
		    wp_send_json_error( esc_html__( 'No search term provided.', 'advanced-extra-fees-woocommerce' ) );
        }

        $posts_per_page = filter_input( INPUT_GET, 'posts_per_page', FILTER_VALIDATE_INT );
		$posts_per_page = ! empty( $posts_per_page ) ? intval( $posts_per_page ) : 0;
		
        $offset         = filter_input( INPUT_GET, 'offset', FILTER_VALIDATE_INT );
		$offset         = ! empty( $offset ) ? intval( $offset ) : 1;

        $display_pid    = filter_input( INPUT_GET, 'display_pid', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$display_pid    = ! empty( $display_pid ) && 'true' === $display_pid  ? true : false;

        $args = array(
            'search'         => '*' . $search . '*',
            'search_columns' => array( 'user_login', 'user_nicename', 'user_email', 'display_name' ),
            'number'         => $posts_per_page,
            'offset'         => $posts_per_page * ( $offset - 1 ),
            'fields'         => 'ID',
        );

        $users = get_users( $args );

        $results = array();
        if(  !empty( $users ) && count( $users ) > 0 ) {
            foreach ( $users as $user ) {
                $results[$user] = $display_pid 
                /* translators: %s is replaced with "string" which show searched user name and %d is replaced with "number" which show searched user ID */
                ? sprintf( __( '%3$s (#%2$d - %1$s)', 'advanced-extra-fees-woocommerce' ), sanitize_email( get_user_by( 'ID', $user )->user_email ), absint( $user ), html_entity_decode( wp_strip_all_tags( get_user_by( 'ID', $user )->display_name ), ENT_QUOTES ) )
                : sanitize_email( get_user_by( 'ID', $user )->user_email );
            }
        }

        wp_send_json( $results );
    }

    
    /**
     * AJAX callback function for return searched product attribute's term data
     * 
     * @since 1.0.0
     */
    public function dsaefw_json_search_pa_terms_callback() {

        check_ajax_referer( 'dsaefw-woo-search', 'security' );

        $search = filter_input( INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $search = ! empty( $search ) ? sanitize_text_field( wc_clean( wp_unslash( $search ) ) ) : '';

        if ( empty( $search ) ) {
		    wp_send_json_error( esc_html__( 'No search term provided.', 'advanced-extra-fees-woocommerce' ) );
        }

        $posts_per_page = filter_input( INPUT_GET, 'posts_per_page', FILTER_VALIDATE_INT );
		$posts_per_page = ! empty( $posts_per_page ) ? intval( $posts_per_page ) : 0;
		
        $offset         = filter_input( INPUT_GET, 'offset', FILTER_VALIDATE_INT );
		$offset         = ! empty( $offset ) ? intval( $offset ) : 1;

        $pa_data        = filter_input( INPUT_GET, 'pa_data', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$pa_data        = ! empty( $pa_data ) ? sanitize_text_field( wc_clean( wp_unslash( $pa_data ) ) ) : '';

        $args               = array(
			'post_type'     => 'product',
			'post_status'   => 'publish',
			'taxonomy'      => $pa_data,
            'parent'        => 0,
			'hide_empty'    => false,
            'search'        => $search,
			'number'        => $posts_per_page,
            'offset'        => $posts_per_page * ( $offset - 1 ),
		);
		$get_all_terms = get_terms( $args );

        $results = array();
        if( !empty( $get_all_terms ) && count( $get_all_terms ) > 0 ) {
            foreach ( $get_all_terms as $term ) {
                $results[$term->slug] = esc_html( $term->name );
            }
        }

        wp_send_json( $results );
    }

    /**
     * Get trail of parent terms
     * 
     * @param object $term
     * 
     * @return array
     * 
     * @since 1.0.0
     */
    public function get_parent_terms( $term ) {
        $arr = [ $term ];
        while ( $term->parent > 0 ) {
            $term = get_term_by("id", $term->parent, "product_cat");
            if ($term) {
                $arr[] = $term;
            } else {
                break;
            }
        }
        return $arr;
    }

    public function dsaefw_export_fees_callback() {
        
        //Check ajax nonce reference
        check_ajax_referer( 'dsaefw_export_fees-action-nonce', 'security');

        // Process export
        dsaefw()->dsaefw_admin_object()->get_export_instance()->process_export();
    }

    public function dsaefw_import_fees_callback() {
        
        //Check ajax nonce reference
        check_ajax_referer( 'dsaefw_import_fees-action-nonce', 'security');
        
        // Process import
        dsaefw()->dsaefw_admin_object()->get_import_instance()->process_import();
    }

    public function dsaefw_check_duplicate_fee_title( $fee_id, $fee_data ) {

        global $wpdb;

        // Only apply this to posts and pages
        if ( $fee_data->post_type !== DSAEFW_FEE_POST_TYPE ) {
            return false;
        }

        $cache_key        = 'dsaefw-duplicate-fee-title-ids';
		$existing_post = wp_cache_get( $cache_key, 'dsaefw_fees' );

        if( !$existing_post ) {
            //phpcs:disable
            // Check if a post with the same title already exists
            $existing_post = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts 
                    WHERE post_title = %s 
                    AND post_type = %s 
                    AND post_status IN ('publish', 'pending', 'draft', 'future') 
                    AND ID != %d",
                    $fee_data->post_title, 
                    $fee_data->post_type, 
                    $fee_id
                ) 
            );
            
            wp_cache_set( $cache_key, $existing_post, 'dsaefw_fees', MINUTE_IN_SECONDS ); // phpcs:ignore
        }

        if ( $existing_post ) {
            return true;
        }

        return false;

    }
}
