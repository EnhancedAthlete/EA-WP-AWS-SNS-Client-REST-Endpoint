<?php


class AjaxTest extends \WP_Mock\Tools\TestCase {

	private $plugin_name;
	private $version;

	public function setUp(): void {

		$this->plugin_name = 'ea-wp-aws-sns-endpoint';
		$this->version = '1.0.0';

		$basedir = dirname( dirname( __FILE__ ) );

		// Needed for the unit under test to access the consts
		require_once( $basedir . '/trunk/includes/class-ea-wp-aws-sns-client-rest-endpoint.php' );

		require_once( $basedir . '/trunk/ajax/class-ea-wp-aws-sns-client-rest-endpoint-ajax.php' );

		\WP_Mock::setUp();
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	public function test_confirm_subscription() {

		$sut = new EA_WP_AWS_SNS_Client_REST_Endpoint_Ajax( $this->plugin_name, $this->version );

		$subscription_id = '12345';
		$subscribe_url = 'https://localhost/subscribe';

		// Should fetch wp_option of pending subscriptions

		$pending_subscriptions = array(
			'abced' => array(),
			$subscription_id => array(
				'subscribe_url' => $subscribe_url
			)
		);

		// uses array() as second argument for default return value when there are no pending subscriptions
		\WP_Mock::userFunction( 'get_option', array(
			'args' => array( 'ea-wp-aws-sns-client-rest-endpoint-pending-subscriptions', array() ),
			'times' => 1,
			'return' => $pending_subscriptions
		) );

		// Then HTTP GET to AWS with the subscription URL

		$request_response = array(
			'response' => array(
				'code' => '200'
			)
		);

		\WP_Mock::userFunction( 'wp_remote_get', array(
			'args' => $subscribe_url,
			'times' => 1,
			'return' => $request_response
		) );

		// Assume no error in this test

		\WP_Mock::userFunction( 'is_wp_error', array(
			'args' => array( $request_response ),
			'times' => 1,
			'return' => false
		) );

		// Update the pending subscriptions option key

		$remaining_pending_subscriptions = array(
			'abced' => array()
		);

		\WP_Mock::userFunction( 'update_option', array(
			'args' => array( 'ea-wp-aws-sns-client-rest-endpoint-pending-subscriptions', $remaining_pending_subscriptions ),
			'times' => 1,
			'return' => true
		) );


		$sut->confirm_subscription( $subscription_id );

		// Otherwise PHPUnit reports "This test did not perform any assertions"
		\WP_Mock::assertActionsCalled();
	}

	public function test_dismiss_subscription() {

		// should delete any reference to the subscription

		$sut = new EA_WP_AWS_SNS_Client_REST_Endpoint_Ajax( $this->plugin_name, $this->version );

		$pending_subscriptions = array(
			'qwerty' => array()
		);

		// uses array() as second argument for default return value when there are no pending subscriptions
		\WP_Mock::userFunction( 'get_option', array(
			'args' => array( 'ea-wp-aws-sns-client-rest-endpoint-pending-subscriptions', array() ),
			'times' => 1,
			'return' => $pending_subscriptions
		) );

		\WP_Mock::userFunction( 'update_option', array(
			'args' => array( 'ea-wp-aws-sns-client-rest-endpoint-pending-subscriptions', array() ),
			'times' => 1,
			'return' => true
		) );

		$sut->dismiss_subscription( 'qwerty' );

		\WP_Mock::assertActionsCalled();
	}

	public function confirm_failure() {

		// {
		//	"headers": {},
		// 	"body": "<ErrorResponse xmlns=\"http:\/\/sns.amazonaws.com\/doc\/2010-03-31\/\">\n  <Error>\n    <Type>Sender<\/Type>\n    <Code>InvalidParameter<\/Code>\n    <Message>Invalid parameter: Token<\/Message>\n  <\/Error>\n  <RequestId>64c46344-086e-51c6-859a-3651e80a830a<\/RequestId>\n<\/ErrorResponse>\n",
		// 	"response": {
		//		"code": 400,
		// 		"message": "Bad Request"
		// 	},
		// 	"cookies": [],
		// 	"filename": null,
		// 	"http_response": {
		//		"data": null,
		// 		"headers": null,
		// 		"status": null
		// 	}
		// }

		//<ErrorResponse xmlns="http://sns.amazonaws.com/doc/2010-03-31/">
		//  <Error>
		//    <Type>Sender</Type>
		//    <Code>InvalidParameter</Code>
		//    <Message>Invalid parameter: Token</Message>
		//  </Error>
		//  <RequestId>041533cd-ccf3-59d9-946d-66d82f70a542</RequestId>
		//</ErrorResponse>
	}
}
