<?php

/**
 * Builds the tables for housing the access rules for WP Sandbox
 *
 * @link       https://521dimensions.com
 * @since      1.0.0
 *
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/includes
 */

/**
 * Builds the tables for housing the access rules for WP Sandbox
 *
 * When activating WP Sandbox, this class will run and build the tables.
 * When deactivating WP Sandbox, the database tables will be deleted.
 *
 * @since      1.0.0
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/includes
 * @author     521 Dimensions <dan@521dimensions.com>
 */
class WP_Sandbox_Database_Management {
	/**
	 * Build the tables required for the plugin
	 *
	 * @since    1.0.0
	 * @access 	 public
	 */
	public function build_tables() {
		$this->build_authenticated_users_table();
		$this->build_settings_table();
		$this->build_ips_table();
		$this->build_ip_ranges_table();
		$this->build_subnets_table();
	}

	/**
	 * Destroys the tables on deactivation
	 *
	 * @since    1.0.0
	 * @access 	 public
	 */
	public function destroy_tables(){
		global $wpdb;

		/*
			If multisite, switch to the top level blog
		*/
		if( is_multisite() ){
			global $switched;
			switch_to_blog(1);
		}

		/*
			Drops all of the tables used by WP Sandbox
		*/
		$wpdb->query( 'DROP TABLE '.$wpdb->prefix.'wps_authenticated_users' );
		$wpdb->query( 'DROP TABLE '.$wpdb->prefix.'wps_settings' );
		$wpdb->query( 'DROP TABLE '.$wpdb->prefix.'wps_ips' );
		$wpdb->query( 'DROP TABLE '.$wpdb->prefix.'wps_ip_ranges' );
		$wpdb->query( 'DROP TABLE '.$wpdb->prefix.'wps_subnets' );

		/*
			Removes the option added ot the option
			table by the plugin.
		*/
		delete_option( 'wp_sandbox_version' );

		/*
			Rstore the current blog on multisite.
		*/
		if( is_multisite() ){
			restore_current_blog();
		}
	}

	/**
	 * Build the tables for Authenticated Users
	 *
	 * @since    1.0.0
	 * @access 	 private
	 */
	private function build_authenticated_users_table(){
		global $wpdb;

		/*
			Switch to the top level blog from multisite.
		*/
		if( is_multisite() ){
			global $switched;
			switch_to_blog(1);
		}

		/*
			Define the authenticated users table names
		*/
		$wpsLoginTableName = $wpdb->prefix."wps_authenticated_users";

		/*
			The script to build the authenticated users table.
		*/
		$wpsLoginTable = 'CREATE TABLE IF NOT EXISTS `'.$wpsLoginTableName.'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `blog_id` int(11) DEFAULT NULL,
		  `user_id` int(11) NOT NULL,
		  `ip` varchar(25) NOT NULL,
		  `expires` DATETIME NOT NULL,
		  `first_login` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

		/*
			Run the query to build the authenticated users table.
		*/
		$wpdb->query( $wpsLoginTable );

		/*
			Restore current blog.
		*/
		if( is_multisite() ){
			restore_current_blog();
		}
	}

	/**
	 * Build the settings table
	 *
	 * @since    1.0.0
	 * @access 	 private
	 */
	private function build_settings_table(){
		global $wpdb;

		/*
			Switch to the top level blog from multisite.
		*/
		if( is_multisite() ){
			global $switched;
			switch_to_blog(1);
		}

		/*
			Define the settings table name
		*/
		$wpsSettingsName = $wpdb->prefix."wps_settings";

		/*
			The script to build the settings table.
		*/
		$wpsSettingsTable = 'CREATE TABLE IF NOT EXISTS `'.$wpsSettingsName.'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `blog_id` int(11) DEFAULT NULL,
		  `preview_hash` varchar(50) NOT NULL,
		  `enabled` int(1) DEFAULT \'1\',
			`logo` text NULL,
			`main_title` varchar(255) NULL,
			`sub_title` varchar(255) NULL,
			`show_login_link` int(1) DEFAULT 0,
			`background_color_1` varchar(7) DEFAULT \'#5CCCF0\',
			`background_color_2` varchar(7) DEFAULT \'#3884E8\',
			`twitter_url` text NULL,
			`facebook_url` text NULL,
			`google_plus_url` text NULL,
			`instagram_url` text NULL,
			`vimeo_url` text NULL,
			`dribbble_url` text NULL,
			`youtube_url` text NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';

		/*
			Run the query to build the settings table.
		*/
		$wpdb->query( $wpsSettingsTable );

		/*
			Restore current blog.
		*/
		if( is_multisite() ){
			restore_current_blog();
		}
	}

	/**
	 * Build the IPs Table
	 *
	 * @since    1.0.0
	 * @access 	 private
	 */
	private function build_ips_table(){
		global $wpdb;

		/*
			Switch to the top level blog from multisite.
		*/
		if( is_multisite() ){
			global $switched;
			switch_to_blog(1);
		}

		/*
			Define the IP table names
		*/
		$wpsIPsName = $wpdb->prefix."wps_ips";

		/*
			The script to build the IPs table.
		*/
		$wpsIPsTable = 'CREATE TABLE IF NOT EXISTS `'.$wpsIPsName.'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `blog_id` int(11) DEFAULT NULL,
		  `added_by` int(11) NOT NULL,
		  `ip` varchar(20) NOT NULL,
		  `expires` DATETIME NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';

		/*
			Run the query to build the authenticated users table.
		*/
		$wpdb->query( $wpsIPsTable );

		/*
			Restore current blog.
		*/
		if( is_multisite() ){
			restore_current_blog();
		}
	}

	/**
	 * Build the IP Ranges Table
	 *
	 * @since    1.0.0
	 * @access 	 private
	 */
	private function build_ip_ranges_table(){
		global $wpdb;

		/*
			Switch to the top level blog from multisite.
		*/
		if( is_multisite() ){
			global $switched;
			switch_to_blog(1);
		}

		/*
			Define the IP Ranges Table
		*/
		$wpsIPRangeName = $wpdb->prefix."wps_ip_ranges";

		/*
			The script to build the IP Ranges table.
		*/
		$wpsIPRangeTable = 'CREATE TABLE IF NOT EXISTS `'.$wpsIPRangeName.'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `blog_id` int(11) DEFAULT NULL,
		  `added_by` int(11) NOT NULL,
		  `start_ip` varchar(20) NOT NULL,
		  `end_ip` varchar(20) NOT NULL,
		  `expires` DATETIME NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

		/*
			Run the query to build the IP Ranges table
		*/
		$wpdb->query( $wpsIPRangeTable );

		/*
			Restore current blog.
		*/
		if( is_multisite() ){
			restore_current_blog();
		}
	}

	/**
	 * Build the tables for Subnets Table
	 *
	 * @since    1.0.0
	 * @access 	 private
	 */
	private function build_subnets_table(){
		global $wpdb;

		/*
			Switch to the top level blog from multisite.
		*/
		if( is_multisite() ){
			global $switched;
			switch_to_blog(1);
		}

		/*
			Define the subnets table names
		*/
		$wpsSubnetName = $wpdb->prefix."wps_subnets";

		/*
			The script to build the subnet table.
		*/
		$wpsSubnetTable = 'CREATE TABLE IF NOT EXISTS `'.$wpsSubnetName.'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `blog_id` int(11) DEFAULT NULL,
		  `added_by` int(11) NOT NULL,
		  `start_ip` varchar(20) NOT NULL,
		  `subnet` varchar(2) NOT NULL,
		  `expires` DATETIME NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

		/*
			Run the query to build the subnets table.
		*/
		$wpdb->query( $wpsSubnetTable );

		/*
			Restore current blog.
		*/
		if( is_multisite() ){
			restore_current_blog();
		}
	}
}
