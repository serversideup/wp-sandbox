<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://521dimensions.com
 * @since      1.0.0
 *
 * @package    Wp_Sandbox
 * @subpackage Wp_Sandbox/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
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
	 */
	public function build_tables() {
		$this->build_authenticated_users_table();
		$this->build_settings_table();
		$this->build_ips_table();
		$this->build_ip_ranges_table();
		$this->build_subnets_table();
	}

	public function destroy_tables(){
		global $wpdb;

		if( is_multisite() ){
			global $switched;
			switch_to_blog(1);
		}

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

		if( is_multisite() ){
			restore_current_blog();
		}
	}

	private function build_authenticated_users_table(){
		global $wpdb;
		
		if( is_multisite() ){
			global $switched;
			switch_to_blog(1);
		}

		$wpsLoginTableName = $wpdb->prefix."wps_authenticated_users";

		$wpsLoginTable = 'CREATE TABLE IF NOT EXISTS `'.$wpsLoginTableName.'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `blog_id` int(11) DEFAULT NULL,
		  `user_id` int(11) NOT NULL,
		  `ip` varchar(25) NOT NULL,
		  `expires` DATETIME NOT NULL,
		  `first_login` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
		
		$wpdb->query( $wpsLoginTable );

		if( is_multisite() ){
			restore_current_blog();
		}
	}

	private function build_settings_table(){
		global $wpdb;
		
		if( is_multisite() ){
			global $switched;
			switch_to_blog(1);
		}

		$wpsSettingsName = $wpdb->prefix."wps_settings";

		$wpsSettingsTable = 'CREATE TABLE IF NOT EXISTS `'.$wpsSettingsName.'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `blog_id` int(11) DEFAULT NULL,
		  `setting_name` varchar(50) NOT NULL,
		  `setting_value` text NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';

		$wpdb->query( $wpsSettingsTable );

		if( is_multisite() ){
			restore_current_blog();
		}
	}

	private function build_ips_table(){
		global $wpdb;
		
		if( is_multisite() ){
			global $switched;
			switch_to_blog(1);
		}

		$wpsIPsName = $wpdb->prefix."wps_ips";

		$wpsIPsTable = 'CREATE TABLE IF NOT EXISTS `'.$wpsIPsName.'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `blog_id` int(11) DEFAULT NULL,
		  `added_by` int(11) NOT NULL,
		  `ip` varchar(20) NOT NULL,
		  `expires` DATETIME NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';

		$wpdb->query( $wpsIPsTable );

		if( is_multisite() ){
			restore_current_blog();
		}
	}

	private function build_ip_ranges_table(){
		global $wpdb;
		
		if( is_multisite() ){
			global $switched;
			switch_to_blog(1);
		}

		$wpsIPRangeName = $wpdb->prefix."wps_ip_ranges";

		$wpsIPRangeTable = 'CREATE TABLE IF NOT EXISTS `'.$wpsIPRangeName.'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `blog_id` int(11) DEFAULT NULL,
		  `added_by` int(11) NOT NULL,
		  `start_ip` varchar(20) NOT NULL,
		  `end_ip` varchar(20) NOT NULL,
		  `expires` DATETIME NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

		$wpdb->query( $wpsIPRangeTable );

		if( is_multisite() ){
			restore_current_blog();
		}
	}

	private function build_subnets_table(){
		global $wpdb;
		
		if( is_multisite() ){
			global $switched;
			switch_to_blog(1);
		}

		$wpsSubnetName = $wpdb->prefix."wps_subnets";

		$wpsSubnetTable = 'CREATE TABLE IF NOT EXISTS `'.$wpsSubnetName.'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `blog_id` int(11) DEFAULT NULL,
		  `added_by` int(11) NOT NULL,
		  `start_ip` varchar(20) NOT NULL,
		  `subnet` varchar(2) NOT NULL,
		  `expires` DATETIME NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

		$wpdb->query( $wpsSubnetTable );

		if( is_multisite() ){
			restore_current_blog();
		}
	}
}