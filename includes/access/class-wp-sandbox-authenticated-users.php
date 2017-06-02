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
class WP_Sandbox_Authenticated_Users{
	/*------------------------------------------------
		Gets all of the authenticated users from 
		the database.
	------------------------------------------------*/
	public static function getAuthenticatedUsers(){
		global $wpdb;

		/*
			If multisite we get all of the
			authenticated users for the current 
			blog.
		*/
		if( is_multisite() ){
			$currentBlogID = get_current_blog_id();

			global $switched;
			switch_to_blog(1);

			$authenticatedUsers = $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM ".$wpdb->prefix."wps_authenticated_users 
				WHERE blog_id = '%d'",
				$currentBlogID
			), ARRAY_A );

			restore_current_blog();
		}else{
			$authenticatedUsers = $wpdb->get_results( 
				"SELECT * 
				 FROM ".$wpdb->prefix."wps_authenticated_users",
			ARRAY_A );
		}

		return $authenticatedUsers;
	}

	/*------------------------------------------------
		Deletes an authenticated user from the
		database.
	------------------------------------------------*/
	public static function deleteAuthenticatedUser( $authenticatedUserID ){
		global $wpdb;

		if( is_multisite() ){
			$currentBlogID = get_current_blog_id();
			
			global $switched;

			switch_to_blog(1);

			$wpdb->query( $wpdb->prepare( 
				"DELETE FROM ".$wpdb->prefix."wps_authenticated_users
				 WHERE id = '%d'
				 AND blog_id = '%d'",
				 $authenticatedUserID,
				 $currentBlogID
			) );

			restore_current_blog();
		}else{
			$wpdb->query( $wpdb->prepare( 
				"DELETE FROM ".$wpdb->prefix."wps_authenticated_users
				 WHERE id = '%d'",
				 $authenticatedUserID
			) );
		}
	}

	/*------------------------------------------------
		Gets an IP for a user to check to see
		if the IP is authenticable.
	------------------------------------------------*/
	public static function check_valid_ip( $ip ){
		global $wpdb;

		/*
			If multisite, we check the IP
			against the current blog.
		*/
		if( is_multisite() ){
			$currentBlogID = get_current_blog_id();

			global $switched;
			switch_to_blog(1);

			$ipAddress = $wpdb->get_results( $wpdb->prepare(
				"SELECT *
				 FROM ".$wpdb->prefix."wps_authenticated_users
				 WHERE ip = '%s'
				 AND blog_id = '%d'",
				 $ip,
				 $currentBlogID
			), ARRAY_A );

			restore_current_blog();
		}else{
			$ipAddress = $wpdb->get_results( $wpdb->prepare(
				"SELECT *
				 FROM ".$wpdb->prefix."wps_authenticated_users
				 WHERE ip = '%s'",
				 $ip
			), ARRAY_A );
		}

		/*
			If there is nothing returned then
			the IP is invalid and returns false
		*/
		if( !empty( $ipAddress ) ){
			return true;
		}else{
			return false;
		}
	}

	/*------------------------------------------------
		Adds an authenticated user
	------------------------------------------------*/
	public static function addAuthenticatedUser( $id, $ip, $expirationTime ){
		global $wpdb;

		/*
			If multisite, add the user
			to the current blog.
		*/
		if( is_multisite() ){
			$currentBlogID = get_current_blog_id();

			global $switched;
			switch_to_blog(1);

			$wpdb->query( $wpdb->prepare(
				"INSERT INTO ".$wpdb->prefix."wps_authenticated_users
				(blog_id, user_id, ip, expires)
				VALUES ( '%d', '%d', %s, '".$expirationTime."')",
				$currentBlogID,
				$id,
				$ip
			) );

			restore_current_blog();
		}else{
			$wpdb->query( $wpdb->prepare(
				"INSERT INTO ".$wpdb->prefix."wps_authenticated_users
				(user_id, ip, expires)
				VALUES ( '%d', %s, '".$expirationTime."')",
				$id,
				$ip
			) );
		}
	}

	/*------------------------------------------------
		Gets all authenticated users. This is only
		called from a multisite instance so we know
		it's multisite.
	------------------------------------------------*/
	public static function getNetworkAuthenticatedUsers(){
		global $wpdb;
		global $switched;

		switch_to_blog(1);

		$authenticatedUsers = $wpdb->get_results(
			"SELECT *
			 FROM ".$wpdb->prefix."wps_authenticated_users",
		ARRAY_A );

		restore_current_blog();

		return $authenticatedUsers;
	}

	public function save_valid_login(){
		global $wpdb;
		/*
			Checks if plugin is enabled
		*/
		$pluginStatus =  WP_Sandbox_Settings::getPluginStatus();

		if( $pluginStatus == '1' ){
			/*
				Checks if the user is
				logged in.
			*/
			if( is_user_logged_in() ){

				$current_user = wp_get_current_user();
				
				$userID = $current_user->data->ID;

				/* 
					Gets the IP for the user 
				*/
				$ip = WP_Sandbox_Check_Valid_Testing::get_ip();

				/*
					If the IP is not in any ranges or networks or
					not individually added anywhere, then we add
					it because the user has authentication rights.
				*/
				if( !self::check_valid_ip( $ip ) 
					&& !WP_Sandbox_IP::check_valid_ip( $ip )  
					&& !WP_Sandbox_IP_Range::check_valid_ip_range( $ip ) 
					&& !WP_Sandbox_Subnet::check_valid_ip_subnet( $ip ) ){

					$defaultExpirationTime = WP_Sandbox_Settings::getDefaultExpirationTime();

					$expirationTime = WP_Sandbox_Settings::getExpirationTime( $defaultExpirationTime );

					self::addAuthenticatedUser( $userID, $ip, $expirationTime );
				}
			}
		}
	}
}