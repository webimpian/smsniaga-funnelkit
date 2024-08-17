<?php

final class BWFAN_SmsNiaga_Integration extends BWFAN_Integration {
	private static $ins = null;
	protected $connector_slug = 'bwfco_smsniaga';
	protected $need_connector = true;

	private function __construct() {
		$this->action_dir = __DIR__;
		$this->nice_name  = __( 'SmsNiaga', 'autonami-automations-connectors' );
		$this->group_name = __( 'Messaging', 'autonami-automations-connectors' );
		$this->group_slug = 'messaging';
		$this->priority   = 55;

		add_filter( 'bwfan_sms_services', array( $this, 'add_as_sms_service' ), 10, 1 );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	protected function do_after_action_registration( BWFAN_Action $action_object ) {
		$action_object->connector = $this->connector_slug;
	}

	/**
	 * Add this integration to SMS services list.
	 *
	 * @param $sms_services
	 *
	 * @return array
	 */
	public function add_as_sms_service( $sms_services ) {
		$slug = $this->get_connector_slug();
		if ( BWFAN_Core()->connectors->is_connected( $slug ) ) {
			$integration                  = $slug;
			$sms_services[ $integration ] = $this->nice_name;
		}

		return $sms_services;
	}

	/** All SMS Providers must expose this function as API to send message */
	public function send_message( $args ) {
		$args = wp_parse_args( $args, array(
			'to'        => '',
			'body'      => '',
			'image_url' => '',
		) );

		$to   = $args['to'];
		$body = $args['body'];

		if ( empty( $to ) || empty( $body ) ) {
			return new WP_Error( 400, 'Data missing to send SmsNiaga SMS' );
		}


		WFCO_Common::get_connectors_data();
		$settings     = WFCO_Common::$connectors_saved_data[ $this->get_connector_slug() ];
		$api_token    = $settings['api_token'];
		$account_type = $settings['account_type'];

		if ( empty( $api_token ) || empty( $account_type ) ) {
			return new WP_Error( 404, 'Invalid / Missing saved connector data' );
		}

		if ( isset( $args['is_test'] ) && ! empty( $args['is_test'] ) ) {
			/** @var set property progress of action to true to decode the url by available shortner service */
			$sms_niaga_ins = BWFAN_SmsNiaga_Send_Sms::get_instance();
			$sms_niaga_ins->set_progress( true );
		}

		$call_args = array(
			'api_token'    => $api_token,
			'account_type' => $account_type,
			'text'         => $body,
			'number'       => $to,
		);

		$load_connectors = WFCO_Load_Connectors::get_instance();
		$call            = $load_connectors->get_call( 'wfco_smsniaga_send_sms' );

		$call->set_data( $call_args );

		return $this->validate_send_message_response( $call->process() );
	}

	public function validate_send_message_response( $response ) {
		if ( is_array( $response ) && ( ( 200 === absint( $response['response'] ) ) || ( isset( $response['body']['status_code'] ) && 200 === absint( $response['body']['status_code'] ) ) ) ) {
			return true;
		}

		$message = __( 'SMS could not be sent. ', 'autonami-automations-connectors' );

		if ( is_array( $response ) && ( ( 200 === absint( $response['response'] ) ) && ( isset( $response['body']['status_code'] ) && 400 === absint( $response['body']['status_code'] ) ) ) ) {
			$message = $response['body']['error'];
		} elseif ( isset( $response['bwfan_response'] ) && ! empty( $response['bwfan_response'] ) ) {
			$message = $response['bwfan_response'];
		}

		return new WP_Error( 500, $message );
	}
}

/**
 * Register this class as an integration.
 */
BWFAN_Load_Integrations::register( 'BWFAN_SmsNiaga_Integration' );
