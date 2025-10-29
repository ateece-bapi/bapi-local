<?php
/*
Plugin Name: Bapi - Data Conversion
Plugin URI: http://www.bapihvac.com
Description: Data conversion functionality for V4
Version: 1.0
Author: Sleeping Giant Studios
Author URI: http://www.sleepinggiantstudios.com
License: GPL2
 */
/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! class_exists( 'BAPI_Data_Conversion' ) ) {
	class BAPI_Data_Conversion {
		private static $background_convert_customer_info;
		private static $background_delete_address_book;

		public function __construct() {
			add_action( 'admin_menu', array( $this, 'register_data_update_page' ) );
			add_action( 'init', array( $this, 'init_background_updater' ), 5 );

			add_action( 'wp_ajax_setup_all_customer_info_conversion', array( $this, 'setup_all_customer_info_conversion' ) );
			add_action( 'wp_ajax_setup_delete_address_book', array( $this, 'setup_delete_address_book' ) );

			add_action( 'wp_ajax_testing_stuff', array( $this, 'testing_stuff' ) );

			add_filter( 'acf/update_value/name=country', array( $this, 'convert_country_to_woo' ), 10, 3 );

			$this->cli_commands();
		}

		function cli_commands(){
			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				include_once plugin_dir_path( __FILE__ ) . 'includes/class-bapi-data-conversion-cli.php';
				WP_CLI::add_command( 'bapi conversion', 'Bapi_Data_Conversion_CLI' );

			}
		}

		function testing_stuff() {
			$users = get_users( array( 'include' => array( 512 ) ) );
			if ( ! empty( $users ) ) {
				foreach ( $users as $user ) {
					if ( have_rows( 'address_book', 'user_' . $user->ID ) ) {

						while ( have_rows( 'address_book', 'user_' . $user->ID ) ) {
							the_row();
							var_dump( get_row_index() );
							delete_row( 'address_book', get_row_index(), 'user_' . $user->ID );
						}
					}
				}
			}
		}

		public function setup_delete_address_book() {
			// Testing with this user http://bapihvac.local/wp-admin/user-edit.php?user_id=512&wp_http_referer=%2Fwp-admin%2Fusers.php%3Fs%3Dhart%26paged%3D1
			if ( function_exists( 'get_field_objects' ) ) {

				$users = get_users();
				if ( ! empty( $users ) ) {
					foreach ( $users as $user ) {
						self::$background_delete_address_book->push_to_queue( array( 'user' => $user ) );

					}
					self::$background_delete_address_book->save()->dispatch();
				}
			}

			exit( 'setup_delete_address_book' );
		}

		public function convert_country_to_woo( $value, $post_id, $field ) {
			if ( $value == 'USA' ) {
				$value = 'US';
			}
			return $value;
		}

		public function setup_all_customer_info_conversion() {
			// Testing with this user http://bapihvac.local/wp-admin/user-edit.php?user_id=512&wp_http_referer=%2Fwp-admin%2Fusers.php%3Fs%3Dhart%26paged%3D1
			if ( function_exists( 'get_cimyFields' ) && function_exists( 'get_field_objects' ) ) {

				$args = array();
				if ( isset( $_REQUEST['include'] ) ) {
					$args['include'] = array( $_REQUEST['include'] );
				}
				$users = get_users( $args );
				if ( ! empty( $users ) ) {
					foreach ( $users as $user ) {
						self::$background_convert_customer_info->push_to_queue( array( 'user' => $user ) );

					}
					self::$background_convert_customer_info->save()->dispatch();
				}
			}

			exit( 'setup_all_customer_info_conversion' );
		}

		public static function init_background_updater() {
			require_once plugin_dir_path( __FILE__ ) . 'background-processes/class-background-convert-customer-info.php';
			self::$background_convert_customer_info = new BAPI_Background_Convert_Customer_Info();

			require_once plugin_dir_path( __FILE__ ) . 'background-processes/class-background-delete-address-book.php';
			self::$background_delete_address_book = new BAPI_Background_Delete_Address_Book();
		}

		public function register_data_update_page() {
			add_menu_page(
				'Bapi Data Updates',
				'Bapi Data Updates',
				'manage_options',
				'bapi-data-updates',
				 function() {
                     $this->data_update_page();
                }
			);
		}

		public function data_update_page() {
			echo '<h1>Bapi Data Updates</h1>';
			echo '<p><a target="_blank" href="' . add_query_arg( array( 'action' => 'setup_all_customer_info_conversion' ), admin_url( 'admin-ajax.php' ) ) . '">Convert all Customer Information</a></p>';
			echo '<p><a target="_blank" href="' . add_query_arg( array( 'action' => 'setup_delete_address_book' ), admin_url( 'admin-ajax.php' ) ) . '">Delete all ACF Address Book Stuff</a></p>';
		}

	}
}

if ( class_exists( 'BAPI_Data_Conversion' ) ) {

	$bapi_data_conversion = new BAPI_Data_Conversion();

}
