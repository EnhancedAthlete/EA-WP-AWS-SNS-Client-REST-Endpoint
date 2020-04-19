<?php
/**
 *
 * @package EA_WP_AWS_SNS_Client_REST_Endpoint
 */

namespace EA_WP_AWS_SNS_Client_REST_Endpoint\cron;

/**
 * Class Cron_Test
 */
class Cron_Test extends \WP_UnitTestCase {


	public function test_cron_registered() {

		$tag = 'ea_wp_aws_sns_client_rest_endpoint_notify_in_background';

		$function = array( $GLOBALS['ea_wp_aws_sns_client_rest_endpoint']->cron, 'notify_in_background' );

		$cron_action_added = has_action($tag, $function );

		$this->assertNotFalse($cron_action_added);

	}


	public function test_cron_action() {

		global $project_root_dir;

		// Set up a job.

		$notification_json = file_get_contents( $project_root_dir . '/tests/data/notification.json' );
		$notification = json_decode( $notification_json );

		$request = new \WP_REST_Request( 'POST', '/ea/v1/aws-sns' );

		$request->set_headers( $notification->headers );
		$request->set_body( wp_json_encode( $notification->body ) );

		rest_do_request( $request );

		// Remove existing actions the cron should fire and add one for the test.


		global $project_root_dir;

		$notification_json = file_get_contents( $project_root_dir . '/tests/data/notification.json' );
		$notification = json_decode( $notification_json );

		$request = new \WP_REST_Request( 'POST', '/ea/v1/aws-sns' );

		$request->set_headers( $notification->headers );
		$request->set_body( wp_json_encode( $notification->body ) );

		rest_do_request( $request );


		// Execute cron.

		// include $project_root_dir . '/wordpress/wp-cron.php';



		$called = false;

		$filter_spy = function( $handled, ...$args ) use ( &$called ) {

			$called = true;

			return $handled;

		};

		add_filter( 'ea_aws_sns_notification', $filter_spy );

		$crons = _get_cron_array();

		// Lifted from wp-cron.php
		foreach ( $crons as $timestamp => $cronhooks ) {

			foreach ( $cronhooks as $hook => $keys ) {

				if( 'ea_wp_aws_sns_client_rest_endpoint_notify_in_background' !== $hook ) {
					continue;
				}

				foreach ( $keys as $k => $v ) {

					/**
					 * Fires scheduled events.
					 *
					 * @ignore
					 * @since 2.1.0
					 *
					 * @param string $hook Name of the hook that was scheduled to be fired.
					 * @param array  $args The arguments to be passed to the hook.
					 */
					do_action_ref_array( $hook, $v['args'] );

				}
			}
		}

		$this->assertTrue( $called );

	}

}
