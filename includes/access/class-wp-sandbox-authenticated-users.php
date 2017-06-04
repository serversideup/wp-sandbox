<?php

/**
 * Handles the authenticated users in the plugin
 *
 * @link       https://521dimensions.com
 * @since      1.0.0
 *
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/includes
 */

/**
 * Handles the authenticated users in the plugin
 *
 * This class handles all of the methods to determine the authenticated users
 * in the plugin.
 *
 * @since      1.0.0
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/includes/access
 * @author     521 Dimensions <dan@521dimensions.com>
 */
class WP_Sandbox_Authenticated_Users{
	/**
	 * Gets all of the authenticated users
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public static function get_authenticated_users(){
		global $wpdb;

		/*
			If multisite we get all of the
			authenticated users for the current 
			blog.
		*/
		if( is_multisite() ){
			/*
				Get the current blog ID and switch to that blog.
			*/
			$currentBlogID = get_current_blog_id();

			global $switched;
			switch_to_blog(1);

			/*
				Get all of the authenticated users
			*/
			$authenticatedUsers = $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM ".$wpdb->prefix."wps_authenticated_users 
				WHERE blog_id = '%d'",
				$currentBlogID
			), ARRAY_A );

			/*
				Restore the current blog.
			*/
			restore_current_blog();
		}else{
			/*
				Gets all of the authenticated users.
			*/
			$authenticatedUsers = $wpdb->get_results( 
				"SELECT * 
				 FROM ".$wpdb->prefix."wps_authenticated_users",
			ARRAY_A );
		}

		/*
			Returns the authenticated users
		*/
		return $authenticatedUsers;
	}

	/**
	 * Deletes an authenticated user
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param 	 int 		$authenticatedUserID 	ID of the authenticated user being deleted.
	 */
	public static function delete_authenticated_user( $authenticatedUserID ){
		global $wpdb;

		/*
			Checks if the site is multisite
		*/
		if( is_multisite() ){
			/*
				Gets the blog ID for the current blog.
			*/
			$currentBlogID = get_current_blog_id();
			
			/*
				Switches to the top level blog
			*/
			global $switched;
			switch_to_blog(1);

			/*
				Deletes the authenticated user from the blog.
			*/
			$wpdb->query( $wpdb->prepare( 
				"DELETE FROM ".$wpdb->prefix."wps_authenticated_users
				 WHERE id = '%d'
				 AND blog_id = '%d'",
				 $authenticatedUserID,
				 $currentBlogID
			) );

			/*
				Restores the current blog.
			*/
			restore_current_blog();
		}else{
			/*
				Deletes the authenticated user from the blog.
			*/
			$wpdb->query( $wpdb->prepare( 
				"DELETE FROM ".$wpdb->prefix."wps_authenticated_users
				 WHERE id = '%d'",
				 $authenticatedUserID
			) );
		}
	}

	/**
	 * Checks if an IP of a user is valid
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param 	 string 		$ip 	IP Address being checked by the user.
	 */
	public static function check_valid_ip( $ip ){
		global $wpdb;

		/*
			If multisite, we check the IP
			against the current blog.
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
				Get the IP Address from the top level blog.
			*/
			$ipAddress = $wpdb->get_results( $wpdb->prepare(
				"SELECT *
				 FROM ".$wpdb->prefix."wps_authenticated_users
				 WHERE ip = '%s'
				 AND blog_id = '%d'",
				 $ip,
				 $currentBlogID
			), ARRAY_A );

			/*
				Restore the current blog
			*/
			restore_current_blog();
		}else{
			/*
				Get the IP address of the authenticated users.
			*/
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

	/**
	 * Adds an authenticated user
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param 	 int 		$id 				ID of the authenticated user being added.
	 * @param 	 string 	$ip 				IP Address being added for the authenticated user.
	 * @param 	 string 	$expirationTime 	The time the authenticated user is valid for.
	 */
	public static function add_authenticated_user( $id, $ip, $expirationTime ){
		global $wpdb;

		/*
			If multisite, add the user
			to the current blog.
		*/
		if( is_multisite() ){
			/*
				Get the current blog ID
			*/
			$currentBlogID = get_current_blog_id();

			/*
				Switch to the top level blog
			*/
			global $switched;
			switch_to_blog(1);

			/*
				Add the authenticated user.
			*/
			$wpdb->query( $wpdb->prepare(
				"INSERT INTO ".$wpdb->prefix."wps_authenticated_users
				(blog_id, user_id, ip, expires)
				VALUES ( '%d', '%d', %s, '".$expirationTime."')",
				$currentBlogID,
				$id,
				$ip
			) );

			/*
				Restore the current blog
			*/
			restore_current_blog();
		}else{
			/*
				Add the authenticated user to the blog.
			*/
			$wpdb->query( $wpdb->prepare(
				"INSERT INTO ".$wpdb->prefix."wps_authenticated_users
				(user_id, ip, expires)
				VALUES ( '%d', %s, '".$expirationTime."')",
				$id,
				$ip
			) );
		}
	}

	/**
	 * Gets network authenticated users.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public static function get_network_authenticated_users(){
		/*
			Switch to the top level blog
		*/
		global $wpdb;
		global $switched;

		switch_to_blog(1);

		/*
			Get all users on the blog
		*/
		$authenticatedUsers = $wpdb->get_results(
			"SELECT *
			 FROM ".$wpdb->prefix."wps_authenticated_users",
		ARRAY_A );

		/*
			Restore the current blog
		*/
		restore_current_blog();

		/*
			Return all of the authenticated users.
		*/
		return $authenticatedUsers;
	}

	/**
	 * Saves a valid login for the authenticated user allowing them to
	 * view the site not logged in.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function save_valid_login(){
		global $wpdb;

		/*
			Gets the plugin status
		*/
		$pluginStatus =  WP_Sandbox_Settings::get_plugin_status();

		/*
			Ensures the plugin is enabled
		*/
		if( $pluginStatus == '1' ){
			/*
				Checks if the user is
				logged in.
			*/
			if( is_user_logged_in() ){

				/*
					Get the current user authenticated and their ID
				*/
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

					/*
						Get the default expiration time set
					*/
					$defaultExpirationTime = WP_Sandbox_Settings::get_default_expiration_time();

					/*
						Get the expiration date for the user's login.
					*/
					$expirationTime = WP_Sandbox_Settings::get_expiration_time( $defaultExpirationTime );

					/*
						Add the authenticated user.
					*/
					self::add_authenticated_user( $userID, $ip, $expirationTime );
				}
			}
		}
	}
}