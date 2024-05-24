<?php

/**
 *
 * @link              https://https://profiles.wordpress.org/olliejones/
 * @package           Personal_Opinion_Tracker
 *
 * @wordpress-plugin
 * Plugin Name:       Personal Opinion Tracker
 * Plugin URI:        https://github.com/OllieJones/personal-opinion-tracker
 * Description:       Citizen tool to keep track of proposed legislation.
 * Version:           0.0.3
 * Author:            Oliver Jones
 * Author URI:        https://https://profiles.wordpress.org/olliejones//
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       personal-opinion-tracker
 * Domain Path:       /languages
 */

namespace Personal_Opinion_Tracker;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'PERSONAL_OPINION_TRACKER_VERSION', '0.0.2' );

function activate() {
	$path = trailingslashit( plugin_dir_path( __FILE__ ) );
	require_once $path . 'core/class-activator.php';
	$slug = explode( DIRECTORY_SEPARATOR, plugin_basename( __FILE__ ) )[0];
	Activator::activate( $slug );
}

register_activation_hook( __FILE__, '\Personal_Opinion_Tracker\activate' );
function deactivate() {
	$path = trailingslashit( plugin_dir_path( __FILE__ ) );
	require_once $path . 'core/class-deactivator.php';
	$slug = explode( DIRECTORY_SEPARATOR, plugin_basename( __FILE__ ) )[0];
	Deactivator::deactivate( $slug );
}

register_deactivation_hook( __FILE__, '\Personal_Opinion_Tracker\deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'core/class-personal-opinion-tracker.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run() {

	$path = trailingslashit( plugin_dir_path( __FILE__ ) );
	$url  = trailingslashit( plugin_dir_url( __FILE__ ) );
	$slug = explode( DIRECTORY_SEPARATOR, plugin_basename( __FILE__ ) )[0];

	$plugin = new Personal_Opinion_Tracker( $path, $url, $slug, PERSONAL_OPINION_TRACKER_VERSION );
	$plugin->run();

}

run();
