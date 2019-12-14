<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://BrianHenry.ie
 * @since      1.0.0
 *
 * @package    EA_WP_AWS_SNS_Client_REST_Endpoint
 * @subpackage EA_WP_AWS_SNS_Client_REST_Endpoint/includes
 */

namespace EA_WP_AWS_SNS_Client_REST_Endpoint\includes;

/**
 * Fired during plugin deactivation.
 *
 * Cancels any background tasks scheduled. Does not delete pending subscription data. Does not cancel subscriptions.
 *
 * @since      1.0.0
 * @package    EA_WP_AWS_SNS_Client_REST_Endpoint
 * @subpackage EA_WP_AWS_SNS_Client_REST_Endpoint/includes
 * @author     Brian Henry <BrianHenryIE@gmail.com>
 */
class Deactivator {

	/**
	 * Cancel scheduled crons (probably unnecessary).
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		wp_clear_scheduled_hook( EA_WP_AWS_SNS_Client_REST_Endpoint::BACKGROUND_NOTIFY_CRON_ACTION );
	}
}
