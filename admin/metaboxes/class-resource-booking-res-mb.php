<?php
/**
 * Created by PhpStorm.
 * User: Fabio
 * Date: 28/05/2015
 * Time: 15:13
 */

class Resource_Booking_Res_Mb {

    /* Constants */
    const NONCE_NAME        = 'rb_config_meta_box_nonce';
    const RESOURCE_TYPE     = 'rb_resource_type';
    const PAGE_DESCRIPTION_ID  = 'rb_resource_page_description_id';
    const OPEN_FROM         = 'rb_resource_open_from';
    const OPEN_TILL         = 'rb_resource_open_till';
    const WORKS_OVERNIGHT   = 'rb_resource_works_overnight';
    const WORKS_HOLIDAYS    = 'rb_resource_works_holidays';
    const SLOT_MIN          = 'rb_resource_slot_min';
    const SLOT_MAX          = 'rb_resource_slot_max';
    const SLOT_LENGTH       = 'rb_resource_slot_length';

    static $resource_type = array(
        "const" => self::RESOURCE_TYPE,
        "label" => "Resource type",
        "desc" => "Type of the resource like classroom, machine. Resources of the same type are grouped together (default empty).",
        "id" => "resource-type",
        "value" => "",
        "default" => "");
    static $resource_page_description_id = array(
        "const" => self::PAGE_DESCRIPTION_ID,
        "label" => "Description page ID",
        "desc" => "ID of the page containing the description of the resource (default empty).",
        "id" => "description-page-id",
        "value" => "",
        "default" => "");
    static $resource_open_from = array(
        "const" => self::OPEN_FROM,
        "label" => "Open from",
        "desc" => "Opening time. Format: hh:mm, multiple of 30 mins (default 08:00).",
        "id" => "open-from",
        "value" => "",
        "default" => "08:00");
    public static $resource_open_till = array(
        "const" => self::OPEN_TILL,
        "label" => "Closed after",
        "desc" => "Closing time. Format: hh:mm, multiple of 30 mins (default 17:30).",
        "id" => "open-till",
        "value" => "",
        "default" => "17:30");
    public static $resource_works_overnight = array(
        "const" => self::WORKS_OVERNIGHT,
        "label" => "Works overnight",
        "desc" => "Is the resource available overnight? If so, bookings have to start before closing time (default: false).",
        "id" => "works-overnight",
        "value" => "",
        "default" => false);
    public static $resource_works_holidays = array(
        "const" => self::WORKS_HOLIDAYS,
        "label" => "Works on holidays",
        "desc" => "Is the resource available on Saturday / Sunday? (default: false).",
        "id" => "works-holidays",
        "value" => "",
        "default" => false);
    public static $resource_slot_min = array(
        "const" => self::SLOT_MIN,
        "label" => "Min slot length",
        "desc" => "Minimum slot length. Format hh:mm, multiple of 30 mins (default 00:30).",
        "id" => "min-slot",
        "value" => "",
        "default" => "00:30");
    public static $resource_slot_max = array(
        "const" => self::SLOT_MAX,
        "label" => "Max slot length",
        "desc" => "Maximum slot length. Format hh:mm, multiple of 30 mins and &gt; min slot time (default: 05:00).",
        "id" => "max-slot",
        "value" => "",
        "default" => "05:00");
    public static $resource_slot_length = array(
        "const" => self::SLOT_LENGTH,
        "label" => "Slot length",
        "desc" => "Bookings must be multiple of this. Format: hh:mm, multiple of 30 mins (default: 00:30).",
        "id" => "slot-length",
        "value" => "",
        "default" => "00:30");

    /**
     * Add the Metabox
     *
     * @since    0.1.0
     */
    public function rb_add_res_mb($post) {
        add_meta_box(
            'config',
            __( 'Configuration' ),
            array( $this, 'rb_config_res_mb_callback' ),
            'resource',
            'normal',
            'default',
            array($post)
        );
    }

    public function rb_config_res_mb_callback($post){
        wp_nonce_field( basename( __FILE__ ), self::NONCE_NAME );

        $resource_id = $post->ID;
        // The resource id
        $resource_id_field = array(
            "const" => "", // Not used
            'label' => "Shortcode",
            "desc" => "After publishing / updating, copy &amp; insert this short code at the bottom of the resource description page",
            "id" => "shortcode-id",
            "value" => "[resource_booking resource_id=$resource_id]",
            "default" => "[resource_booking resource_id=$resource_id]");

        $fields = array(Resource_Booking_Res_Mb::$resource_type,
            Resource_Booking_Res_Mb::$resource_page_description_id, Resource_Booking_Res_Mb::$resource_open_from,
            Resource_Booking_Res_Mb::$resource_open_till, Resource_Booking_Res_Mb::$resource_works_overnight,
            Resource_Booking_Res_Mb::$resource_works_holidays, Resource_Booking_Res_Mb::$resource_slot_min,
            Resource_Booking_Res_Mb::$resource_slot_max, Resource_Booking_Res_Mb::$resource_slot_length);
        $fields_value = array();

        foreach($fields as $field){
            $value = get_post_meta( $post->ID, $field["const"], true);
            $fields_value[$field["const"]] = $value != "" ? $value : $field["default"];
        }

        // Some fixes
        $fields_value[Resource_Booking_Res_Mb::$resource_page_description_id["const"]] =
            is_numeric($fields_value[Resource_Booking_Res_Mb::$resource_page_description_id["const"]]) ?
            $fields_value[Resource_Booking_Res_Mb::$resource_page_description_id["const"]] : "";

        $fields_value[Resource_Booking_Res_Mb::$resource_works_overnight["const"]] =
            $fields_value[Resource_Booking_Res_Mb::$resource_works_overnight["const"]] ? "checked" : "";
        $fields_value[Resource_Booking_Res_Mb::$resource_works_holidays["const"]] =
            $fields_value[Resource_Booking_Res_Mb::$resource_works_holidays["const"]] ? "checked" : "";

        // Html for the resource meta_box
        echo '<table class="form-table">';
        echo '<tr>
                <th><label for="' . $resource_id_field["id"] . '">' . $resource_id_field["label"] . '</label></th>
                <td>';
        echo '<input type="text" name="' . $resource_id_field["id"] . '" id="' . $resource_id_field["id"] . '" size="50" value="'. $resource_id_field["value"] .'" readonly/>';
        echo '<br /><span class="description">' . $resource_id_field["desc"] . '</span>';
        echo '</td></tr>';
        echo '</table>';

        foreach($fields as $field){
            echo '<table class="form-table">';
            echo '<tr>
                <th><label for="'.$field['id'].'">'.$field['label'].'</label></th>
                <td>';
            if($field["const"] == self::RESOURCE_TYPE || $field["const"] == self::PAGE_DESCRIPTION_ID) {
                echo '<input type="text" name="'.$field["id"].'" id="'.$field["id"].'" value="'.$fields_value[$field["const"]].'"/>';
            }else if($field["const"] == self::WORKS_OVERNIGHT || $field["const"] == self::WORKS_HOLIDAYS){
                echo '<input type="checkbox" name="' . $field['id'] . '" id="' . $field['id'] . '"' . $fields_value[$field["const"]] . '/>';
            }else{
                echo '<input type="time" name="'.$field["id"].'" id="'.$field["id"].'" value="'.$fields_value[$field["const"]].'"/>';
            }
            echo '<br /><span class="description">'.$field['desc'].'</span>';
            echo '</td></tr>';
            echo '</table>';
        }
    }

    /**
     * Store the values selected in the Metaboxes
     *
     * @since    0.1.0
     */
    public function rb_store_mb_values($post_id) {

        $post = get_post($post_id);

        // Verify the nonce
        if(!isset($_POST[self::NONCE_NAME]) || !wp_verify_nonce($_POST[self::NONCE_NAME], basename( __FILE__ ))){
            return $post_id;
        }

        // Get the post type
        $post_type = get_post_type_object( $post->post_type );

        // Check current user permission
        if ( !current_user_can( $post_type->cap->edit_post, $post_id ) ){
            return $post_id;
        }

        $fields = array(Resource_Booking_Res_Mb::$resource_type,
            Resource_Booking_Res_Mb::$resource_page_description_id, Resource_Booking_Res_Mb::$resource_open_from,
            Resource_Booking_Res_Mb::$resource_open_till, Resource_Booking_Res_Mb::$resource_works_overnight,
            Resource_Booking_Res_Mb::$resource_works_holidays, Resource_Booking_Res_Mb::$resource_slot_min,
            Resource_Booking_Res_Mb::$resource_slot_max, Resource_Booking_Res_Mb::$resource_slot_length);

        $fields_db_value = array();
        foreach($fields as $field){
            $fields_db_value[$field["const"]] = get_post_meta( $post->ID, $field["const"], true);
        }

        $fields_post_value = array();
        foreach($fields as $field){
            $fields_post_value[$field["const"]] = isset($_REQUEST[$field["id"]]) ? $_REQUEST[$field["id"]] : $field["default"];
        }

        // Validate the values
        $fields = array(Resource_Booking_Res_Mb::$resource_type);
        foreach($fields as $field){
            $fields_post_value[$field["const"]] = sanitize_text_field($fields_post_value[$field["const"]]);
        }

        $fields = array(Resource_Booking_Res_Mb::$resource_page_description_id);
        foreach($fields as $field){
            $fields_post_value[$field["const"]] = is_numeric($fields_post_value[$field["const"]]) ?
                $fields_post_value[$field["const"]] : $field["default"];
        }

        $fields = array(Resource_Booking_Res_Mb::$resource_open_from,
            Resource_Booking_Res_Mb::$resource_open_till, Resource_Booking_Res_Mb::$resource_slot_min,
            Resource_Booking_Res_Mb::$resource_slot_max, Resource_Booking_Res_Mb::$resource_slot_length);
        foreach($fields as $field){
            $fields_post_value[$field["const"]] = Resource_Booking_Res_Mb::check_if_valid_time_or_default(
                $fields_post_value[$field["const"]], $field["default"]);
        }

        $fields = array(Resource_Booking_Res_Mb::$resource_works_overnight,
            Resource_Booking_Res_Mb::$resource_works_holidays);
        foreach($fields as $field){
            $fields_post_value[$field["const"]] = Resource_Booking_Res_Mb::check_if_valid_checkbox_or_default(
                $fields_post_value[$field["const"]], $field["default"]);
        }

        $fields = array(Resource_Booking_Res_Mb::$resource_type,
            Resource_Booking_Res_Mb::$resource_page_description_id, Resource_Booking_Res_Mb::$resource_open_from,
            Resource_Booking_Res_Mb::$resource_open_till, Resource_Booking_Res_Mb::$resource_works_overnight,
            Resource_Booking_Res_Mb::$resource_works_holidays, Resource_Booking_Res_Mb::$resource_slot_min,
            Resource_Booking_Res_Mb::$resource_slot_max, Resource_Booking_Res_Mb::$resource_slot_length);

        foreach($fields as $field){
            if($fields_post_value[$field["const"]] != $fields_db_value[$field["const"]]){
                update_post_meta( $post_id, $field["const"], $fields_post_value[$field["const"]] );
            }
        }

        $rb_db = new Resource_Booking_DB();
        $resource_name = $post->post_title;
        $resource_type = $fields_post_value[self::RESOURCE_TYPE];
        $rb_db->insert_update_resources_table($post_id, $resource_name, $resource_type);

        return $post_id;
    }

    /**
     * Called when a Resource is deleted. It deletes all the reservations of the the deleted Resource.
     *
     * @since    0.1.0
     */
    public function delete_resource_reservations($post_id) {
        $post = get_post($post_id);
        if( 'resource' === $post->post_type ) {
            $rb_db = new Resource_Booking_DB();
            $rb_db->delete_resource($post_id);
            // postmeta rows are deleted automatically by wp
        }
    }

    private static function check_if_valid_time_or_default($time, $default){
        $time = trim($time);
        $time_array = date_parse_from_format ( "G:i" , $time );
        if(0 == $time_array["warning_count"] && 0 == $time_array["error_count"]){
            return $time;
        }else{
            return $default;
        }
    }

    private static function check_if_valid_checkbox_or_default($checked, $default){
        if("on" == $checked){
            return "on";
        }else if("" == $checked){
            return "";
        }else{
            return $default;
        }
    }
}