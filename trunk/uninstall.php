<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * TODO consider the following flow of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://BrianHenry.ie
 * @since      1.0.0
 *
 * @package    EA_WP_AWS_SNS_Client_REST_Endpoint
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Require this file for constants.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-ea-wp-aws-sns-client-rest-endpoint.php';

delete_option( EA_WP_AWS_SNS_Client_REST_Endpoint::PENDING_SUBSCRIPTIONS_OPTION_KEY );

wp_clear_scheduled_hook( EA_WP_AWS_SNS_Client_REST_Endpoint::BACKGROUND_NOTIFY_CRON_ACTION );

