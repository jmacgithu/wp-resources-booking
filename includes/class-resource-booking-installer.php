<?php
/**
 * Fired during plugin activation
 * This class defines all code necessary to run during the plugin's activation / deactivation / uninstall
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 * @author     Your Name <email@example.com>
 */
class Resource_Booking_Installer {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate_resource_booking() {
        if ( ! current_user_can( 'activate_plugins' ) )
            return;

        // At plugin activation creates the database
        $db_man = new Resource_Booking_DB();
        $db_man->check_version();
    }

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function deactivate_resource_booking() {
        if ( ! current_user_can( 'activate_plugins' ) )
            return;
    }

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function uninstall_resource_booking() {
        if ( ! current_user_can( 'activate_plugins' ) )
            return;
    }
}