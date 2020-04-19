<?php
/**
 * Loads all required classes
 *
 * Uses wp-namespace-autoloader for plugin files and require_once for libs.
 *
 * @link       https://BrianHenry.ie
 * @since      2.0.0
 * @package    bh-wp-autologin-urls
 *
 * @see https://github.com/pablo-sg-pacheco/wp-namespace-autoloader/
 */

namespace EA_WP_AWS_SNS_Client_REST_Endpoint;

use EA_WP_AWS_SNS_Client_REST_Endpoint\Pablo_Pacheco\WP_Namespace_Autoloader\WP_Namespace_Autoloader;


// The plugin-scoped namespace for composer required libraries, as specified in composer.json Mozart config.
$dep_namespace = 'EA_WP_AWS_SNS_Client_REST_Endpoint';
// The Mozart config `dep_directory` adjusted for relative path.
$dep_directory = '/vendor';

spl_autoload_register(
	function ( $namespaced_class_name ) use ( $dep_namespace, $dep_directory ) {

		$autoload_directory = __DIR__ . $dep_directory . DIRECTORY_SEPARATOR;

		// The class name with its true namespace.
		$bare_namespaced_class_name = preg_replace( "#$dep_namespace\\\*#", '', $namespaced_class_name );

		$file_path = $autoload_directory . str_replace( '\\', '/', $bare_namespaced_class_name ) . '.php';

		if ( file_exists( $file_path ) ) {
			require_once $file_path;
		}
	}
);

$wpcs_autoloader = new WP_Namespace_Autoloader();
$wpcs_autoloader->init();

