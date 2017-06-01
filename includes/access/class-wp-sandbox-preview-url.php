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
class WP_Sandbox_Preview_URL{
	/*------------------------------------------------
		Creates a new preview URL
	------------------------------------------------*/
	public static function createNewPreviewURL(){
		global $wpdb;

		$previewHash = self::generatePreviewHash();

		/*
			If the install is multisite,
			we need to update the current
			blog's preview hash
		*/
		if( is_multisite() ){
			$currentBlogID = get_current_blog_id();

			global $switched;
			switch_to_blog(1);

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
			$wpdb->query( $wpdb->prepare(
				"UPDATE ".$wpdb->prefix."wps_settings
				SET setting_value = '%s'
				WHERE setting_name = 'Preview Hash'",
				$previewHash
			) );
		}

		return $previewHash;
	}

	/*------------------------------------------------
		Returns the preview hash.
	------------------------------------------------*/
	public static function getPreviewHash(){
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

			$previewHashResult = $wpdb->get_results( $wpdb->prepare(
				"SELECT setting_value
				 FROM ".$wpdb->prefix."wps_settings
				 WHERE blog_id = '%d'
				 AND setting_name = 'Preview Hash'",
				 $currentBlogID
			), ARRAY_A );

			restore_current_blog();
		}else{
			$previewHashResult = $wpdb->get_results( "SELECT setting_value FROM ".$wpdb->prefix."wps_settings WHERE setting_name = 'Preview Hash'", ARRAY_A );
		}

		/*
			Returns the preview hash.
		*/
		return $previewHashResult[0]['setting_value'];
	}

	/*------------------------------------------------
		Generates a new preview hash
	------------------------------------------------*/
	public static function generatePreviewHash(){
		$hash = md5( uniqid( rand(), true ) );

		return substr( $hash, 0, 15 );
	}

	/*------------------------------------------------
		Checks valid preview URL
	------------------------------------------------*/
	public static function check_valid_preview_url(){
		if( isset( $_GET['wp-sandbox-preview'] ) && $_GET['wp-sandbox-preview'] != '' ){
			$hash 		= $_GET['wp-sandbox-preview'];

			$checkHash 	= self::getPreviewHash();

			/*
				If the preview hash matches the
				hash from the query
			*/
			if( $checkHash == $hash ){
				$defaultExpirationTime = WPSSettings::getDefaultExpirationTime();

				/*
					If never, then the cookie is set to expire
					in 10 years.
				*/
				if( $defaultExpirationTime == 'never' ){
					setcookie( 'wp-sandbox-preview-hash', $hash, time() + (10 * 365 * 24 * 60 * 60) );
				}else{
					$futureTimestamp = self::getFutureTimestamp( $defaultExpirationTime );

					setcookie( 'wp-sandbox-preview-hash', $hash, $futureTimestamp );
				}
				
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	/*------------------------------------------------
		Checks valid cookie
	------------------------------------------------*/
	public static function check_valid_cookie(){
		if( isset( $_COOKIE['wp-sandbox-preview-hash'] ) ){
			$hash 		= $_COOKIE['wp-sandbox-preview-hash'];

			$checkHash 	= WPSPreviewURL::getPreviewHash();

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

	/*------------------------------------------------
		Gets the future timestamp from the default
		time.
	------------------------------------------------*/
	public static function getFutureTimestamp( $expirationTime ){
		if( get_option('timezone_string') != '' ){
			date_default_timezone_set( get_option('timezone_string') );
		}

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

	public function regenerate_preview_url(){
		$previewHash = $this->createNewPreviewURL();

		$previewURL = home_url('/').'?wp-sandbox-preview='.$previewHash;

		wp_send_json( array(
			'preview_url' => $previewURL
		) );
	}
}