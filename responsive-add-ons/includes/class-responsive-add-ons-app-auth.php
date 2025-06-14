<?php
/**
 * Class for handling the Cyberchimps App authentication.
 *
 * @package WPCode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Responsive_Add_Ons_App_Auth.
 */
class Responsive_Add_Ons_App_Auth {

	/**
	 * Base URL of Cyberchimps App API
	 */
	const API_BASE_PATH = CC_APP_URL . '/wp-json/api/v1/';

	/**
	 * Is the current plugin authenticated with the Cyberchimps App
	 *
	 * @var bool
	 */
	private $has_auth;

	/**
	 * The api key used for authenticated requests to the Cyberchimps App.
	 *
	 * @var string
	 */
	private $auth_key;

	/**
	 * The auth data from the db.
	 *
	 * @var array
	 */
	private $auth_data;

	/**
	 * Header arguments
	 *
	 * @var array
	 */
	private $headers = array();

	/**
	 * Request max timeout
	 *
	 * @var int
	 */
	private $timeout = 120;

	private $settings;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'wp_ajax_cyberchimps_app_start_auth', array( $this, 'ajax_auth_url' ) );
			add_action( 'wp_ajax_cyberchimps_app_store_auth', array( $this, 'store_app_auth' ) );
			add_action( 'wp_ajax_cyberchimps_app_delete_auth', array( $this, 'delete_app_auth' ) );
			add_action( 'wp_ajax_cyberchimps_app_upgrade_user_plan', array( $this, 'responsive_addons_upgrade_user_plan' ) );
			add_action( 'wp_ajax_cyberchimps_app_sync_user_plan', array( $this, 'responsive_addons_sync_user_plan' ) );
			require_once RESPONSIVE_ADDONS_DIR . 'includes/settings/class-responsive-add-ons-settings.php';
			$this->settings = new Responsive_Add_Ons_Settings();
			register_deactivation_hook( __FILE__, array( $this, 'responsive_addons_deactivate_connection' ) );
		}
	}

	/**
	 * Ajax handler that returns the auth url used to start the Connect process.
	 *
	 * @return void
	 */
	public function ajax_auth_url() {
		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You do not have permissions to connect Responsive Addons to Cyberchimps.', 'responsive-addons' ) );
		}

		$is_new_user = filter_input( INPUT_POST, 'is_new_user', FILTER_VALIDATE_BOOLEAN );

		$site_address = rawurlencode( get_site_url() );
		$rest_url     = rawurlencode( get_rest_url() );

		$api_auth_url = $is_new_user ? $this->get_api_url( 'signup' ) : $this->get_api_url( 'login' );

		$auth_url = add_query_arg(
			array(
				'platform' => 'wordpress',
				'source'   => 'connect',
				'site'     => $site_address,
				'rest_url' => $rest_url,
				'rplus_nonce' => wp_create_nonce( 'wp_rest' ),
			),
			$api_auth_url
		);
		wp_send_json_success(
			array(
				'url' => $auth_url,
			)
		);
	}

	/**
	 * Get the full URL to an API endpoint by passing the path.
	 *
	 * @param string $path The path for the API endpoint.
	 *
	 * @return string
	 */
	public function get_api_url( $path ) {
		return trailingslashit( CC_APP_URL ) . $path;
	}

	/**
	 * Get the full path to an API endpoint by passing the path.
	 *
	 * @param string $path The path for the API endpoint.
	 *
	 * @return string
	 */
	public function get_api_path( $path ) {
		return trailingslashit( self::API_BASE_PATH ) . $path;
	}

	/**
	 * Ajax handler to save the auth API key.
	 *
	 * @return void
	 */
	public function store_app_auth() {
		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You do not have permissions to connect Responsive Addons to Cyberchimps.', 'responsive-addons' ) );
		}
		$data = array();
		if ( isset( $_POST['response'] ) && is_array( $_POST['response'] ) ) {
			// Pre-sanitizing the response using a custom function to ensure all values are cleaned.
			// PHPCS incorrectly flags this as unsanitized, so the warning is suppressed.
			// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$data = $this->recursive_sanitize_text_field( wp_unslash( $_POST['response'] ) );
			// phpcs:enable
		}
		$origin = ! empty( $_POST['origin'] ) ? esc_url_raw( wp_unslash( $_POST['origin'] ) ) : false;

		if ( empty( $data ) || CC_APP_URL !== $origin ) {
			wp_send_json_error();
		}
		update_option( 'reads_app_settings', $data );
		$this->auth_data = $data;
		set_transient( 'responsive_ready_sites_display_connect_success', true, 10 );
		wp_send_json_success(
			array(
				'title' => __( 'Authentication successfully completed', 'responsive-addons' ),
				'text'  => __( 'Reloading page, please wait.', 'responsive-addons' ),
			)
		);
	}

	/**
	 * Recursively sanitize the response fields from $_POST.
	 *
	 * @return mixed
	 */
	public function recursive_sanitize_text_field( $array ) {
		foreach ( $array as $key => &$value ) {
			if ( is_array( $value ) ) {
				$value = $this->recursive_sanitize_text_field( $value );
			} else {
				$value = sanitize_text_field( wp_unslash( $value ) );
			}
		}

		return $array;
	}

	/**
	 * Ajax handler to delete the auth data and disconnect the site from the WPCode Library.
	 *
	 * @return void
	 */
	public function delete_app_auth() {
		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You do not have permissions to disconnect Responsive Addons to Cyberchimps Responsive Domain.', 'responsive-addons' ) );
		}

		$options  = $this->settings->get_defaults();
		$id       = $this->settings->get_user_id();
		$headers = array(
			'Authorization' => 'Bearer ' . $this->get_auth_key(),
			'Content-Type'  => 'application/json',
		);
		update_option( 'reads_app_settings', $options );

		global $wcam_lib_responsive_addons;
		$activation_status = get_option( $wcam_lib_responsive_addons->wc_am_activated_key );

		$args = array(
			'api_key' => ( is_array( $wcam_lib_responsive_addons->data ) && isset( $wcam_lib_responsive_addons->data[ $wcam_lib_responsive_addons->wc_am_api_key_key ] ) ) ? $wcam_lib_responsive_addons->data[ $wcam_lib_responsive_addons->wc_am_api_key_key ] : '',
		);
		
		if ( 'Activated' === $activation_status && '' !== $wcam_lib_responsive_addons->data[ $wcam_lib_responsive_addons->wc_am_api_key_key ] ) {
			// deactivates API Key activation.
			$deactivate_results= json_decode(
				json_decode($wcam_lib_responsive_addons->deactivate(
					$args,
					$this->get_api_path('plugin/disconnect'),
					$headers,
					$id
				), true)['body'],
				true
			);

			if ( isset( $deactivate_results['success'] ) && true === $deactivate_results['success'] && isset( $deactivate_results['deactivated'] ) && true === $deactivate_results['deactivated'] ) {
				if ( ! empty( $wcam_lib_responsive_addons->wc_am_activated_key ) ) {
					update_option( $wcam_lib_responsive_addons->wc_am_activated_key, 'Deactivated' );
				}

				wp_send_json_success(
					array(
						'deactivate_results' => $deactivate_results,
						'error'              => false,
						'message'            => $deactivate_results['activations_remaining'],
					)
				);
			}

			if ( isset( $deactivate_results['data']['error_code'] ) && ! empty( $wcam_lib_responsive_addons->data ) && ! empty( $wcam_lib_responsive_addons->wc_am_activated_key ) ) {
				if ( isset( $wcam_lib_responsive_addons->data[ $wcam_lib_responsive_addons->wc_am_activated_key ] ) ) {
					update_option( $wcam_lib_responsive_addons->data[ $wcam_lib_responsive_addons->wc_am_activated_key ], 'Deactivated' );
				}
				wp_send_json_error(
					array(
						'deactivate_results' => $deactivate_results,
						'error'              => true,
						'message'            => $deactivate_results['data']['error'],
					)
				);
			}
		} else {
			wp_send_json_error(
				array(
					'deactivate_results' => false,
					'error'              => true,
					'message'            => 'Connection Already Deactivated',
				)
			);
		}
	}

	/**
	 * Check if the site is authenticated.
	 *
	 * @return bool
	 */
	public function has_auth() {
		$auth_key       = $this->get_auth_key();
		$this->has_auth = ! empty( $auth_key );
		return $this->has_auth;
	}

	/**
	 * The auth key.
	 *
	 * @return bool|string
	 */
	public function get_auth_key() {
		$data           = $this->get_auth_data();
		$this->auth_key = isset( $data['api']['token'] ) ? $data['api']['token'] : false;
		return $this->auth_key;
	}

	/**
	 * Get the auth data from the db.
	 *
	 * @return array|bool
	 */
	public function get_auth_data() {
		$this->auth_data = get_option( 'reads_app_settings', false );
		return $this->auth_data;
	}

	/**
	 * Make a POST API Call
	 *
	 * @param string $path  Endpoint route.
	 * @param array  $data  Data.
	 *
	 * @return mixed
	 */
	public function post( $path, $data = array() ) {
		try {
			return $this->request( $path, $data );
		} catch ( Exception $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Add a new request argument for GET requests
	 *
	 * @param string $name   Argument name.
	 * @param string $value  Argument value.
	 */
	public function add_header_argument( $name, $value ) {
		$this->headers[ $name ] = $value;
	}

	/**
	 * Make a authenticated request by adding
	 *
	 * @return void
	 */
	protected function make_auth_request() {
		$api_key = $this->get_auth_key();
		if ( ! empty( $api_key ) ) {
			$this->add_header_argument( 'Authorization', 'Bearer ' . $api_key );
			$this->add_header_argument( 'Content-Type', 'application/json' );
		}
	}

	/**
	 * Make an API Request
	 *
	 * @param string $path    Path.
	 * @param array  $data    Arguments array.
	 * @param string $method  Method.
	 *
	 * @return array|mixed|object
	 */
	public function request( $path, $data = array(), $method = 'post' ) {
		$url = $this->get_api_path( $path );

		$this->make_auth_request();

		$args = array(
			'headers' => $this->headers,
			'method'  => strtoupper( $method ),
			'timeout' => $this->timeout,
			'body'    => $data,
		);

		$response = wp_safe_remote_post( $url, $args );

		return $response;
	}

	/**
	 * Ajax handler that returns the auth url used to start the Connect process.
	 *
	 * @return void
	 */
	public function responsive_addons_upgrade_user_plan() {
		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You do not have permission.', 'responsive-addons' ) );
		}

		$site_address = rawurlencode( get_site_url() );
		$rest_url     = rawurlencode( get_rest_url() );

		$api_auth_url = $this->get_api_url( 'pricing' );

		$auth_url = add_query_arg(
			array(
				'platform'    => 'wordpress',
				'site'        => $site_address,
				'rest_url'    => $rest_url,
				'rplus_nonce' => wp_create_nonce( 'wp_rest' ),
			),
			$api_auth_url
		);
		wp_send_json_success(
			array(
				'url' => $auth_url,
			)
		);
	}

	/**
	 * Syncs the user's plan details with the external service and updates local settings if necessary.
	 *
	 * Checks the AJAX nonce for security and verifies the user's permissions.
	 * If valid, retrieves the current user plan from an external API and compares it with local settings.
	 * If the plan differs, updates the local settings with the new plan details.
	 *
	 * @return void
	 */
	public function responsive_addons_sync_user_plan() {
		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You do not have permission.', 'responsive-addons' ) );
		}

		require_once RESPONSIVE_ADDONS_DIR . 'includes/settings/class-responsive-add-ons-settings.php';
		$settings = new Responsive_Add_Ons_Settings();

		$response      = wp_safe_remote_post(
			self::API_BASE_PATH . 'plugin/getuserplan',
			array(
				'method'  => 'POST',
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'user_id' => $settings->get_user_id(),
					)
				),
			)
		);
		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			wp_send_json_error(
				array(
					'message'  => 'Cannot made request with Cyberchimps Responsive Domain. Some data is missing.',
					'error'    => true,
					'code'     => $response_code,
					'response' => $response,
				),
				400
			);
		}
		$response_body = json_decode( wp_remote_retrieve_body( $response ) );
		$settings      = get_option( 'reads_app_settings' );
		$message       = array(
			'message' => 'No App Settings Found',
			'status'  => 404,
		);
		if ( empty( $settings ) ) {
			wp_send_json_error( $message );
		}
		if ( ( $settings['account']['plan'] !== $response_body->user_plan->plan ) && ( $settings['account']['product_id'] !== $response_body->user_plan->product_id ) ) {
			$settings['account']['plan']       = $response_body->user_plan->plan;
			$settings['account']['product_id'] = $response_body->user_plan->product_id;
			$message                           = array(
				'message' => 'Plan Updated',
				'status'  => 200,
			);
			update_option( 'reads_app_settings', $settings );
			update_option( 'resp_plan_updated', 'Your plan details are updated.' );
			set_transient( 'resp_app_last_sync', 'yes', DAY_IN_SECONDS );
			wp_send_json_success( $message );
		}
		$message = array(
			'message' => 'Plan Not Updated',
			'status'  => 400,
		);
		update_option( 'resp_plan_updated', 'Your plan details are updated.' );
		set_transient( 'resp_app_last_sync', 'yes', DAY_IN_SECONDS );
		wp_send_json_error( $message );
	}

	public function responsive_addons_deactivate_connection() {

		global $wcam_lib_responsive_addons;
		$id = $this->settings->get_user_id();
		$headers = array(
			'Authorization' => 'Bearer ' . $this->get_auth_key(),
			'Content-Type'  => 'application/json',
		);
		$args = array(
			'api_key' => ( is_array( $wcam_lib_responsive_addons->data ) && isset( $wcam_lib_responsive_addons->data[ $wcam_lib_responsive_addons->wc_am_api_key_key ] ) ) ? $wcam_lib_responsive_addons->data[ $wcam_lib_responsive_addons->wc_am_api_key_key ] : '',
		);
		$wcam_lib_responsive_addons->deactivate( $args, $this->get_api_path('plugin/disconnect'), $headers, $id );
	}
}