<?php

/**
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

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Currently plugin version.
define( 'EA_WP_AWS_SNS_CLIENT_REST_ENDPOINT_VERSION', '2.0.0' );

function deactivate_ea_wp_aws_sns_client_rest_endpoint() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ea-wp-aws-sns-client-rest-endpoint-deactivator.php';
	EA_WP_AWS_SNS_Client_REST_Endpoint_Deactivator::deactivate();
}

register_deactivation_hook( __FILE__, 'deactivate_ea_wp_aws_sns_client_rest_endpoint' );

require_once plugin_dir_path( __FILE__ ) . 'lib/wppb/interface-wppb-loader.php';
require_once plugin_dir_path( __FILE__ ) . 'lib/wppb/class-wppb-loader.php';
require_once plugin_dir_path( __FILE__ ) . 'lib/wppb/class-wppb-object.php';

// Main plugin file. Defines below class.
require plugin_dir_path( __FILE__ ) . 'includes/class-ea-wp-aws-sns-client-rest-endpoint.php';

/**
 * Configure an instance of the plugin.
 *
 * @return EA_WP_AWS_SNS_Client_REST_Endpoint
 */
function instantiate_ea_wp_aws_sns_client_rest_endpoint() {

	$loader = new WPPB_Loader();

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
$GLOBALS['ea-wp-aws-sns-client-rest-endpoint'] = $ea_wp_aws_sns_client_rest_endpoint = instantiate_ea_wp_aws_sns_client_rest_endpoint();
$ea_wp_aws_sns_client_rest_endpoint->run();
