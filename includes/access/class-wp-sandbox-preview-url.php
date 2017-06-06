<?php

/**
 * Handles the CRUD for the Sandbox Preview URL
 *
 * @link       https://521dimensions.com
 * @since      1.0.0
 *
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/includes
 */

/**
 * Handles the CRUD for the Sandbox Preview URL
 *
 * All CRUD methods for Sandbox Preivew URL are in this class including checking if an
 * Sandbox Preview URL is in a valid range.
 *
 * @since      1.0.0
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/includes/access
 * @author     521 Dimensions <dan@521dimensions.com>
 */
class WP_Sandbox_Preview_URL{
	/**
	 * Creates a new preview URL
	 *
	 * @since      1.0.0
	 * @access     public
	 */
	public static function create_new_preview_url(){
		global $wpdb;

		/*
			Create the new preview hash
		*/
		$previewHash = self::generate_preview_hash();

		/*
			If the install is multisite,
			we need to update the current
			blog's preview hash
		*/
		if( is_multisite() ){
			$currentBlogID = get_current_blog_id();

			global $switched;
			switch_to_blog(1);

			/*
				Update all of the settings with the new preview URL
			*/
			$wpdb->query( $wpdb->prepare(
				"UPDATE ".$wpdb->prefix."wps_settings
				SET setting_value = '%s'
				WHERE blog_id = '%d'
				AND setting_name = 'Preview Hash'",
				$previewHash,
				$currentBlogID
			) );

			restore_current_blog();
		}else{
			/*
				Update the settings for the site with the new preview URL
			*/
			$wpdb->query( $wpdb->prepare(
				"UPDATE ".$wpdb->prefix."wps_settings
				SET setting_value = '%s'
				WHERE setting_name = 'Preview Hash'",
				$previewHash
			) );
		}

		/*
			Return the preview hash
		*/
		return $previewHash;
	}

	/**
	 * Returns the preview hash.
	 *
	 * @since      1.0.0
	 * @access     public
	 */
	public static function get_preview_hash(){
		global $wpdb;

		/*
			If the install is multisite,
			we grab the preview hash from the
			blog id 
		*/
		if( is_multisite() ){
			$currentBlogID = get_current_blog_id();

			global $switched;
			switch_to_blog(1);

			/*
				Get the preview hash for the current site.
			*/
			$previewHashResult = $wpdb->get_results( $wpdb->prepare(
				"SELECT setting_value
				 FROM ".$wpdb->prefix."wps_settings
				 WHERE blog_id = '%d'
				 AND setting_name = 'Preview Hash'",
				 $currentBlogID
			), ARRAY_A );

			restore_current_blog();
		}else{
			/*
				Get the preview hash
			*/
			$previewHashResult = $wpdb->get_results( "SELECT setting_value FROM ".$wpdb->prefix."wps_settings WHERE setting_name = 'Preview Hash'", ARRAY_A );
		}

		/*
			Returns the preview hash.
		*/
		return $previewHashResult[0]['setting_value'];
	}

	/**
	 * Generates a new preview hash
	 *
	 * @since      1.0.0
	 * @access     public
	 */
	public static function generate_preview_hash(){
		$hash = md5( uniqid( rand(), true ) );

		return substr( $hash, 0, 15 );
	}

	/**
	 * Checks valid preview URL
	 *
	 * @since      1.0.0
	 * @access     public
	 */
	public static function check_valid_preview_url(){
		/*
			Check if there is a valid preview url hash in the URL
		*/
		if( isset( $_GET['wp-sandbox-preview'] ) && $_GET['wp-sandbox-preview'] != '' ){
			/*
				Get the hash sent over
			*/
			$hash 		= $_GET['wp-sandbox-preview'];

			/*
				Get the hash setting
			*/
			$checkHash 	= self::get_preview_hash();

			/*
				If the preview hash matches the
				hash from the query
			*/
			if( $checkHash == $hash ){
				$defaultExpirationTime = WP_Sandbox_Settings::get_default_expiration_time();

				/*
					If never, then the cookie is set to expire
					in 10 years.
				*/
				if( $defaultExpirationTime == 'never' ){
					setcookie( 'wp-sandbox-preview-hash', $hash, time() + (10 * 365 * 24 * 60 * 60), '/' );
				}else{
					$futureTimestamp = self::get_future_timestamp( $defaultExpirationTime );

					setcookie( 'wp-sandbox-preview-hash', $hash, $futureTimestamp, '/' );
				}
				
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	/**
	 * Checks valid cookie
	 *
	 * @since      1.0.0
	 * @access     public
	 */
	public static function check_valid_cookie(){
		/*
			Checks to see if there is a cookie with a preview hash.
		*/
		if( isset( $_COOKIE['wp-sandbox-preview-hash'] ) ){
			/*
				If the hash is set in the cookie, retrieve the preview hash
			*/
			$hash 		= $_COOKIE['wp-sandbox-preview-hash'];

			/*
				Get the preview hash currently set
			*/
			$checkHash 	= self::get_preview_hash();

			/*
				If the cookie is set and matches
				the preview hash, then allow
				access. Otherwise clear the cookie
				because it's probably been refreshed.
			*/
			if( $hash == $checkHash ){
				return true;
			}else{
				setcookie( 'wp-sandbox-preview-hash' );

				return false;
			}
		}
	}

	/**
	 * Sets a cookie for the authenticated user
	 *
	 * @since 		1.0.0
	 * @access 		public
	 */
	public static function set_preview_cookie(){
		if( !isset( $_COOKIE['wp-sandbox-preview-hash'] ) ){
			$previewHash = self::get_preview_hash();

			$defaultExpirationTime = WP_Sandbox_Settings::get_default_expiration_time();

			/*
				If never, then the cookie is set to expire
				in 10 years.
			*/
			if( $defaultExpirationTime == 'never' ){
				setcookie( 'wp-sandbox-preview-hash', $previewHash, time() + (10 * 365 * 24 * 60 * 60), '/' );
			}else{
				$futureTimestamp = self::get_future_timestamp( $defaultExpirationTime );

				setcookie( 'wp-sandbox-preview-hash', $previewHash, $futureTimestamp, '/' );
			}
		}
	}

	/**
	 * Gets the future timestamp from the default time.
	 *
	 * @since      1.0.0
	 * @access     public
	 * @var 	   string 		$expirationTime 	The time that will be set for the expiration for the preview url
	 */
	public static function get_future_timestamp( $expirationTime ){
		/*
			Get the option for the timezone of the site.
		*/
		if( get_option('timezone_string') != '' ){
			date_default_timezone_set( get_option('timezone_string') );
		}

		/*
			Generate the new expiration time
		*/
		switch( $expirationTime ){
			case 'day':
				return strtotime( '+1 day', time() );
			break;
			case 'week':
				return strtotime( '+1 week', time() );
			break;
			case 'twoweeks':
				return strtotime( '+2 weeks', time() );
			break;	
			case 'month':
				return strtotime( '+1 month', time() );
			break;
		}
	}

	/**
	 * Rregenerates a preview URL
	 *
	 * @since      1.0.0
	 * @access     public
	 */
	public function regenerate_preview_url(){
		/*
			Create a new preview URL
		*/
		$previewHash = self::create_new_preview_url();

		/*
			Build the URL
		*/
		$previewURL = home_url('/').'?wp-sandbox-preview='.$previewHash;

		/*
			Return the new preview URL
		*/
		wp_send_json( array(
			'preview_url' => $previewURL
		) );
	}
}