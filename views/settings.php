<?php
$saved_data   = WFCO_Common::$connectors_saved_data;
$old_data     = ( isset( $saved_data[ $this->get_slug() ] ) && is_array( $saved_data[ $this->get_slug() ] ) && count( $saved_data[ $this->get_slug() ] ) > 0 ) ? $saved_data[ $this->get_slug() ] : array();
$api_token    = isset( $old_data['api_token'] ) ? $old_data['api_token'] : '';
$account_type = isset( $old_data['account_type'] ) ? $old_data['account_type'] : '';
?>
<div class="wfco-smsniaga-wrap">
    <div class="wfco-form-group featured field-input">
        <label for="automation-name"><?php echo esc_html__( 'Enter API Token', 'autonami-automations-connectors' ); ?></label>
        <div class="field-wrap">
            <div class="wrapper">
                <input type="text" name="api_token" placeholder="<?php echo esc_attr__( 'Enter API Token', 'autonami-automations-connectors' ); ?>" class="form-control wfco_smsniaga_api_token" required value="<?php echo esc_attr__( $api_token ); ?>">
            </div>
        </div>
    </div>

    <div class="wfco-form-group featured field-input">
        <label for="automation-name"><?php echo esc_html__( 'Choose Mode', 'autonami-automations-connectors' ); ?></label>
        <div class="field-wrap">
            <div class="wrapper">
                <input type="radio" name="account_type" class="form-control wfco_smsniaga_account_live" value="<?php echo esc_attr__( 'live' ); ?>" <?php $account_type === 'live' ? 'checked' : '' ?>>
                <input type="radio" name="account_type" class="form-control wfco_smsniaga_account_sandbox" value="<?php echo esc_attr__( 'sandbox' ); ?>" <?php $account_type === 'sandbox' ? 'checked' : '' ?>>
            </div>
        </div>
    </div>

    <div class="wfco-form-groups wfco_form_submit">
		<?php
		if ( isset( $old_data['id'] ) && (int) $old_data['id'] > 0 ) {
			?>
            <input type="hidden" name="edit_nonce" value="<?php echo esc_attr__( wp_create_nonce( 'wfco-connector-edit' ) ); ?>"/>
            <input type="hidden" name="id" value="<?php echo esc_attr__( $old_data['id'] ); ?>"/>
            <input type="hidden" name="wfco_connector" value="<?php echo esc_attr__( $this->get_slug() ); ?>"/>
            <button class="wfco_save_btn_style wfco_connect_to_api"><?php esc_attr_e( 'Connect and Update', 'autonami-automations-connectors' ); ?></button>
		<?php } else { ?>
            <input type="hidden" name="_wpnonce" value="<?php echo esc_attr__( wp_create_nonce( 'wfco-connector' ) ); ?>">
            <input type="hidden" name="wfco_connector" value="<?php echo esc_attr__( $this->get_slug() ); ?>"/>
            <button class="wfco_save_btn_style wfco_connect_to_api"><?php esc_attr_e( 'Connect and Save', 'autonami-automations-connectors' ); ?></button>
		<?php } ?>
    </div>
    <div class="wfco_form_response" style="text-align: center;font-size: 15px;margin-top: 10px;"></div>
</div>
