<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 * @author     Your Name <email@example.com>
 */
class Resource_Booking {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Plugin_Name_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'resource-booking';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();

		$this->define_admin_hooks();
		$this->define_public_hooks();

        $this->define_ajax_admin_hooks();
        $this->define_ajax_public_hooks();
    }

    public function get_plugin_dir(){
        return plugin_dir_url( __FILE__ ) . '../';
    }

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Plugin_Name_Loader. Orchestrates the hooks of the plugin.
	 * - Plugin_Name_i18n. Defines internationalization functionality.
	 * - Plugin_Name_Admin. Defines all hooks for the admin area.
	 * - Plugin_Name_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-resource-booking-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-resource-booking-i18n.php';

        /**
         * The class responsible for managing the database
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-resource-booking-db.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-resource-booking-admin.php';

        /**
         * The class responsible for defining the metabox for the Resource adimn page
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/metaboxes/class-resource-booking-res-mb.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-resource-booking-public.php';

        /**
         * Common functions for ajax callbacks
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-resource-booking-ajax-common.php';

        /**
         * The class responsible for defining ajax callbacks
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-resource-booking-ajax.php';

        /**
         * The class responsible for defining admin ajax callbacks
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-resource-booking-ajax-admin.php';

        $this->loader = new Resource_Booking_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Resource_Booking_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Resource_Booking_Admin( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'init', $plugin_admin, 'generate_resource_menu' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        $plugin_admin_metaboxes = new Resource_Booking_Res_Mb();
        $this->loader->add_action( 'add_meta_boxes_resource', $plugin_admin_metaboxes, 'rb_add_res_mb' );
        // Store resource postmeta hook
        $this->loader->add_action( 'save_post_resource', $plugin_admin_metaboxes, 'rb_store_mb_values' );
        $this->loader->add_action( 'delete_post', $plugin_admin_metaboxes, 'delete_resource_reservations' );

//        add_menu_page( $page_title, $menu_title, $capability, $menu_slug, array( $plugin_admin, 'function_name' ), $icon_url, $position );
    }

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Resource_Booking_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

        // Add the shortcode
        add_shortcode( 'resource_booking', array( $plugin_public, 'res_booking_shortcode' ) );
	}

    private function define_ajax_admin_hooks(){
        $ajax_callbacks_admin = new Resource_Booking_Ajax_Admin();

        $this->loader->add_action( 'wp_ajax_res_admin_insert_booking', $ajax_callbacks_admin, 'res_admin_insert_booking_callback');
        $this->loader->add_action( 'wp_ajax_res_admin_update_booking', $ajax_callbacks_admin, 'res_admin_update_booking_callback');
        $this->loader->add_action( 'wp_ajax_res_admin_delete_booking', $ajax_callbacks_admin, 'res_admin_delete_booking_callback');
        $this->loader->add_action( 'wp_ajax_res_admin_list_bookings_by_resource_id_start_end', $ajax_callbacks_admin, 'res_admin_list_bookings_by_resource_id_start_end_callback');
        $this->loader->add_action( 'wp_ajax_res_admin_user_bookings', $ajax_callbacks_admin, 'res_admin_user_bookings_callback' );
    }

    private function define_ajax_public_hooks(){
        $ajax_callbacks = new Resource_Booking_Ajax();

        $this->loader->add_action( 'wp_ajax_res_user_insert_booking', $ajax_callbacks, 'res_user_insert_booking_callback');
        $this->loader->add_action( 'wp_ajax_res_user_update_booking', $ajax_callbacks, 'res_user_update_booking_callback');
        $this->loader->add_action( 'wp_ajax_res_user_delete_booking', $ajax_callbacks, 'res_user_delete_booking_callback');
        $this->loader->add_action( 'wp_ajax_res_list_bookings_by_resource_id_start_end', $ajax_callbacks, 'res_list_bookings_by_resource_id_start_end_callback');
        $this->loader->add_action( 'wp_ajax_res_user_bookings', $ajax_callbacks, 'res_user_bookings_callback' );
        $this->loader->add_action( 'wp_ajax_res_resource_info', $ajax_callbacks, 'res_resource_info_callback' );
    }

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Plugin_Name_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}