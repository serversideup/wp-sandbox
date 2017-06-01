<?php
	class WP_Sandbox_Default_Settings{
		/*------------------------------------------------
			Sets the default settings for a single
			site instance.
		------------------------------------------------*/
		public function setDefaultSettings(){
			global $wpdb;

			/*
				Adds default page placeholder in the settings table
			*/
			$checkDefaultWPSPage = $wpdb->get_results( 
				"SELECT setting_value
				 FROM ".$wpdb->prefix."wps_settings
				 WHERE setting_name = 'Default Page'",
				 ARRAY_A
			);

			if( empty( $checkDefaultWPSPage ) ){
				$wpdb->query(
					"INSERT INTO ".$wpdb->prefix."wps_settings 
					(setting_name, setting_value) 
					VALUES ('Default Page', '404')"
					);
			}

			/*
				Adds default expiration time for user's authenticated
				IP in settings table and defaults it to never.
			*/
			$checkDefaultWPSExpire = $wpdb->get_results(
				"SELECT setting_value 
				 FROM ".$wpdb->prefix."wps_settings 
				 WHERE setting_name = 'Default Expiration Time'",
				 ARRAY_A
			);

			if( empty( $checkDefaultWPSExpire ) ){
				$wpdb->query(
					"INSERT INTO ".$wpdb->prefix."wps_settings 
					 (setting_name, setting_value) 
					 VALUES ('Default Expiration Time', 'never')"
				);
			}

			/*
				Generates and adds the Preview URL hash to the 
				settings table
			*/
			$checkDefaultWPSHash = $wpdb->get_results(
				"SELECT setting_value 
				 FROM ".$wpdb->prefix."wps_settings 
				 WHERE setting_name = 'Preview Hash'",
				 ARRAY_A
			);

			if( empty( $checkDefaultWPSHash ) ){
				$hash = WP_Sandbox_Preview_URL::generatePreviewHash();

				$wpdb->query( $wpdb->prepare(
					"INSERT INTO ".$wpdb->prefix."wps_settings 
					 (setting_name, setting_value) 
					 VALUES ('Preview Hash', '%s')",
					 $hash
				) );
			}

			/*
				Adds a default setting for the status of the plugin and defaults
				it to 0 which means disabled.
			*/
			$checkEnabled = $wpdb->get_results(
				"SELECT setting_value 
				 FROM ".$wpdb->prefix."wps_settings 
				 WHERE setting_name = 'Enabled'",
				 ARRAY_A 
			);

			if( empty( $checkEnabled ) ){
				$wpdb->query(
					"INSERT INTO ".$wpdb->prefix."wps_settings 
					 (setting_name, setting_value) 
					 VALUES ('Enabled', '0')"
				);
			}
		}

		/*------------------------------------------------
			Sets the default settings for a multisite
			site install.
		------------------------------------------------*/
		public function setDefaultSettingsMultisite(){
			global $wpdb;
			$previewURL = new WP_Sandbox_Preview_URL();

			/*
				Checks to see if get_blog_list exists,
				meaning the multisite install is pre 3.0.

				The wp_get_sites is the new version of the
				function.

				Builds an array of all the blogs on the 
				multisite install.
			*/
			$blogList = array();

			if( function_exists('get_blog_list') ){
				$blogReturn = get_blog_list( 0, 'all' );
				$blogCounter = 0;

				foreach ( $blogReturn AS $blog ) {
					$blogList[ $blogCounter ] = $blog['blog_id'];
					$blogCounter++;
				}
			}else{
				$blogReturn = wp_get_sites();
				$blogCounter = 0;

				foreach ( $blogReturn AS $blog ) {
					$blogList[ $blogCounter ] = $blog['blog_id'];
					$blogCounter++;
				}
			}

			global $switched;
			switch_to_blog(1);
			
			/*
				Iterates through all of the blogs and
				sets the default settings. This ensures
				the settings are the same for all blogs
				on install.  Each blog is managed 
				individually.
			*/
			foreach( $blogList as $blogID ){
				/*
					Adds placeholder for default page setting
				*/
				$checkDefaultWPSPage = $wpdb->get_results( $wpdb->prepare( 
					"SELECT setting_value 
					 FROM ".$wpdb->prefix."wps_settings 
					 WHERE setting_name = 'Default Page' 
					 AND blog_id = '%d'", 
					 $blogID
				), ARRAY_A );

				if( empty( $checkDefaultWPSPage ) ){
					$wpdb->query( $wpdb->prepare(
						"INSERT INTO ".$wpdb->prefix."wps_settings 
						(blog_id, setting_name, setting_value) 
						VALUES ('%d', 'Default Page', '404')",
						$blogID
					) );
				}

				/*
					Adds default setting for authenticated user
					expiration time and sets it to never
				*/
				$checkDefaultWPSExpire = $wpdb->get_results( $wpdb->prepare(
					"SELECT setting_value 
					 FROM ".$wpdb->prefix."wps_settings 
					 WHERE setting_name = 'Default Expiration Time' 
					 AND blog_id = '%d'",
					 $blogID
				), ARRAY_A );

				if( empty( $checkDefaultWPSExpire ) ){
					$wpdb->query( $wpdb->prepare(
						"INSERT INTO ".$wpdb->prefix."wps_settings 
						(blog_id, setting_name, setting_value) 
						VALUES ('%d', 'Default Expiration Time', 'never')",
						$blogID
					), ARRAY_A );
				}

				/*
					Generates a preview hash and sets the setting
					for each blog.
				*/
				$checkDefaultWPSHash = $wpdb->get_results( $wpdb->prepare(
					"SELECT setting_value 
					 FROM ".$wpdb->prefix."wps_settings 
					 WHERE setting_name = 'Preview Hash' 
					 AND blog_id = '%d'",
					 $blogID
				), ARRAY_A );

				if( empty( $checkDefaultWPSHash ) ){
					$hash = WP_Sandbox_Preview_URL::generatePreviewHash();

					$wpdb->query( $wpdb->prepare(
						"INSERT INTO ".$wpdb->prefix."wps_settings 
						(blog_id, setting_name, setting_value) 
						VALUES ('%d', 'Preview Hash', '%s')",
						$blogID,
						$hash
					), ARRAY_A );
				}

				/*
					Adds a default setting for the status of the plugin and defaults
					it to 0 which means disabled.
				*/

				$checkEnabled = $wpdb->get_results( $wpdb->prepare(
					"SELECT setting_value 
					 FROM ".$wpdb->prefix."wps_settings 
					 WHERE setting_name = 'Enabled' 
					 AND blog_id = '%d'",
					 $blogID
				), ARRAY_A );

				if( empty( $checkEnabled ) ){
					$wpdb->query( $wpdb->prepare(
						"INSERT INTO ".$wpdb->prefix."wps_settings 
						 (blog_id, setting_name, setting_value) 
						 VALUES ('%d', 'Enabled', '0')",
						 $blogID
					) );
				}
			}

			restore_current_blog();
		}

		/*------------------------------------------------
			The handler that adds all of the default
			settings for a new blog.
		------------------------------------------------*/
		public static function add_new_blog(){
			global $wpdb;

			global $switched;
			switch_to_blog(1);
			
			/*
				Gets the newest blog
			*/
			$newestBlog = $wpdb->get_results( 
							"SELECT blog_id 
							 FROM ".$wpdb->prefix."blogs 
							 ORDER BY blog_id DESC", 
							 ARRAY_A );

			$blogID = $newestBlog[0]['blog_id'];
			
			/*
				Adds default page setting
			*/
			$wpdb->query( $wpdb->prepare(
				"INSERT INTO ".$wpdb->prefix."wps_settings 
				 (blog_id, setting_name) 
				 VALUES ('%d', 'Default Page')",
				 $blogID
			) );

			/*
				Adds default expiration time setting
			*/
			$wpdb->query( $wpdb->prepare(
				"INSERT INTO ".$wpdb->prefix."wps_settings 
				 (blog_id, setting_name, setting_value) 
				 VALUES ('%d', 'Default Expiration Time', 'never')",
				 $blogID
			) );

			/*
				Generates and adds preview hash setting
			*/
			$hash = WP_Sandbox_Preview_URL::generatePreviewHash();
			
			$wpdb->query( $wpdb->prepare(
				"INSERT INTO ".$wpdb->prefix."wps_settings 
				 (blog_id, setting_name, setting_value) 
				 VALUES ('%d', 'Preview Hash', '%s')",
				 $blogID,
				 $hash
			) );

			/*
				Adds site enabled setting
			*/
			$wpdb->query( $wpdb->prepare(
				"INSERT INTO ".$wpdb->prefix."wps_settings 
				(blog_id, setting_name, setting_value) 
				VALUES ('%d', 'Enabled', '0')",
				$blogID
			) );

			restore_current_blog();
		}

		/*------------------------------------------------
			The handler that removes all of the default
			settings for a deleted blog.
		------------------------------------------------*/
		public static function delete_blog(){
			global $wpdb;
			global $switched;

			$blogID = get_current_blog_id();

			switch_to_blog(1);

			/*
				Deletes all authenticated users
				for the deleted blog.
			*/
			$wpdb->query( $wpdb->prepare(
				"DELETE FROM ".$wpdb->prefix."wps_authenticated_users
				 WHERE blog_id = '%d'",
				 $blogID
			) );

			/*
				Deletes all ips
				for the deleted blog.
			*/
			$wpdb->query( $wpdb->prepare(
				"DELETE FROM ".$wpdb->prefix."wps_ips
				 WHERE blog_id = '%d'",
				 $blogID
			) );

			/*
				Deletes all ip ranges
				for the deleted blog.
			*/
			$wpdb->query( $wpdb->prepare(
				"DELETE FROM ".$wpdb->prefix."wps_ip_ranges
				 WHERE blog_id = '%d'",
				 $blogID
			) );

			/*
				Deletes all subnets
				for the deleted blog.
			*/
			$wpdb->query( $wpdb->prepare(
				"DELETE FROM ".$wpdb->prefix."wps_subnets
				 WHERE blog_id = '%d'",
				 $blogID
			) );
			
			/*
				Deletes all settings
				for the deleted blog.
			*/
			$wpdb->query( $wpdb->prepare(
				"DELETE FROM ".$wpdb->prefix."wps_settings
				 WHERE blog_id = '%d'",
				 $blogID
			) );

			restore_current_blog();
		}
	}
?>