<?php
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link        http://example.com
 * @since       1.0.0
 *
 * @package     Resource_Booking
 * @subpackage  Resource_Booking/public
 * @author     Your Name <email@example.com>
 */

class Resource_Booking_Public {

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
	 * Initializes the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
//		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/resource-booking-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
// Check notes in https://codex.wordpress.org/Function_Reference/wp_localize_script
//        wp_localize_script( $this->plugin_name, 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ) );
    }
    /**
     * return the HTML to be replaced to the shortcode
     *
     * @since    0.1.0
     */
    public function res_booking_shortcode( $atts, $content = "" ) {
        // Extracts attributes and fill the missing with default values
        $atts = shortcode_atts( array('resource_id' => '0' ), $atts );
        $resource_id = $atts["resource_id"];

        $resource = get_post($resource_id);
        if( !$resource ) {
            return '<h4>The resource '.$resource_id.' is no more available. Please contact the site administrator.</h4>';
        }

        $resource_name = $resource->post_title;
        
        $resourceInfo = array(
            "resource_id" => $resource_id,
            "resource_name" => $resource_name,
        );

        $contents = '<script type="text/javascript">var _resourceInfo = ' . json_encode($resourceInfo) . ';</script>';
        return $contents;
    }
}