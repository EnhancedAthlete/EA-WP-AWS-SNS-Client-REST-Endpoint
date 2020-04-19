<?php
/**
 * Class RestTest
 *
 * @package EA_WP_AWS_SNS_Client_REST_Endpoint
 */

namespace EA_WP_AWS_SNS_Client_REST_Endpoint\rest;

/**
 * Class Rest_Test
 */
class Rest_Test extends \WP_UnitTestCase {

	/**
	 * Check the basic endpoint has been registered with WordPress.
	 */
	public function test_rest_endpoint_exists() {

		$rest_server = rest_get_server();

		$this->assertArrayHasKey( '/ea/v1/aws-sns', $rest_server->get_routes() );
	}

	/**
	 * Check we can POST to the endpoint.
	 */
	public function test_endpoint_accepts_post() {

		$rest_server = rest_get_server();

		$route = $rest_server->get_routes()['/ea/v1/aws-sns'][0];

		$this->assertArrayHasKey('POST', $route['methods'] );

		$this->assertTrue( $route['methods']['POST'] );

	}


	/**
	 * A valid request from AWS-SNS should schedule a notification on a cron job and return success.
	 */
	public function test_valid_notification_schedules_cron() {

		global $project_root_dir;

		$notification_json = file_get_contents( $project_root_dir . '/tests/data/notification.json' );
		$notification = json_decode( $notification_json );

		$request = new \WP_REST_Request( 'POST', '/ea/v1/aws-sns' );

		$request->set_headers( $notification->headers );
		$request->set_body( wp_json_encode( $notification->body ) );

		rest_do_request( $request );

		$cron = _get_cron_array();

		$scheduled_cron_hooks = array();

		foreach( $cron as $cron_time_array ){

			foreach( $cron_time_array as $hook_name => $value ) {

				$scheduled_cron_hooks[] = $hook_name;

			}
		}

		$this->assertContains( 'ea_wp_aws_sns_client_rest_endpoint_notify_in_background', $scheduled_cron_hooks );

		// wp_next_scheduled()
	}

}
