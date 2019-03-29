<?php


class CronTest extends \WP_Mock\Tools\TestCase {

	private $plugin_name;
	private $plugin_version;

	public function setUp(): void {

		$this->plugin_name = 'ea-wp-aws-sns-endpoint';
		$this->plugin_version = '1.0.0';

		$basedir = dirname( dirname( __FILE__ ) );

		// Needed for the unit under test to access the consts
		require_once( $basedir . '/trunk/includes/class-ea-wp-aws-sns-client-rest-endpoint.php' );

		require_once( $basedir . '/trunk/cron/class-ea-wp-aws-sns-client-rest-endpoint-cron.php' );

		\WP_Mock::setUp();
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	/**
	 * The only purpose of the cron class is to fire the action in the background. It shouldn't matter
	 * too much, since the handling the REST request won't slow down page rending for a user, but I think
	 * a timeout in responding to AWS might result in AWS resending the notification, so this avoids
	 * potentially redundant messages.
	 */
	public function test_notify_in_background() {

		$topic_arn = 'topic_arn';
		$headers = 'headers';
		$body = 'body';
		$message = 'message';

		$sut = new EA_WP_AWS_SNS_Client_REST_Endpoint_Cron( $this->plugin_name, $this->plugin_version );

		\WP_Mock::expectAction( 'ea_aws_sns_notification', $topic_arn, $headers, $body, $message );

		$sut->notify_in_background( $topic_arn, $headers, $body, $message );

		// Otherwise PHPUnit reports "This test did not perform any assertions"
		\WP_Mock::assertActionsCalled();
	}
}