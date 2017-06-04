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
	 * Add the admin menus
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function add_admin_menu(){
		add_menu_page('WP Sandbox', 'WP Sandbox', 'manage_options', 'wp_sandbox', array( $this, 'settings_page' ), plugins_url().'/wp-sandbox/admin/images/wp-sandbox-logo.png');
		add_submenu_page('wp_sandbox', 'Access', 'Access', 'manage_options', 'wps_access', array ( $this, 'access_page' ) );
	}

	/**
	 * Display the settings page
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function settings_page(){
		$settings = WP_Sandbox_Settings::get_all_settings();

		/*
			Define setting placeholders.
		*/
		$defaultPage 			= '';
		$defaultExpirationTime 	= '';
		$previewHash 			= '';
		$enabled 				= '';

		/*
			Define settings. We have to loop through and grab the
			actual settings from the array since there is no explicit
			connection between the database columns and setting value.
		*/
		foreach( $settings as $setting ){
			switch( $setting['setting_name'] ){
				case 'Default Page':
					$defaultPage = $setting['setting_value'];
				break;
				case 'Default Expiration Time':
					$defaultExpirationTime = $setting['setting_value'];
				break;
				case 'Preview Hash':
					$previewHash = $setting['setting_value'];
				break;
				case 'Enabled':
					$enabled = $setting['setting_value'];
				break;
			}
		}

		/*
			Get all of the site's pages for the default page 
			setting.
		*/
		$pages = get_pages();

		$version = get_option( "wp_sandbox_version" );

		include( WP_SANDBOX_PATH.'/admin/partials/wp-sandbox-admin-display.php' );
	}

	/**
	 * Display the sandbox access page
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function access_page(){
		/*
			Get the preview hash and default expiration times
		*/
		$previewHash 			= WP_Sandbox_Preview_URL::get_preview_hash();
		$defaultExpirationTime 	= WP_Sandbox_Settings::get_default_expiration_time();
			
		/*
			Get all of the access rules
		*/
		$authenticatedUsers 	= WP_Sandbox_Authenticated_Users::get_authenticated_users();
		$ips 					= WP_Sandbox_IP::get_ips();
		$ipRanges 				= WP_Sandbox_IP_Range::get_ip_ranges();
		$subnets 				= WP_Sandbox_Subnet::get_subnets();
		
		/*
			Get the preview URL
		*/
		$previewURL = home_url('/').'?wp-sandbox-preview='.$previewHash;

		/*
			Get the version of Sandbox.
		*/
		$version = get_option( "wp_sandbox_version" );

		/*
			Include the admin access display
		*/
		include( WP_SANDBOX_PATH.'/admin/partials/wp-sandbox-admin-access-display.php' );
	}
}