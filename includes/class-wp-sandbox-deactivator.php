<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://521dimensions.com
 * @since      1.0.0
 *
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/includes
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
class WP_Sandbox_Deactivator {

	/**
	 * Runs the deactivation scripts on deactivation
	 *
	 * Destroys all of the tables for WP Sandbox when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		$databaseManagement = new WP_Sandbox_Database_Management();

		if( is_multisite() ){
			$referringURL = wp_get_referer();

			/*
				If network activating the plugin, we have to destroy all the data for the
				Wordpress sites
			*/
			if( strpos( $referringURL, '/wp-admin/network/plugins.php' ) !== false ){
				$databaseManagement->destroy_tables();
			}else{
				$databaseManagement->delete_deactivated_data();
			}
		}else{
			$databaseManagement->destroy_tables();
		}
	}

}
