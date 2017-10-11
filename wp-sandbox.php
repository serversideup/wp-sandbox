<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://521dimensions.com
 * @since             1.0.0
 * @package           Wp_Sandbox
 *
 * @wordpress-plugin
 * Plugin Name:       WP Sandbox
 * Plugin URI:        https://521dimensions.com/open-source/wp-sandbox
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            521 Dimensions
 * Author URI:        https://521dimensions.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-sandbox
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-sandbox-activator.php
 */
function activate_wp_sandbox() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-sandbox-activator.php';
	WP_Sandbox_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-sandbox-deactivator.php
 */
function deactivate_wp_sandbox() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-sandbox-deactivator.php';
	WP_Sandbox_Deactivator::deactivate();
}

/**
 * Registers the activation and deactivation hooks
 */
register_activation_hook( __FILE__, 'activate_wp_sandbox' );
register_deactivation_hook( __FILE__, 'deactivate_wp_sandbox' );

/**
 * Defines the directory for the plugin to use for including files.
 */
define( 'WP_SANDBOX_PATH', __DIR__ );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-sandbox.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_sandbox() {

	$plugin = new WP_Sandbox();
	$plugin->run();

}
run_wp_sandbox();
