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
 * Version:           1.0.0
 * Author:            Brian Henry
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
define( 'EA_WP_AWS_SNS_CLIENT_REST_ENDPOINT_VERSION', '1.0.0' );


function deactivate_ea_wp_aws_sns_client_rest_endpoint() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ea-wp-aws-sns-client-rest-endpoint-deactivator.php';
	EA_WP_AWS_SNS_Client_REST_Endpoint_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ea_wp_aws_sns_client_rest_endpoint' );
register_deactivation_hook( __FILE__, 'deactivate_ea_wp_aws_sns_client_rest_endpoint' );

// Main plugin file. Defines below class.
require plugin_dir_path( __FILE__ ) . 'includes/class-ea-wp-aws-sns-client-rest-endpoint.php';

function run_ea_wp_aws_sns_client_rest_endpoint() {

	$plugin = new EA_WP_AWS_SNS_Client_REST_Endpoint();
	$plugin->run();

}
run_ea_wp_aws_sns_client_rest_endpoint();
