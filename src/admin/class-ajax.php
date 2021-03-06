<?php
/**
 * The ajax-specific functionality of the plugin.
 *
 * @link       https://BrianHenry.ie
 * @since      1.0.0
 *
 * @package    EA_WP_AWS_SNS_Client_REST_Endpoint
 * @subpackage EA_WP_AWS_SNS_Client_REST_Endpoint/ajax
 */

namespace EA_WP_AWS_SNS_Client_REST_Endpoint\admin;

use EA_WP_AWS_SNS_Client_REST_Endpoint\includes\EA_WP_AWS_SNS_Client_REST_Endpoint;
use EA_WP_AWS_SNS_Client_REST_Endpoint\WPPB\WPPB_Object;

/**
 * The ajax-specific functionality of the plugin.
 *
 * Contains the logic to confirm and to dismiss subscription confirmation requests.
 *
 * @package    EA_WP_AWS_SNS_Client_REST_Endpoint
 * @subpackage EA_WP_AWS_SNS_Client_REST_Endpoint/ajax
 * @author     Brian Henry <BrianHenryIE@gmail.com>
 */
class Ajax extends WPPB_Object {


	/**
	 * Process the AJAX request to confirm the subscription.
	 *
	 * Added to `wp_ajax_ea_aws_sns_confirm_subscription` action.
	 */
	public function ajax_confirm_subscription() {

		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ) ) ) {
			// TODO: Return a descriptive error.
			return;
		}

		if ( ! isset( $_POST['subscription_topic'] ) ) {

			$message = 'Confirm AWS SNS topic attempted with no subscription_topic POSTed';

			do_action(
				'ea_log_notice',
				$this->plugin_name,
				$this->version,
				$message,
				array(
					'post'     => $_POST,
					'file'     => __FILE__,
					'class'    => __CLASS__,
					'function' => __FUNCTION__,
				)
			);

			// TODO: How to change the HTTP status code returned? // if error // 404 if the id doesn't exist?
			$error = array(
				'error'   => 'error',
				'message' => 'No subscription_topic POSTed.',
			);

			echo wp_json_encode( $error );

			wp_die();
		}

		$subscription_topic = filter_input( INPUT_POST, 'subscription_topic' );

		$confirmation_result = $this->confirm_subscription( $subscription_topic );

		echo wp_json_encode( $confirmation_result );

		wp_die();
	}

	/**
	 * Pings AWS to confirm the subscription. Deletes the subscription from the pending subscriptions option.
	 *
	 * @param string $subscription_topic The SNS ARN.
	 *
	 * @return array
	 */
	public function confirm_subscription( $subscription_topic ) {

		do_action(
			'ea_log_debug',
			$this->plugin_name,
			$this->version,
			"Confirming subscription for $subscription_topic.",
			array(
				'subscription_topic' => $subscription_topic,
				'file'               => __FILE__,
				'class'              => __CLASS__,
				'function'           => __FUNCTION__,
			)
		);

		$pending_subscriptions_option_key = EA_WP_AWS_SNS_Client_REST_Endpoint::PENDING_SUBSCRIPTIONS_OPTION_KEY;

		$pending_subscriptions = get_option( $pending_subscriptions_option_key, array() );

		$subscription_to_confirm = $pending_subscriptions[ $subscription_topic ];

		if ( empty( $subscription_to_confirm ) ) {

			$error_message = "$subscription_topic not found in list of pending subscriptions. Maybe already confirmed or dismissed.";

			do_action(
				'ea_log_notice',
				$this->plugin_name,
				$this->version,
				$error_message,
				array(
					'subscription_topic'    => $subscription_topic,
					'pending_subscriptions' => $pending_subscriptions,
					'file'                  => __FILE__,
					'class'                 => __CLASS__,
					'function'              => __FUNCTION__,
				)
			);

			return array(
				'error'   => 'error',
				'message' => $error_message,
			);
		}

		$confirmation_url = $subscription_to_confirm['subscribe_url'];

		$request_response = wp_remote_get( $confirmation_url );

		if ( is_wp_error( $request_response ) ) {
			/**
			 * The request_response is an error, usually when there is no response whatsoever, or a problem
			 * initiating the communication.
			 *
			 * @var \WP_Error $request_response
			 */

			$error_message = 'Error confirming subscription <b><i>' . $subscription_topic . '</i></b>: ' . $request_response->get_error_message();

			do_action(
				'ea_log_error',
				$this->plugin_name,
				$this->version,
				$error_message,
				array(
					'error_code'            => $request_response->get_error_code(),
					'error_message'         => $request_response->get_error_message(),
					'subscription_topic'    => $subscription_topic,
					'pending_subscriptions' => $pending_subscriptions,
					'file'                  => __FILE__,
					'class'                 => __CLASS__,
					'function'              => __FUNCTION__,
				)
			);

			return array(
				'error'   => $request_response->get_error_code(),
				'message' => $error_message,
			);
		}

		// If unsuccessful.
		if ( 2 !== intval( $request_response['response']['code'] / 100 ) ) {

			$xml = new \SimpleXMLElement( $request_response['body'] );

			$error_message = 'Error confirming subscription for topic <b><i>' . $subscription_topic . '</i></b>. ' . $request_response['response']['message'] . ' : ' . $xml->{'Error'}->{'Message'};

			do_action(
				'ea_log_error',
				$this->plugin_name,
				$this->version,
				$error_message,
				array(
					'error_xml'             => $request_response['body'],
					'subscription_topic'    => $subscription_topic,
					'pending_subscriptions' => $pending_subscriptions,
					'file'                  => __FILE__,
					'class'                 => __CLASS__,
					'function'              => __FUNCTION__,
				)
			);

			return array(
				'error'   => $request_response['response']['code'],
				'message' => $error_message,
			);
		}

		// When successful.
		unset( $pending_subscriptions[ $subscription_topic ] );

		update_option( $pending_subscriptions_option_key, $pending_subscriptions );

		$message = "AWS SNS topic <b><i>$subscription_topic</i></b> subscription confirmed.";

		do_action(
			'ea_log_info',
			$this->plugin_name,
			$this->version,
			$message,
			array(
				'subscription_topic' => $subscription_topic,
				'file'               => __FILE__,
				'class'              => __CLASS__,
				'function'           => __FUNCTION__,
			)
		);

		return array(
			'success' => $subscription_topic,
			'message' => $message,
		);
	}


	/**
	 * AJAX handler to process dismissing subscriptions.
	 */
	public function ajax_dismiss_subscription() {

		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ) ) ) {
			// TODO: Return a descriptive error.
			return;
		}

		if ( ! isset( $_POST['subscription_topic'] ) ) {

			$message = 'Dismiss AWS SNS topic attempted with no subscription_topic POSTed';

			do_action(
				'ea_log_notice',
				$this->plugin_name,
				$this->version,
				$message,
				array(
					'post'     => $_POST,
					'file'     => __FILE__,
					'class'    => __CLASS__,
					'function' => __FUNCTION__,
				)
			);

			$error = array(
				'error'   => 'error',
				'message' => 'No subscription_topic POSTed.',
			);

			echo wp_json_encode( $error );

			wp_die();
		}

		$subscription_topic = filter_input( INPUT_POST, 'subscription_topic' );

		$dismiss_result = $this->dismiss_subscription( $subscription_topic );

		echo wp_json_encode( $dismiss_result );

		wp_die();
	}

	/**
	 * Removes any reference for this subscription confirmation request from WordPress.
	 *
	 * DOES NOT remove references in AWS.
	 *
	 * @param string $subscription_topic The SNS ARN.
	 *
	 * @return array to be parsed as JSON in admin UI.
	 */
	public function dismiss_subscription( $subscription_topic ) {

		$pending_subscriptions_option_key = EA_WP_AWS_SNS_Client_REST_Endpoint::PENDING_SUBSCRIPTIONS_OPTION_KEY;

		$pending_subscriptions = get_option( $pending_subscriptions_option_key, array() );

		if ( array_key_exists( $subscription_topic, $pending_subscriptions ) ) {

			$message = "AWS SNS topic <b><i>$subscription_topic</i></b> subscription request discarded.";

			unset( $pending_subscriptions[ $subscription_topic ] );

			update_option( $pending_subscriptions_option_key, $pending_subscriptions );

			do_action(
				'ea_log_info',
				$this->plugin_name,
				$this->version,
				$message,
				array(
					'subscription_topic' => $subscription_topic,
					'file'               => __FILE__,
					'class'              => __CLASS__,
					'function'           => __FUNCTION__,
				)
			);

			return array(
				'success' => $subscription_topic,
				'message' => $message,
			);

		} else {

			$error_message = "$subscription_topic not found in list of pending subscriptions. Maybe already confirmed or dismissed.";

			do_action(
				'ea_log_notice',
				$this->plugin_name,
				$this->version,
				$error_message,
				array(
					'subscription_topic'    => $subscription_topic,
					'pending_subscriptions' => $pending_subscriptions,
					'file'                  => __FILE__,
					'class'                 => __CLASS__,
					'function'              => __FUNCTION__,
				)
			);

			return array(
				'error'   => 'error',
				'message' => $error_message,
			);
		}
	}
}
