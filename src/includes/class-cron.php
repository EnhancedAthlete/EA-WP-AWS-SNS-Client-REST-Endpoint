<?php
/**
 * The cron-specific functionality of the plugin.
 * Allows responding to the AWS HTTP call instantly, and passing
 * the notification data (via action) to its consuming plugin without
 * worrying about timeouts.
 *
 * @link       https://BrianHenry.ie
 * @since      1.0.0
 *
 * @package    EA_WP_AWS_SNS_Client_REST_Endpoint
 * @subpackage EA_WP_AWS_SNS_Client_REST_Endpoint/admin
 */

namespace EA_WP_AWS_SNS_Client_REST_Endpoint\includes;

use EA_WP_AWS_SNS_Client_REST_Endpoint\WPPB\WPPB_Object;

/**
 * The cron-specific functionality of the plugin.
 *
 * Exists solely to fire events in in the background so responses to AWS will be timely.
 *
 * @package    EA_WP_AWS_SNS_Client_REST_Endpoint
 * @subpackage EA_WP_AWS_SNS_Client_REST_Endpoint/cron
 * @author     Brian Henry <BrianHenryIE@gmail.com>
 */
class Cron extends WPPB_Object {

	const NOTIFY_IN_BACKGROUND_JOB_NAME = 'ea_wp_aws_sns_client_rest_endpoint_notify_in_background';

	/**
	 * This method is hooked to an action so WordPress's cron system can be used to process the notification
	 * in the background.
	 * It fires the action `ea_aws_sns_notification` with the notification data and other plugins are expected
	 * to listen for this action.
	 *
	 * @param string $topic_arn The AWS SNS topic Amazon Resource Name.
	 * @param array  $headers   HTTP headers received.
	 * @param object $body      JSON decoded HTTP body received from SNS.
	 * @param object $message   JSON decoded $body->message.
	 */
	public function notify_in_background( $topic_arn, $headers, $body, $message ) {

		$handled = array();

		apply_filters( EA_WP_AWS_SNS_Client_REST_Endpoint::NEW_NOTIFICATION_ACTION, $handled, $topic_arn, $headers, $body, $message );
	}

}
