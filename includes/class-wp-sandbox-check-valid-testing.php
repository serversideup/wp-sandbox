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
 * @subpackage WP_Sandbox/includes/access
 * @author     521 Dimensions <dan@521dimensions.com>
 */
class WP_Sandbox_Check_Valid_Testing{
	public function check_valid_testing(){
		$this->remove_expired_rules();

		if( !is_user_logged_in() ){
			if( !$this->check_if_login_page() ){
				$pluginStatus = WP_Sandbox_Settings::getPluginStatus();

				if( $pluginStatus == '1' ){
					$ip = self::get_ip();

					if( WP_Sandbox_Preview_URL::check_valid_preview_url() ){
						return true;
					}

					/*
						Checks if valid cookie
					*/
					if( WP_Sandbox_Preview_URL::check_valid_cookie() ){
						return true;
					}

					/*
						Check if the IP is valid
						for an authenticated user.
					*/
					if( WP_Sandbox_Authenticated_Users::check_valid_ip( $ip ) ){
						return true;
					}

					/*
						Check if the IP is valid
					*/
					if( WP_Sandbox_IP::check_valid_ip( $ip ) ){
						return true;
					}

					/*
						Check if the IP is in a valid range
					*/
					if( WP_Sandbox_IP_Range::check_valid_ip_range( $ip ) ){
						return true;
					}

					/*
						Check if the IP is in a subnet
					*/
					if( WP_Sandbox_Subnet::check_valid_ip_subnet( $ip ) ){
						return true;
					}

					/*
						Once we are here, display the coming soon default
						screen OR the page selected.
					*/
					$this->display_coming_soon();
				}
			}
		}
	}

	/*------------------------------------------------
		Checks the database for expired rules.  If the rule in the database has an expiration
		date before the current time it is removed.

		The exception being when the date is == '0000-00-00 00:00:00' that means the rule 
		never expires.
	------------------------------------------------*/
	private function remove_expired_rules(){
		global $wpdb;

		if( is_multisite() ){
			$currentBlogID = get_current_blog_id();

			/*
				Gets expired users
			*/
			$users = $wpdb->get_results( $wpdb->prepare(
				"SELECT * 
				 FROM ".$wpdb->prefix."wps_authenticated_users
				 WHERE expires < CURDATE()
				 AND blog_id = '%d'",
				 $currentBlogID
			), ARRAY_A );
			
			/*
				Gets expired IPs
			*/
			$ips = $wpdb->get_results( $wpdb->prepare(
				"SELECT * 
				 FROM ".$wpdb->prefix."wps_ips 
				 WHERE expires < CURDATE() 
				 AND blog_id = '%d'",
				 $currentBlogID
			), ARRAY_A );

			/*
				Gets expired IP Ranges
			*/
			$ipRanges = $wpdb->get_results( $wpdb->prepare(
				"SELECT * 
				 FROM ".$wpdb->prefix."wps_ip_ranges
				 WHERE expires < CURDATE()
				 AND blog_id = '%d'",
				 $currentBlogID
			), ARRAY_A );

			/*
				Gets expired Subnets
			*/
			$subnets = $wpdb->get_results( $wpdb->prepare(
				"SELECT *
				 FROM ".$wpdb->prefix."wps_subnets
				 WHERE expires < CURDATE()
				 AND blog_id = '%d'",
				 $currentBlogID
			), ARRAY_A );
		}else{
			/*
				Gets expired users
			*/
			$users = $wpdb->get_results(
				"SELECT *
				 FROM ".$wpdb->prefix."wps_authenticated_users
				 WHERE expires < CURDATE()",
				 ARRAY_A );

			/*
				Gets expired IPs
			*/
			$ips = $wpdb->get_results(
				"SELECT * 
				 FROM ".$wpdb->prefix."wps_ips 
				 WHERE expires < CURDATE()",
				 ARRAY_A );

			/*
				Gets expired IP Ranges
			*/
			$ipRanges = $wpdb->get_results(
				"SELECT * 
				 FROM ".$wpdb->prefix."wps_ip_ranges
				 WHERE expires < CURDATE()",
				 ARRAY_A );

			/*
				Gets expired Subnets
			*/
			$subnets = $wpdb->get_results(
				"SELECT *
				 FROM ".$wpdb->prefix."wps_subnets
				 WHERE expires < CURDATE()",
			 	 ARRAY_A );
		}
		
		/*
			Removes expired users
		*/
		foreach( $users as $user ){
			if( $user['expires'] != '0000-00-00 00:00:00' ){
				WP_Sandbox_Authenticated_Users::deleteAuthenticatedUser( $user['id'] );
			}
		}

		/*
			Removes expired IPs
		*/
		foreach( $ips as $ip ){
			if( $ip['expires'] != '0000-00-00 00:00:00' ){
				WP_Sandbox_IP::deleteIP( $ip['id'] );
			}
		}
		
		/*
			Removes expired IP Ranges
		*/
		foreach( $ipRanges as $ipRange ){
			if( $ipRange['expires'] != '0000-00-00 00:00:00' ){
				WP_Sandbox_IP_Range::deleteRange( $ipRange['id'] );
			}
		}

		/*
			Removes expired subnets
		*/
		foreach( $subnets as $subnet ){
			if( $subnet['expires'] != '0000-00-00 00:00:00' ){
				WP_Sandbox_Subnet::deleteSubnet( $subnet['id'] );
			}
		}
	}

	/*------------------------------------------------
		Checks if the page is a log in page on Wordpress. 
		Allows front-end users to log in.
		@return bool
	------------------------------------------------*/
	private function check_if_login_page(){
		if( is_admin() ){
			return true;
		}else{
			return in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) );
		}
	}

	/*------------------------------------------------
		Gets the IP address to be tested
	------------------------------------------------*/
	public static function get_ip(){
		/*
			Checks for a CloudFlare IP first.
		*/
		if( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) && $_SERVER['HTTP_CF_CONNECTING_IP'] != '' ){
			return $_SERVER['HTTP_CF_CONNECTING_IP'];
		}

		/*
			Returns the IP address of the user.
		*/
		return $_SERVER['REMOTE_ADDR'];
	}

	private function display_coming_soon(){
		global $wpdb;

		$defaultPage = WP_Sandbox_Settings::getDefaultPage();

		/*
			If 404 is the default, search for the 404 template.  
			If there is no template, throw a 404 error.
		*/
		if( $defaultPage == '404' ){
			header("HTTP/1.0 404 Not Found - Archive Empty");
			$locate_template = locate_template( '404.php' );

			if ( !empty( $locate_template ) ){
				require TEMPLATEPATH.'/404.php';
			}

			exit;
		}

		/*
			If the setting value is blank, throw a blank page.
		*/
		if( $defaultPage == 'blank'){
			header("HTTP/1.0 404 Not Found - Archive Empty");
			exit;
		}

		/*
			If a page is set, display the page, and throw a 'wps' 
			parameter in the GET so there are no infinite redirects.
		*/
		if( !isset($_GET['wps'] ) || $_GET['wps'] != 'true' ){
			$url = $checkDefaultWPSPage[0]['setting_value'].'?wps=true';
			wp_redirect( $url );
			exit;
		}
	}
}