<?php

/**
 * Handles the CRUD for IP Ranges
 *
 * @link       https://521dimensions.com
 * @since      1.0.0
 *
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/includes
 */

/**
 * Handles the CRUD for IP Ranges
 *
 * All CRUD methods for IP Ranges are in this class including checking if an
 * IP is in a valid range.
 *
 * @since      1.0.0
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/includes/access
 * @author     521 Dimensions <dan@521dimensions.com>
 */
class WP_Sandbox_IP_Range{
	/**
	 * Gets all of the IP Ranges in the app.
	 *
	 * @since      1.0.0
	 * @access     public
	 */
	public static function get_ip_ranges(){
		global $wpdb;

		/*
			If the site install is multi site, we get the
			IP ranges for the specific site.
		*/
		if( is_multisite() ){
			$currentBlogID = get_current_blog_id();

			/*
				Switch to the top level blog to query the table.
			*/
			global $switched;
			switch_to_blog(1);

			/*
				Retrieves all of the ip ranges with
				respect to the current site.
			*/
			$ranges = $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM ".$wpdb->prefix."wps_ip_ranges
				WHERE blog_id = '%d'",
				$currentBlogID
			), ARRAY_A );

			restore_current_blog();
		}else{
			/*
				Retrieves all of the ip ranges.
			*/
			$ranges = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."wps_ip_ranges", ARRAY_A );
		}

		return $ranges;
	}

	/**
	 * Adds an IP Range
	 *
	 * @since      	1.0.0
	 * @access     	public
	 * @var 	   		string 		$startIP 		The beginning of the IP Range
	 * @var 	   		string 		$endIP 			The end of the IP Range
	 * @var 	   		string 		$expiration		The time the IP Range expires
	 * @var 	  	 	int 			$userID 		The ID of the user adding the range.
	 */
	public static function add_range( $startIP, $endIP, $expiration, $userID ){
		global $wpdb;

		/*
			Checks if it's multisite and grabs
			the blog ID if needed.
		*/
		if( is_multisite() ){
			$currentBlogID = get_current_blog_id();

			global $switched;
			switch_to_blog(1);

			/*
				Clenses and adds the rule
				to the database.
			*/
			$wpdb->query( $wpdb->prepare(
				"INSERT INTO ".$wpdb->prefix."wps_ip_ranges
				( blog_id, added_by, start_ip, end_ip, expires )
				VALUES( %d, %d, %s, %s, '".$expiration."')",
				$currentBlogID,
				$userID,
				$startIP,
				$endIP
			) );

			$rangeID = $wpdb->insert_id;

			restore_current_blog();
		}else{
			/*
				Clenses and adds the rule
				to the database.
			*/
			$wpdb->query( $wpdb->prepare(
				"INSERT INTO ".$wpdb->prefix."wps_ip_ranges
				( added_by, start_ip, end_ip, expires )
				VALUES( %d, %s, %s, '".$expiration."')",
				$userID,
				$startIP,
				$endIP
			) );

			$rangeID = $wpdb->insert_id;
		}

		/*
			Returns the newly added ID.
		*/
		return $rangeID;
	}

	/**
	 * Deletes an IP range
	 *
	 * @since      	1.0.0
	 * @access     	public
	 * @var 	   		int			$rangeID 		The ID of the range being deleted
	 */
	public static function delete_range( $rangeID ){
		global $wpdb;

		/*
			If multisite, we switch to the top level blog
		*/
		if( is_multisite() ){
			global $switched;
			switch_to_blog(1);

			/*
				Deletes the IP Range by ID
			*/
			$wpdb->query( $wpdb->prepare(
				"DELETE FROM ".$wpdb->prefix."wps_ip_ranges
				WHERE id = '%d'",
				$rangeID
			) );

			restore_current_blog();
		}else{
			/*
				Deletes the IP Range by ID
			*/
			$wpdb->query( $wpdb->prepare(
				"DELETE FROM ".$wpdb->prefix."wps_ip_ranges
				WHERE id = '%d'",
				$rangeID
			) );
		}
	}

	/**
	 * Checks to see if the IP address is in a valid range of IPs.
	 * Help from: http://stackoverflow.com/questions/18336908/php-check-if-ip-address-is-in-a-range-of-ip-addresses
	 *
	 * @since      	1.0.0
	 * @access     	public
	 * @var 	   		int			$rangeID 		The ID of the range being deleted
	 */
	public static function check_valid_ip_range( $ipAddress ){
		global $wpdb;

		/*
			Checks to see if the site is multisite
		*/
		if( is_multisite() ){

			/*
				Gets the current blog ID.
			*/
			$currentBlogID = get_current_blog_id();

			global $switched;
			switch_to_blog(1);

			/*
				Gets IP ranges
			*/
			$ipRanges = $wpdb->get_results( $wpdb->prepare(
				"SELECT *
				 FROM ".$wpdb->prefix."wps_ip_ranges
				 WHERE blog_id = '%d'",
				 $currentBlogID
			), ARRAY_A );

			restore_current_blog();
		}else{
			/*
				Gets IP ranges
			*/
			$ipRanges = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."wps_ip_ranges", ARRAY_A );

		}

		/*
			Loops through all of the IP Ranges
			to see if the ip address is within
			range.
		*/
		foreach( $ipRanges as $ipRange ){
				$min    = ip2long($ipRange['start_ip']);
    		$max    = ip2long($ipRange['end_ip']);
    		$needle = ip2long($ipAddress);

    		/*
					If the IP is in a range, then return true.
    		*/
    		if( ( $needle >= $min ) AND ( $needle <= $max ) ){
    			return true;
    		}
		}

		/*
			Returns false if the ip is not in range.
		*/
		return false;
	}
}
