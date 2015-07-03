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
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/resource-booking-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
//        wp_enqueue_script( 'calendarOpts', plugin_dir_url( __FILE__ ) . 'js/calendarOpts.js', array( 'jquery' ), $this->version, true );
//        wp_enqueue_script( "fullcalendar", "//cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.3.1/fullcalendar.min.js", array( "jquery", "calendarOpts" ), "", true );
//       wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/resource-booking-admin.js', array( 'jquery' ), $this->version, false );
//        wp_localize_script( $this->plugin_name, 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ) );
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

    public function register_resource_booking_settings() {

        add_submenu_page(
            'options-general.php'   //or 'options.php'
            , 'Resources'
            , 'Resources'
            , 'manage_options'
            , 'resources-management'
            ,  array($this, 'generate_resource_booking_settings' )
        );
    }

    public function generate_resource_booking_settings(){
        $rb = new Resource_Booking();
        // Get something like plugins/resource-booking/
        $rb_plugin_dir = $rb->get_plugin_dir();

        //$css_bootstrap = $rb_plugin_dir . 'css/bootstrap.min.css';
        wp_enqueue_style( "bootstrap", $rb_plugin_dir . 'css/bootstrap.css' );
        wp_enqueue_style( 'bootstrap-datetimepicker', $rb_plugin_dir . 'css/bootstrap-datetimepicker.min.css' );
        wp_enqueue_style( 'chosen-jquery', $rb_plugin_dir . 'css/bootstrap-chosen.css' );

        wp_enqueue_script( "bootstrap", "//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js", array( "jquery" ), "", true );
        wp_enqueue_script( 'moment', $rb_plugin_dir . 'js/moment.min.js', array( "jquery" ), "", true);
        wp_enqueue_script( "bootstrap-datetimepicker", $rb_plugin_dir . "js/bootstrap-datetimepicker.min.js", array( "bootstrap", "moment" ), "", true );
        wp_enqueue_script( "chosen-jquery", $rb_plugin_dir . "js/chosen.jquery.js", array( "jquery" ), "", true );

        wp_enqueue_script( 'calendarOpts', $rb_plugin_dir . 'js/calendarOpts.js', array( 'jquery' ), "", true );
        wp_enqueue_script( 'resources-management', $rb_plugin_dir . 'js/resources-management.js', array( 'jquery' ), "", true );
        wp_localize_script( 'resources-management', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ) );
        ?>
        <section>
            <header><h1>Resource Bookings Settings</h1></header>
            <div class="container">
                <div class="row">
                    <p>From this page, the lab manager can ...</p>
                </div>
                <div class="row">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="select-resources" class="col-md-2 control-label">Select a resource:</label>
                            <div class="col-md-6">
                                <select class="form-control" id="select-resources" name="select-resources">
                                    <optgroup label='Apply to al resources'>
                                        <option value='none'></option>
                                        <option value='all'>---All resources---</option>
                                    </optgroup>
                                    <?php
                                    $resourcesA = get_resource_list();
                                    foreach($resourcesA as $type => $r):
                                        $type = ucwords ($type);
                                        echo "<optgroup label='$type'>";
                                        foreach($r as $resource):
                                            $value = $resource->resource_id;
                                            $title = $resource->resource_name;
                                            echo "<option value='$value'>$title</option>";
                                        endforeach;
                                        echo "</optgroup>";
                                    endforeach;
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <p>Or select only a type of resource</p>
                </div>
                <div class="row">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="select-types" class="col-md-2 control-label">Select a resource type:</label>
                            <div class="col-md-6">
                                <select class="form-control" id="select-types" name="select-types">
                                    <option value='none'></option>
                                    <?php
                                    $resources_types = get_resources_types_list();
                                    foreach($resources_types as $type):
                                        $value = $type->resource_type;
                                        $title = ucwords ($value);
                                        echo "<option value='$value'>$title</option>";
                                    endforeach;
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="select-reasons" class="col-md-2 control-label">Select a reason:</label>
                            <div class="col-md-6">
                                <select class="form-control" id="select-reasons" name="select-reasons">
                                    <option value="1">Clean room closed</option>
                                    <option value="2">Machine(s) out of work</option>
                                    <option value="3">Holidays</option>
                                    <option value="0">Available again</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="notify-users" class="col-md-2 control-label">Notify users via email:</label>
                            <div class="col-md-6">
                                <input type="checkbox" class="form-control" id="notify-users" name="notify-users">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="notify-users" class="col-md-2 control-label">From:</label>
                            <div class="col-md-6">
                                <div class='input-group date col-md-4' id='datetimepickerFrom'>
                                    <input type='text' class="form-control" />
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="notify-users" class="col-md-2 control-label">To:</label>
                            <div class="col-md-6">
                                <div class='input-group date col-md-4' id='datetimepickerTo'>
                                    <input type='text' class="form-control" />
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="duration" class="col-md-2 control-label">Duration:</label>
                            <div  class="col-md-6  form-control-static">
                                <p id="duration" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-2">
                                <input class="btn btn-default" type="submit" value="Submit" id="buttonSubmit">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
<?php
    }
}
