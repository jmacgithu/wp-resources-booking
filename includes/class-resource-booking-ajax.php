<?php
/**
 * Created by PhpStorm.
 * User: Fabio
 * Date: 21/05/2015
 * Time: 11:30
 */

class Resource_Booking_Ajax {

    private $rb_db;

    public function __construct(){
        $this->rb_db = new Resource_Booking_DB();
    }

    // Logged OK
    // POST OK
    // User data OK
    // Dates OK
    // Overlapping OK
    public function res_user_insert_booking_callback(){
        // Check if user is logged in or die
        Resource_Booking_Ajax_Common::check_if_user_logged_in_or_die();

        $resource_id    = isset($_POST['resource_id']) ? intval($_POST['resource_id'], 10) : 0;
        $start          = isset($_POST['start']) ? $_POST['start'] : null;
        $end            = isset($_POST['end']) ? $_POST['end'] : null;

        // Check if client_id exists (as user) or die
        global $user_ID;
        get_currentuserinfo();
        $username = Resource_Booking_Ajax_Common::get_username_or_die($user_ID);

        // Check if resource exists or die
        //Resource_Booking_Ajax_Common::check_resource_id_or_die($this->rb_db, $resource_id);
        $resource_info = Resource_Booking_Ajax_Common::get_resource_info_or_die($this->rb_db, $resource_id, "array");

        // Check if valid dates && valid interval
        Resource_Booking_Ajax_Common::check_if_valid_start_end_i_u_d_or_die($start, $end, $resource_info);

        // Check if it doesn't overlap any booking or die
        Resource_Booking_Ajax_Common::check_if_not_overlapping_or_die($this->rb_db, $resource_id, null, $start, $end);

        // Validation done - good to insert data

        //Insert the new booking
        $booking = $this->rb_db->insert_booking(
            $resource_id, $user_ID, $username, $start, $end
        );
        if(false === $booking){
            // And die
            wp_send_json_error(array("message" => "Could not insert the reservation!"));
        }else{
            $response = new stdClass();
            $response->success = true;
            $response->booking = array(
                "id" => $booking->id,
                "resource_id" => $booking->resource_id,
                "user_id" => $booking->user_id,
                "username" => $booking->username,
                "start" => $booking->start,
                "end" => $booking->end,
                "personal" => true,
            );
            echo json_encode($response);
            wp_die(); // this is required to terminate immediately and return a proper response
        }
    }

    // Logged OK
    // POST OK
    // User data OK
    // Dates OK
    // Overlapping OK
    public function res_user_update_booking_callback(){
        // Check if user is logged in or die
        Resource_Booking_Ajax_Common::check_if_user_logged_in_or_die();

        $booking_id     = isset($_POST['id']) ? intval($_POST['id'], 10) : 0;
        $resource_id    = isset($_POST['resource_id']) ? intval($_POST['resource_id'], 10) : 0;
        $start          = isset($_POST['start']) ? $_POST['start'] : null;
        $end            = isset($_POST['end']) ? $_POST['end'] : null;

        // Check if client_id exists (as user) or die
        global $user_ID;
        get_currentuserinfo();
        Resource_Booking_Ajax_Common::get_username_or_die($user_ID);

        //Check if resource exists or die
        Resource_Booking_Ajax_Common::check_resource_id_or_die($this->rb_db, $resource_id);
        $resource_info = Resource_Booking_Ajax_Common::get_resource_info_or_die($this->rb_db, $resource_id, "array");

        // Check if valid dates && valid interval
        Resource_Booking_Ajax_Common::check_if_valid_start_end_i_u_d_or_die($start, $end, $resource_info);

        // Check if it doesn't overlap any booking or die
        Resource_Booking_Ajax_Common::check_if_not_overlapping_or_die($this->rb_db, $resource_id, $booking_id, $start, $end);

        // Validation done - good to update data

        // Update the booking
        $booking = $this->rb_db->update_booking(
            $booking_id, $resource_id, $user_ID, $start, $end
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
                "username" => $booking->username,
                "start" => $booking->start,
                "end" => $booking->end,
                "personal" => true,
            );
            echo json_encode($response);
            wp_die(); // this is required to terminate immediately and return a proper response
        }
    }

    // Logged OK
    // POST OK
    // User data OK
    // Overlapping OK
    public function res_user_delete_booking_callback(){
        // Check if user is logged in or die
        Resource_Booking_Ajax_Common::check_if_user_logged_in_or_die();

        $booking_id    = isset($_POST['id']) ? intval($_POST['id'], 10) : 0;
        $resource_id    = isset($_POST['resource_id']) ? intval($_POST['resource_id'], 10) : 0;
        $start          = isset($_POST['start']) ? $_POST['start'] : null;
        $end            = isset($_POST['end']) ? $_POST['end'] : null;

        // Check if client_id exists (as user) or die
        global $user_ID;
        get_currentuserinfo();
        Resource_Booking_Ajax_Common::get_username_or_die($user_ID);

        //Check if resource exists or die
        // Resource_Booking_Ajax_Common::check_resource_id_or_die($this->rb_db, $resource_id);
        $resource_info = Resource_Booking_Ajax_Common::get_resource_info_or_die($this->rb_db, $resource_id, "array");

        // Check if valid dates && valid interval
        Resource_Booking_Ajax_Common::check_if_valid_start_end_i_u_d_or_die($start, $end, $resource_info);

        // Validation done - good to update data

        // Delete the booking
        $booking = $this->rb_db->delete_booking(
            $booking_id,
            $resource_id,
            $user_ID
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
    // POST OK
    // User data OK
    // Dates OK
    public function res_list_bookings_by_resource_id_start_end_callback(){
        // Check if user is logged in or die
        Resource_Booking_Ajax_Common::check_if_user_logged_in_or_die();

        $resource_id    = isset($_POST['resource_id']) ? intval($_POST['resource_id'], 10) : 0;
        $start          = isset($_POST['start']) ? $_POST['start'] : null;
        $end            = isset($_POST['end']) ? $_POST['end'] : null;

        // Check if client_id exists (as user) or die
        global $user_ID;
        get_currentuserinfo();
        Resource_Booking_Ajax_Common::get_username_or_die($user_ID);

        //Check if resource exists or die
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
        foreach($bookings as $booking){
            if($booking->user_id == $user_ID){
                $user_id = $booking->user_id;
                $username = $booking->username;
                $personal = true;
            }else{
                $user_id = "";
                $username = "Reserved";
                $personal = false;
            }
            $response->events[] = array(
                "id" => $booking->id,
                "resource_id" => $booking->resource_id,
                "user_id" => $user_id,
                "username" => $username,
                "start" => $booking->start,
                "end" => $booking->end,
                "personal" => $personal,
            );
        }
        echo json_encode($response);
        wp_die(); // this is required to terminate immediately and return a proper response
    }

    // Logged OK
    // POST OK
    // Date OK
    public function res_user_bookings_callback(){
        // Check if user is logged in or die
        Resource_Booking_Ajax_Common::check_if_user_logged_in_or_die();

        $start          = isset($_POST['start']) ? $_POST['start'] : null;
        $end            = isset($_POST['end']) ? $_POST['end'] : null;;

        // Check if client_id exists (as user) or die
        global $user_ID;
        get_currentuserinfo();
        $client_data = Resource_Booking_Ajax_Common::get_user_data_or_die($user_ID);

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
        $bookingsA = $this->rb_db->list_bookings_by_user_id_start_end($user_ID, $start, $end);
        foreach($bookingsA as $booking){
            $response->bookings[] = array(
                "booking_id" => $booking->id,
                "resource_id" => $booking->resource_id,
                "created" => $booking->created,
                "user_id" => $booking->user_id,
                "username" => $booking->username,
                "start" => $booking->start,
                "end" => $booking->end,
            );
        }
        echo json_encode($response);
        wp_die();
    }

    // Logged OK
    // POST OK
    public function res_resource_info_callback(){
        // Check if user is logged in or die
        Resource_Booking_Ajax_Common::check_if_user_logged_in_or_die();

        $resource_id    = isset($_POST['resource_id']) ? intval($_POST['resource_id'], 10) : 0;

        // Validation will be done in the next function

        Resource_Booking_Ajax_Common::get_resource_info_or_die($this->rb_db, $resource_id, 'json');
    }
}