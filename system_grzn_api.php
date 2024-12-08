<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://github.com/Dansware03
 * @since             1.0.0
 * @package           System_grzn_api
 *
 * @wordpress-plugin
 * Plugin Name:       Api Laravel GRZN
 * Plugin URI:        https://github.com/Dansware03/plugin-api-laravel-grzn.git
 * Description:       Conecta el sistema con la api de laravel
 * Version:           1.0.0
 * Author:            Dansware Dev
 * Author URI:        https://https://github.com/Dansware03/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       system_grzn_api
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SYSTEM_GRZN_API_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-system_grzn_api-activator.php
 */
function activate_system_grzn_api() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-system_grzn_api-activator.php';
	System_grzn_api_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-system_grzn_api-deactivator.php
 */
function deactivate_system_grzn_api() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-system_grzn_api-deactivator.php';
	System_grzn_api_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_system_grzn_api' );
register_deactivation_hook( __FILE__, 'deactivate_system_grzn_api' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-system_grzn_api.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_system_grzn_api() {

	$plugin = new System_grzn_api();
	$plugin->run();

}
run_system_grzn_api();
