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
class EA_WP_AWS_SNS_Client_REST_Endpoint_Admin {

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

	public function admin_notices() {

		$admin_notices = $this->generate_admin_notices();

		foreach ( $admin_notices as $admin_notice ) {
			print( $admin_notice );
		}
	}

	/**
	 * Builds HTML for confirming/dismissing pending subscriptions.
	 *
	 * @return array
	 * @throws Exception (DateTime)
	 */
	public function generate_admin_notices() {

		$admin_notices = array();

		$pending_subscriptions_option_key = EA_WP_AWS_SNS_Client_REST_Endpoint::PENDING_SUBSCRIPTIONS_OPTION_KEY;

		$pending_subscriptions = get_option( $pending_subscriptions_option_key, array() );

		if( 0 == count( $pending_subscriptions ) ) {
			return $admin_notices;
		}

		$outer_css_class = 'notice notice-info is-dismissible';
		$inner_css_class = 'message';
		$wordpress_admin_date_format = get_option('date_format') . ', ' . get_option('time_format');

		foreach( $pending_subscriptions as $subscription ) {

			// confirm url should get the current url and add the parameters to it?

			$output_array = array();
			preg_match('/.*wp-admin\/(.*)/', $_SERVER['REQUEST_URI'], $output_array);

			$current_url_tail = $output_array[1];

			$html_confirm_url = wp_nonce_url(
				add_query_arg(
					array(
						'action' => 'ea_sns_confirm',
						'subscription' => $subscription['message_id']
					),
					admin_url( $current_url_tail  )
				)
			);

			$html_dismiss_url = wp_nonce_url(
				add_query_arg(
					array(
						'action' => 'ea_sns_dismiss',
						'subscription' => $subscription['message_id']
					),
					admin_url( $current_url_tail )
				)
			);

			// TODO: links expire after 3? days... so don't display them?

			// 2019-03-13T22:28:29.810Z
			$dateTime = new DateTime( $subscription['timestamp'] );

			// TODO: UTC
			$time = $dateTime->format( $wordpress_admin_date_format );

			$topic_arn = $subscription['topic_arn'];

			// TODO: i18n
			// TODO: The same id is being used on both dismiss link and confirm link
			$message = "AWS SNS topic <b><i>$topic_arn</i></b> subscription confirmation request received <i>$time</i>. <a class=\"ea-wp-sns-confirm\" id=\"$topic_arn\" href=\"$html_confirm_url\">Confirm subscription</a>. <a id=\"$topic_arn\" class=\"ea-wp-sns-dismiss\" href=\"$html_dismiss_url\">Dismiss</a>.";

			$admin_notices[] = sprintf( '<div class="%1$s"><p class="%2$s">%3$s</p></div>', esc_attr( $outer_css_class ),  esc_attr( $inner_css_class ), $message );
		}

		return $admin_notices;
	}


	/**
	 * Add a link to AWS SNS console on the plugins list
	 *
	 * @see https://rudrastyh.com/wordpress/plugin_action_links-plugin_row_meta.html
	 *
	 * @param $links_array
	 *
	 * @return array
	 */
	function plugin_action_links( $links_array, $plugin_file_name ){

		if( $this->plugin_name . '/' . $this->plugin_name . '.php' == $plugin_file_name ) {

			array_unshift( $links_array, '<a target="_blank" href="https://console.aws.amazon.com/sns">AWS SNS Console</a>' );
		}

		return $links_array;
	}


	/**
	 * Add a link to EnhancedAthlete.com on the plugins list
	 *
	 * @see https://rudrastyh.com/wordpress/plugin_action_links-plugin_row_meta.html
	 *
	 * @param $links_array
	 *
	 * @return array
	 */
	public function plugin_row_meta( $links_array, $plugin_file_name, $plugin_data, $status ) {

		if( $this->plugin_name . '/' . $this->plugin_name . '.php' == $plugin_file_name ) {

			do_action( 'ea_log_debug', $this->plugin_name, $this->version, 'Adding SNS link to plugin list.', array( 'file'     => __FILE__,
			                                                                                                         'class'    => __CLASS__,
			                                                                                                         'function' => __FUNCTION__
			) );

			foreach( $links_array as $index => $link ) {
				$links_array[ $index ] = str_replace( 'Visit plugin site', 'View plugin on GitHub', $link );
			}

			$links_array[] =  '<a target="_blank" href="https://enhancedathlete.com">Visit EnhancedAthlete.com</a>';
		}

		return $links_array;
	}
}
