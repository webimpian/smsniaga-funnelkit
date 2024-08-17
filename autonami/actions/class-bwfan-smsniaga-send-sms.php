<?php

class BWFAN_SmsNiaga_Send_Sms extends BWFAN_Action
{
	private static $instance = null;
	private $progress = false;
	public $support_language = true;

	public function __construct()
	{
		$this->action_name = __('Send Message', 'autonami-automations-connectors');
		$this->action_desc = __('This action sends a message via SmsNiaga', 'autonami-automations-connectors');
		$this->support_v2  = true;
	}

	/**
	 * @return BWFAN_SmsNiaga_Send_Sms|null
	 */
	public static function get_instance()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function load_hooks()
	{
		add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_assets'), 98);
		add_filter('bwfan_modify_send_sms_body', array($this, 'shorten_link'), 15, 2);
	}

	public function shorten_link($body, $data)
	{ // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis
		if (true === $this->progress) {
			$body = preg_replace_callback('/((\w+:\/\/\S+)|(\w+[\.:]\w+\S+))[^\s,\.]/i', array($this, 'shorten_urls'), $body);
		}

		return preg_replace_callback('/((\w+:\/\/\S+)|(\w+[\.:]\w+\S+))[^\s,\.]/i', array($this, 'unsubscribe_url_with_mode'), $body);
	}

	protected function shorten_urls($matches)
	{
		$string = $matches[0];

		/**
		 * method exist check is required here as it is outside the connector plugin
		 * same is not required for the connector inside the connector plugin
		 */
		if (method_exists('BWFAN_Connectors_Common', 'get_shorten_url')) {
			return BWFAN_Connectors_Common::get_shorten_url($string);
		}

		return do_shortcode('[bwfan_bitly_shorten]' . $string . '[/bwfan_bitly_shorten]');
	}

	/**
	 * Localize data for html fields for the current action.
	 */
	public function admin_enqueue_assets()
	{
		if (BWFAN_Common::is_load_admin_assets('automation')) {
			$sender_id_data = $this->get_sender_ids_view_data();
			$groups_data    = $this->get_groups_view_data();

			BWFAN_Core()->admin->set_actions_js_data($this->get_class_slug(), 'sender_ids', $sender_id_data);
			BWFAN_Core()->admin->set_actions_js_data($this->get_class_slug(), 'groups', $groups_data);
		}
	}

	/*
	 * get the sender ids for view
	 *
	 * @return array
	 */
	public function get_sender_ids_view_data()
	{
		$sender_id_result = WFCO_SmsNiaga_Common::get_smsniaga_sender_ids_setting();

		return $sender_id_result;
	}

	/*
	 * get the groups for view
	 *
	 * @return array
	 */
	public function get_groups_view_data()
	{
		$groups_result = WFCO_SmsNiaga_Common::get_smsniaga_groups_setting();

		return $groups_result;
	}

	/**
	 * Show the html fields for the current action.
	 */
	public function get_view()
	{
		$unique_slug = $this->get_slug();
?>

		<script type="text/html" id="tmpl-action-<?php echo esc_attr__($unique_slug); ?>">
			<# sms_body='' ; phone_merge_tag='{{customer_phone}}' ; sms_body='' ; sms_to=(_.has(data.actionSavedData, 'data' ) && _.has(data.actionSavedData.data, 'sms_to' )) ? _.isEmpty(data.actionSavedData.data.sms_to)?phone_merge_tag:data.actionSavedData.data.sms_to: phone_merge_tag; sms_body_textarea=(_.has(data.actionSavedData, 'data' ) && _.has(data.actionSavedData.data, 'sms_body_textarea' )) ? data.actionSavedData.data.sms_body_textarea : sms_body; selected_sender_id=(_.has(data.actionSavedData, 'data' ) && _.has(data.actionSavedData.data, 'sender_id' )) ? data.actionSavedData.data.sender_id : '' ; selected_group=(_.has(data.actionSavedData, 'data' ) && _.has(data.actionSavedData.data, 'group' )) ? data.actionSavedData.data.group : '' ; bwfan_sms_select=(_.has(data.actionSavedData, 'data' ) && _.has(data.actionSavedData.data, 'bwfan_sms_select' )) ? data.actionSavedData.data.bwfan_sms_select : 'text' ; sms_is_promotional=(_.has(data.actionSavedData, 'data' ) && _.has(data.actionSavedData.data, 'promotional_sms' )) ? 'checked' : '' ; sms_is_append_utm=(_.has(data.actionSavedData, 'data' ) && _.has(data.actionSavedData.data, 'sms_append_utm' )) ? 'checked' : '' ; sms_show_utm_parameters=(_.has(data.actionSavedData, 'data' ) && _.has(data.actionSavedData.data, 'sms_append_utm' )) ? '' : 'bwfan-display-none' ; sms_entered_utm_source=(_.has(data.actionSavedData, 'data' ) && _.has(data.actionSavedData.data, 'sms_utm_source' )) ? data.actionSavedData.data.sms_utm_source : '' ; sms_entered_utm_medium=(_.has(data.actionSavedData, 'data' ) && _.has(data.actionSavedData.data, 'sms_utm_medium' )) ? data.actionSavedData.data.sms_utm_medium : '' ; sms_entered_utm_campaign=(_.has(data.actionSavedData, 'data' ) && _.has(data.actionSavedData.data, 'sms_utm_campaign' )) ? data.actionSavedData.data.sms_utm_campaign : '' ; sms_entered_utm_term=(_.has(data.actionSavedData, 'data' ) && _.has(data.actionSavedData.data, 'sms_utm_term' )) ? data.actionSavedData.data.sms_utm_term : '' ; #>
				<div data-element-type="bwfan-editor" class="bwfan-<?php echo esc_attr__($unique_slug); ?>">
					<label for="" class="bwfan-label-title">
						<?php
						echo esc_html__('To', 'autonami-automations-connectors');
						echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput
						?>
					</label>
					<div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan-mb-15">
						<input required type="text" class="bwfan-input-wrapper bwfan-field-<?php echo esc_attr__($unique_slug); ?>" name="bwfan[{{data.action_id}}][data][sms_to]" placeholder="E.g. 9999999999" value="{{sms_to}}" />
					</div>

					<label for="" class="bwfan-label-title">
						<?php
						echo esc_html__('Text', 'autonami-automations-connectors');
						?>
					</label>
					<div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan-mb-15">
						<textarea class="bwfan-input-wrapper" id="bwfan-sms-textarea" placeholder="<?php echo esc_attr__('Message Body', 'autonami-automations-connectors'); ?>" name="bwfan[{{data.action_id}}][data][sms_body_textarea]" style="{{bwfan_sms_select =='text'?'display:block;':'display:none;'}}">{{sms_body_textarea}}</textarea>
					</div>

					<label for="" class="bwfan-label-title">
						<?php
						echo esc_html__('Select Sender Id', 'autonami-automations-connectors');
						$message = __('Select Sender id if any or leave empty', 'autonami-automations-connectors');
						echo $this->add_description($message, '2xl', 'top'); //phpcs:ignore WordPress.Security.EscapeOutput
						echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput
						?>
					</label>
					<select id="" class="bwfan-input-wrapper bwfan-single-select" name="bwfan[{{data.action_id}}][data][sender_id]" id="sender-id-dropdown">
						<option value=""><?php echo esc_html__('Select Sender Id', 'autonami-automations-connectors'); ?></option>
						<# if(_.has(data.actionFieldsOptions, 'sender_ids' ) && _.isObject(data.actionFieldsOptions.sender_ids) ) { sender_ids=data.actionFieldsOptions.sender_ids; #>
							<# _.each( sender_ids, function( value, key ){ selected=(value==selected_sender_id) ? 'selected' : '' ; #>
								<option value="{{value}}" {{selected}}> {{value}}</option>
								<# }) #>
									<# } #>
					</select>

					<label for="" class="bwfan-label-title">
						<?php
						echo esc_html__('Select Group', 'autonami-automations-connectors');
						$message = __('Select Group if any or leave empty', 'autonami-automations-connectors');
						echo $this->add_description($message, '2xl', 'right'); //phpcs:ignore WordPress.Security.EscapeOutput
						echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput
						?>
					</label>
					<select id="" class="bwfan-input-wrapper bwfan-single-select" name="bwfan[{{data.action_id}}][data][group]" id="group-dropdown">
						<option value=""><?php echo esc_html__('Select Group', 'autonami-automations-connectors'); ?></option>
						<# if(_.has(data.actionFieldsOptions, 'groups' ) && _.isObject(data.actionFieldsOptions.groups) ) { groups=data.actionFieldsOptions.groups; #>
							<# _.each( groups, function( value, key ){ selected=(value==selected_group) ? 'selected' : '' ; #>
								<option value="{{value}}" {{selected}}> {{value}}</option>
								<# }) #>
									<# } #>
					</select>

					<div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan-mb-15">
						<label for="" class="bwfan-label-title"><?php esc_html_e('Send Test Message', 'autonami-automations-connectors'); ?></label>
						<div class="bwfan_smsniaga_send_test_message">
							<input type="text" name="test_message" id="bwfan_smsniaga_test_message">
							<input type="button" class="button bwfan-btn-inner" id="bwfan_smsniaga_test_message_btn" value="<?php esc_html_e('Send', 'autonami-automations-connectors'); ?>">
						</div>
						<div class="clearfix bwfan_field_desc">
							<?php esc_html_e('Enter Mobile no with country code', 'wp-marketing-automations'); ?>
						</div>
					</div>

					<div class="bwfan_sms_tracking bwfan-mb-15">
						<label for="bwfan_promotional_sms" class="bwfan-label-title-normal">
							<input type="checkbox" name="bwfan[{{data.action_id}}][data][promotional_sms]" id="bwfan_promotional_sms" value="1" {{sms_is_promotional}} />
							<?php
							echo esc_html__('Mark as Promotional', 'autonami-automations-connectors');
							$message = __('SMS marked as promotional will not be send to the unsubscribers.', 'autonami-automations-connectors');
							echo $this->add_description($message, 'xl'); //phpcs:ignore WordPress.Security.EscapeOutput
							?>
						</label>
						<label for="bwfan_append_utm" class="bwfan-label-title-normal">
							<input type="checkbox" name="bwfan[{{data.action_id}}][data][sms_append_utm]" id="bwfan_append_utm" value="1" {{sms_is_append_utm}} />
							<?php
							echo esc_html__('Add UTM parameters to the links', 'autonami-automations-connectors');
							$message = __('Add UTM parameters in all the links present in the sms.', 'autonami-automations-connectors');
							echo $this->add_description($message, 'xl'); //phpcs:ignore WordPress.Security.EscapeOutput
							?>
						</label>
						<div class="bwfan_utm_sources {{sms_show_utm_parameters}}">
							<div class="bwfan-input-form clearfix">
								<div class="bwfan-col-sm-4 bwfan-pl-0"><span class="bwfan_label_input"><?php echo esc_html__('UTM Source', 'autonami-automations-connectors'); ?></span></div>
								<div class="bwfan-col-sm-8 bwfan-pr-0">
									<input type="text" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][sms_utm_source]" value="{{sms_entered_utm_source}}" />
								</div>
							</div>
							<div class="bwfan-input-form clearfix">
								<div class="bwfan-col-sm-4 bwfan-pl-0"><span class="bwfan_label_input"><?php echo esc_html__('UTM Medium', 'autonami-automations-connectors'); ?></span></div>
								<div class="bwfan-col-sm-8 bwfan-pr-0">
									<input type="text" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][sms_utm_medium]" value="{{sms_entered_utm_medium}}" />
								</div>
							</div>
							<div class="bwfan-input-form clearfix">
								<div class="bwfan-col-sm-4 bwfan-pl-0"><span class="bwfan_label_input"><?php echo esc_html__('UTM Campaign', 'autonami-automations-connectors'); ?></span></div>
								<div class="bwfan-col-sm-8 bwfan-pr-0">
									<input type="text" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][sms_utm_campaign]" value="{{sms_entered_utm_campaign}}" />
								</div>
							</div>
							<div class="bwfan-input-form clearfix">
								<div class="bwfan-col-sm-4 bwfan-pl-0"><span class="bwfan_label_input"><?php echo esc_html__('UTM Term', 'autonami-automations-connectors'); ?></span></div>
								<div class="bwfan-col-sm-8 bwfan-pr-0">
									<input type="text" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][sms_utm_term]" value="{{sms_entered_utm_term}}" />
								</div>
							</div>
						</div>
					</div>
				</div>
		</script>
		<script>
			jQuery(document).on('click', '#bwfan_smsniaga_test_message_btn', function() {
				var smsInputElem = jQuery('#bwfan_smsniaga_test_message');
				var el = jQuery(this);
				el.prop('disabled', true);
				smsInputElem.prop('disabled', true);
				var sms = smsInputElem.val();
				var form_data = jQuery('#bwfan-actions-form-container').bwfan_serializeAndEncode();
				form_data = bwfan_deserialize_obj(form_data);
				var group_id = jQuery('.bwfan-selected-action').attr('data-group-id');
				var data_to_send = form_data.bwfan[group_id];
				data_to_send.source = BWFAN_Auto.uiDataDetail.trigger.source;
				data_to_send.event = BWFAN_Auto.uiDataDetail.trigger.event;
				data_to_send._wpnonce = bwfanParams.ajax_nonce;
				data_to_send.automation_id = bwfan_automation_data.automation_id;
				data_to_send.data['sms_to'] = sms;
				var ajax = new bwf_ajax();
				ajax.ajax('smsniaga_test_message', data_to_send);

				ajax.success = function(resp) {
					el.prop('disabled', false);
					smsInputElem.prop('disabled', false);

					if (resp.status == true) {
						var $iziWrap = jQuery("#modal_automation_success");

						if ($iziWrap.length > 0) {
							$iziWrap.iziModal('setTitle', resp.msg);
							$iziWrap.iziModal('open');
						}
					} else {
						swal({
							type: 'error',
							title: window.bwfan.texts.sync_oops_title,
							text: resp.msg
						});
					}
				};
			});
		</script>
<?php
	}

	/**
	 * Make all the data which is required by the current action.
	 * This data will be used while executing the task of this action.
	 *
	 * @param $integration_object
	 * @param $task_meta
	 *
	 * @return array|void
	 */
	public function make_data($integration_object, $task_meta)
	{
		$this->add_action();
		$this->progress = true;
		$sms_body       = isset($task_meta['data']['sms_body_textarea']) ? $task_meta['data']['sms_body_textarea'] : '';

		$data_to_set = array(
			'name'            => BWFAN_Common::decode_merge_tags('{{customer_first_name}}'),
			'promotional_sms' => (isset($task_meta['data']['promotional_sms'])) ? 1 : 0,
			'append_utm'      => (isset($task_meta['data']['sms_append_utm'])) ? 1 : 0,
			'number'          => (isset($task_meta['data']['sms_to'])) ? BWFAN_Common::decode_merge_tags($task_meta['data']['sms_to']) : '',
			'event'           => (isset($task_meta['event_data']) && isset($task_meta['event_data']['event_slug'])) ? $task_meta['event_data']['event_slug'] : '',
			'text'            => BWFAN_Common::decode_merge_tags($sms_body),
			'sender_id'       => (isset($task_meta['data']['sender_id'])) ? $task_meta['data']['sender_id'] : '',
			'group'           => (isset($task_meta['data']['group'])) ? $task_meta['data']['group'] : '',
		);
		if (isset($task_meta['data']['sms_utm_source']) && !empty($task_meta['data']['sms_utm_source'])) {
			$data_to_set['utm_source'] = BWFAN_Common::decode_merge_tags($task_meta['data']['sms_utm_source']);
		}
		if (isset($task_meta['data']['sms_utm_medium']) && !empty($task_meta['data']['sms_utm_medium'])) {
			$data_to_set['utm_medium'] = BWFAN_Common::decode_merge_tags($task_meta['data']['sms_utm_medium']);
		}
		if (isset($task_meta['data']['sms_utm_campaign']) && !empty($task_meta['data']['sms_utm_campaign'])) {
			$data_to_set['utm_campaign'] = BWFAN_Common::decode_merge_tags($task_meta['data']['sms_utm_campaign']);
		}
		if (isset($task_meta['data']['sms_utm_term']) && !empty($task_meta['data']['sms_utm_term'])) {
			$data_to_set['utm_term'] = BWFAN_Common::decode_merge_tags($task_meta['data']['sms_utm_term']);
		}

		if (isset($task_meta['global']) && isset($task_meta['global']['order_id'])) {
			$data_to_set['order_id'] = $task_meta['global']['order_id'];
		} elseif (isset($task_meta['global']) && isset($task_meta['global']['cart_abandoned_id'])) {
			$data_to_set['cart_abandoned_id'] = $task_meta['global']['cart_abandoned_id'];
		}

		/** If promotional checkbox is not checked, then empty the {{unsubscribe_link}} merge tag */
		if (isset($data_to_set['promotional_sms']) && 0 === absint($data_to_set['promotional_sms'])) {
			$data_to_set['text'] = str_replace('{{unsubscribe_link}}', '', $data_to_set['text']);
		}

		$data_to_set['text'] = stripslashes($data_to_set['text']);

		$this->remove_action();

		return $data_to_set;
	}

	/**
	 * making data for v2
	 *
	 * @param $automation_data
	 * @param $step_data
	 *
	 * @return array
	 */
	public function make_v2_data($automation_data, $step_data)
	{
		$this->add_action();
		$this->progress = true;
		$sms_body       = isset($step_data['sms_body_textarea']) ? $step_data['sms_body_textarea'] : '';

		$data_to_set = array(
			'name'            => BWFAN_Common::decode_merge_tags('{{customer_first_name}}'),
			'promotional_sms' => (isset($step_data['promotional_sms'])) ? 1 : 0,
			'append_utm'      => (isset($step_data['sms_append_utm'])) ? 1 : 0,
			'number'          => (isset($step_data['sms_to'])) ? BWFAN_Common::decode_merge_tags($step_data['sms_to']) : '',
			'phone'           => (isset($step_data['sms_to'])) ? BWFAN_Common::decode_merge_tags($step_data['sms_to']) : '',
			'event'           => (isset($step_data['event_data']) && isset($step_data['event_data']['event_slug'])) ? $step_data['event_data']['event_slug'] : '',
			'text'            => BWFAN_Common::decode_merge_tags($sms_body),
			'sender_id'       => (isset($step_data['sender_id'])) ? $step_data['sender_id'] : '',
			'group'           => (isset($step_data['group'])) ? $step_data['group'] : '',
			'step_id'         => isset($automation_data['step_id']) ? $automation_data['step_id'] : '',
			'automation_id'   => isset($automation_data['automation_id']) ? $automation_data['automation_id'] : '',
		);

		$data_to_set['api_token']    = isset($step_data['connector_data']['api_token']) ? $step_data['connector_data']['api_token'] : '';
		$data_to_set['account_type'] = isset($step_data['connector_data']['account_type']) ? $step_data['connector_data']['account_type'] : '';

		if (isset($step_data['sms_utm_source']) && !empty($step_data['sms_utm_source'])) {
			$data_to_set['utm_source'] = BWFAN_Common::decode_merge_tags($step_data['sms_utm_source']);
		}

		if (isset($step_data['sms_utm_medium']) && !empty($step_data['sms_utm_medium'])) {
			$data_to_set['utm_medium'] = BWFAN_Common::decode_merge_tags($step_data['sms_utm_medium']);
		}
		if (isset($step_data['sms_utm_campaign']) && !empty($step_data['sms_utm_campaign'])) {
			$data_to_set['utm_campaign'] = BWFAN_Common::decode_merge_tags($step_data['sms_utm_campaign']);
		}
		if (isset($step_data['sms_utm_term']) && !empty($step_data['sms_utm_term'])) {
			$data_to_set['utm_term'] = BWFAN_Common::decode_merge_tags($step_data['sms_utm_term']);
		}

		if (isset($automation_data['global']) && isset($automation_data['global']['order_id'])) {
			$data_to_set['order_id'] = $automation_data['global']['order_id'];
		} elseif (isset($automation_data['global']) && isset($automation_data['global']['cart_abandoned_id'])) {
			$data_to_set['cart_abandoned_id'] = $automation_data['global']['cart_abandoned_id'];
		}

		/** If promotional checkbox is not checked, then empty the {{unsubscribe_link}} merge tag */
		if (isset($data_to_set['promotional_sms']) && 0 === absint($data_to_set['promotional_sms'])) {
			$data_to_set['text'] = str_replace('{{unsubscribe_link}}', '', $data_to_set['text']);
		}

		$data_to_set['text'] = stripslashes($data_to_set['text']);

		/** Append UTM and Create Conversation (Engagement Tracking) */
		$data_to_set['text'] = BWFAN_Connectors_Common::modify_sms_body($data_to_set['text'], $data_to_set);

		$this->remove_action();

		return $data_to_set;
	}

	private function add_action()
	{
		add_filter('bwfan_order_billing_address_separator', array($this, 'change_br_to_slash_n'));
		add_filter('bwfan_order_shipping_address_separator', array($this, 'change_br_to_slash_n'));
	}

	private function remove_action()
	{
		remove_filter('bwfan_order_billing_address_params', array($this, 'change_br_to_slash_n'));
		remove_filter('bwfan_order_shipping_address_separator', array($this, 'change_br_to_slash_n'));
	}

	/**
	 * Execute the current action.
	 * Return 3 for successful execution , 4 for permanent failure.
	 *
	 * @param $action_data
	 *
	 * @return array
	 */
	public function execute_action($action_data)
	{
		global $wpdb;
		$this->set_data($action_data['processed_data']);
		$this->data['task_id'] = $action_data['task_id'];

		/** Attaching track id */
		$sql_query         = 'Select meta_value FROM {table_name} WHERE bwfan_task_id = %d AND meta_key = %s';
		$sql_query         = $wpdb->prepare($sql_query, $this->data['task_id'], 't_track_id'); //phpcs:ignore WordPress.DB.PreparedSQL
		$gids              = BWFAN_Model_Taskmeta::get_results($sql_query);
		$this->data['gid'] = '';
		if (!empty($gids) && is_array($gids)) {
			foreach ($gids as $gid) {
				$this->data['gid'] = $gid['meta_value'];
			}
		}

		/** Validating promotional sms */
		if (1 === absint($this->data['promotional_sms']) && (false === apply_filters('bwfan_force_promotional_sms', false, $this->data))) {
			$where             = array(
				'recipient' => $this->data['number'],
				'mode'      => 2,
			);
			$check_unsubscribe = BWFAN_Model_Message_Unsubscribe::get_message_unsubscribe_row($where);

			if (!empty($check_unsubscribe)) {
				$this->progress = false;

				return array(
					'status'  => 4,
					'message' => __('User is already unsubscribed', 'autonami-automations-connectors'),
				);
			}
		}

		/** Append UTM and Create Conversation (Engagement Tracking) */
		$this->data['text'] = BWFAN_Connectors_Common::modify_sms_body($this->data['text'], $this->data);

		/** Validating connector */
		$load_connector = WFCO_Load_Connectors::get_instance();
		$call_class     = $load_connector->get_call('wfco_smsniaga_send_sms');
		if (is_null($call_class)) {
			$this->progress = false;

			return array(
				'status'  => 4,
				'message' => __('Send sms call not found', 'autonami-automations-connectors'),
			);
		}

		$integration                = BWFAN_SmsNiaga_Integration::get_instance();
		$this->data['api_token']    = $integration->get_settings('api_token');
		$this->data['account_type'] = $integration->get_settings('account_type');
		$this->data['sender_id']    = $this->data['sender_id'];
		$this->data['group']        = $this->data['group'];

		$call_class->set_data($this->data);
		$response = $call_class->process();
		do_action('bwfan_sendsms_action_response', $response, $this->data);

		if (is_array($response) && ((200 === absint($response['response'])) && (isset($response['body']['status_code']) && 200 === absint($response['body']['status_code'])))) {
			$this->progress = false;

			return array(
				'status'  => 3,
				'message' => __('Message sent successfully.', 'autonami-automations-connectors'),
			);
		}

		$message = __('Message could not be sent. ', 'autonami-automations-connectors');
		$status  = 4;

		if (is_array($response) && ((200 === absint($response['response'])) && (isset($response['body']['status_code']) && 400 === absint($response['body']['status_code'])))) {
			$message = $response['body']['message'];
		} elseif (isset($response['bwfan_response']) && !empty($response['bwfan_response'])) {
			$message = $response['bwfan_response'];
		} elseif (isset($response['body']['message']) && !empty($response['body']['message'])) {
			$message = $response['body']['message'];
		}

		$this->progress = false;

		return array(
			'status'  => $status,
			'message' => $message,
		);
	}

	public function handle_response_v2($response)
	{
		do_action('bwfan_sendsms_action_response', $response, $this->data);
		if (is_array($response) && ((200 === absint($response['response'])) && (isset($response['body']['status_code']) && 200 === absint($response['body']['status_code'])))) {
			$this->progress = false;

			return $this->success_message(__('SMS sent successfully.', 'autonami-automations-connectors'));
		}

		$message = __('SMS could not be sent. ', 'autonami-automations-connectors');
		if (isset($response['body']['error'])) {
			$message = $response['body']['error'];
		} elseif (isset($response['body']['message'])) {
			$message = $response['body']['message'];
		} elseif (isset($response['bwfan_response']) && !empty($response['bwfan_response'])) {
			$message = $response['bwfan_response'];
		} elseif (is_array($response['body']) && isset($response['body'][0]) && is_string($response['body'][0])) {
			$message = $message . $response['body'][0];
		} elseif (isset($response['message']) && !empty($response['message'])) {
			$message = $response['message'];
		}
		$this->progress = false;

		return $this->skipped_response($message);
	}

	public function add_unsubscribe_query_args($link)
	{
		if (empty($this->data)) {
			return $link;
		}
		if (isset($this->data['number'])) {
			$link = add_query_arg(array(
				'subscriber_recipient' => $this->data['number'],
			), $link);
		}
		if (isset($this->data['name'])) {
			$link = add_query_arg(array(
				'subscriber_name' => $this->data['name'],
			), $link);
		}

		return $link;
	}

	public function skip_name_email($flag)
	{ // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis
		return true;
	}

	public function before_executing_task()
	{
		add_filter('bwfan_change_tasks_retry_limit', array($this, 'modify_retry_limit'), 99);
		add_filter('bwfan_unsubscribe_link', array($this, 'add_unsubscribe_query_args'));
		add_filter('bwfan_skip_name_email_from_unsubscribe_link', array($this, 'skip_name_email'));
	}

	public function after_executing_task()
	{
		remove_filter('bwfan_change_tasks_retry_limit', array($this, 'modify_retry_limit'), 99);
		remove_filter('bwfan_unsubscribe_link', array($this, 'add_unsubscribe_query_args'));
		remove_filter('bwfan_skip_name_email_from_unsubscribe_link', array($this, 'skip_name_email'));
	}

	public function modify_retry_limit($retry_data)
	{
		$retry_data[] = DAY_IN_SECONDS;

		return $retry_data;
	}

	public function change_br_to_slash_n($params)
	{ // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis
		return "\n";
	}

	/**
	 * while braodcasting, set progress true then revert it to false after broadcasting done
	 *
	 * @param $progress
	 */
	public function set_progress($progress)
	{
		$this->progress = $progress;
	}

	/**
	 * adding mode in unsubscribe link
	 *
	 * @param $matches
	 *
	 * @return string
	 */
	protected function unsubscribe_url_with_mode($matches)
	{
		$string = $matches[0];

		/** if its a unsubscriber link then pass the mode in url */
		if (strpos($string, 'unsubscribe') !== false) {
			$string = add_query_arg(array(
				'mode' => 2,
			), $string);
		}

		return $string;
	}

	/**
	 * v2 Method: Get field Schema
	 *
	 * @return array[]
	 */
	public function get_fields_schema()
	{
		$default_sender_id = [
			'' => 'Select Sender Id'
		];
		$sender_ids        = array_replace($default_sender_id, $this->get_sender_ids_view_data());
		$sender_ids        = BWFAN_PRO_Common::prepared_field_options($sender_ids);

		$default_group = [
			'' => 'Select Group'
		];
		$groups        = array_replace($default_group, $this->get_groups_view_data());
		$groups        = BWFAN_PRO_Common::prepared_field_options($groups);

		$mapped_sender_ids = [];

		foreach ($sender_ids as $sender_id) {
			if ($sender_id['label'] === 'Select Sender Id') {
				$mapped_sender_ids[] = [
					'label' => $sender_id['label'],
					'value' => "",
				];
			} else {
				$mapped_sender_ids[] = [
					'label' => $sender_id['label'],
					'value' => $sender_id['label'],
				];
			}
		}

		return [
			[
				'id'          => 'sms_to',
				'label'       => __("To", 'wp-marketing-automations'),
				'type'        => 'text',
				'placeholder' => "",
				"class"       => 'bwfan-input-wrapper',
				'tip'         => __('', 'autonami-automations-connectors'),
				"description" => '',
				"required"    => true,
			],
			[
				'id'          => 'sms_body_textarea',
				'label'       => __("Text", 'wp-marketing-automations'),
				'type'        => 'textarea',
				'placeholder' => "Message Body",
				"class"       => 'bwfan-input-wrapper',
				'tip'         => __('', 'autonami-automations-connectors'),
				"description" => '',
				"required"    => true,
			],
			[
				'id'          => 'sender_id',
				'label'       => __("Select Sender Id", 'wp-marketing-automations'),
				'type'        => 'select',
				'options'     => $mapped_sender_ids,
				'placeholder' => "Choose a Sender Id",
				"class"       => 'bwfan-input-wrapper',
				'tip'         => __('Select Sender id if any or leave empty', 'autonami-automations-connectors'),
				"description" => '',
				"required"    => false,
			],
			[
				'id'          => 'test_sms',
				'label'       => __("Send Test Message", 'wp-marketing-automations'),
				'type'        => 'text',
				'placeholder' => "",
				"class"       => 'bwfan-input-wrapper',
				'tip'         => '',
				"description" => __('Enter Mobile no with country code', 'autonami-automations-connectors'),
				"required"    => false,
			],
			[
				'id'          => 'send_test_sms',
				'type'        => 'send_data',
				'label'       => __('', 'wp-marketing-automations'),
				'send_action' => 'bwf_test_sms',
				'send_field'  => [
					'test_sms_to' => 'test_sms',
					'sms_body'    => 'sms_body_textarea',
				],
				"hint"        => __("", 'wp-marketing-automations')
			],
			[
				'id'            => 'promotional_sms',
				'checkboxlabel' => __("Mark as Promotional", 'wp-marketing-automations'),
				'type'          => 'checkbox',
				"class"         => '',
				'hint'          => __('SMS marked as promotional will not be send to the unsubscribers.', 'wp-marketing-automations'),
				'description'   => __('SMS marked as promotional will not be send to the unsubscribers.', 'autonami-automations-connectors'),
				"required"      => false,
			],
			[
				'id'            => 'bwfan_bg_add_utm_params',
				'checkboxlabel' => __(" Add UTM parameters to the links", 'wp-marketing-automations'),
				'type'          => 'checkbox',
				"class"         => '',
				'hint'          => 'Add UTM parameters in all the links present in the sms.',
				'description'   => __('Add UTM parameters in all the links present in the sms.', 'autonami-automations-connectors'),
				"required"      => false,
			],
			[
				'id'          => 'sms_utm_source',
				'label'       => __("UTM Source", 'wp-marketing-automations'),
				'type'        => 'text',
				'placeholder' => "",
				"class"       => 'bwfan-input-wrapper',
				'tip'         => '',
				"description" => __('', 'autonami-automations-connectors'),
				"required"    => false,
				'toggler'     => array(
					'fields'   => array(
						array(
							'id'    => 'bwfan_bg_add_utm_params',
							'value' => true,
						),
					),
					'relation' => 'AND',
				),
			],
			[
				'id'          => 'sms_utm_medium',
				'label'       => __("UTM Medium", 'wp-marketing-automations'),
				'type'        => 'text',
				'placeholder' => "",
				"class"       => 'bwfan-input-wrapper',
				'tip'         => '',
				"description" => __('', 'autonami-automations-connectors'),
				"required"    => false,
				'toggler'     => array(
					'fields'   => array(
						array(
							'id'    => 'bwfan_bg_add_utm_params',
							'value' => true,
						),
					),
					'relation' => 'AND',
				),
			],
			[
				'id'          => 'sms_utm_campaign',
				'label'       => __("UTM Campaign", 'wp-marketing-automations'),
				'type'        => 'text',
				'placeholder' => "",
				"class"       => 'bwfan-input-wrapper',
				'tip'         => '',
				"description" => __('', 'autonami-automations-connectors'),
				"required"    => false,
				'toggler'     => array(
					'fields'   => array(
						array(
							'id'    => 'bwfan_bg_add_utm_params',
							'value' => true,
						),
					),
					'relation' => 'AND',
				),
			],
			[
				'id'          => 'utm_utm_term',
				'label'       => __("UTM Term", 'wp-marketing-automations'),
				'type'        => 'text',
				'placeholder' => "",
				"class"       => 'bwfan-input-wrapper',
				'tip'         => '',
				"description" => __('', 'autonami-automations-connectors'),
				"required"    => false,
				'toggler'     => array(
					'fields'   => array(
						array(
							'id'    => 'bwfan_bg_add_utm_params',
							'value' => true,
						),
					),
					'relation' => 'AND',
				),
			],
		];
	}
}

return 'BWFAN_SmsNiaga_Send_Sms';
