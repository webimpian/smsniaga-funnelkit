<?php

/**
 * Plugin Name: Autonami Marketing Automations Connectors - SmsNiaga
 * Plugin URI: https://buildwoofunnels.com
 * Description: Now create SmsNiaga CRM based automations with Autonami Marketing Automations for WordPress
 * Version: 1.2.3
 * Author: WooFunnels
 * Author URI: https://buildwoofunnels.com
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: autonami-automations-connectors
 *
 * Requires at least: 5.0.0
 * Tested up to: 6.0.1
 */

if (!defined('ABSPATH')) {
    exit;
}

final class WFCO_SmsNiaga
{
    /**
     * @var WFCO_SmsNiaga
     */

    public static $_instance = null;

    private function __construct()
    {
        /**
         * Load important variables and constants
         */
        $this->define_plugin_properties();

        /**
         * Loads common file
         */
        $this->load_commons();
    }

    /**
     * Defining constants
     */
    public function define_plugin_properties()
    {
        define('WFCO_SMSNIAGA_VERSION', '1.2.0');
        define('WFCO_SMSNIAGA_FULL_NAME', 'Autonami Marketing Automations Connectors : SmsNiaga');
        define('WFCO_SMSNIAGA_PLUGIN_FILE', __FILE__);
        define('WFCO_SMSNIAGA_PLUGIN_DIR', __DIR__);
        define('WFCO_SMSNIAGA_PLUGIN_URL', untrailingslashit(plugin_dir_url(WFCO_SMSNIAGA_PLUGIN_FILE)));
        define('WFCO_SMSNIAGA_PLUGIN_BASENAME', plugin_basename(__FILE__));
        define('WFCO_SMSNIAGA_MAIN', 'autonami-automations-connectors');
        define('WFCO_SMSNIAGA_ENCODE', sha1(WFCO_SMSNIAGA_PLUGIN_BASENAME));
    }

    /**
     * Load common hooks
     */
    public function load_commons()
    {
        $this->load_hooks();
    }

    public function load_hooks()
    {
        add_action('wfco_load_connectors', [$this, 'load_connector_classes']);
        add_action('bwfan_automations_loaded', [$this, 'load_autonami_classes']);
        add_action('bwfan_loaded', [$this, 'init_smsniaga']);
    }

    public static function get_instance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function init_smsniaga()
    {
        require WFCO_SMSNIAGA_PLUGIN_DIR . '/includes/class-wfco-smsniaga-common.php';
        require WFCO_SMSNIAGA_PLUGIN_DIR . '/includes/class-wfco-smsniaga-call.php';
    }

    /**
     * Load Connector Classes
     */
    public function load_connector_classes()
    {
        require_once WFCO_SMSNIAGA_PLUGIN_DIR . '/includes/class-wfco-smsniaga-common.php';
        require_once WFCO_SMSNIAGA_PLUGIN_DIR . '/includes/class-wfco-smsniaga-call.php';
        require_once WFCO_SMSNIAGA_PLUGIN_DIR . '/connector.php';

        do_action('wfco_smsniaga_connector_loaded', $this);
    }

    /**
     * Load Autonami Integration classes
     */
    public function load_autonami_classes()
    {
        $integration_dir = WFCO_SMSNIAGA_PLUGIN_DIR . '/autonami';
        foreach (glob($integration_dir . '/class-*.php') as $_field_filename) {
            require_once $_field_filename;
        }
        do_action('wfco_smsniaga_integrations_loaded', $this);
    }
}

if (!function_exists('WFCO_SmsNiaga_Core')) {
    /**
     * Global Common function to load all the classes
     * @return WFCO_SmsNiaga
     */

    function WFCO_SmsNiaga_Core()
    {
        //@codingStandardsIgnoreLine
        return WFCO_SmsNiaga::get_instance();
    }
}

WFCO_SmsNiaga_Core();
