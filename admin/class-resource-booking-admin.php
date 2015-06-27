<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 * @author     Your Name <email@example.com>
 */
class Resource_Booking_Admin {

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
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/resource-booking-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

//        wp_enqueue_script( 'moment', plugin_dir_url( __FILE__ ) . 'js/moment.min.js', array( "jquery" ), "", true);
        wp_enqueue_script( 'calendarOpts', plugin_dir_url( __FILE__ ) . 'js/calendarOpts.js', array( 'jquery' ), $this->version, true );
//        wp_enqueue_script( "fullcalendar", "//cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.3.1/fullcalendar.min.js", array( "jquery", "calendarOpts" ), "", true );
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/resource-booking-admin.js', array( 'jquery' ), $this->version, false );
        wp_localize_script( $this->plugin_name, 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ) );
    }

    public function generate_resource_menu(){
        // Resources Post Type
        $labels = array(
            'name'               => _x( 'Resources', 'post type general name' ),
            'singular_name'      => _x( 'Resource', 'post type singular name' ),
            'add_new'            => _x( 'Add New', 'resource' ),
            'add_new_item'       => __( 'Add New Resource' ),
            'edit_item'          => __( 'Edit Resource' ),
            'new_item'           => __( 'New Resource' ),
            'all_items'          => __( 'All Resources' ),
            'view_item'          => __( 'View Resource' ),
            'search_items'       => __( 'Search Resources' ),
            'not_found'          => __( 'No resources found' ),
            'not_found_in_trash' => __( 'No resources found in the Trash' ),
            'parent_item_colon'  => '',
            'menu_name'          => 'Resources'
        );
        $args = array(
            'labels'        		=> $labels,
            'description'   		=> 'Holds resources for booking',
            'public'        		=> true,
            'show_in_nav_menus'   	=> false,
            'publicly_queryable' 	=> false,
            'query_var'           	=> false,
            'menu_position' 		=> 20,
            'menu_icon'				=> 'dashicons-screenoptions',
            'supports'      		=> array( 'title', 'thumbnail' ),
            'has_archive'   		=> true,
        );

        register_post_type( 'resource', $args );
    }
}
