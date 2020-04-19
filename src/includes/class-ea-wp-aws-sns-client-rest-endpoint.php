<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://BrianHenry.ie
 * @since      1.0.0
 *
 * @package    EA_WP_AWS_SNS_Client_REST_Endpoint
 * @subpackage EA_WP_AWS_SNS_Client_REST_Endpoint/includes
 */

namespace EA_WP_AWS_SNS_Client_REST_Endpoint\includes;

use EA_WP_AWS_SNS_Client_REST_Endpoint\admin\Admin;
use EA_WP_AWS_SNS_Client_REST_Endpoint\admin\Ajax;
use EA_WP_AWS_SNS_Client_REST_Endpoint\admin\Plugins_Page;
use EA_WP_AWS_SNS_Client_REST_Endpoint\rest\REST;
use EA_WP_AWS_SNS_Client_REST_Endpoint\WPPB\WPPB_Loader_Interface;
use EA_WP_AWS_SNS_Client_REST_Endpoint\WPPB\WPPB_Object;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    EA_WP_AWS_SNS_Client_REST_Endpoint
 * @subpackage EA_WP_AWS_SNS_Client_REST_Endpoint/includes
 * @author     Brian Henry <BrianHenryIE@gmail.com>
 */
class EA_WP_AWS_SNS_Client_REST_Endpoint extends WPPB_Object {

	/**
	 * The WordPress Plugin Boilerplate loader that's responsible for maintaining and
	 * registering all hooks that power the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var     WPPB_Loader_Interface    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	const PENDING_SUBSCRIPTIONS_OPTION_KEY = 'ea-wp-aws-sns-client-rest-endpoint-pending-subscriptions';

	const BACKGROUND_NOTIFY_CRON_ACTION = 'ea_wp_aws_sns_client_rest_endpoint_notify_in_background';

	const NEW_NOTIFICATION_ACTION = 'ea_aws_sns_notification';

	/**
	 * The Cron object so other plugins can access it for unhooking.
	 *
	 * @var Cron
	 */
	public $cron;
	/**
	 * @var Admin
	 */
	public $admin;
	/**
	 * @var Plugins_Page
	 */
	public $plugins_page;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 *
	 * @param WPPB_Loader_Interface $loader The WordPress Plugin Boilerplate loader object.
	 */
	public function __construct( $loader ) {
		if ( defined( 'EA_WP_AWS_SNS_CLIENT_REST_ENDPOINT_VERSION' ) ) {
			$version = EA_WP_AWS_SNS_CLIENT_REST_ENDPOINT_VERSION;
		} else {
			$version = '2.0.1';
		}
		$plugin_name = 'ea-wp-aws-sns-client-rest-endpoint';

		parent::__construct( $plugin_name, $version );

		$this->loader = $loader;

		$this->define_admin_hooks();
		$this->define_rest_hooks();
		$this->define_cron_hooks();

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$this->admin = new Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $this->admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_notices', $this->admin, 'admin_notices' );

		$this->plugins_page = new Plugins_Page( $this->get_plugin_name(), $this->get_version() );
		
		$this->loader->add_filter( 'plugin_action_links', $this->plugins_page, 'plugin_action_links', 20, 2 );
		$this->loader->add_filter( 'plugin_row_meta', $this->plugins_page, 'plugin_row_meta', 20, 4 );
		$this->loader->add_filter( 'all_plugins', $this->plugins_page, 'add_rest_url_to_description', 20, 1 );

		
		$plugin_ajax = new Ajax( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_ajax_ea_aws_sns_confirm_subscription', $plugin_ajax, 'ajax_confirm_subscription' );
		$this->loader->add_action( 'wp_ajax_ea_aws_sns_dismiss_subscription', $plugin_ajax, 'ajax_dismiss_subscription' );
	}

	/**
	 * Register all of the hooks related to the REST functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_rest_hooks() {

		$plugin_rest = new REST( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'rest_api_init', $plugin_rest, 'rest_api_init' );
	}

	/**
	 * Register all of the hooks related to the cron functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_cron_hooks() {

		$this->cron = new Cron( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( Cron::NOTIFY_IN_BACKGROUND_JOB_NAME, $this->cron, 'notify_in_background', 10, 4 );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    WPPB_Loader_Interface    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

}
