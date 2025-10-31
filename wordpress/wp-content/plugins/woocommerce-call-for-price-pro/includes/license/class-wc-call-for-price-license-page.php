<?php 
/**
 * License Page Class
 *
 * Displays on plugin activation
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Alg_WC_Call_For_Price_License_Page Class
 *
 * A general class for About page.
 *
 * @since 3.2.5
 */

if( ! class_exists( 'Alg_WC_Call_For_Price_License_Page' ) ) {
	class Alg_WC_Call_For_Price_License_Page {

		public static $plugin_name = 'Call for Price for WooCommerce';	
		/**
		 * @var string Plugin License Key Name in Options table
		 * @access public
		 */
		public static $plugin_license_key = 'edd_license_key_call_for_price';

		/**
		 * @var string Plugin License Status name in Options table
		 * @access public
		 */
		public static $plugin_license_status = 'edd_license_key_call_for_price_status';

		/**
		 * @var string Plugin prefix
		 * @access public
		 */
		public static $plugin_prefix = 'wc_call_for_price';


		public static $plugin_context = 'woocommerce-call-for-price';

		public static $plugin_folder    = 'woocommerce-call-for-price-pro/';
		
	 	public static $plugin_file_path;

		public static $plugin_version;

		public static $previous_plugin_version = '3.2.4';

		public static $plugin_url;

		public static $template_base;

		public static $ts_welcome_header_text;
		/**
		 * Get things started
		 *
		 * @since 7.7
		 */
		public function __construct() {
			self::$plugin_file_path = dirname( dirname ( untrailingslashit( plugin_dir_path ( __FILE__ ) ) ) ) . '/woocommerce-call-for-price-pro.php' ;
			
			register_activation_hook( self::$plugin_file_path, array( $this, 'wc_call_for_price_installation_completed' ) );
		
			add_action( 'admin_init', array( $this, 'wc_call_for_price_license_page' ) );

			add_action( 'admin_menu', array( $this, 'admin_menus' ) );
			add_action( 'admin_head', array( $this, 'admin_head' ) );

			self::$plugin_version 		   = $this->ts_get_version();
		
			self::$plugin_url     		   = $this->ts_get_plugin_url();
			self::$template_base  		   = $this->ts_get_template_path();
			self::$ts_welcome_header_text  = sprintf( esc_html__( 'Welcome to %s %s', self::$plugin_context ), self::$plugin_name, self::$plugin_version );
		}

		/**
		 * Run this on activation
		 * Set a transient so that we know we've just activated the plugin
		 */
		function wc_call_for_price_installation_completed() {
			set_transient( 'wc_call_for_price_pro_activated', 1 );
		}

		/**
		 * Sends user to the Welcome page on first activation of the plugin as well as each
		 * time the plugin is updated is upgraded to a new version
		 *
		 * @access public
		 * @since  7.7
		 *
		 * @return void
		 */
		public function wc_call_for_price_license_page() {
			if( get_transient( 'wc_call_for_price_pro_activated' ) &&
				get_option( self::$plugin_prefix . '_pro_license_page_shown' ) != 'yes' ) {
				delete_transient( 'wc_call_for_price_pro_activated' );
				wp_safe_redirect( admin_url( 'index.php?page=' . self::$plugin_prefix . '-license-page' ) );
				exit;
			}
		}

		/**
		 * Register the Dashboard Page which is later hidden but this pages
		 * is used to render the License page.
		 *
		 * @access public
		 * @since  7.7
		 * @return void
		 */
		public function admin_menus() {
			$display_version = self::$plugin_version;
			// About Page
			add_dashboard_page(
				sprintf( self::$ts_welcome_header_text ),
				esc_html__( 'Welcome to ' . self::$plugin_name, self::$plugin_context ),
				'manage_options',
				self::$plugin_prefix . '-license-page',
				array( $this, 'about_screen' )
			);

		}

		/**
		 * Hide Individual Dashboard Pages
		 *
		 * @access public
		 * @since  7.7
		 * @return void
		 */
		public function admin_head() {
			remove_submenu_page( 'index.php', self::$plugin_prefix . '-license-page' );
		}

		/**
		 * Render About Screen
		 *
		 * @access public
		 * @since  7.7
		 * @return void
		 */
		public function about_screen() {
			$display_version = self::$plugin_version;
			$ts_file_path    = plugin_dir_url( __FILE__ ) ; 
			
			$license_key_name = self::$plugin_license_key;
			$license_status_name = self::$plugin_license_status;
			
			$existing_license = get_option( $license_key_name ) ? get_option( $license_key_name ) : '';
			$license_status = get_option( $license_status_name ) ? get_option( $license_status_name ) : '';
			
			$plugin_name = self::$plugin_name;
			$plugin_context = self::$plugin_context;
			$plugin_prefix = self::$plugin_prefix;
			
			$site_name = "<a href='https://www.tychesoftwares.com/' target='_blank'>Tyche Softwares</a>";
			$purchase_history = "<a href='https://www.tychesoftwares.com/checkout/purchase-history' target='_blank'>Account->Purchase History</a>";
			
			$display_failed = false;

			ob_start();

			$accept = false;

			if( isset( $_POST[ $plugin_prefix . '_license_display' ] ) &&  $_POST[ $plugin_prefix . '_license_display' ] == '2' ) { 
				// the license 	activation failed the first time round
			    $insert = false;
			    $license_key = '';
			    // check if a license key is entered
			    if( isset( $_POST[ 'license_key' ] ) && '' != $_POST[ 'license_key' ] ) {
			        $license_key = $_POST[ 'license_key' ];
			        update_option( $license_key_name, $license_key );
			        Alg_Woocommerce_Call_For_Price::cfp_activate_license(); // call the respective plugin's license activation function
			       
			    }
			    $license_details = array( 'license_key' => $license_key );
			
			    if( isset( $_POST[ $plugin_prefix . '_accept_terms' ] ) && '1' == $_POST[ $plugin_prefix . '_accept_terms' ] ) {
			        $license_details[ $plugin_prefix . '_accept_terms' ] = '1';
			        $accept = true;
			        $insert = true;
			    }
			
			    if( get_option( $license_status_name ) == 'valid' ) {
			        $license_details[ 'is_valid' ] = true;
			        $license_status = get_option( $license_status_name );
			        $insert = true;
			    }
			
			    // if accept terms is enabled or the license was valid, save and move on to the welcome page
			    if( $insert ) {
			        add_option( $plugin_prefix . '_installation_wizard_license_key', json_encode( $license_details ) );
			    }

		    	wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=alg_call_for_price' ) );
			} else if( isset( $_POST[ $plugin_prefix . '_license_display' ] ) && $_POST[ $plugin_prefix . '_license_display' ] == '1' ) { 
				if( isset( $_POST[ 'license_key' ] ) && '' != $_POST[ 'license_key' ] ) {
					// only for first time
				    update_option( $license_key_name, $_POST[ 'license_key' ] );
				   	Alg_Woocommerce_Call_For_Price::cfp_activate_license(); // call the respective plugin's license activation function
				 }
			    
			    if( get_option( $license_status_name ) == 'valid' ) { // license key validation was successful
			        wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=alg_call_for_price' ) );
			    } else { // license key validation failed
			        $display_failed = true;

			        //display the template that allows them to proceed without the license key
			        wc_get_template( '/license-activation-failed.php', array(
				        'plugin_name'         => $plugin_name,
				        'plugin_context'      => $plugin_context,
				        'get_welcome_header'  => $this->get_welcome_header(),
				        'site_name'           => $site_name,
				        'purchase_history'    => $purchase_history,
				        'plugin_prefix'       => $plugin_prefix,
			        ), self::$plugin_folder, self::$template_base );
			    }
			}

			if( isset( $license_status ) && $license_status != 'valid' && $accept == false && ! $display_failed ) {
			    wc_get_template( '/license-activation.php', array(
				    'plugin_name'         => $plugin_name,
				    'plugin_context'      => $plugin_context,
				    'get_welcome_header'  => $this->get_welcome_header(),
				    'site_name'           => $site_name,
				    'purchase_history'    => $purchase_history,
				    'plugin_prefix'       => $plugin_prefix,
			    ), self::$plugin_folder, self::$template_base );
			}
	        
	        echo ob_get_clean();

			add_option( self::$plugin_prefix . '_pro_license_page_shown', 'yes' );
		}

		/**
		 * The header section for the welcome screen.
		 *
		 * @since 7.7
		 */
		public function get_welcome_header() {
			// Badge for welcome page
			$ts_file_path    = plugin_dir_url( __FILE__ ) ;
			// Badge for welcome page
			$badge_url = $ts_file_path . '/assets/images/icon-256x256.png';
			?>
	        <h1 class="welcome-h1"><?php echo get_admin_page_title(); ?></h1>
			<?php $this->social_media_elements();
		}

		/**
		 * Social Media Like Buttons
		 *
		 * Various social media elements to Tyche Softwares
		 */
		public function social_media_elements() { 
			ob_start();
			wc_get_template( '/social-media-elements.php', 
							 array(), 
							 self::$plugin_folder, 
							 self::$template_base );
	        echo ob_get_clean();
		}

		/**
	     * This function returns the plugin version number.
	     *
	     * @access public 
	     * @since 7.7
	     * @return $plugin_version
	     */
	    public function ts_get_version() {
	        $plugin_version = '';
			$plugin_data = get_file_data( self::$plugin_file_path, array( 'Version' => 'Version' ) );
	        if ( ! empty( $plugin_data['Version'] ) ) {
	            $plugin_version = $plugin_data[ 'Version' ];
	        }
	        return $plugin_version;
	    }

	     /**
	     * It will retrun the plguin name.
	     * @return string $ts_plugin_name Name of the plugin
	     */
		public static function ts_get_plugin_name() {
	        $ordd_plugin_dir =  dirname ( dirname ( __FILE__ ) );
	        $ordd_plugin_dir .= '/order_delivery_date.php';

	        $ts_plugin_name = '';
	        $plugin_data = get_file_data( $ordd_plugin_dir, array( 'name' => 'Plugin Name' ) );
	        if ( ! empty( $plugin_data['name'] ) ) {
	            $ts_plugin_name = $plugin_data[ 'name' ];
	        }
	        return $ts_plugin_name;
	    }

	    /**
	     * This function returns the plugin url 
	     *
	     * @access public 
	     * @since 7.7
	     * @return string
	     */
	    public function ts_get_plugin_url() {
	        return plugins_url() . '/' . self::$plugin_folder . '/';
	    }

	    /**
	    * This function returns the template directory path
	    *
	    * @access public 
	    * @since 7.7
	    * @return string
	    */
	    public function ts_get_template_path() {
	    	return untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates/';
	    } 
	}

	$Alg_WC_Call_For_Price_License_Page = new Alg_WC_Call_For_Price_License_Page();
}