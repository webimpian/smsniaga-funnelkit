<?php

class WFCO_SmsNiaga_Send_Sms extends WFCO_Call
{
	private static $ins = null;
	private $api_end_point = null;

	public function __construct()
	{
		$this->required_fields = array('api_token', 'number', 'text', 'account_type');
	}

	public static function get_instance()
	{
		if (null === self::$ins) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 * Process and do the actual processing for the current action.
	 * This function is present in every action class.
	 *
	 * @return array
	 */
	public function process()
	{
		$params             = array();
		$numbers            = trim(stripslashes($this->data['number']));
		$numbers            = explode(',', $numbers);
		$this->data['text'] = apply_filters('bwfan_modify_send_sms_body', $this->data['text'], $this->data);

		BWFCO_SmsNiaga::set_headers($this->data['api_token']);

		$params["body"] = $this->data['text'];

		foreach ($numbers as $number) {
			$params["phones"] = [$number];
		}

		if (!empty($this->data['sender_id'])) {
			$params["sender_id"] = $this->data['sender_id'];
		}
		/*
		 * preview : 0|1
		 * 0 : will send the message to the receiver
		 * 1 : will not send the message but the data return will be actual, so if we are troubleshooting
		 * then we can use 1 as it will not deduct any amount from account
		 */
		$params["preview"] = 0;

		/**
		 * creating the api endpoint
		 */
		$this->api_end_point = BWFCO_SmsNiaga::get_api_endpoint($this->data['account_type']) . '/api/send';

		$res = $this->make_wp_requests($this->api_end_point, wp_json_encode($params), BWFCO_SmsNiaga::get_headers(), BWF_CO::$POST);

		return $res;
	}
}

return 'WFCO_SmsNiaga_Send_Sms';
