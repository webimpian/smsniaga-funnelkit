<?php

abstract class WFCO_SmsNiaga_Call extends WFCO_Call {

	/**
	 * Checks the required fields for every action & check the validity of Access Token
	 *
	 * @param $data
	 * @param $required_fields
	 *
	 * @return bool
	 */
	public function check_fields( $data, $required_fields ) {
		$check_required_fields = parent::check_fields( $data, $required_fields );

		if ( false === $check_required_fields ) {
			return false;
		}

		if ( isset( $data['connector_initialising'] ) && true === $data['connector_initialising'] ) {
			return true;
		}

		return true;
	}

}
