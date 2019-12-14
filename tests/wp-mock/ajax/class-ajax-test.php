<?php

namespace EA_WP_AWS_SNS_Client_REST_Endpoint\ajax;

class Ajax_Test extends \WP_Mock\Tools\TestCase {

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

	/**
	 * Straightforward successful test of pressing the confirm subscription button.
	 */
	public function test_confirm_subscription() {

		$sut = new Ajax( $this->plugin_name, $this->plugin_version );

		$subscription_id = '12345';
		$subscribe_url   = 'https://localhost/subscribe';

		// Should fetch wp_option of pending subscriptions.
		$pending_subscriptions = array(
			'abced'          => array(),
			$subscription_id => array(
				'subscribe_url' => $subscribe_url,
			),
		);

		// uses array() as second argument for default return value when there are no pending subscriptions.
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => array( 'ea-wp-aws-sns-client-rest-endpoint-pending-subscriptions', array() ),
				'times'  => 1,
				'return' => $pending_subscriptions,
			)
		);

		// Then HTTP GET to AWS with the subscription URL.
		$request_response = array(
			'response' => array(
				'code' => '200',
			),
		);

		\WP_Mock::userFunction(
			'wp_remote_get',
			array(
				'args'   => $subscribe_url,
				'times'  => 1,
				'return' => $request_response,
			)
		);

		// Assume no error in this test.
		\WP_Mock::userFunction(
			'is_wp_error',
			array(
				'args'   => array( $request_response ),
				'times'  => 1,
				'return' => false,
			)
		);

		// Update the pending subscriptions option key.
		$remaining_pending_subscriptions = array(
			'abced' => array(),
		);

		\WP_Mock::userFunction(
			'update_option',
			array(
				'args'   => array( 'ea-wp-aws-sns-client-rest-endpoint-pending-subscriptions', $remaining_pending_subscriptions ),
				'times'  => 1,
				'return' => true,
			)
		);

		$sut->confirm_subscription( $subscription_id );

		// Otherwise PHPUnit reports "This test did not perform any assertions".
		\WP_Mock::assertActionsCalled();
	}

	/**
	 * Straightforward test of dismiss subscription button.
	 */
	public function test_dismiss_subscription() {

		// should delete any reference to the subscription.
		$sut = new Ajax( $this->plugin_name, $this->plugin_version );

		$pending_subscriptions = array(
			'qwerty' => array(),
		);

		// uses array() as second argument for default return value when there are no pending subscriptions.
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => array( 'ea-wp-aws-sns-client-rest-endpoint-pending-subscriptions', array() ),
				'times'  => 1,
				'return' => $pending_subscriptions,
			)
		);

		\WP_Mock::userFunction(
			'update_option',
			array(
				'args'   => array( 'ea-wp-aws-sns-client-rest-endpoint-pending-subscriptions', array() ),
				'times'  => 1,
				'return' => true,
			)
		);

		$sut->dismiss_subscription( 'qwerty' );

		\WP_Mock::assertActionsCalled();
	}

}
