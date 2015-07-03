<?php
/**
 * Created by PhpStorm.
 * User: Fabio
 * Date: 21/05/2015
 * Time: 11:30
 */

class Resource_Booking_Ajax_Admin {

    private $rb_db;

    public function __construct(){
        $this->rb_db = new Resource_Booking_DB();
    }

    // Logged in OK
    // Labmanager OK
    // POST OK
    // Dates OK
    // Overlapping OK
    public function res_admin_insert_booking_callback(){
        // Check if user is logged in or die
        Resource_Booking_Ajax_Common::check_if_user_logged_in_or_die();

        // Check if user is a labmanager or die
        Resource_Booking_Ajax_Common::check_if_labmanager_or_die();
        // If here, user is labmanager (hopefully)

        $resource_id    = isset($_POST['resource_id']) ? intval($_POST['resource_id'], 10) : 0;
        $client_id      = isset($_POST['client_id']) ? intval($_POST['client_id'], 10) : 0;
        $start          = isset($_POST['start']) ? $_POST['start'] : null;
        $end            = isset($_POST['end']) ? $_POST['end'] : null;
        $details        = isset($_POST['details']) ? $_POST['details'] : "";

        // Check if client_id exists (as user) or die
        $client_username = Resource_Booking_Ajax_Common::get_username_or_die($client_id);

        // Check if resource exists or die
        $resource_info = Resource_Booking_Ajax_Common::get_resource_info_or_die($this->rb_db, $resource_id, "array");

        // Check if valid dates && valid interval
        Resource_Booking_Ajax_Common::check_if_valid_start_end_i_u_d_or_die($start, $end, $resource_info);

        // Check if it doesn't overlap any booking or die
        Resource_Booking_Ajax_Common::check_if_not_overlapping_or_die($this->rb_db, $resource_id, null, $start, $end);

        // Sanitize details
        $details = strip_tags($details);

        // Validation done - good to insert data

        // Insert the new booking
        $booking = $this->rb_db->insert_booking(
            $resource_id, $client_id, $client_username, $start, $end, $details
        );
        if(false === $booking){
            // And die
            wp_send_json_error(array("message" => "Could not insert the booking!"));
        }else{
            $response = new stdClass();
            $response->success = true;
            $response->booking = array(
                "id" => $booking->id,
                "resource_id" => $booking->resource_id,
                "user_id" => $booking->user_id,
                "username" => esc_html($booking->username),
                "start" => $booking->start,
                "end" => $booking->end,
                "details" => esc_html($booking->details),
                "personal" => true,
            );
            echo json_encode($response);
            wp_die(); // this is required to terminate immediately and return a proper response
        }
    }

    // Logged in OK
    // Labmanager OK
    // POST OK
    // Dates OK
    // Overlapping OK
    public function res_admin_update_booking_callback(){
        // Check if user is logged in or die
        Resource_Booking_Ajax_Common::check_if_user_logged_in_or_die();

        // Check if user is a labmanager or die
        Resource_Booking_Ajax_Common::check_if_labmanager_or_die();
        // If here, user is labmanager (hopefully)

        $booking_id     = isset($_POST['id']) ? intval($_POST['id'], 10) : 0;
        $resource_id    = isset($_POST['resource_id']) ? intval($_POST['resource_id'], 10) : 0;
        $start          = isset($_POST['start']) ? $_POST['start'] : null;
        $end            = isset($_POST['end']) ? $_POST['end'] : null;

        //Check if resource exists
        $resource_info = Resource_Booking_Ajax_Common::get_resource_info_or_die($this->rb_db, $resource_id, "array");

        // Check if valid dates && valid interval
        Resource_Booking_Ajax_Common::check_if_valid_start_end_i_u_d_or_die($start, $end, $resource_info);

        // Check if it doesn't overlap any booking or die
        Resource_Booking_Ajax_Common::check_if_not_overlapping_or_die($this->rb_db, $resource_id, $booking_id, $start, $end);

        // Validation done - good to update data

        // Update the reservation
        $booking = $this->rb_db->update_booking(
            // User is null because labmanager overrides
            $booking_id, $resource_id, null, $start, $end
        );
        if(false === $booking){
            // And die
            wp_send_json_error(array("message" => "Could not update the reservation!"));
        }else{
            $response = new stdClass();
            $response->success = true;
            $response->booking = array(
                "id" => $booking->id,
                "resource_id" => $booking->resource_id,
                "user_id" => $booking->user_id,
                "username" => esc_html($booking->username),
                "start" => $booking->start,
                "end" => $booking->end,
                "details" => esc_html($booking->details),
                "personal" => true,
            );
            echo json_encode($response);
            wp_die(); // this is required to terminate immediately and return a proper response
        }
    }

    // Logged OK
    // Labmanager OK
    // POST OK
    public function res_admin_delete_booking_callback(){
        // Check if user is logged in or die
        Resource_Booking_Ajax_Common::check_if_user_logged_in_or_die();

        // Check if user is a labmanager or die
        Resource_Booking_Ajax_Common::check_if_labmanager_or_die();
        // If here, user is labmanager (hopefully)

        $booking_id     = isset($_POST['id']) ? intval($_POST['id'], 10) : 0;
        $resource_id    = isset($_POST['resource_id']) ? intval($_POST['resource_id'], 10) : 0;
        $start          = isset($_POST['start']) ? $_POST['start'] : null;
        $end            = isset($_POST['end']) ? $_POST['end'] : null;

        //Check if resource exists
        $resource_info = Resource_Booking_Ajax_Common::get_resource_info_or_die($this->rb_db, $resource_id, "array");

        // Check if valid dates && valid interval
        Resource_Booking_Ajax_Common::check_if_valid_start_end_i_u_d_or_die($start, $end, $resource_info);

        // Validation done - good to delete data

        // Delete the booking
        $booking = $this->rb_db->delete_booking(
            // User_id is null because labmanager overrides
            $booking_id,
            $resource_id,
            null
        );
        if(false === $booking){
            // And die
            wp_send_json_error(array("message" => "Could not delete the reservation!"));
        }else{
            $response = new stdClass();
            $response->success = true;
            $response->booking = array(
                "id" => $booking_id,
            );
            echo json_encode($response);
            wp_die(); // this is required to terminate immediately and return a proper response
        }
    }

    // Logged OK
    // Labmanager OK
    // Dates OK
    public function res_admin_list_bookings_by_resource_id_start_end_callback(){
        // Check if user is logged in or die
        Resource_Booking_Ajax_Common::check_if_user_logged_in_or_die();

        // Check if user is a labmanager or die
        Resource_Booking_Ajax_Common::check_if_labmanager_or_die();
        // If here, user is labmanager (hopefully)

        $resource_id    = isset($_POST['resource_id']) ? intval($_POST['resource_id'], 10) : 0;
        $start          = isset($_POST['start']) ? $_POST['start'] : null;
        $end            = isset($_POST['end']) ? $_POST['end'] : null;;

        //Check if resource exists
        Resource_Booking_Ajax_Common::check_resource_id_or_die($this->rb_db, $resource_id);

        // Check if valid dates && valid interval
        Resource_Booking_Ajax_Common::check_if_valid_start_end_date_calendar_or_die($start, $end);

        // Validation done - good to query data

        // Search for bookings
        $bookings = $this->rb_db->list_bookings_by_resource_id_start_end(
            $resource_id, $start, $end
        );
        $response = new stdClass();
        $response->success = true;
        $response->events = array();
        foreach ($bookings as $booking) {
            $user_id = $booking->user_id;
            $personal = true;
            $response->events[] = array(
                "id" => $booking->id,
                "resource_id" => $booking->resource_id,
                "user_id" => $user_id,
                "username" => esc_html($booking->username),
                "start" => $booking->start,
                "end" => $booking->end,
                "details" => esc_html($booking->details),
                "personal" => $personal,
                "closed" => $booking->closed,
            );
        }
        echo json_encode($response);
        wp_die(); // this is required to terminate immediately and return a proper response
    }

    // Logged OK
    // Labmanager OK
    // POST OK
    // TODO Test dates
    public function res_admin_user_bookings_callback(){
        // Check if user is logged in or die
        Resource_Booking_Ajax_Common::check_if_user_logged_in_or_die();

        // Check if user is a labmanager or die
        Resource_Booking_Ajax_Common::check_if_labmanager_or_die();
        // If here, user is labmanager (hopefully)

        $client_id      = isset($_POST['client_id']) ? intval($_POST['client_id'], 10) : 0;
        $start          = isset($_POST['start']) ? $_POST['start'] : null;
        $end            = isset($_POST['end']) ? $_POST['end'] : null;;

        $client_data = Resource_Booking_Ajax_Common::get_user_data_or_die($client_id);
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

        // Validation done

        $response = new stdClass();
        $response->success = true;
        $response->userInfo = array();
        $response->userInfo["display_name"] = sanitize_user($client_data->display_name, true);
        $response->userInfo["login"] = sanitize_user($client_data->user_login, true);
        $response->userInfo["email"] = sanitize_user($client_data->user_email, true);
        $response->bookings = array();
        $bookingsA = $this->rb_db->list_bookings_by_user_id_start_end($client_id, $start, $end);
        foreach($bookingsA as $booking){
            $response->bookings[] = array(
                "booking_id" => $booking->id,
                "resource_id" => $booking->resource_id,
                "resource_name" => $booking->resource_name,
                "resource_type" => $booking->resource_type,
                "created" => $booking->created,
                "user_id" => $booking->user_id,
                "username" => esc_html($booking->username),
                "start" => $booking->start,
                "end" => $booking->end,
                "details" => esc_html($booking->details),
            );
        }
        echo json_encode($response);
        wp_die();
    }

    public function res_admin_insert_out_of_work_callback(){
        // Check if user is logged in or die
        Resource_Booking_Ajax_Common::check_if_user_logged_in_or_die();

        // Check if user is a labmanager or die
        Resource_Booking_Ajax_Common::check_if_labmanager_or_die();
        // If here, user is labmanager (hopefully)

        $resource_id    = isset($_POST['resource_id']) ? $_POST['resource_id'] : 'none';
        $type           = isset($_POST['type']) ? $_POST['type'] : 'none';
        $reason         = isset($_POST['reason']) ? intval($_POST['reason']) : 0;

        $start          = isset($_POST['start']) ? $_POST['start'] : null;
        $end            = isset($_POST['end']) ? $_POST['end'] : null;

        Resource_Booking_Ajax_Common::check_if_valid_date_with_time_or_die($start);
        Resource_Booking_Ajax_Common::check_if_valid_date_with_time_or_die($end);

        if('none' == $resource_id && 'none' == $type){
            // And die
            wp_send_json_error(array("message" => "Select at least a resource or a type"));
        }

        if('none' != $resource_id && 'none' != $type){
            // And die
            wp_send_json_error(array("message" => "Select only a resource or a type"));
        }

        $reason_string = "";
        switch($reason){
            case 0: break;
            case 1: $reason_string = "Clean room closed"; break;
            case 2: $reason_string = "Resource out of work"; break;
            case 3: $reason_string = "Holiday"; break;
            default:
                wp_send_json_error(array("message" => "Wrong reason"));
        }


        $resources = array();
        if('none' != $resource_id){
            // Resource selected
            if('all' == $resource_id){
                //Check if resource exists
                $resourcesA = $this->rb_db->get_resources_list();
                foreach($resourcesA as $resource){
                    $resource_id = $resource->resource_id;
                    $resource_info = Resource_Booking_Ajax_Common::get_resource_info_or_die($this->rb_db, $resource_id, "array");
                    $resources[] = $resource_info;
                }
            }else{
                $resource_id = intval($resource_id, 10);
                $resource_info = Resource_Booking_Ajax_Common::get_resource_info_or_die($this->rb_db, $resource_id, "array");
                $resources[] = $resource_info;
            }
        }else{
            // Type selected
            $resourcesA = $this->rb_db->get_resources_list($type);
            foreach($resourcesA as $resource){
                $resource_id = $resource->resource_id;
                $resource_info = Resource_Booking_Ajax_Common::get_resource_info_or_die($this->rb_db, $resource_id, "array");
                $resources[] = $resource_info;
            }
        }

        foreach($resources as $resource){
            $resource_id = $resource->resourceInfo["resource_id"];
            $bookingsA = $this->rb_db->list_bookings_by_resource_id_start_end($resource_id, $start, $end);
            foreach($bookingsA as $booking) {
                $booking_id = $booking->id;
                $this->rb_db->delete_booking($booking_id, $resource_id, null);
            }

            if($reason != 0){
                $booking = $this->rb_db->insert_booking(
                    $resource_id, 1, $reason_string, $start, $end, $reason_string, $reason
                );
            }
        }

        $response = new StdClass();
        $response->success = true;
        echo json_encode($response);
        wp_die();
    }
}