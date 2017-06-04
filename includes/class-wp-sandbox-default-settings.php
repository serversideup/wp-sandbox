<?php
	/**
	 * Handles the default settings for the plugin
	 *
	 * @link       https://521dimensions.com
	 * @since      1.0.0
	 *
	 * @package    WP_Sandbox
	 * @subpackage WP_Sandbox/includes
	 */

	/**
	 * Configures the default settings for all of the plugin
	 *
	 * @since      1.0.0
	 * @package    WP_Sandbox
	 * @subpackage WP_Sandbox/includes
	 * @author     521 Dimensions <dan@521dimensions.com>
	 */
	class WP_Sandbox_Default_Settings{
		/**
		 * Sets the default settings for a single site instance.
		 *
		 * @since      1.0.0
		 * @access     public
		 */
		public function set_default_settings(){
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

			/*
				If the default page is empty, insert the default.
			*/
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

			/*
				If the default expiration time is empty, add the
				default expiration time.
			*/
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

			/*
				If the default hash is empty, add a preview
				hash
			*/
			if( empty( $checkDefaultWPSHash ) ){
				$hash = WP_Sandbox_Preview_URL::generate_preview_hash();

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

			/*
				If the plugin enabled is empty, add the default plugin enabled
				setting.
			*/
			if( empty( $checkEnabled ) ){
				$wpdb->query(
					"INSERT INTO ".$wpdb->prefix."wps_settings 
					 (setting_name, setting_value) 
					 VALUES ('Enabled', '0')"
				);
			}
		}

		/**
		 * Sets the default settings for a multi site instance.
		 *
		 * @since      1.0.0
		 * @access     public
		 */
		public function set_default_settings_multisite(){
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
				/*
					Get all of the blogs
				*/
				$blogReturn = get_blog_list( 0, 'all' );
				$blogCounter = 0;

				/*
					Get an entire list of blogs and add the blog ID
					to a blog list array.
				*/
				foreach( $blogReturn as $blog ){
					$blogList[ $blogCounter ] = $blog['blog_id'];
					$blogCounter++;
				}
			}else{
				/*
					Get all of the sites
				*/
				$blogReturn = wp_get_sites();
				$blogCounter = 0;

				/*
					Get the entire list of blogs and add the blog ID
					to a blog list array.
				*/
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

				/*
					If the default page is not set on the blog, add
					add a default setting.
				*/
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

				/*
					If the default expire is not set on the blog, add a default
					expire setting.
				*/
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

				/*
					Checks to see if a default preview hash is not set
					generate one and add a default preview hash to for the
					site.
				*/
				if( empty( $checkDefaultWPSHash ) ){
					$hash = WP_Sandbox_Preview_URL::generate_preview_hash();

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

				/*
					Checks to see if the default blog enabled is set, if not
					then add the default enabled setting for the blog.
				*/
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

		/**
		 * When a new blog is added, ensure we add the default settings
		 * for the new blog
		 *
		 * @since      1.0.0
		 * @access     public
		 */
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
			$hash = WP_Sandbox_Preview_URL::generate_preview_hash();
			
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

		/**
		 * When a blog is deleted, ensure the settings are deleted
		 * as well.
		 *
		 * @since      1.0.0
		 * @access     public
		 */
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