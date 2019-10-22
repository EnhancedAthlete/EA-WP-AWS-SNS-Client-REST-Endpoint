<?php
/**
 * Tests the ea_aws_sns_notification action is called by the function which will be scheduled to cron.
 *
 * @package ea-wp-aws-sns-client-rest-endpoint
 *
 * @see EA_WP_AWS_SNS_Client_REST_Endpoint_Cron
 *
 * @author Brian Henry <BrianHenryIE@gmail.com>
 */

/**
 * Class Cron_Test
 */
class Cron_Test extends \WP_Mock\Tools\TestCase {

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
	private $plugin_version = '2.0.0';

	/**
	 * WP_Mock test setup method.
	 */
	public function setUp(): void {

		global $plugin_root_dir;

		// Needed for the unit under test to access the consts.
		require_once $plugin_root_dir . '/includes/class-ea-wp-aws-sns-client-rest-endpoint.php';

		require_once $plugin_root_dir . '/cron/class-ea-wp-aws-sns-client-rest-endpoint-cron.php';

		\WP_Mock::setUp();
	}

	/**
	 * Fire the action which passes data to other plugins in the background.
	 *
	 * The only purpose of the cron class is to fire the action in the background. It shouldn't matter
	 * too much, since the handling the REST request won't slow down page rending for a user, but I think
	 * a timeout in responding to AWS might result in AWS resending the notification, so this avoids
	 * potentially redundant messages.
	 */
	public function test_notify_in_background() {

		$handled   = array();
		$topic_arn = 'topic_arn';
		$headers   = array( 'headers' );
		$body      = json_decode( '"body": "body"' );
		$message   = json_decode( '"message": "message"' );

		$sut = new EA_WP_AWS_SNS_Client_REST_Endpoint_Cron( $this->plugin_name, $this->plugin_version );

		\WP_Mock::expectFilter( 'ea_aws_sns_notification', $handled, $topic_arn, $headers, $body, $message );

		$sut->notify_in_background( $topic_arn, $headers, $body, $message );

		$this->assertConditionsMet();
	}
}
