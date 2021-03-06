<?php
/**
 * WooCommerce API Client Class
 *
 * @author Gerhard Potgieter
 * @since 2013.12.05
 * @copyright Gerhard Potgieter
 * @version 0.3.1
 * @license GPL 3 or later http://www.gnu.org/licenses/gpl.html
 */

class WC_API_Client {

	/**
	 * API base endpoint
	 */
	const API_ENDPOINT = 'wc-api/v2/';

	/**
	 * The HASH alorithm to use for oAuth signature, SHA256 or SHA1
	 */
	const HASH_ALGORITHM = 'SHA256';

	/**
	 * The API URL
	 * @var string
	 */
	private $_api_url;

	/**
	 * The WooCommerce Consumer Key
	 * @var string
	 */
	private $_consumer_key;

	/**
	 * The WooCommerce Consumer Secret
	 * @var string
	 */
	private $_consumer_secret;

	/**
	 * If the URL is secure, used to decide if oAuth or Basic Auth must be used
	 * @var boolean
	 */
	private $_is_ssl;

	/**
	 * Return the API data as an array, object, or keep it in JSON string format
	 * @var boolean
	 */
	private $_return_as = 'array';

	private $_headers = array();
    private $_links = array();

	/**
	 * Default contructor
	 * @param string  $consumer_key    The consumer key
	 * @param string  $consumer_secret The consumer secret
	 * @param string  $store_url       The URL to the WooCommerce store
	 * @param boolean $is_ssl          If the URL is secure or not, optional
	 */
	public function __construct( $consumer_key, $consumer_secret, $store_url, $is_ssl = false ) {
		if ( ! empty( $consumer_key ) && ! empty( $consumer_secret ) && ! empty( $store_url ) ) {
			$this->_api_url = (  rtrim($store_url,'/' ) . '/' ) . self::API_ENDPOINT;
			$this->set_consumer_key( $consumer_key );
			$this->set_consumer_secret( $consumer_secret );
			$this->set_is_ssl( $is_ssl );
		} else if ( ! isset( $consumer_key ) && ! isset( $consumer_secret ) ) {
			throw new Exception( 'Error: __construct() - Consumer Key / Consumer Secret missing.' );
		} else {
			throw new Exception( 'Error: __construct() - Store URL missing.' );
		}
	}

	/**
	 * Get API Index
	 * @return mixed|json string
	 */
	public function get_index() {
		return $this->_make_api_call( '' );
	}

	/**
	 * Get all orders
	 * @param  array  $params
	 * @return mixed|json string
	 */
	public function get_orders( $params = array() ) {
		return $this->_make_api_call( 'orders', $params );
	}

	/**
	 * Get a single order
	 * @param  integer $order_id
	 * @return mixed|json string
	 */
	public function get_order( $order_id ) {
		return $this->_make_api_call( 'orders/' . $order_id );
	}

	/**
	 * Get the total order count
	 * @return mixed|json string
	 */
	public function get_orders_count() {
		return $this->_make_api_call( 'orders/count' );
	}

	/**
	 * Get orders notes for an order
	 * @param  integer $order_id
	 * @return mixed|json string
	 */
	public function get_order_notes( $order_id ) {
		return $this->_make_api_call( 'orders/' . $order_id . '/notes' );
	}

	/**
	 * Update the order, currently only status update suported by API
	 * @param  integer $order_id
	 * @param  array  $data
	 * @return mixed|json string
	 */
	public function update_order( $order_id, $data = array() ) {
		return $this->_make_api_call( 'orders/' . $order_id, $data, 'POST' );
	}

	/**
	 * Delete the order, not suported in WC 2.1, scheduled for 2.2
	 * @param  integer $order_id
	 * @return mixed|json string
	 */
	public function delete_order( $order_id ) {
		return $this->_make_api_call( 'orders/' . $order_id, $data = array(), 'DELETE' );
	}

	/**
	 * Get all coupons
	 * @param  array  $params
	 * @return mixed|json string
	 */
	public function get_coupons( $params = array() ) {
		return $this->_make_api_call( 'coupons', $params );
	}

	/**
	 * Get a single coupon
	 * @param  integer $coupon_id
	 * @return mixed|json string
	 */
	public function get_coupon( $coupon_id ) {
		return $this->_make_api_call( 'coupons/' . $coupon_id );
	}

	/**
	 * Get the total coupon count
	 * @return mixed|json string
	 */
	public function get_coupons_count() {
		return $this->_make_api_call( 'coupons/count' );
	}

	/**
	 * Get a coupon by the coupon code
	 * @param  string $coupon_code
	 * @return mixed|json string
	 */
	public function get_coupon_by_code( $coupon_code ) {
		return $this->_make_api_call( 'coupons/code/' . rawurlencode( rawurldecode( $coupon_code ) ) );
	}

	/**
	 * Get all customers
	 * @param  array  $params
	 * @return mixed|json string
	 */
	public function get_customers( $params = array() ) {
		return $this->_make_api_call( 'customers', $params );
	}

	/**
	 * Get a single customer
	 * @param  integer $customer_id
	 * @return mixed|json string
	 */
	public function get_customer( $customer_id ) {
		return $this->_make_api_call( 'customers/' . $customer_id );
	}

	/**
	 * Get a single customer by email
	 * @param  string $email
	 * @return mixed|json string
	 */
	public function get_customer_by_email( $email ) {
		return $this->_make_api_call( 'customers/email/' . $email );
	}

	/**
	 * Get the total customer count
	 * @return mixed|json string
	 */
	public function get_customers_count() {
		return $this->_make_api_call( 'customers/count' );
	}

	/**
	 * Get the customer's orders
	 * @param  integer $customer_id
	 * @return mixed|json string
	 */
	public function get_customer_orders( $customer_id ) {
		return $this->_make_api_call( 'customers/' . $customer_id . '/orders' );
	}

	/**
	 * Get all the products
	 * @param  array  $params
	 * @return mixed|json string
	 */
	public function get_products( $params = array() ) {
		return $this->_make_api_call( 'products', $params );
	}

	/**
	 * Get a single product
	 * @param  integer $product_id
	 * @return mixed|json string
	 */
	public function get_product( $product_id ) {
		return $this->_make_api_call( 'products/' . $product_id );
	}

	/**
	 * Get the total product count
	 * @return mixed|json string
	 */
	public function get_products_count() {
		return $this->_make_api_call( 'products/count' );
	}

	/**
	 * Get reviews for a product
	 * @param  integer $product_id
	 * @return mixed|json string
	 */
	public function get_product_reviews( $product_id ) {
		return $this->_make_api_call( 'products/' . $product_id . '/reviews' );
	}

	/**
	 * Get reports
	 * @param  array  $params
	 * @return mixed|json string
	 */
	public function get_reports( $params = array() ) {
		return $this->_make_api_call( 'reports', $params );
	}

	/**
	 * Get the sales report
	 * @param  array  $params
	 * @return mixed|json string
	 */
	public function get_sales_report( $params = array() ) {
		return $this->_make_api_call( 'reports/sales', $params );
	}

	/**
	 * Get the top sellers report
	 * @param  array  $params
	 * @return mixed|json string
	 */
	public function get_top_sellers_report( $params = array() ) {
		return $this->_make_api_call( 'reports/sales/top_sellers', $params );
	}

	/**
	 * Run a custom endpoint call, for when you extended the API with your own endpoints
	 * @param  string $endpoint
	 * @param  array  $params
	 * @param  string $method
	 * @return mixed|json string
	 */
	public function make_custom_endpoint_call( $endpoint, $params = array(), $method = 'GET' ) {
		return $this->_make_api_call( $endpoint, $params, $method );
	}

	/**
	 * Set the consumer key
	 * @param string $consumer_key
	 */
	public function set_consumer_key( $consumer_key ) {
		$this->_consumer_key = $consumer_key;
	}

	/**
	 * Set the consumer secret
	 * @param string $consumer_secret
	 */
	public function set_consumer_secret( $consumer_secret ) {
		$this->_consumer_secret = $consumer_secret;
	}

	/**
	 * Set SSL variable
	 * @param boolean $is_ssl
	 */
	public function set_is_ssl( $is_ssl ) {
		if ( $is_ssl == '' ) {
			if ( strtolower( substr( $this->_api_url, 0, 5 ) ) == 'https' ) {
				$this->_is_ssl = true;
			} else $this->_is_ssl = false;
		} else $this->_is_ssl = $is_ssl;
	}

	/**
	 * Set the return data as object
	 * @param boolean $is_object
	 */
	public function set_return_as( $type ) {
        $valid = array('array', 'object', 'string');
        if (!in_array($type, $valid)) {
            throw new Exception("invalid return type $type: must be one of '".implode("', ", $valid)."'");
        }
		$this->_return_as = $type;
	}

	/**
	 * Make the call to the API
	 * @param  string $endpoint
	 * @param  array  $params
	 * @param  string $method
	 * @return mixed|json string
	 */
	private function _make_api_call( $endpoint, $params = array(), $method = 'GET' ) {
		$ch = curl_init();
		$this->_headers = array();
        $this->_links = array();

        $urlParams = $method === 'GET' ? $params : array();

		// Check if we must use Basic Auth or 1 legged oAuth, if SSL we use basic, if not we use OAuth 1.0a one-legged
		if ( $this->_is_ssl ) {
			curl_setopt( $ch, CURLOPT_USERPWD, $this->_consumer_key . ":" . $this->_consumer_secret );
		} else {
			$urlParams['oauth_consumer_key'] = $this->_consumer_key;
			$urlParams['oauth_timestamp'] = time();
			$urlParams['oauth_nonce'] = sha1( microtime() );
			$urlParams['oauth_signature_method'] = 'HMAC-' . self::HASH_ALGORITHM;
			$urlParams['oauth_signature'] = $this->generate_oauth_signature( $urlParams, $method, $endpoint );
		}

        $paramString = $urlParams ? '?'.http_build_query($urlParams) : '';

		// Set up the enpoint URL
		curl_setopt( $ch, CURLOPT_URL, $this->_api_url . $endpoint . $paramString );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 30 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_HEADER, 1 );

        if ( 'POST' === $method ) {
			curl_setopt( $ch, CURLOPT_POST, true );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $params ) );
    	} elseif ('PUT' === $method ) {
    	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode( $params ) );
		} elseif ( 'DELETE' === $method ) {
			curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'DELETE' );
    	}

		$response = curl_exec( $ch );

        if ( empty( $response ) ) {
            $code = curl_errno($ch);
            $msg = curl_error($ch);
            if (!$code) {
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $msg = $code;
            }
			$body = json_encode(array(
                'errors' => array(array('code' => $code, 'message' => $msg)),
            ));
		} else {
            //list($header, $body) = explode("\r\n\r\n", $response, 2);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $header_size);
            $body = substr($response, $header_size);
            $this->_headers = http_parse_headers($header);
            $this->set_links();
        }

        curl_close($ch);
		return $this->format_output($body);
	}

    private function format_output($json_str)
    {
        switch ($this->_return_as) {
            case 'array': return json_decode($json_str, true);
            case 'object': return json_decode($json_str, false);
        }
        return $json_str;
    }

    private function set_links()
    {
        if (!$this->_headers || empty($this->_headers['Link'])) return;
        $a = $this->_headers['Link'];
        if (!is_array($a)) $a = array($this->_headers['Link']);
        foreach ($a as $v) {
            if (preg_match("/\<([^\>]+)\>\s*;\s*rel=\"(next|last|first|prev)\"/", $v, $matches)) {
                $this->_links[$matches[2]] = $matches[1];
            }
        }
    }

    public function get_headers()
	{
	    return $this->_headers;
	}

    public function get_links()
    {
        return $this->_links;
    }

	/**
	 * Generate oAuth signature
	 * @param  array  $params
	 * @param  string $http_method
	 * @param  string $endpoint
	 * @return string
	 */
	public function generate_oauth_signature( $params, $http_method, $endpoint ) {
		$base_request_uri = rawurlencode( $this->_api_url . $endpoint );

		// normalize parameter key/values and sort them
		$params = $this->normalize_parameters( $params );
		uksort( $params, 'strcmp' );

		// form query string
		$query_params = array();
		foreach ( $params as $param_key => $param_value ) {
			$query_params[] = $param_key . '%3D' . $param_value; // join with equals sign
		}

		$query_string = implode( '%26', $query_params ); // join with ampersand

		// form string to sign (first key)
		$string_to_sign = $http_method . '&' . $base_request_uri . '&' . $query_string;

		return base64_encode( hash_hmac( self::HASH_ALGORITHM, $string_to_sign, $this->_consumer_secret, true ) );
	}

	/**
	 * Normalize each parameter by assuming each parameter may have already been
	 * encoded, so attempt to decode, and then re-encode according to RFC 3986
	 *
	 * Note both the key and value is normalized so a filter param like:
	 *
	 * 'filter[period]' => 'week'
	 *
	 * is encoded to:
	 *
	 * 'filter%5Bperiod%5D' => 'week'
	 *
	 * This conforms to the OAuth 1.0a spec which indicates the entire query string
	 * should be URL encoded
	 *
	 * @since 0.3.1
	 * @see rawurlencode()
	 * @param array $parameters un-normalized pararmeters
	 * @return array normalized parameters
	 */
	private function normalize_parameters( $parameters ) {

		$normalized_parameters = array();

		foreach ( $parameters as $key => $value ) {

			// percent symbols (%) must be double-encoded
			$key   = str_replace( '%', '%25', rawurlencode( rawurldecode( $key ) ) );
			$value = str_replace( '%', '%25', rawurlencode( rawurldecode( $value ) ) );

			$normalized_parameters[ $key ] = $value;
		}

		return $normalized_parameters;
	}

}

if (!function_exists('http_parse_headers'))
{
    function http_parse_headers($raw_headers) {
        $headers = array();
        $key = '';
        foreach (explode("\n", $raw_headers) as $i => $h) {
            $h = explode(':', $h, 2);

            if (isset($h[1])) {
                if (!isset($headers[$h[0]]))
                    $headers[$h[0]] = trim($h[1]);
                elseif (is_array($headers[$h[0]])) {
                    $headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1])));
                } else {
                    $headers[$h[0]] = array_merge(array($headers[$h[0]]), array(trim($h[1])));
                }
                $key = $h[0];
            } else {
                if (substr($h[0], 0, 1) == "\t") {
                    $headers[$key] .= "\r\n\t".trim($h[0]);
                } elseif (!$key) {
                    $headers[0] = trim($h[0]);
                }
            }
        }
        return $headers;
    }

}
