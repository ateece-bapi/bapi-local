<?php
/**
 * Plugin Name: WC Currency Switcher
 * Description: Currency Converter
 * Version: 1.5.6
 * Tags: currency switcher, currency switcher woocommerce, currency switcher WordPress, currency converter plugin, currency switcher extension, currency switcher plugin, currency switcher at checkout, woocommerce, WordPress, woocommerce extension, donation currency switcher
 * Author: wpexpertsio
 * Author URI: http://wpexpert.io/
 * Developer: wpexpertsio
 * Developer URI: https://wpexperts.io/
 * Text Domain: wccs
 *
 * Woo: 6302270:6147044df74946ce8d021941c85612a6
 * WC requires at least: 3.0
 * WC tested up to: 3.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// $debug_tags = array();
// add_action( 'all', function ( $tag ) {
// global $debug_tags;
// if ( in_array( $tag, $debug_tags ) ) {
// return;
// }
// echo "<pre>" . $tag . "</pre>";
// $debug_tags[] = $tag;
// } );

/**
 * Define plugin constants
 */
define( 'WCCS_VERSION', '1.5.6' );
define( 'WCCS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WCCS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Activation and Deactivation hooks
 */
register_activation_hook( __FILE__, 'wccs_activation' );
register_deactivation_hook( __FILE__, 'wccs_deactivation' );

function wccs_activation() {
	require_once WCCS_PLUGIN_PATH . 'includes/wccs-activator.php';
	WCCS_Activator::activate();
}

function wccs_deactivation() {
	require_once WCCS_PLUGIN_PATH . 'includes/wccs-deactivator.php';
	WCCS_Deactivator::deactivate();
}

/**
 * The plugin core
 */
require WCCS_PLUGIN_PATH . 'includes/wccs-core.php';

function get_all_active_payment_gateways() {
	$gateways         = WC()->payment_gateways->get_available_payment_gateways();
	$enabled_gateways = array();
	if ( $gateways ) {
		foreach ( $gateways as $gateway ) {
			if ( 'yes' == $gateway->enabled ) {
				$arr                = array();
				$arr['id']          = $gateway->id;
				$arr['title']       = $gateway->title;
				$arr['enabled']     = $gateway->enabled;
				$enabled_gateways[] = $arr;
			}
		}
	}

	return $enabled_gateways;
}

function wccs_delete_all_between( $beginning, $end, $string ) {
	$beginningPos = strpos( $string, $beginning );
	$endPos       = strpos( $string, $end );
	if ( false === $beginningPos || false === $endPos ) {
		return $string;
	}

	$textToDelete = substr( $string, $beginningPos, ( $endPos + strlen( $end ) ) - $beginningPos );

	return wccs_delete_all_between( $beginning, $end, str_replace( $textToDelete, '', $string ) ); // recursion to ensure all occurrences are replaced
}

if ( ! function_exists( 'wccs_get_country_currency' ) ) {
	/**
	 * Get_country_currency.
	 *
	 * 237 countries.
	 * Two-letter country code (ISO 3166-1 alpha-2) => Three-letter currency code (ISO 4217).
	 */
	function wccs_get_country_currency( $code ) {
		$arr = array(
			'AF' => 'AFN',
			'AL' => 'ALL',
			'DZ' => 'DZD',
			'AS' => 'USD',
			'AD' => 'EUR',
			'AO' => 'AOA',
			'AI' => 'XCD',
			'AQ' => 'XCD',
			'AG' => 'XCD',
			'AR' => 'ARS',
			'AM' => 'AMD',
			'AW' => 'AWG',
			'AU' => 'AUD',
			'AT' => 'EUR',
			'AZ' => 'AZN',
			'BS' => 'BSD',
			'BH' => 'BHD',
			'BD' => 'BDT',
			'BB' => 'BBD',
			'BY' => 'BYR',
			'BE' => 'EUR',
			'BZ' => 'BZD',
			'BJ' => 'XOF',
			'BM' => 'BMD',
			'BT' => 'BTN',
			'BO' => 'BOB',
			'BA' => 'BAM',
			'BW' => 'BWP',
			'BV' => 'NOK',
			'BR' => 'BRL',
			'IO' => 'USD',
			'BN' => 'BND',
			'BG' => 'BGN',
			'BF' => 'XOF',
			'BI' => 'BIF',
			'KH' => 'KHR',
			'CM' => 'XAF',
			'CA' => 'CAD',
			'CV' => 'CVE',
			'KY' => 'KYD',
			'CF' => 'XAF',
			'TD' => 'XAF',
			'CL' => 'CLP',
			'CN' => 'CNY',
			'HK' => 'HKD',
			'CX' => 'AUD',
			'CC' => 'AUD',
			'CO' => 'COP',
			'KM' => 'KMF',
			'CG' => 'XAF',
			'CD' => 'CDF',
			'CK' => 'NZD',
			'CR' => 'CRC',
			'HR' => 'HRK',
			'CU' => 'CUP',
			'CY' => 'EUR',
			'CZ' => 'CZK',
			'DK' => 'DKK',
			'DJ' => 'DJF',
			'DM' => 'XCD',
			'DO' => 'DOP',
			'EC' => 'ECS',
			'EG' => 'EGP',
			'SV' => 'SVC',
			'GQ' => 'XAF',
			'ER' => 'ERN',
			'EE' => 'EUR',
			'ET' => 'ETB',
			'FK' => 'FKP',
			'FO' => 'DKK',
			'FJ' => 'FJD',
			'FI' => 'EUR',
			'FR' => 'EUR',
			'GF' => 'EUR',
			'TF' => 'EUR',
			'GA' => 'XAF',
			'GM' => 'GMD',
			'GE' => 'GEL',
			'DE' => 'EUR',
			'GH' => 'GHS',
			'GI' => 'GIP',
			'GR' => 'EUR',
			'GL' => 'DKK',
			'GD' => 'XCD',
			'GP' => 'EUR',
			'GU' => 'USD',
			'GT' => 'QTQ',
			'GG' => 'GGP',
			'GN' => 'GNF',
			'GW' => 'GWP',
			'GY' => 'GYD',
			'HT' => 'HTG',
			'HM' => 'AUD',
			'HN' => 'HNL',
			'HU' => 'HUF',
			'IS' => 'ISK',
			'IN' => 'INR',
			'ID' => 'IDR',
			'IR' => 'IRR',
			'IQ' => 'IQD',
			'IE' => 'EUR',
			'IM' => 'GBP',
			'IL' => 'ILS',
			'IT' => 'EUR',
			'JM' => 'JMD',
			'JP' => 'JPY',
			'JE' => 'GBP',
			'JO' => 'JOD',
			'KZ' => 'KZT',
			'KE' => 'KES',
			'KI' => 'AUD',
			'KP' => 'KPW',
			'KR' => 'KRW',
			'KW' => 'KWD',
			'KG' => 'KGS',
			'LA' => 'LAK',
			'LV' => 'EUR',
			'LB' => 'LBP',
			'LS' => 'LSL',
			'LR' => 'LRD',
			'LY' => 'LYD',
			'LI' => 'CHF',
			'LT' => 'EUR',
			'LU' => 'EUR',
			'MK' => 'MKD',
			'MG' => 'MGF',
			'MW' => 'MWK',
			'MY' => 'MYR',
			'MV' => 'MVR',
			'ML' => 'XOF',
			'MT' => 'EUR',
			'MH' => 'USD',
			'MQ' => 'EUR',
			'MR' => 'MRO',
			'MU' => 'MUR',
			'YT' => 'EUR',
			'MX' => 'MXN',
			'FM' => 'USD',
			'MD' => 'MDL',
			'MC' => 'EUR',
			'MN' => 'MNT',
			'ME' => 'EUR',
			'MS' => 'XCD',
			'MA' => 'MAD',
			'MZ' => 'MZN',
			'MM' => 'MMK',
			'NA' => 'NAD',
			'NR' => 'AUD',
			'NP' => 'NPR',
			'NL' => 'EUR',
			'AN' => 'ANG',
			'NC' => 'XPF',
			'NZ' => 'NZD',
			'NI' => 'NIO',
			'NE' => 'XOF',
			'NG' => 'NGN',
			'NU' => 'NZD',
			'NF' => 'AUD',
			'MP' => 'USD',
			'NO' => 'NOK',
			'OM' => 'OMR',
			'PK' => 'PKR',
			'PW' => 'USD',
			'PA' => 'PAB',
			'PG' => 'PGK',
			'PY' => 'PYG',
			'PE' => 'PEN',
			'PH' => 'PHP',
			'PN' => 'NZD',
			'PL' => 'PLN',
			'PT' => 'EUR',
			'PR' => 'USD',
			'QA' => 'QAR',
			'RE' => 'EUR',
			'RO' => 'RON',
			'RU' => 'RUB',
			'RW' => 'RWF',
			'SH' => 'SHP',
			'KN' => 'XCD',
			'LC' => 'XCD',
			'PM' => 'EUR',
			'VC' => 'XCD',
			'WS' => 'WST',
			'SM' => 'EUR',
			'ST' => 'STD',
			'SA' => 'SAR',
			'SN' => 'XOF',
			'RS' => 'RSD',
			'SC' => 'SCR',
			'SL' => 'SLL',
			'SG' => 'SGD',
			'SK' => 'EUR',
			'SI' => 'EUR',
			'SB' => 'SBD',
			'SO' => 'SOS',
			'ZA' => 'ZAR',
			'GS' => 'GBP',
			'SS' => 'SSP',
			'ES' => 'EUR',
			'LK' => 'LKR',
			'SD' => 'SDG',
			'SR' => 'SRD',
			'SJ' => 'NOK',
			'SZ' => 'SZL',
			'SE' => 'SEK',
			'CH' => 'CHF',
			'SY' => 'SYP',
			'TW' => 'TWD',
			'TJ' => 'TJS',
			'TZ' => 'TZS',
			'TH' => 'THB',
			'TG' => 'XOF',
			'TK' => 'NZD',
			'TO' => 'TOP',
			'TT' => 'TTD',
			'TN' => 'TND',
			'TR' => 'TRY',
			'TM' => 'TMT',
			'TC' => 'USD',
			'TV' => 'AUD',
			'UG' => 'UGX',
			'UA' => 'UAH',
			'AE' => 'AED',
			'GB' => 'GBP',
			'US' => 'USD',
			'UM' => 'USD',
			'UY' => 'UYU',
			'UZ' => 'UZS',
			'VU' => 'VUV',
			'VE' => 'VEF',
			'VN' => 'VND',
			'VI' => 'USD',
			'WF' => 'XPF',
			'EH' => 'MAD',
			'YE' => 'YER',
			'ZM' => 'ZMW',
			'ZW' => 'ZWD',
		);

		return isset( $arr[ $code ] ) ? $arr[ $code ] : '';
	}
}

function wccs_get_client_ip_server() {
	$ipaddress = '';
	if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) && sanitize_text_field( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		$ipaddress = sanitize_text_field( $_SERVER['HTTP_CLIENT_IP'] );
	} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && sanitize_text_field( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ipaddress = sanitize_text_field( $_SERVER['HTTP_X_FORWARDED_FOR'] );
	} elseif ( isset( $_SERVER['HTTP_X_FORWARDED'] ) && sanitize_text_field( $_SERVER['HTTP_X_FORWARDED'] ) ) {
		$ipaddress = sanitize_text_field( $_SERVER['HTTP_X_FORWARDED'] );
	} elseif ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) && sanitize_text_field( $_SERVER['HTTP_FORWARDED_FOR'] ) ) {
		$ipaddress = sanitize_text_field( $_SERVER['HTTP_FORWARDED_FOR'] );
	} elseif ( isset( $_SERVER['HTTP_FORWARDED'] ) && sanitize_text_field( $_SERVER['HTTP_FORWARDED'] ) ) {
		$ipaddress = sanitize_text_field( $_SERVER['HTTP_FORWARDED'] );
	} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) && sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) ) {
		$ipaddress = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
	} else {
		$ipaddress = 'UNKNOWN';
	}

	// $ipaddress = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
	$data                           = array();
	$data['geoplugin_currencyCode'] = '';
	$access_key                     = get_option('wccs_ipapi_key'); //'934e8ba6b71576d5841ffedfa6f8a2b7'; // our access key.
	if ( ! empty( trim( $access_key ) ) ) {
		$url                            = apply_filters( 'wc_currency_get_location_url', 'http://api.ipapi.com/api/' . $ipaddress . '?access_key=' . $access_key . '&output=json&format=1', $ipaddress );		
		$temp                           = json_decode( file_get_contents( $url ) );
		
		if ( isset( $temp->error ) ) {
			$data['geoplugin_error'] = 'WC Currency Switcher -> Location API: ' . $temp->error->info;
		} else {
			$data['geoplugin_currencyCode'] = wccs_get_country_currency( $temp->country_code );
			$data['geoplugin_countryCode']  = $temp->country_code;
			$data                           = apply_filters( 'wc_currency_location_data', $data, $ipaddress, $url );
		}
	}
	return $data;
}

// show notice if WooCommerce plugin is not active
add_action('admin_notices', 'wccs_limit_location_reach' );
function wccs_limit_location_reach() {

	$data = wccs_get_client_ip_server();

	if ( isset( $data['geoplugin_error'] ) ) {				
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php echo esc_attr( $data['geoplugin_error'] ); ?></p>
		</div>
		<?php				
	}
}

function wccs_free_shipping_criteria() {
	if ( class_exists( 'WCCS' ) ) {

		// remove filter from plugin first
		remove_filter( 'woocommerce_package_rates', array( $GLOBALS['WCCS'], 'wccs_change_shipping_rates_cost' ), 10, 2 );

		// override shipping price
		add_filter( 'woocommerce_package_rates', 'wccs_change_free_shipping_price_criteria', 99, 2 );
		function wccs_change_free_shipping_price_criteria( $rates, $package ) {

			$coversion_rate = $GLOBALS['WCCS']->wccs_get_currency_rate();
			$decimals       = $GLOBALS['WCCS']->wccs_get_currency_decimals();

			$amount_for_free_shipping = 70.00; // set free shipping threshold
			$cart_total               = WC()->cart->subtotal;

			foreach ( $rates as $id => $rate ) {

				if ( $coversion_rate ) {

					$amount_for_free_shipping = round( ( $amount_for_free_shipping * $coversion_rate ), $decimals );

					if ( $cart_total < $amount_for_free_shipping ) {
						if ( 'free_shipping' === $rate->method_id ) {
							unset( $rates[ $id ] );
						}
					}

					if ( isset( $rates[ $id ] ) ) {
						$rates[ $id ]->cost = round( ( $rates[ $id ]->cost * $coversion_rate ), $decimals );
						// Taxes rate cost (if enabled)
						$taxes = array();
						foreach ( $rates[ $id ]->taxes as $key => $tax ) {
							if ( $tax > 0 ) { // set the new tax cost
								// set the new line tax cost in the taxes array
								$taxes[ $key ] = round( ( $tax * $coversion_rate ), $decimals );
							}
						}
						// Set the new taxes costs
						$rates[ $id ]->taxes = $taxes;
					}
				} else {

					if ( $cart_total < $amount_for_free_shipping ) {

						if ( 'free_shipping' === $rate->method_id ) {
							unset( $rates[ $id ] );
						}
					}
				}
			}

			return $rates;
		}
	}
}

add_action( 'plugins_loaded', 'wccs_load_textdomain' );
function wccs_load_textdomain() {
	//$locale = apply_filters( 'plugin_locale', get_locale(), 'my-plugin' );
	load_plugin_textdomain( 'wccs', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}

// Instantiate plugin
$wccs_core = WCCS_Core::getInstance();
