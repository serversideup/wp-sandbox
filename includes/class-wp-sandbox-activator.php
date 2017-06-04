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
 * @subpackage WP_Sandbox/includes
 * @author     521 Dimensions <dan@521dimensions.com>
 */
class WP_Sandbox_Activator {

	/**
	 * Handles plugin activation
	 *
	 * When the plugin activation is fired, we configure the database
	 * and set the default settings.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		/*
			Build the tables for the database management.
		*/
		$databaseManagement = new WP_Sandbox_Database_Management();
		$databaseManagement->build_tables();

		/*
			Set the default settings for the plugin.
		*/
		$defaultSettings = new WP_Sandbox_Default_Settings();

		/*
			If the site is multisite set the default settings for 
			multisite.
		*/
		if( is_multisite() ){
			$defaultSettings->set_default_settings_multisite();
		}else{
			$defaultSettings->set_default_settings();
		}
	}
}
