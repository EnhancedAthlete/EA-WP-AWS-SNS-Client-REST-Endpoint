<?php
/**
 * The REST API functionality of the plugin.
 *
 * Adds a wp-json/ea/v1/aws-sns REST endpoint.
 *
 * @link       https://BrianHenry.ie
 * @since      1.0.0
 *
 * @package    EA_WP_AWS_SNS_Client_REST_Endpoint
 * @subpackage EA_WP_AWS_SNS_Client_REST_Endpoint/rest
 */

/**
 * The REST API functionality of the plugin.
 *
 * Defines the REST endpoint and handles the various types of requests that may come from AWS SNS
 *
 * @package    EA_WP_AWS_SNS_Client_REST_Endpoint
 * @subpackage EA_WP_AWS_SNS_Client_REST_Endpoint/rest
 * @author     Brian Henry <BrianHenryIE@gmail.com>
 */
class EA_WP_AWS_SNS_Client_REST_Endpoint_REST extends WPPB_Object {

	/**
	 * Defines the REST endpoint itself. Added on WordPress `rest_api_init` action.
	 */
	public function rest_api_init() {

		// TODO: add description so it explains itself with HEAD/GET /wp-json/.
		register_rest_route(
			'ea/v1',
			'/aws-sns/',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'process_new_aws_sns_notification' ),
			)
		);
	}

	/**
	 * Parse the REST request for the SNS notification.
	 *
	 * @param WP_REST_Request $request The HTTP request received at our REST endpoint.
	 *
	 * @return bool
	 */
	public function process_new_aws_sns_notification( WP_REST_Request $request ) {

		$headers = $request->get_headers();

		// If this is not an AWS SNS message.
		if ( ! isset( $headers['x_amz_sns_message_type'] ) || ! isset( $headers['x_amz_sns_topic_arn'] ) ) {

			// TODO: Add log context.
			do_action( 'ea_log_notice', $this->plugin_name, $this->version, 'Non AWS SNS message received.' );

			return false;
		}

		$body = json_decode( $request->get_body() );

		do_action( 'ea_log_info', $this->plugin_name, $this->version, $headers['x_amz_sns_topic_arn'][0] . ' ' . $headers['x_amz_sns_message_type'][0] . ' received.' );

		/**
		 * The possible message type values are SubscriptionConfirmation, Notification, and UnsubscribeConfirmation.
		 *
		 * @see https://docs.aws.amazon.com/sns/latest/dg/sns-message-and-json-formats.html
		 */
		$message_type = $headers['x_amz_sns_message_type'][0];

		switch ( $message_type ) {
			case 'SubscriptionConfirmation':
				$this->handle_subscription_confirmation_request( $headers, $body );

				return true;

			case 'UnsubscribeConfirmation':
				$this->handle_unsubscribe_confirmation( $headers, $body );

				break;
			case 'Notification':
				$this->handle_notification( $headers, $body );

				break;
			default:
				do_action( 'ea_log_notice', $this->plugin_name, $this->version, 'Unexpected message type received: ' . $message_type );

				return false;
		}

		return true;
	}

	/**
	 * Stores the subscription request in WordPress options for later display in the admin UI.
	 *
	 * @param array  $headers  The HTTP headers received from AWS SNS.
	 * @param object $body     The parsed JSON received from AWS SNS.
	 */
	public function handle_subscription_confirmation_request( $headers, $body ) {

		$pending_subscriptions_option_key = EA_WP_AWS_SNS_Client_REST_Endpoint::PENDING_SUBSCRIPTIONS_OPTION_KEY;

		$pending_subscriptions = get_option( $pending_subscriptions_option_key, array() );

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$subscription_confirmation = array(
			'message_id'    => $body->MessageId,
			'topic_arn'     => $body->TopicArn,
			'subscribe_url' => $body->SubscribeURL,
			'timestamp'     => $body->Timestamp,
		);

		$pending_subscriptions[ $body->TopicArn ] = $subscription_confirmation;

		update_option( $pending_subscriptions_option_key, $pending_subscriptions );
	}

	/**
	 * Unimplemented. Store the unsubscribed ARN information to later present/communicate to admins.
	 *
	 * @param array  $headers  The HTTP headers received from AWS SNS.
	 * @param object $body     The parsed JSON received from AWS SNS.
	 */
	private function handle_unsubscribe_confirmation( $headers, $body ) {

		// TODO: Store.
	}

	/**
	 * Enqueue a cron'd notification for other plugins to catch and process.
	 * Cron'd so HTTP success response can be sent back without a timeout that could be incurred by other plugins'
	 * long processing times.
	 *
	 * @see https://docs.aws.amazon.com/sns/latest/dg/sns-message-and-json-formats.html#http-notification-json
	 *
	 * @param array  $headers  The HTTP headers received from AWS SNS.
	 * @param object $body     The parsed JSON received from AWS SNS.
	 */
	public function handle_notification( $headers, $body ) {

		$topic_arn = $body->TopicArn;
		$message   = json_decode( $body->Message );

		$args = array(
			$topic_arn,
			$headers,
			$body,
			$message,
		);

		wp_schedule_single_event( time(), EA_WP_AWS_SNS_Client_REST_Endpoint::BACKGROUND_NOTIFY_CRON_ACTION, $args );

	}
}
