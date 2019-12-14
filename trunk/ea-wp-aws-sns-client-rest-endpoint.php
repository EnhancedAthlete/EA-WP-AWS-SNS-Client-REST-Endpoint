<?php
/**
 * A WordPress plugin providing a REST API endpoint to receive messages from
 *  Amazon Web Services Simple Notification Service for other plugins to consume.
 *
 * @link              https://BrianHenry.ie
 * @since             1.0.0
 * @package           EA_WP_AWS_SNS_Client_REST_Endpoint
 *
 * @wordpress-plugin
 * Plugin Name:       EA WP AWS SNS – Client REST Endpoint
 * Plugin URI:        https://github.com/EnhancedAthlete/ea-wp-aws-sns-client-rest-endpoint
 * Description:       Receives messages from Amazon Web Services Simple Notification Service for other plugins.
 * Version:           2.0.0
 * Author:            BrianHenryIE
 * Author URI:        https://BrianHenry.ie
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ea-wp-aws-sns-client-rest-endpoint
 * Domain Path:       /languages
 */

namespace EA_WP_AWS_SNS_Client_REST_Endpoint;

// If this file is called directly, abort.
use EA_WP_AWS_SNS_Client_REST_Endpoint\cron\Cron;
use EA_WP_AWS_SNS_Client_REST_Endpoint\includes\EA_WP_AWS_SNS_Client_REST_Endpoint;

if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once plugin_dir_path( __FILE__ ) . 'autoload.php';

// Currently plugin version.
define( 'EA_WP_AWS_SNS_CLIENT_REST_ENDPOINT_VERSION', '2.0.0' );

/**
 * The deactivation hook, which ultimately deletes any cron jobs configured.
 */
function deactivate_ea_wp_aws_sns_client_rest_endpoint() {

	Deactivator::deactivate();
}

register_deactivation_hook( __FILE__, 'deactivate_ea_wp_aws_sns_client_rest_endpoint' );



/**
 * Configure an instance of the plugin.
 *
 * @return EA_WP_AWS_SNS_Client_REST_Endpoint
 */
function instantiate_ea_wp_aws_sns_client_rest_endpoint() {

	$loader = new \WPPB_Loader();

	$ea_wp_aws_sns_client_rest_endpoint = new EA_WP_AWS_SNS_Client_REST_Endpoint( $loader );

	return $ea_wp_aws_sns_client_rest_endpoint;
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 *
 * phpcs:disable Squiz.PHP.DisallowMultipleAssignments.Found
 */
$GLOBALS['ea_wp_aws_sns_client_rest_endpoint'] = $ea_wp_aws_sns_client_rest_endpoint = instantiate_ea_wp_aws_sns_client_rest_endpoint();
$ea_wp_aws_sns_client_rest_endpoint->run();


add_action( Cron::NOTIFY_IN_BACKGROUND_JOB_NAME, array( 'EA_WP_AWS_SNS_Client_REST_Endpoint_Cron', 'notify_in_background_static' ), 10, 4 );
