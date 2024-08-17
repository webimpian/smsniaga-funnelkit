<?php

class WFCO_SmsNiaga_Get_Sender_Ids extends WFCO_Call {
	private static $instance = null;
	private $api_end_point = null;

	public function __construct() {
		$this->required_fields = array( 'api_token', 'account_type' );
	}

	/**
	 * @return WFCO_SmsNiaga_Get_Sender_Ids|null
	 */

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Process and do the actual processing for the current action.
	 * This function is present in every action class.
	 */
	public function process() {
		$is_required_fields_present = $this->check_fields( $this->data, $this->required_fields );
		if ( false === $is_required_fields_present ) {
			return $this->show_fields_error();
		}

		BWFCO_SmsNiaga::set_headers( $this->data['api_token'] );

		/**
		 * creating the api endpoint
		 */
		$this->api_end_point = BWFCO_SmsNiaga::get_api_endpoint( $this->data['account_type'] ) . '/api/sender_identifiers';

		$res = $this->make_wp_requests( $this->api_end_point, array(), BWFCO_SmsNiaga::get_headers(), BWF_CO::$GET );

		return $res;
	}

}

return 'WFCO_SmsNiaga_Get_Sender_Ids';
