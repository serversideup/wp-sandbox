<?php

/**
 * Handles all of the IP Access Rules
 *
 * @link       https://521dimensions.com
 * @since      1.0.0
 *
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/includes/access
 */

/**
 * Handles all of the IP Access Rules
 *
 * The CRUD for managing the IP Access
 *
 * @since      1.0.0
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/includes/access
 * @author     521 Dimensions <dan@521dimensions.com>
 */
class WP_Sandbox_IP{
	/**
	 * Get all of the authenticated IPs
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public static function get_ips(){
		global $wpdb;

		/*
			If multisite, get the authenticated IPs
		*/
		if( is_multisite() ){
			$currentBlogID = get_current_blog_id();

			global $switched;
			switch_to_blog(1);

			/*
				Retrieves all of the ips with
				respect to the current site.
			*/
			$ips = $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM ".$wpdb->prefix."wps_ips
				WHERE blog_id = '%d'",
				$currentBlogID
			), ARRAY_A );

			restore_current_blog();
		}else{
			/*
				Retrieves all of the ips.
			*/
			$ips = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."wps_ips", ARRAY_A );
		}

		/*
			Return all of the ips for the site
		*/
		return $ips;
	}

	/**
	 * Adds an authenticated IP
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var 	 string 	$ip 		IP being added
	 * @var 	 string 	$expiration The expiration time
	 * @var 	 string 	$userID 	The ID of the user adding the rule
	 */
	public static function add_ip( $ip, $expiration, $userID ){
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
				Clenses and adds the IP
				to the database.
			*/
			$wpdb->query( $wpdb->prepare(
				"INSERT INTO ".$wpdb->prefix."wps_ips
				( blog_id, added_by, ip, expires )
				VALUES( %d, %d, %s, '".$expiration."')",
				$currentBlogID,
				$userID,
				$ip
			) );

			$ipID = $wpdb->insert_id;

			restore_current_blog();
		}else{
			/*
				Clenses and adds the IP
				to the database.
			*/
			$wpdb->query( $wpdb->prepare(
				"INSERT INTO ".$wpdb->prefix."wps_ips
				( added_by, ip, expires )
				VALUES( %d, %s, '".$expiration."')",
				$userID,
				$ip
			) );

			$ipID = $wpdb->insert_id;
		}

		/*
			Returns the newly added ID.
		*/
		return $ipID;
	}

	/**
	 * Deletes an IP address rule
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var 	 string 	$ip 		IP being deleted
	 */
	public static function delete_ip( $ipID ){
		global $wpdb;

		/*
			Checks if the site is multisite
		*/
		if( is_multisite() ){
			/*
				Gets the current blog ID
			*/
			$currentBlogID = get_current_blog_id();

			global $switched;
			switch_to_blog(1);

			/*
				Deletes the IP rule for the blog
			*/
			$wpdb->query( $wpdb->prepare(
				"DELETE FROM ".$wpdb->prefix."wps_ips
				WHERE id = '%d'
				AND blog_id = '%d'",
				$ipID,
				$currentBlogID
			) );

			restore_current_blog();
		}else{
			/*
				Deletes the IP rule
			*/
			$wpdb->query( $wpdb->prepare(
				"DELETE FROM ".$wpdb->prefix."wps_ips
				WHERE id = '%d'",
				$ipID
			) );
		}
	}

	/**
	 * Checks to see if an IP is valid.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var 	 string 	$ipAddress 		IP being tested
	 */
	public static function check_valid_ip( $ipAddress ){
		global $wpdb;

		/*
			Checks to see if the setup is multisite
		*/
		if( is_multisite() ){
			/*
				Get the current blog ID
			*/
			$currentBlogID = get_current_blog_id();

			/*
				Switch to the top level blog.
			*/
			global $switched;
			switch_to_blog(1);

			/*
				Check to see if a record is in the table for the IP for
				the site.
			*/
			$ips = $wpdb->get_results( $wpdb->prepare(
				"SELECT ip
				 FROM ".$wpdb->prefix."wps_ips
				 WHERE ip = '%s'
				 AND blog_id = '%d'",
				 $ipAddress,
				 $currentBlogID
			), ARRAY_A );

			restore_current_blog();
		}else{
			/*
				Checks to see if the IP is a valid IP for the blog
			*/
			$ips = $wpdb->get_results( $wpdb->prepare(
				"SELECT ip
				 FROM ".$wpdb->prefix."wps_ips
				 WHERE ip = '%s'",
				 $ipAddress
			), ARRAY_A );
		}

		/*
			If there is nothing returned then
			the IP is invalid and returns false
		*/
		if( !empty( $ips ) ){
			return true;
		}else{
			return false;
		}
	}
}
