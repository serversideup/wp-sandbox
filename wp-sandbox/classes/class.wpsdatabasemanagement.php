<?php
	/*------------------------------------------------
		Class that handles the creation of
		database tables that WP Sandbox uses.
	------------------------------------------------*/
	class WPSDatabaseManagement{
		public function __construct(){

		}

		/*------------------------------------------------
			Builds the database tables used in a
			single site instance. Called on 
			plugin activation.
		------------------------------------------------*/
		public function buildTables(){
			global $wpdb;

			global $switched;
			switch_to_blog(1);

			/*
				Builds the authenticated users table
				which saves the users and what IP
				they are coming from.
			*/
			$wps_login_table_name = $wpdb->prefix."wps_authenticated_users";

			$wps_login_table = 'CREATE TABLE IF NOT EXISTS `'.$wps_login_table_name.'` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `user_id` int(11) NOT NULL,
			  `ip` varchar(25) NOT NULL,
			  `expires` DATETIME NOT NULL,
			  `first_login` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
			
			$wpdb->query($wps_login_table);

			/*
				Builds the settings table which stores
				the user defined settings.
			*/
			$wps_settings_name = $wpdb->prefix."wps_settings";

			$wps_settings_table = 'CREATE TABLE IF NOT EXISTS `'.$wps_settings_name.'` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `setting_name` varchar(50) NOT NULL,
			  `setting_value` text NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';

			$wpdb->query($wps_settings_table);

			/*
				Builds the single IP table for storing
				individual IP addresses.
			*/
			$wps_ips_name = $wpdb->prefix."wps_ips";

			$wps_ips_table = 'CREATE TABLE IF NOT EXISTS `'.$wps_ips_name.'` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `added_by` int(11) NOT NULL,
			  `ip` varchar(20) NOT NULL,
			  `expires` DATETIME NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';

			$wpdb->query($wps_ips_table);

			/*
				Builds the IP ranges table which stores
				valid IP ranges.
			*/
			$wps_ip_range_name = $wpdb->prefix."wps_ip_ranges";

			$wps_ip_range_table = 'CREATE TABLE IF NOT EXISTS `'.$wps_ip_range_name.'` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `added_by` int(11) NOT NULL,
			  `start_ip` varchar(20) NOT NULL,
			  `end_ip` varchar(20) NOT NULL,
			  `expires` DATETIME NOT NULL,
			   PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

			$wpdb->query($wps_ip_range_table);

			/*
				Builds the subnets table which stores
				valid subnets.
			*/
			$wps_subnet_name = $wpdb->prefix."wps_subnets";

			$wps_subnet_table = 'CREATE TABLE IF NOT EXISTS `'.$wps_subnet_name.'` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `added_by` int(11) NOT NULL,
			  `start_ip` varchar(20) NOT NULL,
			  `subnet` varchar(2) NOT NULL,
			  `expires` DATETIME NOT NULL,
			   PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
	
			$wpdb->query($wps_subnet_table);

			restore_current_blog();
		}

		/*------------------------------------------------
			Builds the database tables used in a
			multi-site instance. Called on plugin
			activation.
		------------------------------------------------*/
		public function buildTablesMultisite(){
			global $wpdb;

			global $switched;
			switch_to_blog(1);
			/*
				Builds the authenticated users table
				which saves the users and what IP
				they are coming from.
			*/
			$wps_login_table_name = $wpdb->prefix."wps_authenticated_users";

			$wps_login_table = 'CREATE TABLE IF NOT EXISTS `'.$wps_login_table_name.'` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `blog_id` int(11) NOT NULL,
			  `user_id` int(11) NOT NULL,
			  `ip` varchar(25) NOT NULL,
			  `expires` DATETIME NOT NULL,
			  `first_login` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
			
			$wpdb->query($wps_login_table);

			/*
				Builds the settings table which stores
				the user defined settings.
			*/
			$wps_settings_name = $wpdb->prefix."wps_settings";

			$wps_settings_table = 'CREATE TABLE IF NOT EXISTS `'.$wps_settings_name.'` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `blog_id` int(11) NOT NULL,
			  `setting_name` varchar(50) NOT NULL,
			  `setting_value` text NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';

			$wpdb->query($wps_settings_table);

			/*
				Builds the single IP table for storing
				individual IP addresses.
			*/
			$wps_ips_name = $wpdb->prefix."wps_ips";

			$wps_ips_table = 'CREATE TABLE IF NOT EXISTS `'.$wps_ips_name.'` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `blog_id` int(11) NOT NULL,
			  `added_by` int(11) NOT NULL,
			  `ip` varchar(20) NOT NULL,
			  `expires` DATETIME NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';

			$wpdb->query($wps_ips_table);

			/*
				Builds the IP ranges table which stores
				valid IP ranges.
			*/
			$wps_ip_range_name = $wpdb->prefix."wps_ip_ranges";

			$wps_ip_range_table = 'CREATE TABLE IF NOT EXISTS `'.$wps_ip_range_name.'` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `blog_id` int(11) NOT NULL,
			  `added_by` int(11) NOT NULL,
			  `start_ip` varchar(20) NOT NULL,
			  `end_ip` varchar(20) NOT NULL,
			  `expires` DATETIME NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

			$wpdb->query($wps_ip_range_table);

			/*
				Builds the subnets table which stores
				valid subnets.
			*/
			$wps_subnet_name = $wpdb->prefix."wps_subnets";

			$wps_subnet_table = 'CREATE TABLE IF NOT EXISTS `'.$wps_subnet_name.'` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `blog_id` int(11) NOT NULL,
			  `added_by` int(11) NOT NULL,
			  `start_ip` varchar(20) NOT NULL,
			  `subnet` varchar(2) NOT NULL,
			  `expires` DATETIME NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
	
			$wpdb->query($wps_subnet_table);

			restore_current_blog();
		}

		/*------------------------------------------------
			Destroys tables associted with WP Sandbox
			on uninstall.
		------------------------------------------------*/
		public function destroyTables(){
			global $wpdb;

			global $switched;
			switch_to_blog(1);

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

			restore_current_blog();
		}
	}
?>