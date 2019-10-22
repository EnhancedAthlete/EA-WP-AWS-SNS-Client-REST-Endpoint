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
class EA_WP_AWS_SNS_Client_REST_Endpoint {

	/**
	 * The WordPress Plugin Boilerplate loader that's responsible for maintaining and
	 * registering all hooks that power the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var     WPPB_Loader_Interface    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	const PENDING_SUBSCRIPTIONS_OPTION_KEY = 'ea-wp-aws-sns-client-rest-endpoint-pending-subscriptions';

	const BACKGROUND_NOTIFY_CRON_ACTION = 'ea_wp_aws_sns_client_rest_endpoint_notify_in_background';

	const NEW_NOTIFICATION_ACTION = 'ea_aws_sns_notification';

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
			$this->version = EA_WP_AWS_SNS_CLIENT_REST_ENDPOINT_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'ea-wp-aws-sns-client-rest-endpoint';

		$this->load_dependencies();

		$this->loader = $loader;

		$this->define_admin_hooks();
		$this->define_ajax_hooks();

		$this->define_rest_hooks();
		$this->define_cron_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ea-wp-aws-sns-client-rest-endpoint-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the rest area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'ajax/class-ea-wp-aws-sns-client-rest-endpoint-ajax.php';

		/**
		 * The class responsible for defining all actions that occur in the rest area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'rest/class-ea-wp-aws-sns-client-rest-endpoint-rest.php';

		/**
		 * The class responsible for defining all actions that occur in the cron area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'cron/class-ea-wp-aws-sns-client-rest-endpoint-cron.php';
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new EA_WP_AWS_SNS_Client_REST_Endpoint_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_notices', $plugin_admin, 'admin_notices' );

		$this->loader->add_filter( 'plugin_action_links', $plugin_admin, 'plugin_action_links', 20, 2 );
		$this->loader->add_filter( 'plugin_row_meta', $plugin_admin, 'plugin_row_meta', 20, 4 );

		// TODO: $this->loader->add_action( 'init?', $plugin_admin, 'process non AJAX confirmation' );
	}

	/**
	 * Register all of the hooks related to the ajax functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_ajax_hooks() {

		$plugin_ajax = new EA_WP_AWS_SNS_Client_REST_Endpoint_Ajax( $this->get_plugin_name(), $this->get_version() );

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

		$plugin_rest = new EA_WP_AWS_SNS_Client_REST_Endpoint_REST( $this->get_plugin_name(), $this->get_version() );

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

		$plugin_cron = new EA_WP_AWS_SNS_Client_REST_Endpoint_Cron( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'ea_wp_aws_sns_client_rest_endpoint_notify_in_background', $plugin_cron, 'notify_in_background', 10, 4 );
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
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    \WPPB_Loader_Interface    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
