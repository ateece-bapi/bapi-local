<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.thedotstore.com/
 * @since      1.0.0
 *
 * @package    Advanced_Extra_Fees_Woocommerce
 * @subpackage Advanced_Extra_Fees_Woocommerce/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Advanced_Extra_Fees_Woocommerce
 * @subpackage Advanced_Extra_Fees_Woocommerce/includes
 * @author     theDotstore <support@thedotstore.com>
 */
use Automattic\WooCommerce\Utilities\FeaturesUtil;

defined( 'ABSPATH' ) or exit;

class Advanced_Extra_Fees_Woocommerce {

    /** the message id GET name */
    const DSAEFW_MESSAGE_ID = 'dsaefw_message_id';

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Advanced_Extra_Fees_Woocommerce_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

    /** 
     * The currenct instance of the plugin
     * 
     * @since   1.0.0
     * @access  protected
     * @var     \Advanced_Extra_Fees_Woocommerce single instance of this plugin 
     */
	protected static $instance;

    /** @var array the admin notices to add */
    protected $notices = array();

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'ADVANCED_EXTRA_FEES_WOOCOMMERCE_VERSION' ) ) {
			$this->version = ADVANCED_EXTRA_FEES_WOOCOMMERCE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'advanced-extra-fees-woocommerce';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
        $this->load_plugin_compatibilities();

        // Add plugin action links for plugin listing page
		add_filter( "plugin_action_links_" . DSAEFW_PLUGIN_BASENAME, [ $this, 'dsaefw_plugin_action_links' ], 20 );

        add_filter( 'plugin_row_meta', array( $this, 'dsaefw_filter_plugin_row_meta' ), 10, 2 );

        // HPOS & Block Cart/Checkout Compatibility declare
        add_action( 'before_woocommerce_init', [ $this, 'dsaefw_handle_features_compatibility' ] );

        // display admin messages
		add_action( 'admin_notices', array( $this, 'show_admin_messages' ), 2 );
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Advanced_Extra_Fees_Woocommerce_Loader. Orchestrates the hooks of the plugin.
	 * - Advanced_Extra_Fees_Woocommerce_i18n. Defines internationalization functionality.
	 * - Advanced_Extra_Fees_Woocommerce_Admin. Defines all hooks for the admin area.
	 * - Advanced_Extra_Fees_Woocommerce_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-advanced-extra-fees-woocommerce-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-advanced-extra-fees-woocommerce-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-advanced-extra-fees-woocommerce-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-advanced-extra-fees-woocommerce-public.php';

        /**
         * The class responsible for defining all compatibility actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'compatibility/class-advanced-extra-fees-woocommerce-wc-subscription-compatibility.php';

		$this->loader = new Advanced_Extra_Fees_Woocommerce_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Advanced_Extra_Fees_Woocommerce_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Advanced_Extra_Fees_Woocommerce_i18n();

		// We are directly call it instead of hook because already this class is load thorugh hook
        $plugin_i18n->load_plugin_textdomain();
	}

    /**
     * Returns the instance of the admin class
     * 
     * @internal
     * 
     * @since 1.0.0
     */
    public function dsaefw_admin_object() {
        return new \Advanced_Extra_Fees_Woocommerce_Admin( $this->get_plugin_name(), $this->get_version() );
    }

    /**
     * Returns the instance of the public class
     * 
     * @internal
     * 
     * @since 1.0.0
     */
    public function dsaefw_public_object() {
        return new \Advanced_Extra_Fees_Woocommerce_Public( $this->get_plugin_name(), $this->get_version() );
    }

    /**
     * Load plugin compatibility classes
     * 
     * @internal
     * 
     * @since 1.0.0
     */
    public function load_plugin_compatibilities() {
        new Advanced_Extra_Fees_Woocommerce_WC_Subscription_Compatibility();
    }

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Advanced_Extra_Fees_Woocommerce_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        if( is_admin() ) {
            $this->loader->add_action( 'woocommerce_get_settings_pages', $plugin_admin, 'dsaefw_add_settings_page' );

            // render WooCommerce Settings tabs while in the Fees edit screens
            $this->loader->add_action( 'all_admin_notices', $plugin_admin, 'dsaefw_output_woocommerce_settings_tabs_html', 5 );

            /**
             * Admin AJAX Calls
             */
            // Get product data
            $this->loader->add_action( 'wp_ajax_dsaefw_json_search_products', $plugin_admin, 'dsaefw_json_search_products_callback' );

            // Get category data
		    $this->loader->add_action( 'wp_ajax_dsaefw_json_search_categories', $plugin_admin, 'dsaefw_json_search_categories_callback' );

            // Get tag data
		    $this->loader->add_action( 'wp_ajax_dsaefw_json_search_tags', $plugin_admin, 'dsaefw_json_search_tags_callback' );

            // Get users data
		    $this->loader->add_action( 'wp_ajax_dsaefw_json_search_users', $plugin_admin, 'dsaefw_json_search_users_callback' );

            // Get users data
		    $this->loader->add_action( 'wp_ajax_dsaefw_json_search_pa_terms', $plugin_admin, 'dsaefw_json_search_pa_terms_callback' );

            // Export fees
            $this->loader->add_action( 'wp_ajax_dsaefw_export_fees', $plugin_admin, 'dsaefw_export_fees_callback' );

            // Import fees
            $this->loader->add_action( 'wp_ajax_dsaefw_import_fees', $plugin_admin, 'dsaefw_import_fees_callback' );
        }

	}

	/**
	 * Register all of the hooks related to the public-facing functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
        
        // Check for admin panel (We are using this because is_admin() is not working for block editor)
        $current_url = '';
        if( isset($_SERVER['REQUEST_URI']) ) {
            $current_url = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL, FILTER_VALIDATE_URL );
        }
        $referrer = home_url( $current_url );
        if( $referrer && strpos( $referrer, admin_url() ) !== false && !wp_doing_ajax() ) {
            return;
        }

		$plugin_public = new Advanced_Extra_Fees_Woocommerce_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

        $this->loader->add_action( 'woocommerce_cart_calculate_fees', $plugin_public, 'dsaefw_conditional_fee_add_to_cart' );
        
        $this->loader->add_filter( 'woocommerce_cart_totals_fee_html', $plugin_public, 'dsaefw_fee_tooltip', 10, 2 );

        /**
         * Block Cart & Checkout Compatibility
         */
        // Pass client side data to server side for process
        $this->loader->add_action( 'woocommerce_blocks_loaded', $plugin_public, 'dsaefw_block_checokut_data_register' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

    /**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function init_plugin() {

        // static class for custom post types handling
		require_once( plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-dsaefw-post-types.php' );

		\DSAEFW_Post_Types::init();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Advanced_Extra_Fees_Woocommerce_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

    /**
	 * Require and instantiate a class
	 *
	 * @since 1.0.0
	 * @param string $local_path path to class file in plugin, e.g. '/includes/class-wc-foo.php'
	 * @param string $class_name class to instantiate
	 * @return object instantiated class instance
	 */
	public function load_class( $local_path, $class_name ) {

		require_once( plugin_dir_path( dirname( __FILE__ ) ) . $local_path ); // nosemgrep

		return new $class_name;
	}

    /**
	 * Declares plugin list page quick links.
	 *
	 * @since 1.0.0
	 */
    public function dsaefw_plugin_action_links( $actions ) {

        $custom_actions = array();
        if( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            // Define custom action links with appropriate URLs and labels.
            $custom_actions = array(
                'configure' => sprintf(
                    '<a href="%s">%s</a>',
                    esc_url( add_query_arg( array( 'post_type' => DSAEFW_FEE_POST_TYPE ), admin_url( 'edit.php' ) ) ),
                    __( 'Settings', 'advanced-extra-fees-woocommerce' )
                ),
            );
        }

        // Merge the custom action links with the existing action links.
        return array_merge( $custom_actions, $actions );
    }

    /**
	 * Filters the array of row meta for each plugin in the Plugins list table.
	 *
	 * @param array     $plugin_meta    An array of the plugin's metadata.
	 * @param string    $plugin_file    Path to the plugin file relative to the plugins directory.
	 * @return array                    Updated array of the plugin's metadata.
     * 
     * @since 1.0.0
	 */
    public function dsaefw_filter_plugin_row_meta( array $plugin_meta, $plugin_file ) {
        if ( DSAEFW_PLUGIN_BASENAME !== $plugin_file ) {
			return $plugin_meta;
		}

        $row_meta = [
            'docs'    => sprintf(
                '<a href="%s" target="_blank">%s</a>',
                esc_url( DSAEFW_DOC_LINK ),
                __( 'Docs', 'advanced-extra-fees-woocommerce' )
            ),
            'support' => sprintf(
                '<a href="%s" target="_blank">%s</a>',
                esc_url( 'https://www.thedotstore.com/support' ),
                __( 'Support', 'advanced-extra-fees-woocommerce' )
            ),
        ];
        return array_merge( $plugin_meta, $row_meta );
    }

     /**
	 * Declares compatibility with specific WooCommerce features.
	 *
	 * @since 1.0.0
	 */
    public function dsaefw_handle_features_compatibility(){

        if ( ! class_exists( FeaturesUtil::class ) ) {
			return;
		}
        
        FeaturesUtil::declare_compatibility( 'custom_order_tables', DSAEFW_PLUGIN_BASENAME, true );
        FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', DSAEFW_PLUGIN_BASENAME, true );
    }

    /**
     * Allowed html tags used for wp_kses function
     *
     * @param array add custom tags (Not used)
     *
     * @return array
     * @since     1.0.0
     *
     */
    public static function dsaefw_allowed_html_tags( ) {
        $allowed_tags = array(
            'a'        => array(
                'href'         => array(),
                'title'        => array(),
                'class'        => array(),
                'target'       => array(),
                'data-tooltip' => array(),
            ),
            'ul'       => array( 'class' => array() ),
            'li'       => array( 'class' => array() ),
            'div'      => array( 'class' => array(), 'id' => array() ),
            'select'   => array(
                'rel-id'            => array(),
                'id'                => array(),
                'name'              => array(),
                'class'             => array(),
                'multiple'          => array(),
                'style'             => array(),
                'data-width'        => array(),
                'data-placeholder'  => array(),
                'data-action'       => array(),
                'data-sortable'     => array(),
                'data-allow-clear'  => array(),
                'data-display_id'   => array(),
            ),
            'input'    => array(
                'id'         => array(),
                'value'      => array(),
                'name'       => array(),
                'class'      => array(),
                'type'       => array(),
                'data-index' => array(),
            ),
            'textarea' => array( 'id' => array(), 'name' => array(), 'class' => array() ),
            'option'   => array( 'id' => array(), 'selected' => array(), 'name' => array(), 'value' => array() ),
            'br'       => array(),
            'p'        => array(),
            'b'        => array( 'style' => array() ),
            'em'       => array(),
            'strong'   => array(),
            'i'        => array( 'class' => array() ),
            'span'     => array( 'class' => array(), 'style' => array() ),
            'small'    => array( 'class' => array() ),
            'label'    => array( 'class' => array(), 'id' => array(), 'for' => array() ),
        );
        return $allowed_tags;
    }

    /**
	 * Remove WooCommerce currency symbol
	 *
	 * @param float $price
	 *
	 * @return float $final_price
	 * @since  1.0.0
	 *
	 * @uses   get_woocommerce_currency_symbol()
	 *
	 */
	public function dsaefw_remove_currency_symbol( $price ) {

        $args  = array(
            'decimal_separator'  => wc_get_price_decimal_separator(),
            'thousand_separator' => wc_get_price_thousand_separator(),
        );

        $wc_currency_symbol = get_woocommerce_currency_symbol();
        $cleanText          = wp_strip_all_tags($price);
		$new_price          = str_replace( $wc_currency_symbol, '', $cleanText );

        $tnew_price         = str_replace( $args['thousand_separator'], '', $new_price);
        $dnew_price         = str_replace( $args['decimal_separator'], '.', $tnew_price);
        $final_price        = preg_replace( '/[^.\d]/', '', $dnew_price );
        
		return $final_price;
	}

    /**
     * Adds an admin notice to be displayed.
     *
     * @since 1.0.0
     *
     * @param string $slug the slug for the notice
     * @param string $class the css class for the notice
     * @param string $message the notice message
     */
    public function add_admin_notice( $slug, $class, $message ) {
        
        $this->notices[ $slug ] = array(
            'class'   => $class,
            'message' => $message
        );

        // Save the notices to the transient
        set_transient( self::DSAEFW_MESSAGE_ID, $this->notices, 60 );
    }

    /**
     * Displays any admin notices
     *
     * @since 1.0.0
     */
    public function show_admin_messages() {

        // Get the notices from the transient if any
        $this->notices = get_transient( self::DSAEFW_MESSAGE_ID ) ?: $this->notices;

        $get_post_type = filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $post_type = ! empty( $get_post_type ) ? sanitize_text_field( wc_clean( wp_unslash( $get_post_type ) ) ) : '';

        if ( empty( $this->notices ) && isset( $post_type ) && DSAEFW_FEE_POST_TYPE !== $post_type ) {
            return;
        }
        
        foreach ( $this->notices as $notice ) : ?>
            <div class="<?php echo esc_attr( $notice['class'] ); ?>">
                <p><?php echo wp_kses( $notice['message'], array( 'a' => array( 'href' => array() ) ) ); ?></p>
            </div>
            <?php
        endforeach;

        // After displaying notices, remove them from the transient
        delete_transient( self::DSAEFW_MESSAGE_ID );
    }

    /**
	 * Include template with arguments
	 *
	 * @param string $__template
	 * @param array  $__variables
     * 
     * @since   1.0.0
     * 
     * @link https://woocommerce.github.io/code-reference/files/woocommerce-includes-wc-core-functions.html#source-view.340
	 */
	public function include_template( $__template, array $__variables = [] ) {

        $template_file = DSAEFW_PLUGIN_BASE_DIR . $__template;
        
		if ( file_exists( $template_file ) ) {
			extract( $__variables ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
			include $template_file; // nosemgrep
		}
	}

    /**
     * Check woocommerce page has block cart and checkout
     * 
     * @param string $isBlockPage
     * 
     * @return boolean
     * 
     * @since 1.0.0
     */
    public function dsaefw_is_wc_has_block( $isBlockPage = '' ){

        $isBlockCart = WC_Blocks_Utils::has_block_in_page( wc_get_page_id('cart'), 'woocommerce/cart' );

        $isBlockCheckout = WC_Blocks_Utils::has_block_in_page( wc_get_page_id('checkout'), 'woocommerce/checkout' );

        if( empty( $isBlockPage ) ){
            return $isBlockCart || $isBlockCheckout;
        }

        if( 'cart' === $isBlockPage ){
            return $isBlockCart;
        }

        if( 'checkout' === $isBlockPage ){
            return $isBlockCheckout;
        }

        return false;
    }

    /**
	 * Gets the main Advanced Extra Fees for WooCommerce instance.
	 *
	 * Ensures only one instance loaded at one time.
	 *
	 * @see \dsaefw()
	 *
	 * @since 1.0.0
	 *
	 * @return \Advanced_Extra_Fees_Woocommerce
	 */
	public static function instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

/**
 * Returns the One True Instance of Advance Extra Fee WooCommerce class object.
 *
 * @since 1.0.0
 *
 * @return \Advanced_Extra_Fees_Woocommerce
 */
function dsaefw(){
    return \Advanced_Extra_Fees_Woocommerce::instance();
}
