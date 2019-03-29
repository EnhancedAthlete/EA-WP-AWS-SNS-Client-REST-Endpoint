<?php

/**
 * The cron-specific functionality of the plugin.
 *
 * @link       https://BrianHenry.ie
 * @since      1.0.0
 *
 * @package    EA_WP_AWS_SNS_Client_REST_Endpoint
 * @subpackage EA_WP_AWS_SNS_Client_REST_Endpoint/admin
 */

/**
 * The cron-specific functionality of the plugin.
 *
 * Exists solely to fire events in in the background so responses to AWS will be timely.
 *
 * @package    EA_WP_AWS_SNS_Client_REST_Endpoint
 * @subpackage EA_WP_AWS_SNS_Client_REST_Endpoint/cron
 * @author     Brian Henry <BrianHenryIE@gmail.com>
 */
class EA_WP_AWS_SNS_Client_REST_Endpoint_Cron {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * This typically runs in the background.
	 * It wouldn't make much sense to run it directly when the action it fires is clearer.
	 *
	 * @param $topic_arn
	 * @param $headers
	 * @param $body
	 * @param $message
	 */
	public function notify_in_background( $topic_arn, $headers, $body, $message ) {

		do_action( EA_WP_AWS_SNS_Client_REST_Endpoint::NEW_NOTIFICATION_ACTION, $topic_arn, $headers, $body, $message );
	}

}
