<?php
	class WPSAuthenticatedUsers{
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
				$authenticatedUsers = $wpdb->pget_results( 
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

			global $switched;
			switch_to_blog(1);

			$wpdb->query( $wpdb->prepare( 
				"DELETE FROM ".$wpdb->prefix."wps_authenticated_users
				 WHERE id = '%d'",
				 $authenticatedUserID
			) );

			restore_current_blog();
		}

		/*------------------------------------------------
			Gets an IP for a user to check to see
			if the IP is authenticable.
		------------------------------------------------*/
		public static function checkValidIP( $ip ){
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
	}
?>