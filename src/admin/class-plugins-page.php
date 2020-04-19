<?php
/**
 * The plugin page output of the plugin.
 *
 * @link
 * @since      1.0.0
 *
 * @package    EA_WP_AWS_SNS_Client_REST_Endpoint
 * @subpackage EA_WP_AWS_SNS_Client_REST_Endpoint/admin
 */

namespace EA_WP_AWS_SNS_Client_REST_Endpoint\admin;

use EA_WP_AWS_SNS_Client_REST_Endpoint\WPPB\WPPB_Object;

/**
 * This class adds a `Settings` link on the plugins.php page.
 *
 * @package    EA_WP_AWS_SNS_Client_REST_Endpoint
 * @subpackage EA_WP_AWS_SNS_Client_REST_Endpoint/admin
 * @author     BrianHenryIE <BrianHenryIE@gmail.com>
 */
class Plugins_Page extends WPPB_Object {

	/**
	 * Add a link to AWS SNS console on the plugins.php list.
	 *
	 * @see https://rudrastyh.com/wordpress/plugin_action_links-plugin_row_meta.html
	 *
	 * @hooked plugin_action_links
	 *
	 * @param array  $links_array      The existing plugin links (usually "Deactivate").
	 * @param string $plugin_file_name The plugin filename to match when filtering.
	 *
	 * @return array The links to display below the plugin name on plugins.php.
	 */
	public function plugin_action_links( $links_array, $plugin_file_name ) {

		if ( $this->plugin_name . '/' . $this->plugin_name . '.php' === $plugin_file_name ) {

			array_unshift( $links_array, '<a target="_blank" href="https://console.aws.amazon.com/sns/home?#">AWS SNS Console</a>' );
		}

		return $links_array;
	}


	/**
	 * Add a link to EnhancedAthlete.com on the plugins list.
	 *
	 * @see https://rudrastyh.com/wordpress/plugin_action_links-plugin_row_meta.html
	 *
	 * @hooked plugin_row_meta
	 *
	 * @param string[] $plugin_meta The meta information/links displayed by the plugin description.
	 * @param string   $plugin_file_name The plugin filename to match when filtering.
	 * @param array    $plugin_data Associative array including PluginURI, slug, Author, Version.
	 * @param string   $status The plugin status, e.g. 'Inactive'.
	 *
	 * @return array The filtered $plugin_meta.
	 */
	public function plugin_row_meta( $plugin_meta, $plugin_file_name, $plugin_data, $status ) {

		if ( $this->get_plugin_name() . '/' . $this->get_plugin_name() . '.php' === $plugin_file_name ) {

			do_action(
				'ea_log_debug',
				$this->plugin_name,
				$this->version,
				'Adding SNS link to plugin list.',
				array(
					'file'     => __FILE__,
					'class'    => __CLASS__,
					'function' => __FUNCTION__,
				)
			);

			foreach ( $plugin_meta as $index => $link ) {
				$plugin_meta[ $index ] = str_replace( 'Visit plugin site', 'View plugin on GitHub', $link );
			}

			$plugin_meta[] = '<a target="_blank" href="https://enhancedathlete.com">Visit EnhancedAthlete.com</a>';
		}

		return $plugin_meta;
	}

	/**
	 * Edit the plugin's description so the REST endpoint is easy to find and copy.
	 *
	 * @hooked all_plugins
	 * @since 2.2.0
	 * @param array[] $all_plugins Associative array of plugin-slug: plugin data.
	 *
	 * @return array
	 */
	public function add_rest_url_to_description( $all_plugins ) {

		$plugin_file_name = $this->get_plugin_name() . '/' . $this->get_plugin_name() . '.php';

		if ( isset( $all_plugins[ $plugin_file_name ] ) ) {

			// The description as read from the base plugin file.
			$description = $all_plugins[ $plugin_file_name ]['Description'];

			$description .= ' Use endpoint: <em>' . get_rest_url( null, 'ea/v1/aws-sns/' ) . '</em>';

			$all_plugins[ $plugin_file_name ]['Description'] = $description;

		}

		return $all_plugins;

	}

}
