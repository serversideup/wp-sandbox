<?php

/**
 * Fired during plugin activation
 *
 * @link       https://521dimensions.com
 * @since      1.0.0
 *
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/includes/access
 * @author     521 Dimensions <dan@521dimensions.com>
 */
class WP_Sandbox_IP{
	public static function getIPs(){
		global $wpdb;

		if( is_multisite() ){
			$currentBlogID = get_current_blog_id();

			global $switched;
			switch_to_blog(1);

			/*
				Retrieves all of the ips with
				respect to the current site.
			*/
			$ranges = $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM ".$wpdb->prefix."wps_ips
				WHERE blog_id = '%d'",
				$currentBlogID
			), ARRAY_A );

			restore_current_blog();
		}else{
			/*
				Retrieves all of the ips.
			*/
			$ranges = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."wps_ips", ARRAY_A );
		}

		return $ranges;
	}

	public static function addIP( $ip, $expiration, $userID ){
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

	public static function deleteIP( $ipID ){
		global $wpdb;

		global $switched;
		switch_to_blog(1);

		$wpdb->query( $wpdb->prepare(
			"DELETE FROM ".$wpdb->prefix."wps_ips
			WHERE id = '%d'",
			$ipID
		) );

		restore_current_blog();
	}

	public static function check_valid_ip( $ipAddress ){
		global $wpdb;

		if( is_multisite() ){
			$currentBlogID = get_current_blog_id();
			
			global $switched;
			switch_to_blog(1);

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

	public static function getNetworkAuthenticatedIPs(){
		global $wpdb;
		global $switched;

		switch_to_blog(1);

		$authenticatedIPs = $wpdb->get_results(
			"SELECT *
			 FROM ".$wpdb->prefix."wps_ips",
		ARRAY_A );

		restore_current_blog();

		return $authenticatedIPs;
	}
}
