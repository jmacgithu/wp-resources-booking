<?php
/**
 * Created by PhpStorm.
 * User: Fabio
 * Date: 21/05/2015
 * Time: 12:27
 */

class Resource_Booking_DB {

    /**
     * The version of the database
     *
     * @since    0.1.0
     */
    public static $rb_db_version = 1;
    private static $booking_table = 'rb_bookings';
    private static $resources_table = 'rb_resources';

    /**
     * Creates the database
     *
     * @since    0.1.0
     */
    public static function create_tables() {
        global $wpdb;
        $booking_table_name = $wpdb->prefix . Resource_Booking_DB::$booking_table;

        // Sql query
        $sql = "CREATE TABLE $booking_table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			resource_id mediumint(9) NOT NULL,
			created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			user_id bigint(20) NOT NULL,
			username varchar(250) NOT NULL,
			start datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			end datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			UNIQUE KEY id (id)
		) ENGINE = INNODB
		DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";

        // Compare with db if there are differences executes the sql
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        // Update database version
        update_option( 'rb_db_version', Resource_Booking_DB::$rb_db_version );
    }

    public function insert_booking($resource_id, $user_id, $username, $start, $end){
        global $wpdb;
        $booking_table_name = $wpdb->prefix . Resource_Booking_DB::$booking_table;

        $data = array(
            'resource_id' => $resource_id,
            'created' => current_time( 'mysql' ),
            'user_id' => $user_id,
            'username' => $username,
            'start' => $start,
            'end' => $end,
        );

        $result = $wpdb->insert( $booking_table_name, $data );
        $new_res_id = $wpdb->insert_id;

        if( !$result ) {
            return false;
        } else {
            $prepared = $wpdb->prepare(
                "SELECT * from $booking_table_name WHERE id = %d",
                $new_res_id
            );

            $booking = $wpdb->get_row( $prepared );
            return $booking;
        }
    }

    public function update_booking($booking_id, $resource_id, $user_id, $start, $end){
        global $wpdb;
        $booking_table_name = $wpdb->prefix . Resource_Booking_DB::$booking_table;
        $data = array(
            'start' => $start,
            'end' => $end,
        );

        $where = array("id" => $booking_id,
            "resource_id" => $resource_id);
        if($user_id != null){
            $where["user_id"] = $user_id;
        }

        $result = $wpdb->update( $booking_table_name,
            $data,
            $where);
        $new_res_id = $booking_id;

        if( !$result ) {
            return false;
        } else {
            $prepared = $wpdb->prepare(
                "SELECT * from $booking_table_name WHERE id = %d",
                $new_res_id
            );
            $booking = $wpdb->get_row( $prepared );
            return $booking;
        }
    }

    /**
     *
     * Deletes a booking of a user.
     *
     * @param $booking_id
     * @param $resource_id
     * @param $user_id
     * @return bool
     */
    public function delete_booking($booking_id, $resource_id, $user_id = null){
        global $wpdb;
        $booking_table_name = $wpdb->prefix . Resource_Booking_DB::$booking_table;
        $data = array(
            'id' => $booking_id,
            'resource_id' => $resource_id,
        );

        if($user_id != null){
            $data["user_id"] = $user_id;
        }

        $result = $wpdb->delete( $booking_table_name, $data);

        if( !$result ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Checks if a resource exists (has been created)
     *
     * @param $resource_id
     * @return bool
     */
    public function check_resource_id( $resource_id ){
        $resource_rows = $this->get_resource_info($resource_id);
        if(count($resource_rows) > 0){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Checks if a resource exists (has been created)
     *
     * @param $resource_id
     * @return array
     */
    public function get_resource_info( $resource_id ){
        global $wpdb;
        $table_name = $wpdb->prefix . "posts";
        $prepared = $wpdb->prepare(
            "SELECT * from $table_name WHERE ID = %d AND post_type='resource' LIMIT 1;",
            $resource_id
        );
        $resource_rows = $wpdb->get_results( $prepared );
        return $resource_rows;
    }

    /**
     * Get the meta of a resource
     */
    public function get_resource_meta( $resource_id ){
        global $wpdb;
        $table_name = $wpdb->prefix . "postmeta";
        $prepared = $wpdb->prepare(
            "SELECT * from $table_name WHERE post_id = %d;",
            $resource_id
        );
        $resource_meta_rows = $wpdb->get_results( $prepared );
        return $resource_meta_rows;
    }

    /**
     *
     * Sanitize params please.
     *
     * @param $resource_id
     * @param $start yyyy-mm-dd HH:mm:ss ?
     * @param $end yyyy-mm-dd HH:mm:ss ?
     * @return mixed
     */
    public function list_bookings_by_resource_id_start_end( $resource_id, $start, $end ) {
        global $wpdb;
        $booking_table_name = $wpdb->prefix . Resource_Booking_DB::$booking_table;

        $prepared = $wpdb->prepare(
            "SELECT * from $booking_table_name WHERE resource_id = %d AND start >= %s AND end <= %s;",
            $resource_id,
            $start,
            $end
        );
        $booking_rows = $wpdb->get_results( $prepared );

        return $booking_rows;
    }

    /**
     *
     * Sanitize params please.
     *
     * @param $resource_id
     * @param $start yyyy-mm-dd HH:mm:ss
     * @param $end yyyy-mm-dd HH:mm:ss
     * @return mixed
     */
    public function list_bookings_by_user_id_start_end( $user_id, $start = null, $end = null) {
        global $wpdb;
        $booking_table_name = $wpdb->prefix . Resource_Booking_DB::$booking_table;
        $resources_table_name = $wpdb->prefix . Resource_Booking_DB::$resources_table;

        if(null != $start && null != $end){
            $prepared = $wpdb->prepare(
                "SELECT b.*, r.resource_name, r.resource_type from $booking_table_name as b
                    LEFT JOIN $resources_table_name as r on (b.resource_id = r.resource_id)
                    WHERE user_id = %d AND start >= %s AND end <= %s;",
                $user_id,
                $start,
                $end
            );
        }else if(null != $start){
            $prepared = $wpdb->prepare(
                "SELECT b.*, r.resource_name, r.resource_type from $booking_table_name as b
                    LEFT JOIN $resources_table_name as r on (b.resource_id = r.resource_id)
                    WHERE user_id = %d AND start >= %s;",

                $user_id,
                $start
            );
        }else if(null != $end){
            $prepared = $wpdb->prepare(
                "SELECT b.*, r.resource_name, r.resource_type from $booking_table_name as b
                    LEFT JOIN $resources_table_name as r on (b.resource_id = r.resource_id)
                    WHERE user_id = %d AND end <= %s;",
                $user_id,
                $end
            );
        }else{
            // All bookings
            $prepared = $wpdb->prepare(
                "SELECT b.*, r.resource_name, r.resource_type from $booking_table_name as b
                    LEFT JOIN $resources_table_name as r on (b.resource_id = r.resource_id)
                    WHERE user_id = %d;",
                $user_id
            );
        }

        $booking_rows = $wpdb->get_results( $prepared );
        return $booking_rows;
    }

    public function check_if_overlapping($resource_id, $booking_id, $start, $end){
        global $wpdb;
        $booking_table_name = $wpdb->prefix . Resource_Booking_DB::$booking_table;

        if($booking_id == null){
            // s1, e1 new start & stop
            // Overlapping if exists a row:
            // s1 < start && e1 > start
            // s1 > start && s1 < stop
            $prepared = $wpdb->prepare(
                "SELECT * from $booking_table_name WHERE
                  resource_id = %d AND (
                  ((start > %s) AND (start < %s)) OR
                  ((start < %s) AND (end > %s))
                  ) LIMIT 1;",
                $resource_id,
                $start,
                $end,
                $start,
                $start
            );
        }else{
            // A booking can overlap itself on update
            // s1, e1 new start & stop
            // Overlapping if exists a row:
            // s1 < start && e1 > start
            // s1 > start && s1 < stop
            // b1 != booking
            $prepared = $wpdb->prepare(
                "SELECT * from $booking_table_name WHERE
                  resource_id = %d AND id != %d AND (
                  ((start > %s) AND (start < %s)) OR
                  ((start < %s) AND (end > %s))
                  ) LIMIT 1;",
                $resource_id,
                $booking_id,
                $start,
                $end,
                $start,
                $start
            );
        }
        $booking_rows = $wpdb->get_results( $prepared );
        return $booking_rows;
    }

    public function insert_update_resources_table($post_id, $resource_name, $resource_type){
        global $wpdb;
        $resources_table_name = $wpdb->prefix . Resource_Booking_DB::$resources_table;
        $prepared = $wpdb->prepare(
            "INSERT INTO $resources_table_name (resource_id, resource_name, resource_type)
            VALUES(%d, %s, %s) ON DUPLICATE KEY UPDATE resource_name=%s, resource_type=%s",
            $post_id,$resource_name,$resource_type,$resource_name,$resource_type);
        $resource_id = $wpdb->query( $prepared );
        return $resource_id;
    }

    public function get_resources_list( $resource_type = null ){
        global $wpdb;
        $resources_table_name = $wpdb->prefix . Resource_Booking_DB::$resources_table;
        if(null == $resources_type) {
            // Can't prepare without parameters...
            $sql = "SELECT * FROM $resources_table_name ORDER BY resource_type ASC, resource_name ASC;";
            $resource_rows = $wpdb->get_results( $sql );
        }else{
            $prepared = $wpdb->prepare(
            "SELECT * from $resources_table_name WHERE
                  resource_type = %s
                  ORDER BY resource_type ASC, resource_name ASC;",
                $resource_type);
            $resource_rows = $wpdb->get_results( $prepared );
        }
        return $resource_rows;
    }

    public function delete_resource($resource_id){
        global $wpdb;
        $resources_table_name = $wpdb->prefix . Resource_Booking_DB::$resources_table;
        $data = array(
            'resource_id' => $resource_id,
        );

        $result = $wpdb->delete( $resources_table_name, $data);
        if( !$result ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Check if the if the database needs to be updated
     *
     * @since    0.1.0
     */
    public function check_version() {
        global $wpdb;
        $booking_table_name = $wpdb->prefix . Resource_Booking_DB::$booking_table;
        if( get_option( 'rb_db_version' ) != Resource_Booking_DB::$rb_db_version
            || $wpdb->get_var( "SHOW TABLES LIKE '$booking_table_name'" ) != $booking_table_name ) {
            Resource_Booking_DB::create_tables();
        }
    }
}