<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link                http://example.com
 * @since               1.0.0
 * @package             Resource_Booking
 *
 * @wordpress-plugin
 * Plugin Name:       Resource Booking
 * Plugin URI:        http://example.com/plugin-name-uri/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Fabio Kruger
 * Author URI:        http://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       resource-booking
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-resource-booking-installer.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-resource-booking-installer.php
 */
register_activation_hook( __FILE__, array( 'Resource_Booking_Installer', 'activate_resource_booking' ) );
/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-resource-booking-installer.php
 */
register_deactivation_hook( __FILE__, array( 'Resource_Booking_Installer', 'deactivate_resource_booking' ) );
/**
 * The code that runs during plugin uninstall.
 * This action is documented in includes/class-resource-booking-installer.php
 */
register_deactivation_hook( __FILE__, array( 'Resource_Booking_Installer', 'uninstall_resource_booking' ) );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-resource-booking.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_resource_booking() {
	$plugin = new Resource_Booking();
	$plugin->run();
}
run_resource_booking();
