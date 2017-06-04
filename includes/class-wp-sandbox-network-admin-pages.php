<?php
/**
 * Displays the network admin pages
 *
 * @link       https://521dimensions.com
 * @since      1.0.0
 *
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/includes
 */

/**
 * Handles the display of network admin pages
 *
 * @since      1.0.0
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/includes
 * @author     521 Dimensions <dan@521dimensions.com>
 */
class WP_Sandbox_Network_Admin_Pages{
	/**
	 * Adds the menu pages for the network admin
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function add_network_admin_menu_pages(){
		add_menu_page( 'WP Sandbox', 'WP Sandbox', 'manage_network', 'wp_sandbox', array( $this, 'network_menu' ), plugins_url().'/wp-sandbox/admin/images/wp-sandbox-logo.png' );
	}

	/**
	 * Builds the network admin menu page
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function network_menu(){
		/*
			Gets all of the sites status.
		*/
		$sites = WP_Sandbox_Settings::get_sites_status();

		/*
			Get all of the rules for the plugin
		*/
		$authenticatedUsers = WP_Sandbox_AuthenticatedUsers::get_network_authenticated_users();
		$ips 				= WP_Sandbox_IP::get_network_authenticated_ips();
		$ipRanges 			= WP_Sandbox_IP_Range::get_network_authenticated_ip_ranges();
		$subnets 			= WP_Sandbox_Subnet::get_network_authenticated_subnets();

		/*
			Gets the version for the plugin
		*/
		$version = get_option( "wp_sandbox_version" );

		/*
			Display the network admin display page.
		*/
		include( WP_SANDBOX_PATH.'/admin/partials/wp-sandbox-network-admin-display.php' );
	}
}