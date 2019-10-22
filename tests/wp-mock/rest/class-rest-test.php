<?php

/**
 * Class Rest_Test
 *
 * phpcs:disable WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
 */
class Rest_Test extends \WP_Mock\Tools\TestCase {

	/**
	 * This will never change!
	 *
	 * @var string Plugin name.
	 */
	private $plugin_name = 'ea-wp-aws-sns-endpoint';

	/**
	 * The latest version this test class was written against.
	 *
	 * @var string Plugin version.
	 */
	private $plugin_version = '1.0.0';


	public function setUp(): void {

		global $plugin_root_dir;

		// Needed for the unit under test to access the consts.
		require_once $plugin_root_dir . '/includes/class-ea-wp-aws-sns-client-rest-endpoint.php';

		require_once $plugin_root_dir . '/rest/class-ea-wp-aws-sns-client-rest-endpoint-rest.php';

		\WP_Mock::setUp();
	}


	/**
	 * The new subscription should be added to the list of pending subscriptions. (which are later displayed in wp-admin)
	 */
	public function test_handle_subscription_confirmation_request() {

		$sut = new EA_WP_AWS_SNS_Client_REST_Endpoint_REST( $this->plugin_name, $this->plugin_version );

		global $project_root_dir;

		$notification_json = file_get_contents( $project_root_dir . '/tests/data/subscription_confirmation_request.json' );
		$notification      = json_decode( $notification_json );

		$headers = $notification->headers;
		$body    = $notification->body;

		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => array( 'ea-wp-aws-sns-client-rest-endpoint-pending-subscriptions', \WP_Mock\Functions::type( 'array' ) ),
				'times'  => 1,
				'return' => array(),
			)
		);

		// $pending_subscriptions = array(
		// '88685c78-1e4b-4aab-be1c-5aa353b575dd' => array(
		// 'message_id'    => '88685c78-1e4b-4aab-be1c-5aa353b575dd',
		// 'topic_arn'     => 'arn:aws:sns:us-east-1:112382221323:new_transcription_complete',
		// 'subscribe_url' => 'https:\/\/sns.us-east-1.amazonaws.com\/?Action=ConfirmSubscription&TopicArn=arn:aws:sns:us-east-1:112382221323:new_transcription_complete&Token=2336412f37fb687f5d51e6e241dbca52ec0845de64a63a12839c5d8e7953c0bde092259e53d64184e4fece59732ed0b50d8a7f1fecfe55d513750c40a0e0647d7cd7cf03cdaa83484d7f9f928c6cd3db247178eae9281a4064bd4cfcdfefd60b8aa8541d086aa7b3ad69c0e2bdcffbd9dc221b86a29902aeeee14823e715f74f',
		// 'timestamp'     => '2019-03-13T22:28:29.810Z'
		// )
		// );
		\WP_Mock::userFunction(
			'update_option',
			array(
				'args'   => array( 'ea-wp-aws-sns-client-rest-endpoint-pending-subscriptions', \WP_Mock\Functions::type( 'array' ) ),
				'times'  => 1,
				'return' => true,
			)
		);

		$sut->handle_subscription_confirmation_request( $headers, $body );

		\WP_Mock::assertActionsCalled();
	}

	public function test_handle_notification() {

		$sut = new EA_WP_AWS_SNS_Client_REST_Endpoint_REST( $this->plugin_name, $this->plugin_version );

		global $project_root_dir;

		$notification_json = file_get_contents( $project_root_dir . '/tests/data/notification.json' );
		$notification      = json_decode( $notification_json );

		$headers = $notification->headers;
		$body    = $notification->body;

		\WP_Mock::userFunction(
			'wp_schedule_single_event',
			array(
				'args'  => array( \WP_Mock\Functions::type( 'int' ), 'ea_wp_aws_sns_client_rest_endpoint_notify_in_background', \WP_Mock\Functions::type( 'array' ) ),
				'times' => 1,
			)
		);

		$sut->handle_notification( $headers, $body );

		// Otherwise PHPUnit reports "This test did not perform any assertions".
		\WP_Mock::assertActionsCalled();
	}
}
