<?php

class WFCO_SmsNiaga_Common
{

	private static $instance = null;

	public static function get_instance()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get Api Token if present, otherwise return empty string
	 *
	 * @return string
	 */
	public static function get_api_token()
	{
		$data = self::get_smsniaga_settings();

		return isset($data['api_token']) && !empty($data['api_token']) ? $data['api_token'] : '';
	}

	/**
	 * Get SmsNiaga Saved Settings
	 *
	 * @return array
	 */
	public static function get_smsniaga_settings()
	{
		if (false === WFCO_Common::$saved_data) {
			WFCO_Common::get_connectors_data();
		}
		$data = WFCO_Common::$connectors_saved_data;
		$slug = self::get_connector_slug();
		$data = (isset($data[$slug]) && is_array($data[$slug])) ? $data[$slug] : array();

		return $data;
	}

	public static function get_connector_slug()
	{
		return sanitize_title(BWFCO_SmsNiaga::class);
	}

	public static function update_settings($settings = array())
	{
		if (empty($settings)) {
			return false;
		}

		$old_settings = self::get_smsniaga_settings();
		$settings     = array_merge($old_settings, $settings);

		$active_connectors = WFCO_Load_Connectors::get_active_connectors();
		/** @var BWF_CO $connector_ins */
		$connector_ins = $active_connectors[self::get_connector_slug()];
		$response      = $connector_ins->handle_settings_form($settings, 'update');

		return is_array($response) && $response['status'] === 'success' ? true : false;
	}


	/**
	 * Get Sender ids
	 *
	 * @return array
	 */
	public static function get_smsniaga_sender_ids_setting()
	{
		$data = self::get_smsniaga_settings();
		//by default FK will stored initial data into cache or persistance storage
		//so we just bypass during any refresh event into direct api call
		//for this case within this plugin there is get groups and sender ids
		$sender_ids_from_api_call = BWFCO_SmsNiaga::get_sender_ids($data['api_token'], $data['account_type']);
		return (isset($sender_ids_from_api_call['sender_ids']) && !empty($sender_ids_from_api_call['sender_ids'])) ? $sender_ids_from_api_call['sender_ids'] : '';
	}

	/**
	 * Get Groups
	 *
	 * @return array
	 */
	public static function get_smsniaga_groups_setting()
	{
		$data = self::get_smsniaga_settings();
		//bypass persistent data stored and call directly from API
		$groups_from_api_call = BWFCO_SmsNiaga::get_groups($data['api_token'], $data['account_type']);
		return (isset($groups_from_api_call['groups']) && !empty($groups_from_api_call['groups'])) ? $groups_from_api_call['groups'] : '';
	}
}

WFCO_SmsNiaga_Common::get_instance();
