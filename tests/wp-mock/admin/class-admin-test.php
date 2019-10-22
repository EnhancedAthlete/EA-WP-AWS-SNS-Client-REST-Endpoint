<?php


class Admin_Test extends \WP_Mock\Tools\TestCase {

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

		require_once $plugin_root_dir . '/admin/class-ea-wp-aws-sns-client-rest-endpoint-admin.php';

		\WP_Mock::setUp();
	}


	// $subscription_confirmation = array(
	// 'message_id'    => $body->MessageId,
	// 'topic_arn'     => $body->TopicArn,
	// 'subscribe_url' => $body->SubscribeURL,
	// 'timestamp'     => $body->Timestamp
	// );

	/**
	 * Manual test:
	 * Add an entry to wp_option by cli
	 * wp shell
	 * update_option( 'ea-wp-aws-sns-client-rest-endpoint-pending-subscriptions', array( '88685c78-1e4b-4aab-be1c-5aa353b575dd' => array( 'message_id' => '88685c78-1e4b-4aab-be1c-5aa353b575dd', 'topic_arn' => 'arn:aws:sns:us-east-1:112382221323:new_transcription_complete', 'subscribe_url' => 'https:\/\/sns.us-east-1.amazonaws.com\/?Action=ConfirmSubscription&TopicArn=arn:aws:sns:us-east-1:112382221323:new_transcription_complete&Token=2336412f37fb687f5d51e6e241dbca52ec0845de64a63a12839c5d8e7953c0bde092259e53d64184e4fece59732ed0b50d8a7f1fecfe55d513750c40a0e0647d7cd7cf03cdaa83484d7f9f928c6cd3db247178eae9281a4064bd4cfcdfefd60b8aa8541d086aa7b3ad69c0e2bdcffbd9dc221b86a29902aeeee14823e715f74f', 'timestamp' => '2019-03-13T22:28:29.810Z' ) ) );
	 *
	 * @throws Exception
	 */
	public function test_display_pending_subscriptions() {

		$sut = new EA_WP_AWS_SNS_Client_REST_Endpoint_Admin( $this->plugin_name, $this->plugin_version );

		$pending_subscriptions = array(
			'88685c78-1e4b-4aab-be1c-5aa353b575dd' => array(
				'message_id'    => '88685c78-1e4b-4aab-be1c-5aa353b575dd',
				'topic_arn'     => 'arn:aws:sns:us-east-1:112382221323:new_transcription_complete',
				'subscribe_url' => 'https:\/\/sns.us-east-1.amazonaws.com\/?Action=ConfirmSubscription&TopicArn=arn:aws:sns:us-east-1:112382221323:new_transcription_complete&Token=2336412f37fb687f5d51e6e241dbca52ec0845de64a63a12839c5d8e7953c0bde092259e53d64184e4fece59732ed0b50d8a7f1fecfe55d513750c40a0e0647d7cd7cf03cdaa83484d7f9f928c6cd3db247178eae9281a4064bd4cfcdfefd60b8aa8541d086aa7b3ad69c0e2bdcffbd9dc221b86a29902aeeee14823e715f74f',
				'timestamp'     => '2019-03-13T22:28:29.810Z',
			),
		);

		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => array( 'ea-wp-aws-sns-client-rest-endpoint-pending-subscriptions', array() ),
				'times'  => 1,
				'return' => $pending_subscriptions,
			)
		);

		$html_confirm_url = 'https://wordpress.site/wp-admin/plugins.php?plugin_status=all&paged=1&s&ea_sns_confirm=88685c78-1e4b-4aab-be1c-5aa353b575dd';
		$html_dismiss_url = 'https://wordpress.site/fwp-admin/plugins.php?plugin_status=all&paged=1&s&ea_sns_dismiss=88685c78-1e4b-4aab-be1c-5aa353b575dd';

		$_SERVER['REQUEST_URI'] = '/wp-admin/plugins.php?plugin_status=all&paged=1&s';

		global $wp;
		$wp             = new stdClass();
		$wp->query_vars = 'wp-admin/plugins.php?plugin_status=all&paged=1&s';
		$wp->request    = '';

		\WP_Mock::userFunction(
			'add_query_arg',
			array(
				'args'   => array( 'wp-admin/plugins.php?plugin_status=all&paged=1&s', '' ),
				'times'  => 1,
				'return' => 'wp-admin/plugins.php?plugin_status=all&paged=1&s',
			)
		);

		\WP_Mock::userFunction(
			'home_url',
			array(
				'args'   => 'wp-admin/plugins.php?plugin_status=all&paged=1&s',
				'times'  => 1,
				'return' => 'https://wordpress.site/wp-admin/plugins.php?plugin_status=all&paged=1&s',
			)
		);

		\WP_Mock::userFunction(
			'admin_url',
			array(
				'args'   => 'plugins.php?plugin_status=all&paged=1&s',
				'times'  => 1,
				'return' => 'https://wordpress.site/wp-admin/plugins.php?plugin_status=all&paged=1&s',
			)
		);

		\WP_Mock::userFunction(
			'add_query_arg',
			array(
				'args'   => array(
					array(
						'action'       => 'ea_sns_confirm',
						'subscription' => '88685c78-1e4b-4aab-be1c-5aa353b575dd',
					),
					'https://wordpress.site/wp-admin/plugins.php?plugin_status=all&paged=1&s',
				),
				'times'  => 1,
				'return' => 'https://wordpress.site/wp-admin/plugins.php?plugin_status=all&paged=1&s&action=ea_sns_confirm&subscription=88685c78-1e4b-4aab-be1c-5aa353b575dd',
			)
		);

		\WP_Mock::userFunction(
			'wp_nonce_url',
			array(
				'args'   => 'https://wordpress.site/wp-admin/plugins.php?plugin_status=all&paged=1&s&action=ea_sns_confirm&subscription=88685c78-1e4b-4aab-be1c-5aa353b575dd',
				'times'  => 1,
				'return' => $html_confirm_url,
			)
		);

		\WP_Mock::userFunction(
			'admin_url',
			array(
				'args'   => 'plugins.php?plugin_status=all&paged=1&s',
				'times'  => 1,
				'return' => 'https://wordpress.site/wp-admin/plugins.php?plugin_status=all&paged=1&s',
			)
		);

		\WP_Mock::userFunction(
			'add_query_arg',
			array(
				'args'   => array(
					array(
						'action'       => 'ea_sns_dismiss',
						'subscription' => '88685c78-1e4b-4aab-be1c-5aa353b575dd',
					),
					'https://wordpress.site/wp-admin/plugins.php?plugin_status=all&paged=1&s',
				),
				'times'  => 1,
				'return' => 'https://wordpress.site/wp-admin/plugins.php?plugin_status=all&paged=1&s&action=ea_sns_dismiss&subscription=88685c78-1e4b-4aab-be1c-5aa353b575dd',
			)
		);

		\WP_Mock::userFunction(
			'wp_nonce_url',
			array(
				'args'   => 'https://wordpress.site/wp-admin/plugins.php?plugin_status=all&paged=1&s&action=ea_sns_dismiss&subscription=88685c78-1e4b-4aab-be1c-5aa353b575dd',
				'times'  => 1,
				'return' => $html_dismiss_url,
			)
		);

		// Time/date format should be based on the WordPress date and time format, so specify them here.
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => 'date_format',
				'times'  => 1,
				'return' => 'F j, Y',
			)
		);

		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => 'time_format',
				'times'  => 1,
				'return' => 'g:i a',
			)
		);

		$actual = $sut->generate_admin_notices();

		$expected = array( "<div class=\"notice notice-info is-dismissible\"><p class=\"message\">AWS SNS topic <b><i>arn:aws:sns:us-east-1:112382221323:new_transcription_complete</i></b> subscription confirmation request received <i>March 13, 2019, 10:28 pm</i>. <a class=\"ea-wp-sns-confirm\" id=\"arn:aws:sns:us-east-1:112382221323:new_transcription_complete\" href=\"$html_confirm_url\">Confirm subscription</a>. <a id=\"arn:aws:sns:us-east-1:112382221323:new_transcription_complete\" class=\"ea-wp-sns-dismiss\" href=\"$html_dismiss_url\">Dismiss</a>.</p></div>" );

		self::assertEquals( $expected, $actual );

	}
}
