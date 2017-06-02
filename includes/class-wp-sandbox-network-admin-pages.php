<?php

class WP_Sandbox_Network_Admin_Pages{
	public function add_network_admin_menu_pages(){
		add_menu_page( 'WP Sandbox', 'WP Sandbox', 'manage_network', 'wp_sandbox', array( $this, 'wpsNetworkMenu' ), plugins_url().'/wp-sandbox/images/wp-sandbox-logo.png' );
	}

	public function network_menu(){
		$sites = WP_Sandbox_Settings::getSitesStatus();

		$authenticatedUsers = WP_Sandbox_AuthenticatedUsers::getNetworkAuthenticatedUsers();
		$ips 				= WP_Sandbox_IP::getNetworkAuthenticatedIPs();
		$ipRanges 			= WP_Sandbox_IP_Range::getNetworkAuthenticatedIPRanges();
		$subnets 			= WP_Sandbox_Subnet::getNetworkAuthenticatedSubnets();

		$version = get_option( "wp_sandbox_version" );

		include( WP_SANDBOX_PATH.'/admin/partials/wp-sandbox-network-admin-display.php' );
	}
}