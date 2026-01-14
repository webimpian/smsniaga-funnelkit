<?php

class WFCO_SmsNiaga_Common
{
	/**
	 * Singleton instance
	 *
	 * @var WFCO_SmsNiaga_Common|null
	 */
	private static $instance = null;

	/**
	 * Enable/disable debug logging
	 *
	 * @var bool
	 */
	private static $debug_enabled = null;

	/**
	 * Get singleton instance
	 *
	 * @return WFCO_SmsNiaga_Common
	 */
	public static function get_instance()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Check if debug logging is enabled
	 *
	 * @return bool
	 */
	public static function is_debug_enabled()
	{
		if (null === self::$debug_enabled) {
			self::$debug_enabled = defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG;
		}

		return self::$debug_enabled;
	}

	/**
	 * Log an error message
	 *
	 * @param string $message The error message to log
	 * @param array  $context Additional context data
	 * @param string $level   Log level (error, warning, info, debug)
	 *
	 * @return void
	 */
	public static function log($message, $context = array(), $level = 'error')
	{
		if (!self::is_debug_enabled()) {
			return;
		}

		$log_message = sprintf(
			'[SmsNiaga %s] %s',
			strtoupper($level),
			$message
		);

		if (!empty($context)) {
			$log_message .= ' | Context: ' . wp_json_encode($context);
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log($log_message);
	}

	/**
	 * Log an API error
	 *
	 * @param string $endpoint The API endpoint that failed
	 * @param mixed  $response The API response
	 * @param array  $request  The request data (sensitive data should be redacted)
	 *
	 * @return void
	 */
	public static function log_api_error($endpoint, $response, $request = array())
	{
		if (isset($request['api_token'])) {
			$request['api_token'] = '***REDACTED***';
		}

		$context = array(
			'endpoint' => $endpoint,
			'response' => $response,
			'request'  => $request,
		);

		self::log('API request failed', $context, 'error');
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
	 */
	public static function get_smsniaga_sender_ids_setting()
	{
		$data = self::get_smsniaga_settings();

		if ( empty( $data['api_token'] ) || empty( $data['account_type'] ) ) {
			return ( isset( $data['sender_ids'] ) && ! empty( $data['sender_ids'] ) ) ? $data['sender_ids'] : '';
		}

		$connector = BWFCO_SmsNiaga::get_instance();
		$sender_ids_from_api = $connector->get_sender_ids( $data['api_token'], $data['account_type'] );

		if ( isset( $sender_ids_from_api['sender_ids'] ) && ! empty( $sender_ids_from_api['sender_ids'] ) ) {
			return $sender_ids_from_api['sender_ids'];
		}

		return ( isset( $data['sender_ids'] ) && ! empty( $data['sender_ids'] ) ) ? $data['sender_ids'] : '';
	}

	/**
	 * Get Groups
	 */
	public static function get_smsniaga_groups_setting()
	{
		$data = self::get_smsniaga_settings();

		if ( empty( $data['api_token'] ) || empty( $data['account_type'] ) ) {
			return ( isset( $data['groups'] ) && ! empty( $data['groups'] ) ) ? $data['groups'] : '';
		}

		$connector = BWFCO_SmsNiaga::get_instance();
		$groups_from_api = $connector->get_groups( $data['api_token'], $data['account_type'] );

		if ( isset( $groups_from_api['groups'] ) && ! empty( $groups_from_api['groups'] ) ) {
			return $groups_from_api['groups'];
		}

		return ( isset( $data['groups'] ) && ! empty( $data['groups'] ) ) ? $data['groups'] : '';
	}
}

WFCO_SmsNiaga_Common::get_instance();
