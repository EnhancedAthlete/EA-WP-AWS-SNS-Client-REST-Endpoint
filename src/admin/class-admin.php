<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://BrianHenry.ie
 * @since      1.0.0
 *
 * @package    EA_WP_AWS_SNS_Client_REST_Endpoint
 * @subpackage EA_WP_AWS_SNS_Client_REST_Endpoint/admin
 */

namespace EA_WP_AWS_SNS_Client_REST_Endpoint\admin;

use EA_WP_AWS_SNS_Client_REST_Endpoint\includes\EA_WP_AWS_SNS_Client_REST_Endpoint;
use EA_WP_AWS_SNS_Client_REST_Endpoint\WPPB\WPPB_Object;

/**
 * The admin-specific functionality of the plugin.
 *
 * Enqueues JavaScript for confirming and dismissing subscriptions.
 * Adds links on wp-admin/plugins.php for config etc.
 *
 * @package    EA_WP_AWS_SNS_Client_REST_Endpoint
 * @subpackage EA_WP_AWS_SNS_Client_REST_Endpoint/admin
 * @author     Brian Henry <BrianHenryIE@gmail.com>
 */
class Admin extends WPPB_Object {

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * Admin notices printed by `admin_notices()` will have two links, one for each the confirm and dismiss action,
	 * each with a data attribute for the relevant subscription.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ea-wp-aws-sns-client-rest-endpoint-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Checks for pending subscription notifications and displays them.
	 *
	 * Hooked on WordPress `admin_notices`.
	 */
	public function admin_notices() {

		$admin_notices = $this->generate_admin_notices();

		foreach ( $admin_notices as $admin_notice ) {
			print( esc_html( $admin_notice ) );
		}
	}

	/**
	 * Builds HTML for confirming/dismissing pending subscriptions.
	 *
	 * @return array
	 */
	public function generate_admin_notices() {

		$admin_notices = array();

		$pending_subscriptions_option_key = EA_WP_AWS_SNS_Client_REST_Endpoint::PENDING_SUBSCRIPTIONS_OPTION_KEY;

		$pending_subscriptions = get_option( $pending_subscriptions_option_key, array() );

		if ( 0 === count( $pending_subscriptions ) ) {
			return $admin_notices;
		}

		$outer_css_class             = 'notice notice-info is-dismissible';
		$inner_css_class             = 'message';
		$wordpress_admin_date_format = get_option( 'date_format' ) . ', ' . get_option( 'time_format' );

		foreach ( $pending_subscriptions as $subscription ) {

			global $wp;
			$current_url = home_url( add_query_arg( $wp->query_vars, $wp->request ) );

			// confirm url should get the current url and add the parameters to it?
			$output_array = array();
			preg_match( '/.*wp-admin\/(.*)/', $current_url, $output_array );

			$current_url_tail = $output_array[1];

			// TODO: Nonces.

			$html_confirm_url = wp_nonce_url(
				add_query_arg(
					array(
						'action'       => 'ea_sns_confirm',
						'subscription' => $subscription['message_id'],
					),
					admin_url( $current_url_tail )
				)
			);

			$html_dismiss_url = wp_nonce_url(
				add_query_arg(
					array(
						'action'       => 'ea_sns_dismiss',
						'subscription' => $subscription['message_id'],
					),
					admin_url( $current_url_tail )
				)
			);

			// TODO: subscription requests expire after three days, so check and discard. Maybe start a cron
			// when they're received.
			try {
				$subscription_datetime = new \DateTime( $subscription['timestamp'] );
				// TODO: UTC -> timezones: 2019-03-13T22:28:29.810Z -> ???.
				$time = $subscription_datetime->format( $wordpress_admin_date_format );
			} catch ( \Exception $e ) {
				$time = $subscription['timestamp'];
			}

			$topic_arn = $subscription['topic_arn'];

			// TODO: i18n.
			// TODO: The same id is being used on both dismiss link and confirm link.
			// TODO: nonce.
			$message = "AWS SNS topic <b><i>$topic_arn</i></b> subscription confirmation request received <i>$time</i>. <a class=\"ea-wp-sns-confirm\" id=\"$topic_arn\" href=\"$html_confirm_url\">Confirm subscription</a>. <a id=\"$topic_arn\" class=\"ea-wp-sns-dismiss\" href=\"$html_dismiss_url\">Dismiss</a>.";

			$admin_notices[] = sprintf( '<div class="%1$s"><p class="%2$s">%3$s</p></div>', esc_attr( $outer_css_class ), esc_attr( $inner_css_class ), $message );
		}

		return $admin_notices;
	}


}
