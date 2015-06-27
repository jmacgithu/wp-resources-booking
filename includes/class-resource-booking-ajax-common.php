<?php
/**
 * Created by PhpStorm.
 * User: Fabio
 * Date: 10/06/2015
 * Time: 11:30
 */

class Resource_Booking_Ajax_Common {

    /* User functions */
    public static function check_if_user_logged_in_or_die(){
        if(! is_user_logged_in()){
            // And die
            wp_send_json_error(array("message" => "User not logged in"));
        }
    }

    public static function get_user_data_or_die($user_id){
        $user_data = get_user_by( "id", $user_id );
        if(false === $user_data){
            // And die
            wp_send_json_error(array("message" => "Wrong user id"));
        }
        return $user_data;
    }

    public static function get_username_or_die($user_id){
        $user_data = Resource_Booking_Ajax_Common::get_user_data_or_die($user_id);
        $username = $user_data->display_name != "" ? $user_data->display_name . " (" . $user_data->user_email . ")" : $user_data->user_email;
        $username = sanitize_user($username, true);
        return $username;
    }

    public static function check_if_labmanager_or_die(){
        if ( ! current_user_can('labmanager') ){
            // And die
            wp_send_json_error(array("message" => "User not authorized"));
        }
    }
    /* End user functions */

    /* Resource functions */
    public static function check_resource_id_or_die(Resource_Booking_DB $rb_db, $resource_id){
        if( ! $rb_db->check_resource_id( $resource_id) ){
            // And die
            wp_send_json_error(array("message" => "Wrong resource"));
        }
    }

    /**
     * @param Resource_Booking_DB $rb_db
     * @param $resource_id
     * @param string $format Either 'json' or 'array'
     * @return mixed
     */
    public static function get_resource_info_or_die(Resource_Booking_DB $rb_db, $resource_id, $format = "json"){
        $resourceA = $rb_db->get_resource_info( $resource_id);
        if( ! $resourceA || count( $resourceA ) != 1 ){
            // And die
            wp_send_json_error(array("message" => "Wrong resource"));
        }

        $resource = $resourceA[0];
        $resourceMetaA = $rb_db->get_resource_meta( $resource_id );

        $response = new stdClass();
        $response->success = true;

        $response->resourceInfo = array(
            "resource_id" => $resource->ID,
            "title" => $resource->post_title,
        );

        foreach($resourceMetaA as $meta){
            switch($meta->meta_key){
                case Resource_Booking_Res_Mb::RESOURCE_TYPE: $response->resourceInfo["resource_type"] = $meta->meta_value; break;
                case Resource_Booking_Res_Mb::PAGE_DESCRIPTION_ID: $response->resourceInfo["page_description_id"] = $meta->meta_value; break;
                case Resource_Booking_Res_Mb::OPEN_FROM: $response->resourceInfo["open_from"] = $meta->meta_value; break;
                case Resource_Booking_Res_Mb::OPEN_TILL: $response->resourceInfo["open_till"] = $meta->meta_value; break;
                case Resource_Booking_Res_Mb::WORKS_OVERNIGHT: $response->resourceInfo["works_overnight"] = $meta->meta_value; break;
                case Resource_Booking_Res_Mb::WORKS_HOLIDAYS: $response->resourceInfo["works_holidays"] = $meta->meta_value; break;
                case Resource_Booking_Res_Mb::SLOT_MIN: $response->resourceInfo["slot_min"] = $meta->meta_value; break;
                case Resource_Booking_Res_Mb::SLOT_MAX: $response->resourceInfo["slot_max"] = $meta->meta_value; break;
                case Resource_Booking_Res_Mb::SLOT_LENGTH: $response->resourceInfo["slot_length"] = $meta->meta_value; break;
                default: break;
            }
        }
        if(!isset($response->resourceInfo["resource_type"])){
            $response->resourceInfo["resource_type"] = Resource_Booking_Res_Mb::$resource_type["default"];
        }
        if(!isset($response->resourceInfo["page_description_id"])){
            $response->resourceInfo["page_description_id"] = Resource_Booking_Res_Mb::$resource_page_description_id["default"];
        }else{
            $link = get_permalink($response->resourceInfo["page_description_id"]);
            if($link){
                $response->resourceInfo["page_description_id"] = $link;
            }else{
                $response->resourceInfo["page_description_id"] = Resource_Booking_Res_Mb::$resource_page_description_id["default"];
            }
        }
        if(!isset($response->resourceInfo["open_from"])){
            $response->resourceInfo["open_from"] = Resource_Booking_Res_Mb::$resource_open_from["default"];
        }
        if(!isset($response->resourceInfo["open_till"])){
            $response->resourceInfo["open_till"] = Resource_Booking_Res_Mb::$resource_open_till["default"];
        }
        if(!isset($response->resourceInfo["works_overnight"])){
            $response->resourceInfo["works_overnight"] = Resource_Booking_Res_Mb::$resource_works_overnight["default"];
        }
        if(!isset($response->resourceInfo["works_holidays"])){
            $response->resourceInfo["works_holidays"] = Resource_Booking_Res_Mb::$resource_works_holidays["default"];
        }
        if(!isset($response->resourceInfo["slot_min"])){
            $response->resourceInfo["slot_min"] = Resource_Booking_Res_Mb::$resource_slot_min["default"];
        }
        if(!isset($response->resourceInfo["slot_max"])){
            $response->resourceInfo["slot_max"] = Resource_Booking_Res_Mb::$resource_slot_max["default"];
        }
        if(!isset($response->resourceInfo["slot_length"])){
            $response->resourceInfo["slot_length"] = Resource_Booking_Res_Mb::$resource_slot_length["default"];
        }

        if("json" == $format){
            echo json_encode($response);
            wp_die(); // this is required to terminate immediately and return a proper response
        }else{
            return $response;
        }
    }
    /* Resource functions */

    /* Date functions */
    /**
     * @param $date Date with time string in this format: Y-m-d*H:i:sP i.e. 2015-06-22T21:30:00+02:00
     * @return DateTime
     */
    public static function check_if_valid_date_with_time_or_die($date){
        if(null === $date || "" == trim($date) || false === $date){
            // And die
            wp_send_json_error(array("message" => "Wrong date time format"));
        }
        $date_parsed = DateTime::createFromFormat('Y-m-d*H:i:sP', $date);
        if(false === $date_parsed){
            // And die
            wp_send_json_error(array("message" => "Wrong date time format"));
        }
        return $date_parsed;
    }

    /**
     * @param $date Date string in this format: Y-m-d i.e. 2015-06-22
     * @return DateTime
     */
    public static function check_if_valid_date_or_die($date){
        if(null === $date || "" == trim($date) || false === $date){
            // And die
            wp_send_json_error(array("message" => "Wrong date format"));
        }
        $date_parsed = DateTime::createFromFormat('Y-m-d', $date);
        if(false === $date_parsed){
            // And die
            wp_send_json_error(array("message" => "Wrong date format"));
        }
        return $date_parsed;
    }

    public static function check_if_start_time_before_end_or_die(DateTime $start, DateTime $end){
        // DateTimes can be compared with normal operators
        if($start >= $end){
            // And die
            wp_send_json_error(array("message" => "Wrong start - end interval (t_start >= t_end)"));
        }
    }

    public static function check_if_start_time_after_now_or_die(DateTime $start){
        // DateTimes can be compared with normal operators
        $now = new DateTime("now");
        if($start <= $now){
            // And die
            wp_send_json_error(array("message" => "Wrong start time ( <= now )"));
        }
    }

    /**
     * Check date time for insert update delete
     *
     * @param $start Date with time string in this format: Y-m-d*H:i:sP i.e. 2015-06-22T21:30:00+02:00
     * @param $end Date with time string in this format: Y-m-d*H:i:sP i.e. 2015-06-22T21:30:00+02:00
     * @param $resource
     */
    public static function check_if_valid_start_end_i_u_d_or_die($start, $end, $resource){
        // Check if start date is valid
        $start_parsed = Resource_Booking_Ajax_Common::check_if_valid_date_with_time_or_die($start);
        // Check if end date is valid
        $end_parsed = Resource_Booking_Ajax_Common::check_if_valid_date_with_time_or_die($end);
        // Check if start < end
        Resource_Booking_Ajax_Common::check_if_start_time_before_end_or_die($start_parsed, $end_parsed);
        // Check if start > now (and because of before, end > now)
        Resource_Booking_Ajax_Common::check_if_start_time_after_now_or_die($start_parsed);
        // Perform various tests on the booking interval
        Resource_Booking_Ajax_Common::check_if_valid_booking_for_resource_or_die($start_parsed, $end_parsed, $resource);
    }

    /**
     * @param DateTime $start
     * @param DateTime $end
     * @param $resource
     */
    public static function check_if_valid_booking_for_resource_or_die(DateTime $start, DateTime $end, $resource){
        // Check if min booking slot < booking length < max booking slot
        $time_array_min = date_parse_from_format("G:i", $resource->resourceInfo["slot_min"]);
        $min_slot_in_min = $time_array_min["hour"] * 60 + $time_array_min["minute"];

        $time_array_max = date_parse_from_format("G:i", $resource->resourceInfo["slot_max"]);
        $max_slot_in_min = $time_array_max["hour"] * 60 + $time_array_max["minute"];

        $duration_in_min = ($end->getTimestamp() - $start->getTimestamp()) / 60;
        if ( $duration_in_min < $min_slot_in_min ){
            // And die
            wp_send_json_error(array("message" => "Booking duration < min slot"));
        }
        if ( $duration_in_min > $max_slot_in_min ){
            // And die
            wp_send_json_error(array("message" => "Booking duration > max slot"));
        }

        // Check if booking length is multiple of slot length
        $slot_length_in_min = 30;
        if ( $duration_in_min % $slot_length_in_min != 0 ){
            // And die
            wp_send_json_error(array("message" => "Booking duration must be multiple of $slot_length_in_min mins"));
        }

        // Check if start time is multiple of slot length
        // And because duration is multiple of slot length (checked above)
        // also end time should be multiple
        $start_time_in_min = $start->getTimestamp() / 60;
        if ( $start_time_in_min % $slot_length_in_min != 0 ){
            // And die
            wp_send_json_error(array("message" => "Booking start time must be multiple of $slot_length_in_min mins"));
        }

        // Check if earliest booking time < booking time < max booking time
        $earliest = $resource->resourceInfo["open_from"] . ":00";
        $latest = $resource->resourceInfo["open_till"] . ":00";
        $booking_earliest = $start->format("Y-m-d") . "T" . $earliest . $start->format("P");
        $booking_earliest_parsed = DateTime::createFromFormat('Y-m-d*H:i:sP', $booking_earliest);
        if ( $start < $booking_earliest_parsed ){
            // And die
            wp_send_json_error(array("message" => "Booking start time must be later than $earliest"));
        }

        // If resource is not open overnight, some more checks
        if(! $resource->resourceInfo["works_overnight"]){
            // End time <= open_till
            $booking_latest = $end->format("Y-m-d") . "T" . $latest . $end->format("P");
            $booking_latest_parsed = DateTime::createFromFormat('Y-m-d*H:i:sP', $booking_latest);
            if ( $end > $booking_latest_parsed ){
                // And die
                wp_send_json_error(array("message" => "Booking end time must be earlier than $latest"));
            }
            // Check if start & end are on the same day
            if( $start->format("Y-m-d") != $end->format("Y-m-d") ){
                // And die
                wp_send_json_error(array("message" => "Booking start and end must be on the same day"));
            }
            // Check if end days are not on Sat/Sun
            if ($end->format('N') == '6' || $end->format('N') == 7){
                // And die
                wp_send_json_error(array("message" => "Bookings can not be on Saturdays/Sundays"));
            }
        }

        // Check if start days are not on Sat/Sun
        if ($start->format('N') == '6' || $start->format('N') == 7){
            // And die
            wp_send_json_error(array("message" => "Bookings can not be on Saturdays/Sundays"));
        }

        // If here, it booking time has been checked successfully
    }

    /**
     * Checks id the dates for the calendar are valid
     *
     * @param $start Date string in this format: Y-m-d i.e. 2015-06-22
     * @param $end Date string in this format: Y-m-d i.e. 2015-06-22
     */
    public static function check_if_valid_start_end_date_calendar_or_die($start, $end){
        // Check if start date is valid
        $start_parsed = Resource_Booking_Ajax_Common::check_if_valid_date_or_die($start);
        // Check if end date is valid
        $end_parsed = Resource_Booking_Ajax_Common::check_if_valid_date_or_die($end);
        // Check if start < end
        Resource_Booking_Ajax_Common::check_if_start_time_before_end_or_die($start_parsed, $end_parsed);
    }

    /* End date functions */

    public static function check_if_not_overlapping_or_die(Resource_Booking_DB $rb_db, $resource_id, $booking_id, $start, $end){
        $bookings = $rb_db->check_if_overlapping($resource_id, $booking_id, $start, $end);
        if ( count($bookings) > 0 ){
            // And die
            wp_send_json_error(array("message" => "Booking overlapping"));
        }
    }
}