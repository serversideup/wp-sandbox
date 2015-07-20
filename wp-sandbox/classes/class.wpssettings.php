<?php
	class WPSSettings{
		public static function saveSettings(){
			global $wpdb;

			/*
				Calls the methods needed to 
				save settings that are not
				saved directly through an AJAX
				call.
			*/
			$defaultPage 			= self::saveDefaultPageSetting();
			$defaultExpirationTime 	= self::saveDefaultExpireTime();
			$publicAccess 			= self::savePublicAccess();

			/*
				Return success
			*/
			$return = array(
				'settings_updated'	=> true,
				'public_access' 	=> $publicAccess
			);

			wp_send_json( $return );

			die();
		}

		/*------------------------------------------------
			Saves the default pate setting.
			Called from saveAdminSettings() which is 
			CALLED THROUGH AJAX
		------------------------------------------------*/
		public static function saveDefaultPageSetting(){
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
				$currentBlogID = get_current_blog_id();

				global $switched;
				switch_to_blog(1);

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
				$wpdb->query( $wpdb->prepare(
					"UPDATE ".$wpdb->prefix."wps_settings 
					SET setting_value = '%s' 
					WHERE setting_name = 'Default Page'",
					$defaultPage
				) );
			}

			return $defaultPage;
		}

		/*------------------------------------------------
			Saves the default expiration time setting.
			Called from saveAdminSettings() which is 
			CALLED THROUGH AJAX
		------------------------------------------------*/
		public static function saveDefaultExpireTime(){
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
				$currentBlogID = get_current_blog_id();

				global $switched;
				switch_to_blog(1);

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
				$wpdb->query( $wpdb->prepare(
					"UPDATE ".$wpdb->prefix."wps_settings 
					SET setting_value = '%s' 
					WHERE setting_name = 'Default Expiration Time'",
					$expirationTime
				) );
			}

			return $expirationTime;
		}

		/*------------------------------------------------
			Saves the public access setting.
			Called from saveAdminSettings() which is 
			CALLED THROUGH AJAX
		------------------------------------------------*/
		public static function savePublicAccess(){
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
				$wpdb->query( $wpdb->prepare(
					"UPDATE ".$wpdb->prefix."wps_settings 
					SET setting_value = '%d' 
					WHERE setting_name = 'Enabled'",
					$publicAccess
				) );
			}

			return $publicAccess;
		}

		/*------------------------------------------------
			Returns the default expiration time for the
			site.
		------------------------------------------------*/
		public static function getDefaultExpirationTime(){
			global $wpdb;

			if( is_multisite() ){
				$currentBlogID = get_current_blog_id();

				global $switched;
				switch_to_blog(1);

				$defaultExpirationTime = $wpdb->get_results( $wpdb->prepare(
					"SELECT setting_value
					 FROM ".$wpdb->prefix."wps_settings 
					 WHERE setting_name = 'Default Expiration Time' 
					 AND blog_id = '%d'",
					 $currentBlogID
				), ARRAY_A );

				restore_current_blog();
			}else{
				$defaultExpirationTime = $wpdb->get_results(
					"SELECT setting_value 
					FROM ".$wpdb->prefix."wps_settings 
					WHERE setting_name = 'Default Expiration Time'",
					ARRAY_A );
			}

			return $defaultExpirationTime[0]['setting_value'];
		}

		/*------------------------------------------------
			Returns the plugin status.
		------------------------------------------------*/
		public static function getPluginStatus(){
			global $wpdb;

			if( is_multisite() ){
				$currentBlogID = get_current_blog_id();

				global $switched;
				switch_to_blog(1);

				$pluginStatus = $wpdb->get_results( $wpdb->prepare(
					"SELECT setting_value
					 FROM ".$wpdb->prefix."wps_settings 
					 WHERE setting_name = 'Enabled' 
					 AND blog_id = '%d'",
					 $currentBlogID
				), ARRAY_A );

				restore_current_blog();
			}else{
				$pluginStatus = $wpdb->get_results(
					"SELECT setting_value 
					 FROM ".$wpdb->prefix."wps_settings 
					 WHERE setting_name = 'Enabled'",
					ARRAY_A );
			}

			return $pluginStatus[0]['setting_value'];
		}

		/*------------------------------------------------
			Returns the default page
		------------------------------------------------*/
		public static function getDefaultPage(){
			global $wpdb;

			if( is_multisite() ){
				$currentBlogID = get_current_blog_id();

				global $switched;
				switch_to_blog(1);

				$defaultPage = $wpdb->get_results( $wpdb->prepare(
					"SELECT setting_value
					 FROM ".$wpdb->prefix."wps_settings 
					 WHERE setting_name = 'Enabled' 
					 AND blog_id = '%d'",
					 $currentBlogID
				), ARRAY_A );

				restore_current_blog();
			}else{
				$defaultPage = $wpdb->get_results(
					"SELECT setting_value 
					 FROM ".$wpdb->prefix."wps_settings 
					 WHERE setting_name = 'Enabled'",
					ARRAY_A );
			}

			return $defaultPage[0]['setting_value'];
		}

		/*------------------------------------------------
			Returns all of the settings for the site.
		------------------------------------------------*/
		public static function getAllSettings(){
			global $wpdb;

			if( is_multisite() ){
				$currentBlogID = get_current_blog_id();

				global $switched;
				switch_to_blog(1);

				$allSettings = $wpdb->get_results( $wpdb->prepare( 
					"SELECT * FROM ".$wpdb->prefix."wps_settings
					WHERE blog_id = '%d'",
					$currentBlogID
				), ARRAY_A );

				restore_current_blog();
			}else{
				$allSettings = $wpdb->get_results(
					"SELECT * 
					FROM ".$wpdb->prefix."wps_settings", 
					ARRAY_A );
			}
			
			return $allSettings;
		}

		/*------------------------------------------------
			Gets all enabled sites on a network install.
			Only called from the network admin screen 
			so we asssume it's a multisite install.
		------------------------------------------------*/
		public static function getSitesStatus(){
			global $wpdb;
			global $switched;

			switch_to_blog(1);

			$enabledSites = $wpdb->get_results( 
				"SELECT * 
				 FROM wp_wps_settings 
				 WHERE setting_name = 'Enabled'",
				 ARRAY_A);

			restore_current_blog();

			return $enabledSites;
		}

		/*------------------------------------------------
			CALLED FROM AJAX
			Enables and disables the selected blogs
			from the network admin management screen.
		------------------------------------------------*/
		public static function enableDisableBlogs(){
			global $wpdb;
			global $switched;

			switch_to_blog(1);

			$blogs = $_POST['status'];

			foreach( $blogs as $status ){
				$enabled 	= ( $status['active'] == 'true' ? '1' : '0' );
				$blogID 	= $status['id'];

				$wpdb->query( $wpdb->prepare(
					"UPDATE ".$wpdb->prefix."wps_settings
					 SET setting_value = '%d'
					 WHERE setting_name = 'Enabled'
					 AND blog_id = '%d'",
					 $enabled,
					 $blogID
				) );
			}

			restore_current_blog();

			die();
		}
	}
?>