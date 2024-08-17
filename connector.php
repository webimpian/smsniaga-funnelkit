<?php

class BWFCO_SmsNiaga extends BWF_CO {
	public static $headers = null;
	private static $ins = null;
	public $v2 = true;

	/** only require for oauth check  */

	public function __construct() {
		/** Connector.php initialization */
		$this->keys_to_track = [
			'api_token',
			'account_type'
		];
		$this->form_req_keys = [
			'api_token',
			'account_type'
		];

		$this->sync              = true;
		$this->connector_url     = WFCO_SMSNIAGA_PLUGIN_URL;
		$this->dir               = __DIR__;
		$this->nice_name         = __( 'SMSNIAGA', 'autonami-automations-connectors' );
		$this->autonami_int_slug = 'BWFAN_SmsNiaga_Integration';

		add_filter( 'wfco_connectors_loaded', array( $this, 'add_card' ) );
		add_action( 'wp_ajax_bwf_smsniaga_test_message', array( __CLASS__, 'test_smsniaga_message' ) );
	}

	public function get_fields_schema() {
		return array(
			array(
				'id'          => 'api_token',
				'label'       => __( 'API Token', 'wp-marketing-automations-connectors' ),
				'type'        => 'text',
				'class'       => 'bwfan_smsniaga_api_token',
				'placeholder' => __( 'API Token', 'wp-marketing-automations-connectors' ),
				'required'    => true,
				'toggler'     => array(),
			),
			array(
				'id'          => 'account_type',
				'label'       => __( "Mode", 'wp-marketing-automations-connectors' ),
				'class'       => 'bwfan_smsniaga_account_type',
				'type'        => 'radio',
				'options'     => [
					[
						'label' => __( "Live", 'wp-marketing-automations-connectors' ),
						'value' => 'live'
					],
					[
						'label' => __( "Sandbox", 'wp-marketing-automations-connectors' ),
						'value' => 'sandbox'
					]
				],
				"description" => __( "Note: Account type gives you the option to use either live mode(https://manage.smsniaga.com) or sandbox mode(https://sandbox.manage.smsniaga.com.my).", 'wp-marketing-automations-connectors' ),
			)
		);
	}


	public function get_settings_fields_values() {
		$saved_data = WFCO_Common::$connectors_saved_data;
		$old_data   = ( isset( $saved_data[ $this->get_slug() ] ) && is_array( $saved_data[ $this->get_slug() ] ) && count( $saved_data[ $this->get_slug() ] ) > 0 ) ? $saved_data[ $this->get_slug() ] : array();
		$vals       = array();
		if ( isset( $old_data['api_token'] ) ) {
			$vals['api_token'] = $old_data['api_token'];
		}

		if ( isset( $old_data['account_type'] ) ) {
			$vals['account_type'] = $old_data['account_type'];
		} else {
			$vals['account_type'] = 'live';
		}

		return $vals;
	}

	/**
	 * Get data from the API call, must required function otherwise call
	 *
	 * @param $data
	 *
	 * @return array
	 */
	protected function get_api_data( $posted_data ) {
		$load_connector = WFCO_Load_Connectors::get_instance();
		$call_class     = $load_connector->get_call( 'wfco_smsniaga_oauth_check' );

		$resp_array = array(
			'api_data' => $posted_data,
			'status'   => 'failed',
			'message'  => __( 'There was problem authenticating your account. Confirm entered details.', 'autonami-automations-connectors' ),
		);

		if ( is_null( $call_class ) ) {
			return $resp_array;

		}

		$data = array(
			'api_token'    => isset( $posted_data['api_token'] ) ? $posted_data['api_token'] : '',
			'account_type' => isset( $posted_data['account_type'] ) ? $posted_data['account_type'] : '',
		);

		$call_class->set_data( $data );
		$sn_status = $call_class->process();


		if ( is_array( $sn_status ) && 200 === $sn_status['response'] && ! isset( $sn_status['body']['status_code'] ) ) {
			$response                             = [];
			$response['status']                   = 'success';
			$response['api_data']['api_token']    = $posted_data['api_token'];
			$response['api_data']['account_type'] = $posted_data['account_type'];

			$sender_ids = $this->get_sender_ids( $posted_data['api_token'], $posted_data['account_type'] );
			if ( is_array( $sender_ids ) && COUNT( $sender_ids ) > 0 ) {
				$response['api_data']['sender_ids'] = $sender_ids['sender_ids'];
			}

			$groups = $this->get_groups( $posted_data['api_token'], $posted_data['account_type'] );
			if ( is_array( $groups ) && COUNT( $groups ) > 0 ) {
				$response['api_data']['groups'] = $groups['groups'];
			}

			return $response;

		} else if ( is_array( $sn_status ) && 500 === $sn_status['response'] ) {
			$resp_array['status']  = 'failed';
			$resp_array['message'] = isset( $sn_status['body'][0] ) ? $sn_status['body'][0] : __( 'Undefined Api Error', 'autonami-automations-connectors' );

			return $resp_array;
		} else {
			$resp_array['status']  = 'failed';
			$resp_array['message'] = isset( $sn_status['body']['message'] ) ? $sn_status['body']['message'] : __( 'Undefined Api Error', 'autonami-automations-connectors' );

			return $resp_array;
		}
	}


	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public static function set_headers( $api_token ) {

		$headers = array(
			'Authorization' => "Bearer " . $api_token,
			'Content-type'  => 'application/json',
		);

		self::$headers = $headers;
	}

	public static function get_headers() {
		return self::$headers;
	}

	/**
	 * @param $account_type
	 * endpoint base url
	 *
	 * @return string
	 */
	public static function get_api_endpoint( $account_type ) {
		if ( $account_type === 'live' ) {
			return "https://manage.smsniaga.com";
		} else {
			return "https://sandbox.manage.smsniaga.com.my";
		}
	}

	public function add_card( $available_connectors ) {
		$available_connectors['autonami']['connectors']['bwfco_smsniaga'] = array(
			'name'            => 'SmsNiaga',
			'desc'            => __( 'Send SMS', 'autonami-automations-connectors' ),
			'connector_class' => 'BWFCO_SmsNiaga',
			'image'           => $this->get_image(),
			'source'          => '',
			'file'            => '',
		);

		return $available_connectors;
	}

	/**
	 * Get Sender ids
	 *
	 * @param $api_token
	 *
	 * @return array
	 */
	public function get_sender_ids( $api_token, $account_type ) {
		$load_connector = WFCO_Load_Connectors::get_instance();
		$call_class     = $load_connector->get_call( 'wfco_smsniaga_get_sender_ids' );

		$resp_array = array(
			'api_data' => array( $api_token, $account_type ),
			'status'   => 'failed',
			'message'  => __( 'No such call', 'autonami-automations-connectors' ),
		);

		if ( is_null( $call_class ) ) {
			return $resp_array;

		}

		$data = array(
			'api_token'    => isset( $api_token ) ? $api_token : '',
			'account_type' => isset( $account_type ) ? $account_type : '',
		);

		$call_class->set_data( $data );
		$sender_ids_result = $call_class->process();

		$all_sender_ids = array();

		if ( is_array( $sender_ids_result ) && 200 === $sender_ids_result['response'] && count( $sender_ids_result['body']['data'] ) > 0 ) {
			$fetched_sender_ids = $sender_ids_result['body']['data'];
			$sender_ids         = array();
			foreach ( $fetched_sender_ids as $sender_id ) {
				$sender_ids[ $sender_id['uuid'] ] = $sender_id['name'];
			}

			$all_sender_ids['sender_ids'] = $sender_ids;
		}

		return $all_sender_ids;
	}

	/**
	 * Get Groups
	 *
	 * @param $api_token
	 *
	 * @return array
	 */
	public function get_groups( $api_token, $account_type ) {
		$load_connector = WFCO_Load_Connectors::get_instance();
		$call_class     = $load_connector->get_call( 'wfco_smsniaga_get_groups' );

		$resp_array = array(
			'api_data' => array( $api_token, $account_type ),
			'status'   => 'failed',
			'message'  => __( 'No such call', 'autonami-automations-connectors' ),
		);

		if ( is_null( $call_class ) ) {
			return $resp_array;

		}

		$data = array(
			'api_token'    => isset( $api_token ) ? $api_token : '',
			'account_type' => isset( $account_type ) ? $account_type : '',
		);

		$call_class->set_data( $data );
		$groups_result = $call_class->process();

		$all_groups = array();

		if ( is_array( $groups_result ) && 200 === $groups_result['response'] && COUNT( $groups_result['body']['data'] ) > 0 ) {
			$fetched_groups = $groups_result['body']['data'];
			$groups         = array();
			foreach ( $fetched_groups as $group ) {
				$groups[ $group['uuid'] ] = $group['name'];
			}

			$all_groups['groups'] = $groups;
		}

		return $all_groups;
	}

	/**
	 * sending test message
	 */
	public static function test_smsniaga_message() {

		BWFAN_Common::check_nonce();
		// phpcs:disable WordPress.Security.NonceVerification
		$result = array(
			'status' => false,
			'msg'    => __( 'Error', 'wp-marketing-automations' ),
		);
		if ( ! isset( $_POST['data']['sms_to'] ) ) {
			$result['msg'] = __( 'Phone number can\'t be blank', 'wp-marketing-automations' );
			wp_send_json( $result );
		}

		$post = $_POST;

		$sms_body = isset( $_POST['data']['sms_body_textarea'] ) ? sanitize_text_field( $_POST['data']['sms_body_textarea'] ) : '';

		$post['data']['number'] = $post['data']['sms_to'];
		$post['data']['body']   = $sms_body;

		$post['event_data']['event_slug'] = $post['event'];

		// is_preview set to true for merge tag before sending data for sms;
		BWFAN_Merge_Tag_Loader::set_data( array(
			'is_preview' => true,
		) );

		$action_object       = BWFAN_Core()->integration->get_action( 'smsniaga_send_sms' );
		$data_to_set         = $action_object->make_data( '', $post );
		$data_to_set['test'] = true;

		/** @var  $global_settings */
		$global_settings = WFCO_Common::$connectors_saved_data;
		if ( ! array_key_exists( 'bwfco_smsniaga', $global_settings ) ) {
			wp_send_json( array(
				'msg'    => __( 'SmsNiaga is not connected', 'wp-marketing-automations' ),
				'status' => false,
			) );
		}

		$smsniaga_settings = $global_settings['bwfco_smsniaga'];

		$load_connector = WFCO_Load_Connectors::get_instance();
		$call_class     = $load_connector->get_call( 'wfco_smsniaga_send_sms' );

		$data_to_set['api_token']    = $smsniaga_settings['api_token'];
		$data_to_set['account_type'] = $smsniaga_settings['account_type'];
		$call_class->set_data( $data_to_set );
		$response = $call_class->process();
		if ( is_array( $response ) && ( ( 200 === absint( $response['response'] ) ) && ( isset( $response['body']['status_code'] ) && 200 === absint( $response['body']['status_code'] ) ) ) ) {

			wp_send_json( array(
				'status' => true,
				'msg'    => __( 'Message sent successfully.', 'wp-marketing-automations' ),
			) );
		}

		$message = __( 'Message could not be sent. ', 'autonami-automations-connectors' );
		$status  = 4;

		if ( is_array( $response ) && ( ( 200 === absint( $response['response'] ) ) && ( isset( $response['body']['status_code'] ) && 400 === absint( $response['body']['status_code'] ) ) ) ) {
			$message = $response['body']['message'];
		} elseif ( isset( $response['bwfan_response'] ) && ! empty( $response['bwfan_response'] ) ) {
			$message = $response['bwfan_response'];
		} elseif ( isset( $response['body']['message'] ) && ! empty( $response['body']['message'] ) ) {
			$message = $response['body']['message'];
		}

		wp_send_json( array(
			'status' => $status,
			'msg'    => $message,
		) );
	}

}

WFCO_Load_Connectors::register( 'BWFCO_SmsNiaga' );
