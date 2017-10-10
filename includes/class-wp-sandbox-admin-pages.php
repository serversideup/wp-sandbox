<?php
/**
 * Handles the functionality of displaying admin pages
 *
 * @link       https://521dimensions.com
 * @since      1.0.0
 *
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/includes
 */

/**
 * Handles the functionality of displaying admin pages
 *
 * Loads all of the information for the admin settings pages.
 *
 * @since      1.0.0
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/includes
 * @author     521 Dimensions <dan@521dimensions.com>
 */
class WP_Sandbox_Admin_Pages{
	/**
	 * Add the admin menu to the Settings page.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function add_admin_menu(){
		add_submenu_page('options-general.php', 'WP Sandbox', 'WP Sandbox', 'manage_options', 'wp_sandbox_settings', array( $this, 'plugin_settings_page' ) );
	}

	/**
	 * Add the settings link to the plugins list page.
	 *
	 * @since 	1.0.0
	 * @access 	public
	 * @param 	array 		$links 		Array of links on the plugin page that we add the Settings page to.
	 */
	public function add_settings_link( $links ){
		$settings_link = '<a href="options-general.php?page=wp_sandbox_settings">' . __( 'Settings' ) . '</a>';
    array_push( $links, $settings_link );

  	return $links;
	}

	/**
	 * Add the settings link page.
	 *
	 * @since 	1.0.0
	 * @access 	public
	 */
	public function plugin_settings_page(){
		$settings = WP_Sandbox_Settings::get_all_settings();

		/*
			Get the preview hash
		*/
		$previewHash 			= WP_Sandbox_Preview_URL::get_preview_hash();

		/*
			Get all of the access rules
		*/
		$authenticatedUsers 	= WP_Sandbox_Authenticated_Users::get_authenticated_users();
		$ips 									= WP_Sandbox_IP::get_ips();
		$ipRanges 						= WP_Sandbox_IP_Range::get_ip_ranges();
		$subnets 							= WP_Sandbox_Subnet::get_subnets();

		/*
			Get the preview URL
		*/
		$previewURL = home_url('/').'?wp-sandbox-preview='.$previewHash;

		include( WP_SANDBOX_PATH.'/admin/partials/wp-sandbox-settings-display.php' );
	}
}
