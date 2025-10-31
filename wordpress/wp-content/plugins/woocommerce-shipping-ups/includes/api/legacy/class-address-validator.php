<?php
/**
 * Address validator class.
 *
 * @package WC_Shipping_UPS
 */

namespace WooCommerce\UPS\API\Legacy;

use SimpleXMLElement;
use WooCommerce\UPS\API\Abstract_Address_Validator;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Class Address_Validator
 */
class Address_Validator extends Abstract_Address_Validator {

	/**
	 * The UPS access request XML element.
	 *
	 * @var SimpleXMLElement|mixed
	 */
	private $access_request_xml_element;

	/**
	 * The address validation request.
	 *
	 * @var string
	 */
	private string $request = '';

	/**
	 * Response XML Element.
	 *
	 * @var SimpleXMLElement|mixed
	 */
	private $response_xml_element;

	/**
	 * Address_Validator constructor.
	 *
	 * @param array                  $address_to_validate        The address to validate.
	 * @param SimpleXMLElement|mixed $access_request_xml_element A UPS access request XML element.
	 */
	public function __construct( array $address_to_validate, $access_request_xml_element ) {
		parent::__construct( $address_to_validate );

		$this->access_request_xml_element = $access_request_xml_element;
	}

	/**
	 * Build the address validation request.
	 *
	 * @return void
	 */
	public function build_address_validation_request() {
		$address = $this->get_address_to_validate();

		$address_line = $address['address_1'];

		if ( ! empty( $address['address_2'] ) ) {
			$address_line .= ' ' . $address['address_2'];
		}

		$address_validation_request_xml_element = new SimpleXMLElement( '<AddressValidationRequest ></AddressValidationRequest >' );

		$request = $address_validation_request_xml_element->addChild( 'Request' );
		$request->addChild( 'RequestAction', 'XAV' );
		$request->addChild( 'RequestOption', '3' );

		$address_key_format = $address_validation_request_xml_element->addChild( 'AddressKeyFormat' );
		$address_key_format->addChild( 'AddressLine', $address_line );
		$address_key_format->addChild( 'PoliticalDivision2', $address['city'] );
		$address_key_format->addChild( 'PoliticalDivision1', $address['state'] );
		$address_key_format->addChild( 'PostcodePrimaryLow', $address['postcode'] );
		$address_key_format->addChild( 'CountryCode', $address['country'] );

		$this->request = $this->access_request_xml_element->asXML() . $address_validation_request_xml_element->asXML();
	}

	/**
	 * Get the address validation request.
	 */
	public function get_request(): string {
		return $this->request;
	}

	/**
	 * Get the address validation response.
	 */
	public function get_response() {
		return $this->response;
	}

	/**
	 * Check if the address validation request found a potential match.
	 *
	 * @return bool
	 */
	public function found_potential_match(): bool {
		return $this->is_valid_response_xml_element() && ! isset( $this->response_xml_element->NoCandidatesIndicator );
	}

	/**
	 * Check if the response XML element is valid.
	 *
	 * @return bool
	 */
	private function is_valid_response_xml_element(): bool {
		return $this->response_xml_element instanceof SimpleXMLElement;
	}

	/**
	 * Check if the address validation request returned a valid address classification.
	 * Valid classifications are:
	 * - Residential
	 * - Commercial
	 *
	 * @return bool
	 */
	public function found_valid_classification(): bool {
		$classification = $this->get_address_classification();
		if ( empty( $classification ) ) {
			return false;
		}

		return in_array( $classification, array( 'Residential', 'Commercial' ), true );
	}

	/**
	 * Get the address classification from the address validation response if it exists.
	 *
	 * @return string|null
	 */
	public function get_address_classification(): ?string {
		if ( ! $this->is_valid_response_xml_element() || ! isset( $this->response_xml_element->AddressClassification->Description ) ) {
			return null;
		}

		return $this->response_xml_element->AddressClassification->Description->__toString();
	}

	/**
	 * Post the address validation request.
	 *
	 * @return array|WP_Error
	 */
	protected function post_address_validation_request() {
		$request = str_replace( array( "\n", "\r" ), '', $this->request );

		/**
		 * Filter the address validation request body before sending it to the UPS API.
		 *
		 * @param string $request The request body.
		 * @param string $class   The class name.
		 *
		 * @since 3.5.0
		 */
		$body = apply_filters( 'woocommerce_shipping_ups_address_validation_request', $request, get_class( $this ) );

		return wp_remote_post(
			$this->get_endpoint(),
			array(
				'body' => $body,
			)
		);
	}


	/**
	 * Get the address validation endpoint.
	 *
	 * @return string
	 */
	protected function get_endpoint(): string {
		return 'https://onlinetools.ups.com/ups.app/xml/XAV';
	}


	/**
	 * Get the cached response key prefix.
	 *
	 * @return string
	 */
	protected function get_cached_response_key_prefix(): string {
		return 'ups_legacy_av_';
	}


	/**
	 * Process the address validation response.
	 *
	 * @param array|WP_Error $response The address validation response.
	 *
	 * @return void
	 */
	protected function process_response( $response ) {
		$this->set_response( $response );
		$this->convert_response_string_to_xml_element();
		$this->maybe_set_cached_response( $response );
	}


	/**
	 * Set the address validation response.
	 *
	 * @param array|WP_Error $response The address validation response.
	 *
	 * @return void
	 */
	protected function set_response( $response ) {
		if ( is_wp_error( $response ) ) {
			$this->response = $response;

			return;
		}

		$this->response = str_replace( '<?xml version="1.0"?>', '', wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Convert the response string to a SimpleXMLElement.
	 *
	 * @return void
	 */
	private function convert_response_string_to_xml_element() {
		$this->response_xml_element = simplexml_load_string( $this->response );
	}


	/**
	 * Get the first suggested address from the address validation response.
	 *
	 * @return array|false
	 */
	public function get_first_suggested_address() {
		if ( ! $this->found_potential_match() ) {
			return false;
		}

		if ( ! $this->is_valid_response_xml_element() || ! isset( $this->response_xml_element->AddressKeyFormat ) ) {
			return false;
		}

		$address = $this->response_xml_element->AddressKeyFormat;

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase --- Reason: This is the UPS API response.
		$address_line = $address->AddressLine;

		return array(
			'address_1' => $address_line[0]->__toString(),
			'address_2' => $address_line[1] ? $address_line[1]->__toString() : '',
			'city'      => $address->PoliticalDivision2->__toString(),
			'state'     => $address->PoliticalDivision1->__toString(),
			'postcode'  => $address->PostcodePrimaryLow->__toString(),
			'country'   => $address->CountryCode->__toString(),
		);
		// phpcs:enable
	}
}
