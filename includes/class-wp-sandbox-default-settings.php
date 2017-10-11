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
				Generates the initial preview hash
			*/
			$hash = WP_Sandbox_Preview_URL::generate_preview_hash();

			/*
				Inserts the default settings for the plugin.
			*/
			$wpdb->query( $wpdb->prepare(
				"INSERT INTO ".$wpdb->prefix."wps_settings
				(`preview_hash`, `enabled` )
				VALUES ('%s', '1')",
				$hash
			) );
		}

		/**
		 * Sets the default settings for a multi site instance.
		 *
		 * @since      1.0.0
		 * @access     public
		 */
		public function set_default_settings_network_activated_multisite(){
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

			/*
				Get all of the sites
			*/
			$blogReturn = get_sites();
			$blogCounter = 0;

			/*
				Get the entire list of blogs and add the blog ID
				to a blog list array.
			*/
			foreach ( $blogReturn as $blog ) {
				$blogList[ $blogCounter ] = $blog->blog_id;
				$blogCounter++;
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
					Checks to see if any existing settings exist for the blog. This would
					only happen if a site has the plugin activated and the network admin
					activates the plugin on the network.
				*/
				$existingSettings = $wpdb->get_results( $wpdb->prepare(
					"SELECT * FROM ".$wpdb->prefix."wps_settings
					 WHERE blog_id = '%d'",
					 $blogID
				), ARRAY_A );

				/*
					If empty, we add the default settings for the plugin.
				*/
				if( empty( $existingSettings ) ){
					$hash = WP_Sandbox_Preview_URL::generate_preview_hash();

					$wpdb->query( $wpdb->prepare(
						"INSERT INTO ".$wpdb->prefix."wps_settings
						(blog_id, preview_hash, enabled, background_color_1, background_color_2)
						VALUES ('%d', '%s', '%d', '%s', '%s')",
						$blogID,
						$hash,
						1,
						'#5CCCF0',
						'#3884E8'
					), ARRAY_A );
				}
			}

			restore_current_blog();
		}

		/**
		 * Sets the default settings for a site in a multi site instance.
		 *
		 * @since      1.0.0
		 * @access     public
		 */
		public function set_default_settings_multisite(){
			global $wpdb;
			$previewURL = new WP_Sandbox_Preview_URL();

			$currentBlogID = get_current_blog_id();

			global $switched;
			switch_to_blog(1);

			$hash = WP_Sandbox_Preview_URL::generate_preview_hash();

			$wpdb->query( $wpdb->prepare(
				"INSERT INTO ".$wpdb->prefix."wps_settings
				(blog_id, preview_hash, enabled, background_color_1, background_color_2)
				VALUES ('%d', '%s', '%d', '%s', '%s')",
				$currentBlogID,
				$hash,
				1,
				'#5CCCF0',
				'#3884E8'
			), ARRAY_A );

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

			$hash = WP_Sandbox_Preview_URL::generate_preview_hash();

			$wpdb->query( $wpdb->prepare(
				"INSERT INTO ".$wpdb->prefix."wps_settings
				(blog_id, preview_hash, enabled, background_color_1, background_color_2)
				VALUES ('%d', '%s', '%d', '%s', '%s')",
				$blogID,
				$hash,
				1,
				'#5CCCF0',
				'#3884E8'
			), ARRAY_A );

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
