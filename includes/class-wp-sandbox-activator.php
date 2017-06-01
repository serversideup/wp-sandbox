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
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		$databaseManagement = new WP_Sandbox_Database_Management();
		$databaseManagement->build_tables();

		$defaultSettings = new WP_Sandbox_Default_Settings();

		if( is_multisite() ){
			$defaultSettings->setDefaultSettingsMultisite();
		}else{
			$defaultSettings->setDefaultSettings();
		}
		
	}
}
