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
        $atts = shortcode_atts( array('resource_id' => '-1' ), $atts );
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

    public function export_csv() {
/*        if ('/download/data' != $_SERVER['REQUEST_URI']) {
            return;
        }

        Resource_Booking_Ajax_Common::check_if_user_logged_in_or_die();

        // Check if client_id exists (as user) or die
        global $user_ID;
        get_currentuserinfo();
        $username = Resource_Booking_Ajax_Common::get_username_or_die($user_ID);

        $client_id = isset($_REQUEST["client_id"]) ? $_REQUEST["client_id"] : 0;
        $start = isset($_REQUEST["start"]) ? $_REQUEST["start"] : null;
        $end = isset($_REQUEST["end"]) ? $_REQUEST["end"] : null;

        if($client_id != $user_ID){
            // Check if user is a labmanager or die
            Resource_Booking_Ajax_Common::check_if_labmanager_or_die();
            // If here, user is labmanager (hopefully)
        }

        if(null != $start){
            $start_parsed = Resource_Booking_Ajax_Common::check_if_valid_date_or_die($start);
        }
        if(null != $end){
            $end_parsed = Resource_Booking_Ajax_Common::check_if_valid_date_or_die($end);
        }
        if(null != $start && null != $end){
            $t_s = $start_parsed->getTimestamp();
            $t_e = $end_parsed->getTimestamp();
            if($t_e < $t_s){
                // And die
                wp_send_json_error(array("message" => "Wrong start - end interval"));
            }
        }


        $rb_db = new Resource_Booking_DB();
        $bookings = $rb_db->list_bookings_by_user_id_start_end($client_id, $start, $end);

        foreach ($bookings as $booking) {
            var_dump($booking);
            exit();
        }


        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");

        // force download
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

//            header("Content-type: application/x-msdownload",true,200);
        header("Content-Disposition: attachment; filename=data.csv");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo 'data';
        wp_die();

            // disposition / encoding on response body
//            header("Content-Disposition: attachment;filename={$filename}");
//            header("Content-Transfer-Encoding: binary");
    */
    }

    public function array2csv(array &$array){
        if (count($array) == 0) {
            return null;
        }
        ob_start();
        $df = fopen("php://output", 'w');
        fputcsv($df, array_keys(reset($array)));
        foreach ($array as $row) {
            fputcsv($df, $row);
        }
        fclose($df);
        return ob_get_clean();
    }
}