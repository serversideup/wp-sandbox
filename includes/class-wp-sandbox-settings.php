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
class WP_Sandbox_Settings{
	/**
	 * Saves the plugin settings.
	 *
	 * @since    1.0.0
	 * @access 	 public
	 */
	public static function save_settings(){
		global $wpdb;

		/*
			Calls the methods needed to 
			save settings that are not
			saved directly through an AJAX
			call.
		*/
		$defaultPage 			= self::save_default_page_setting();
		$defaultExpirationTime 	= self::save_default_expire_time();
		$publicAccess 			= self::save_public_access();

		/*
			Builds the successful return response.
		*/
		$return = array(
			'settings_updated'	=> true,
			'public_access' 	=> $publicAccess
		);

		/*
			Send back the JSON as the response.
		*/
		wp_send_json( $return );

		die();
	}

	/**
	 * Saves the default page setting.
	 *
	 * @since    1.0.0
	 * @access 	 public
	 */
	public static function save_default_page_setting(){
		global $wpdb;

		/*
			Gets the default page set
			by the user.
		*/
		$defaultPage 	= $_POST['default_page'];

		/*
			If it's a multisite install, switch
			the blog and update the values
		*/
		if( is_multisite() ){
			/*
				Get the current blog ID
			*/
			$currentBlogID = get_current_blog_id();

			/*
				Switches to the correct blog and update the default page setting.
			*/
			global $switched;
			switch_to_blog(1);

			/*
				Updates the default page settings.
			*/
			$wpdb->query( $wpdb->prepare(
				"UPDATE ".$wpdb->prefix."wps_settings 
				SET setting_value = '%s' 
				WHERE setting_name = 'Default Page' 
				AND blog_id = '%d'",
				$defaultPage,
				$currentBlogID
			) );
			
			restore_current_blog();
		}else{
			/*
				Updates the default page settings.
			*/
			$wpdb->query( $wpdb->prepare(
				"UPDATE ".$wpdb->prefix."wps_settings 
				SET setting_value = '%s' 
				WHERE setting_name = 'Default Page'",
				$defaultPage
			) );
		}

		/*
			Returns the default page.
		*/
		return $defaultPage;
	}

	/**
	 * Saves the default expiration time setting.
	 *
	 * @since    1.0.0
	 * @access 	 public
	 */
	public static function save_default_expire_time(){
		global $wpdb;

		/*
			Gets the default expiration time set
			by the user.
		*/
		$expirationTime = $_POST['expiration_time'];

		/*
			If it's a multisite install, switch
			the blog and update the values
		*/
		if( is_multisite() ){
			/*
				Get the current blog ID
			*/
			$currentBlogID = get_current_blog_id();

			global $switched;
			switch_to_blog(1);

			/*
				Update the default expire time for the specific blog.
			*/
			$wpdb->query( $wpdb->prepare(
				"UPDATE ".$wpdb->prefix."wps_settings 
				SET setting_value = '%s' 
				WHERE setting_name = 'Default Expiration Time' 
				AND blog_id = '%d'",
				$expirationTime,
				$currentBlogID
			) );
			
			restore_current_blog();
		}else{
			/*
				Update the default expire time
			*/
			$wpdb->query( $wpdb->prepare(
				"UPDATE ".$wpdb->prefix."wps_settings 
				SET setting_value = '%s' 
				WHERE setting_name = 'Default Expiration Time'",
				$expirationTime
			) );
		}

		/*
			Return the udpated expiration time.
		*/
		return $expirationTime;
	}

	/**
	 * Gets the expiration time as a certain date in the future
	 *
	 * @since    1.0.0
	 * @access 	 public
	 * @var 	 $expiration 	The length of time in the future for expiration.
	 */
	public static function get_expiration_time( $expiration ){
		/*
			Get the timezone setting for the site.
		*/
		if( get_option('timezone_string') != '' ){
			date_default_timezone_set( get_option('timezone_string') );
		}
		
		switch( $expiration ){
			case 'day':
				return date('Y-m-d G:i:s', time() + '86400');
			break;
			case 'week':
				return date('Y-m-d G:i:s', time() + '604800');
			break;
			case 'twoweeks':
				return date('Y-m-d G:i:s', time() + '1209600');
			break;	
			case 'month':
				return date('Y-m-d G:i:s', time() + '2592000');
			break;
			case 'never':
				return '';
			break;
		}
	}

	/**
	 * Saves the public access setting.
	 *
	 * @since    1.0.0
	 * @access 	 public
	 */
	public static function save_public_access(){
		global $wpdb;

		/*
			Gets the public access settings
			by the user.
		*/
		$publicAccess = $_POST['public_access'];

		/*
			If it's a multisite install, switch
			the blog and update the values
		*/
		if( is_multisite() ){
			$currentBlogID = get_current_blog_id();

			global $switched;
			switch_to_blog(1);

			/*
				Update the public access setting for the specific blog.
			*/
			$wpdb->query( $wpdb->prepare(
				"UPDATE ".$wpdb->prefix."wps_settings 
				SET setting_value = '%d' 
				WHERE setting_name = 'Enabled' 
				AND blog_id = '%d'",
				$publicAccess,
				$currentBlogID
			) );

			restore_current_blog();
		}else{
			/*
				Update the public access setting
			*/
			$wpdb->query( $wpdb->prepare(
				"UPDATE ".$wpdb->prefix."wps_settings 
				SET setting_value = '%d' 
				WHERE setting_name = 'Enabled'",
				$publicAccess
			) );
		}

		return $publicAccess;
	}

	/**
	 * Returns the default expiration time for the site.
	 *
	 * @since    1.0.0
	 * @access 	 public
	 */
	public static function get_default_expiration_time(){
		global $wpdb;

		/*
			If the site is multi site get the default expiration time
		*/
		if( is_multisite() ){
			$currentBlogID = get_current_blog_id();

			global $switched;
			switch_to_blog(1);

			/*
				Sets the default expiration time on the blog.
			*/
			$defaultExpirationTime = $wpdb->get_results( $wpdb->prepare(
				"SELECT setting_value
				 FROM ".$wpdb->prefix."wps_settings 
				 WHERE setting_name = 'Default Expiration Time' 
				 AND blog_id = '%d'",
				 $currentBlogID
			), ARRAY_A );

			restore_current_blog();
		}else{
			/*
				Sets the default expiration time.
			*/
			$defaultExpirationTime = $wpdb->get_results(
				"SELECT setting_value 
				FROM ".$wpdb->prefix."wps_settings 
				WHERE setting_name = 'Default Expiration Time'",
				ARRAY_A );
		}

		/*
			Returns the default expiration time
		*/
		return $defaultExpirationTime[0]['setting_value'];
	}

	/**
	 * Returns the plugin status.
	 *
	 * @since    1.0.0
	 * @access 	 public
	 */
	public static function get_plugin_status(){
		global $wpdb;
		
		/*
			If the site is multisite we get the plugin status
		*/
		if( is_multisite() ){
			$currentBlogID = get_current_blog_id();

			global $switched;
			switch_to_blog(1);

			/*
				Gets the plugin status for the blog
			*/
			$pluginStatus = $wpdb->get_results( $wpdb->prepare(
				"SELECT setting_value
				 FROM ".$wpdb->prefix."wps_settings 
				 WHERE setting_name = 'Enabled' 
				 AND blog_id = '%d'",
				 $currentBlogID
			), ARRAY_A );

			restore_current_blog();
		}else{
			/*
				Gets the plugin status.
			*/
			$pluginStatus = $wpdb->get_results(
				"SELECT setting_value 
				 FROM ".$wpdb->prefix."wps_settings 
				 WHERE setting_name = 'Enabled'",
				ARRAY_A );
		}

		/*
			Returns the setting value
		*/
		return $pluginStatus[0]['setting_value'];
	}

	/**
	 * Returns the plugin page.
	 *
	 * @since    1.0.0
	 * @access 	 public
	 */
	public static function get_default_page(){
		global $wpdb;

		/*
			If the setup is multisite, get the default page for the blog.
		*/
		if( is_multisite() ){
			$currentBlogID = get_current_blog_id();

			global $switched;
			switch_to_blog(1);

			/*
				Get the default page for the plugin for un authorized users.
			*/
			$defaultPage = $wpdb->get_results( $wpdb->prepare(
				"SELECT setting_value
				 FROM ".$wpdb->prefix."wps_settings 
				 WHERE setting_name = 'Default Page' 
				 AND blog_id = '%d'",
				 $currentBlogID
			), ARRAY_A );

			/*
				Restores the current blog game pack.
			*/
			restore_current_blog();
		}else{
			/*
				Get the default page for the plugin for un authorized users.
			*/
			$defaultPage = $wpdb->get_results(
				"SELECT setting_value 
				 FROM ".$wpdb->prefix."wps_settings 
				 WHERE setting_name = 'Default Page'",
				ARRAY_A );
		}

		/*
			Returns the setting value
		*/
		return $defaultPage[0]['setting_value'];
	}

	/**
	 * Returns all of the settings for the site.
	 *
	 * @since    1.0.0
	 * @access 	 public
	 */
	public static function get_all_settings(){
		global $wpdb;

		/*
			If the site is multisite, get all of the settings for
			the individual blog.
		*/
		if( is_multisite() ){
			/*
				Get the current blog ID
			*/
			$currentBlogID = get_current_blog_id();

			global $switched;
			switch_to_blog(1);

			/*
				Get all the settings for the current blog.
			*/
			$allSettings = $wpdb->get_results( $wpdb->prepare( 
				"SELECT * FROM ".$wpdb->prefix."wps_settings
				WHERE blog_id = '%d'",
				$currentBlogID
			), ARRAY_A );

			/*
				Restores the current blog
			*/
			restore_current_blog();
		}else{
			/*
				Get all of the settings.
			*/
			$allSettings = $wpdb->get_results(
				"SELECT * 
				FROM ".$wpdb->prefix."wps_settings", 
				ARRAY_A );
		}
		
		return $allSettings;
	}

	/**
	 * Gets all enabled sites on a network install. Only called from the 
	 * network admin screen so we asssume it's a multisite install.
	 *
	 * @since    1.0.0
	 * @access 	 public
	 */
	public static function get_sites_status(){
		global $wpdb;
		global $switched;

		/*
			Switch to the top level blog
		*/
		switch_to_blog(1);

		/*
			Get all of the enabled statuses for the site
		*/
		$enabledSites = $wpdb->get_results( 
			"SELECT * 
			 FROM ".$wpdb->prefix."wps_settings 
			 WHERE setting_name = 'Enabled'",
			 ARRAY_A);

		/*
			Restore the current blog
		*/
		restore_current_blog();

		/*
			Returns all of the enabled sites
		*/
		return $enabledSites;
	}

	/**
	 * Enables and disables the selected blogs from the network admin management
	 * screen.
	 *
	 * @since    1.0.0
	 * @access 	 public
	 */
	public static function enable_disable_blogs(){
		global $wpdb;
		global $switched;

		/*
			Switch to the top level blog
		*/
		switch_to_blog(1);

		/*
			Gets the blogs that are being enabled/disabled
		*/
		$blogs = $_POST['status'];

		/*
			Iterate over all of the blogs and update the status to be
			enabled or disbled.
		*/
		foreach( $blogs as $status ){
			$enabled 	= ( $status['active'] == 'true' ? '1' : '0' );
			$blogID 	= $status['id'];

			/*
				Update the setting of the blog
			*/
			$wpdb->query( $wpdb->prepare(
				"UPDATE ".$wpdb->prefix."wps_settings
				 SET setting_value = '%d'
				 WHERE setting_name = 'Enabled'
				 AND blog_id = '%d'",
				 $enabled,
				 $blogID
			) );
		}

		/*
			Restore the current blog
		*/
		restore_current_blog();

		die();
	}
}