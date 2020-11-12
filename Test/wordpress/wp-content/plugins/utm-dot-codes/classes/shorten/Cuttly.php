<?php
/**
 * Cuttly API shortener class.
 *
 * @package UtmDotCodes
 */

namespace UtmDotCodes;


/**
 * Class Cuttly.
 */
class Cuttly implements \UtmDotCodes\Shorten {

	const API_URL = 'https://cutt.ly/cuttly-api';

	/**
	 * API credentials for Cuttly API.
	 *
	 * @var string|null The API key for the shortener.
	 */
	private $api_key = '739711b79e8d33834f1bdd97980e746cc536b';

	/**
	 * Response from API.
	 *
	 * @var object|null The response object from the shortener.
	 */
	private $response;

	/**
	 * Error message.
	 *
	 * @var object|null Error object with code and message properties.
	 */
	private $error_code;

	/**
	 * Cuttly constructor.
	 *
	 * @param string $api_key Credentials for API.
	 */
	public function __construct( $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * See interface for docblock.
	 *
	 * @inheritDoc
	 *
	 * @param array  $data See interface.
	 * @param string $query_string See interface.
	 *
	 * @return void
	 */
	public function shorten( $data, $query_string ) {
		if ( isset( $data['meta_input'] ) ) {
			$data = $data['meta_input'];
		}

		if ( '' !== $this->api_key ) {
			$response = wp_remote_post(
				self::API_URL . '/shorten',
				// Selective overrides of WP_Http() defaults.
				array(
					'method'      => 'POST',
					'timeout'     => 15,
					'redirection' => 5,
					'httpversion' => '1.1',
					'blocking'    => true,
					'headers'     => array(
						'Authorization' => 'Bearer ' . $this->api_key,
						'Content-Type'  => 'application/json',
					),
					'body'        => wp_json_encode( array( 'long_url' => $data['utmdclink_url'] . $query_string ) ),
				)
			);

			if ( isset( $response->errors ) ) {
				$this->error_code = 100;
			} else {
				$body          = json_decode( $response['body'] );
				$response_code = intval( $response['response']['code'] );

				if ( 200 === $response_code || 201 === $response_code ) {
					$response_url = '';

					if ( isset( $body->link ) ) {
						$response_url = $body->link;
					}

					if ( filter_var( $response_url, FILTER_VALIDATE_URL ) ) {
						$this->response = esc_url( wp_unslash( $body->link ) );
					}
				} elseif ( 403 === $response_code ) {
					$this->error_code = 4030;
				} else {
					$this->error_code = 500;
				}
			}
		}
	}

	/**
	 * Get response from Cuttly API for the request.
	 *
	 * @inheritDoc
	 */
	public function get_response() {
		return $this->response;
	}

	/**
	 * Get error code/message returned by Cuttly API for the request.
	 *
	 * @inheritDoc
	 */
	public function get_error() {
		return $this->error_code;
	}
}

/** Analysis ~ I created a cuttly account. Used the account to generate an API Key for cuttly. Found a class created for Bittly online, and changed values to redirect to Cuttly.
