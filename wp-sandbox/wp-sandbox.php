<?php
/*
	Plugin Name: WP Sandbox
	Plugin URI: http://open.521dimensions.com
	Description: Conveniently blocks out users during development without interferring with testing
	Author: Dan Pastori
	Version: 0.1
	Author URI: http://www.521dimensions.com
*/
	class WPSandbox{
		/*
			Sets up the WPSandbox plugin to work within Wordpress
		*/
		public function __construct(){
			/*
				Activation and de-activation hooks
			*/
			register_activation_hook(__FILE__, array($this, 'wps_install'));
			register_deactivation_hook(__FILE__, array($this, 'wps_uninstall'));

			/*
				Adds the necessary styles and scripts for the plugin to use
			*/
			add_action('admin_init', array($this, 'wps_enqueue_admin_styles'));
			add_action('admin_init', array($this, 'wps_enqueue_admin_scripts'));

			/*
				Configures the admin menu for the plugin
			*/
			add_action('admin_menu', array($this, 'wps_plugin_settings'));

			/*
				Saves the admin user's IP if it's not already in the database
			*/
			add_action('admin_init', array($this, 'wps_save_valid_login'));

			/*
				Adds the actions to make sure that the user is valid and clear
				expired users.
			*/
			add_action('init', array($this, 'wps_check_valid_testing'));
			add_action('init', array($this, 'wps_check_expired_users'));

			/*
				Registers all the outlets for AJAX functions
			*/
			add_action('wp_ajax_wps_save_admin_settings', array($this, 'wps_save_admin_settings'));
			add_action('wp_ajax_wps_remove_user', array($this, 'wps_remove_user'));
			add_action('wp_ajax_wps_reload_users', array($this, 'wps_reload_users'));
			add_action('wp_ajax_wps_allow_ip', array($this, 'wps_allow_ip'));
			add_action('wp_ajax_wps_generate_preview_hash_url', array($this, 'wps_generate_preview_hash_url'));
			add_action('wp_ajax_wps_enable_plugin', array($this, 'wps_enable_plugin'));
			add_action('wp_ajax_wps_save_ip_ranges', array($this, 'wps_save_ip_ranges'));
			add_action('wp_ajax_wps_delete_ip_range', array($this, 'wps_delete_ip_range'));
			add_action('wp_ajax_wps_reload_ip_range_table', array($this, 'wps_reload_ip_range_table'));
			add_action('wp_ajax_wps_save_subnets', array($this, 'wps_save_subnets'));
			add_action('wp_ajax_wps_remove_subnet', array($this, 'wps_remove_subnet'));
			add_action('wp_ajax_wps_reload_subnet_table', array($this, 'wps_reload_subnet_table'));
		}

		public function __destruct(){

		}

		//-------------------------------------------------------//
		/* ACTIVATION AND DE-ACTIVATION FUNCTIONS */

		/*
			Installs the plugin by building the necessary tables, then adds 
			admin to the database as a valid user.
		*/
		public function wps_install(){
			$this->wps_build_tables();
			$this->wps_check_valid_testing();
		}

		/*
			Uninstalls the plugin by removing the tables created.
		*/
		public function wps_uninstall(){
			$this->wps_destroy_tables();
		}

		//-------------------------------------------------------//
		/* SCRIPT AND STYLE SET UP FUNCTIONS */

		/*
			Sets up scripts for the admin backend used by the plugin.
		*/
		public function wps_enqueue_admin_scripts(){
			wp_enqueue_script('wps-admin-scripts', plugins_url().'/wp-sandbox/js/wp-sandbox-admin-scripts.js', array('jquery'));
		}

		/*
			Sets up styles for the admin backend used by the plugin.
		*/
		public function wps_enqueue_admin_styles(){
			wp_enqueue_style('wps-admin-styles', plugins_url().'/wp-sandbox/css/wp-sandbox-admin-styles.css');
		}

		//-------------------------------------------------------//
		/* ADMIN MENU SET UP */
		
		/*
			Adds the plugin administration menu to the admin backend under Settings->WP Sandbox
		*/
		public function wps_plugin_settings() {
			add_options_page('WP Sandbox', 'WP Sandbox', 'manage_options', 'wp-sandbox-settings-page', array($this, 'wps_settings_page'));
		}

		/*
			Displays the sandbox settings page
		*/
		public function wps_settings_page(){
			global $wpdb;

			$checkDefaultWPSPageQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Default Page'";
			$checkDefaultWPSPage = $wpdb->get_results($checkDefaultWPSPageQuery, ARRAY_A);

			$getAllValidatedUsersQuery = "SELECT * FROM ".$wpdb->prefix."wps_coming_soon";
			$allValidatedUsers = $wpdb->get_results($getAllValidatedUsersQuery, ARRAY_A);

			$getPreviewHashQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Preview Hash'";
			$previewHash = $wpdb->get_results($getPreviewHashQuery, ARRAY_A);

			$getIPRangesQuery = "SELECT start_ip, end_ip FROM ".$wpdb->prefix."wps_ip_ranges";
			$ipRanges = $wpdb->get_results($getIPRangesQuery, ARRAY_A);

			$getSubnetsQuery = "SELECT start_ip, subnet FROM ".$wpdb->prefix."wps_subnets";
			$subnets = $wpdb->get_results($getSubnetsQuery, ARRAY_A);

			echo '<h1>WP Sandbox Settings</h1>';
			echo '<div id="wps-left">';
				echo '<div id="wps-settings-saved">Settings Saved!</div>';
				echo '<div class="wps-settings"><p>Please select a page to redirect users to who don\'t have permissions to view your site. If no page is selected, the default 404 page will show.</p>';
				echo '<strong>Page for Unauthorized Users: </strong><br><select name="wps-default-page" id="wps-default-page"> 
	 					<option value="">'.esc_attr( __( 'Select page' ) ).'</option>';
	 					if($checkDefaultWPSPage[0]['setting_value'] == 'blank'){
							echo '<option value="blank" selected>Blank</option>';
						}else{
							echo '<option value="blank">Blank</option>';
						}
	 
	  					$pages = get_pages(); 
						foreach ( $pages as $page ) {
							$link = get_page_link($page->ID);
							if($link == $checkDefaultWPSPage[0]['setting_value']){
								$option = '<option value="' . get_page_link( $page->ID ) . '" selected>';
							}else{
						  		$option = '<option value="' . get_page_link( $page->ID ) . '">';
						  	}
							$option .= $page->post_title;
							$option .= '</option>';
							echo $option;
						 }
				echo '</select><br>';
				echo '<button id="wps-save-default-page-button" onclick="wps_save_default_page_setting()">Save Default Page</button></div>';

				$checkDefaultWPSExpireQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Default Expiration Time'";
				$checkDefaultWPSExpire = $wpdb->get_results($checkDefaultWPSExpireQuery, ARRAY_A);

				$checkDefaultEnabledQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Enabled'";
				$checkDefaultEnabled = $wpdb->get_results($checkDefaultEnabledQuery, ARRAY_A);

				echo '<div class="wps-settings">';
					echo '<strong>Set Default Expiration Time: </strong><br>';
					echo '<select name="wps-default-expire-time" id="wps-default-expire-time">';
						if($checkDefaultWPSExpire[0]['setting_value'] == 'day'){
							echo '<option value="day" selected>Day</option>';
						}else{
							echo '<option value="day">Day</option>';
						}
						
						if($checkDefaultWPSExpire[0]['setting_value'] == 'week'){
							echo '<option value="week" selected>Week</option>';
						}else{
							echo '<option value="week">Week</option>';
						}
						
						if($checkDefaultWPSExpire[0]['setting_value'] == 'twoweeks'){
							echo '<option value="twoweeks" selected>Two Weeks</option>';
						}else{
							echo '<option value="twoweeks">Two Weeks</option>';
						}
						
						if($checkDefaultWPSExpire[0]['setting_value'] == 'month'){
							echo '<option value="month" selected>Month</option>';
						}else{
							echo '<option value="month">Month</option>';
						}
						
						if($checkDefaultWPSExpire[0]['setting_value'] == 'never'){
							echo '<option value="never" selected>Never Expire</option>';
						}else{
							echo '<option value="never">Never Expire</option>';
						}
					echo '</select><br>';
					echo '<button id="wps-save-default-expire-time-button" onclick="wps_save_default_expire_time()">Save Default Expiration Time</button>';
				echo '</div>';
				echo '<div class="wps-settings">';
					echo '<div id="wps-allow-ip">';
						echo '<strong>Allow this IP: </strong><br><input type="text" id="wps-allowed-ip" name="wps-allowed-ip"/><br>';
						echo '<strong>For: </strong><select name="wps-ip-allowed-expire-time" id="wps-ip-allowed-expire-time">';
							echo '<option value="day">One Day</option>';
							echo '<option value="week">One Week</option>';
							echo '<option value="twoweeks">Two Weeks</option>';
							echo '<option value="month">One Month</option>';
							echo '<option value="never">Never Expire</option>';
						echo '</select>';
						echo '<button onclick="wps_allow_ip()" id="wps-allow-ip-button">Allow Access</button>';
					echo '</div>';
				echo '</div>';
			echo '</div>';

			echo '<div id="wps-right">';
                echo '<span class="switch-label"><strong>Enabled</strong></span>';
                echo '<span class="switch-off">OFF</span>';
                echo '<div class="onoffswitch">';
                	if($checkDefaultEnabled[0]['setting_value'] == '1'){
                		echo '<input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="sandbox-enabled" checked>';
                	}else{
                		echo '<input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="sandbox-enabled">';
                	}
	                echo '<label class="onoffswitch-label" for="sandbox-enabled">';
	                    echo '<div class="onoffswitch-inner"></div>';
	                    echo '<div class="onoffswitch-switch"></div>';
	                echo '</label>';
                echo '</div>';
                echo '<span class="switch-on">ON</span><br>';

                //IP Ranges
				echo '<div id="wps-ip-range">';
					echo '<h3>IP Ranges</h3>';
					echo '<div class="ip-range-row">';
						echo '<div class="inner-left-range">';
							echo '<strong>Starting IP: </strong><br><input type="text" name="wps-starting-ip[]"/>';
						echo '</div>';
						echo '<div class="inner-middle-range">';
							echo 'to';
						echo '</div>';
						echo '<div class="inner-right-range">';
							echo '<strong>Ending IP: </strong><br><input type="text" name="wps-ending-ip[]"/>';
						echo '</div>';
					echo '</div>';
				echo '</div>';
				echo '<div id="wps-additional-ip-range">';

				echo '</div>';
				
				echo '<button onclick="wps_add_ip_range()" id="wps-add-ip-button">Add IP Range</button>';
				echo '<button onclick="wps_save_ip_ranges()" id="wps-save-ip-ranges-button">Save IP Ranges</button>';

				//Subnets
				echo '<div id="wps-subnets">';
					echo '<h3>Subnets</h3>';
					echo '<div class="wps-subnet-row">';
						echo '<div class="inner-left-subnet">';
							echo '<strong>IP: </strong><br><input type="text" name="wps-subnet-ip[]"/>';
						echo '</div>';
						echo '<div class="inner-middle-subnet">';
							echo '/';
						echo '</div>';
						echo '<div class="inner-right-subnet">';
							echo '<strong>Subnet</strong><br><input type="text" name="wps-subnet-subnet[]"/>';
						echo '</div>';
					echo '</div>';
					echo '<div id="wps-additional-subnets">';

					echo '</div>';

					echo '<button onclick="wps_add_subnet()" id="wps-add-subnet-button">Add Subnet</button>';
					echo '<button onclick="wps_save_subnets()" id="wps-save-subnets-button">Save Subnets</button>';
				echo '</div>';
				echo '<div id="wps-preview-hash-div">';
					echo '<p><strong>Copy the URL below to share with users who need access without IP authentication. NOTE: Any user with this URL will be able to access the site unless the URL is regenerated.</strong></p><br>';
					echo '<strong>Copy this URL</strong>: <input type="text" id="wps-preview-hash" name="wps-preview-hash" onClick="this.select()" value="'.home_url('/').'?wp-sandbox-preview='.$previewHash[0]['setting_value'].'">';
					echo '<button onclick="wps_update_preview_hash()" id="wps-update-preview-hash-button">Update Preview Hash</button>';
				echo '</div>';
				echo '<div id="wps-user-removed">User Removed!</div>';
				echo '<div id="wps-ip-added">IP Added</div>';
				echo '<p>These IPs are auhtenticated to browse the entire site (that includes other machines on the same network)</p>';
				echo '<table id="wps-validated-users">';
					echo '<thead>';
						echo '<tr>';
							echo '<th>Added By</th><th>IP</th><th>Last Login</th><th>Expires</th><th>Remove User</th>';
						echo '</tr>';
					echo '</thead>';
					echo '<tbody id="wps-users-body">';
						foreach($allValidatedUsers as $user){
							$userInfo = get_userdata($user['user_id']);
							echo '<tr>';
								echo '<td>'.$userInfo->user_login.'</td>';
								echo '<td>'.$user['ip'].'</td>';
								echo '<td>'.$user['last_login'].'</td>';
								
								if($user['expires'] == '0000-00-00 00:00:00'){
									echo '<td>Never</td>';
								}else{
									echo '<td>'.$user['expires'].'</td>';
								}

								echo '<td><span class="wps-remove" onclick="wps_remove_user('.$user['user_id'].', \''.$user['ip'].'\')"></span></td>';
							echo '</tr>';
						}
					echo '</tbody>';
				echo '</table>';
				echo '<table id="wps-ip-ranges">';
					echo '<thead>';
						echo '<tr>';
							echo '<th>Start IP</th><th>End IP</th><th>Remove Range</th>';
						echo '</tr>';
					echo '</thead>';
					echo '<tbody id="wps-ip-ranges-body">';
						foreach($ipRanges as $ipRange){
							echo '<tr>';
								echo '<td>'.$ipRange['start_ip'].'</td>';
								echo '<td>'.$ipRange['end_ip'].'</td>';
								echo '<td><span class="wps-remove" onclick="wps_remove_range(\''.$ipRange['start_ip'].'\', \''.$ipRange['end_ip'].'\')"></span></td>';
							echo '</tr>';
						}
					echo '</tbody>';
				echo '</table>';
				echo '<table id="wps-subnets-table">';
					echo '<thead>';
						echo '<tr>';
							echo '<th>Subnet</th><th>Remove Subnet</th>';
						echo '</tr>';
					echo '</thead>';
					echo '<tbody id="wps-subnets-table-body">';
						foreach($subnets as $subnet){
							echo '<tr>';
								echo '<td>'.$subnet['start_ip'].'/'.$subnet['subnet'].'</td>';
								echo '<td><span class="wps-remove" onclick="wps_remove_subnet(\''.$subnet['start_ip'].'\', \''.$subnet['subnet'].'\')"></span></td>';
							echo '</tr>';
						}
					echo '</table>';
				echo '</table>';
			echo '</div>';
		}
		
		//-------------------------------------------------------//
		/* PLUGIN FUNCTIONALITY */

		/*
			Saves the valid login to the database.  The default expiration time is also added
			for the user.
		*/
		public function wps_save_valid_login(){
			global $wpdb;

			if(is_user_logged_in()){
				global $current_user;
				get_currentuserinfo();

				$userID = $current_user->ID;
				$ipAddress = $_SERVER['REMOTE_ADDR'];
				$checkCurrentUserQuery = "SELECT user_id, ip  FROM ".$wpdb->prefix."wps_coming_soon WHERE user_id = '".$userID."' AND ip = '".$ipAddress."'";
				$checkCurrentUser = $wpdb->get_results($checkCurrentUserQuery, ARRAY_A);

				if(empty($checkCurrentUser)){

					$getExpireTime = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Default Expiration Time'";
					$expireTimeOffset = $wpdb->get_results($getExpireTime, ARRAY_A);
					$expireTime = '';

					switch($expireTimeOffset[0]['setting_value']){
						case 'day':
							$expireTime = date('Y-m-d G:i:s', time() + '86400');
						break;
						case 'week':
							$expireTime = date('Y-m-d G:i:s', time() + '604800');
						break;
						case 'twoweeks':
							$expireTime = date('Y-m-d G:i:s', time() + '1209600');
						break;	
						case 'month':
							$expireTime = date('Y-m-d G:i:s', time() + '18144000');
						break;
						case 'never':
							$expireTime = '';
						break;
					}

					$insertValidIPQuery = "INSERT INTO ".$wpdb->prefix."wps_coming_soon (user_id, ip, expires) VALUES ('".$userID."', '".$ipAddress."', '".$expireTime."')";
					
					$wpdb->query($insertValidIPQuery);
				}
			}
		}

		/*
			Checks the database for expired users.  If the user in the database has an expiration
			date before the current time it is removed.

			The exception being when the date is == '0000-00-00 00:00:00' that means the user 
			never expires.
		*/
		public function wps_check_expired_users(){
			global $wpdb;

			$checkExpiredUsersQuery = "SELECT * FROM ".$wpdb->prefix."wps_coming_soon WHERE expires < CURDATE()";
			$expiredUsers = $wpdb->get_results($checkExpiredUsersQuery, ARRAY_A);

			foreach($expiredUsers as $expired){
				if($expired['expires'] != '0000-00-00 00:00:00'){
					$deleteUserQuery = "DELETE FROM ".$wpdb->prefix."wps_coming_soon WHERE user_id = '".$expired['user_id']."' AND ip = '".$expired['ip']."'";
					$wpdb->query($deleteUserQuery);
				}
			}
		}

		/*
			Allows a manual IP to view the page. This is mainly used if a static IP is known.
		*/
		public function wps_allow_ip(){
			global $wpdb;
			if(!$this->wps_check_existing_ip($_POST['ip'])){
				global $current_user;
				get_currentuserinfo();

				$userID = $current_user->ID;
				$ipAddress = $_POST['ip'];
				$expires = $_POST['expires'];

				switch($expires){
					case 'day':
						$expireTime = date('Y-m-d G:i:s', time() + '86400');
					break;
					case 'week':
						$expireTime = date('Y-m-d G:i:s', time() + '604800');
					break;
					case 'twoweeks':
						$expireTime = date('Y-m-d G:i:s', time() + '1209600');
					break;	
					case 'month':
						$expireTime = date('Y-m-d G:i:s', time() + '18144000');
					break;
					case 'never':
						$expireTime = '';
					break;
				}

				$insertValidIPQuery = "INSERT INTO ".$wpdb->prefix."wps_coming_soon (user_id, ip, expires) VALUES ('".$userID."', '".$ipAddress."', '".$expireTime."')";
				$wpdb->query($insertValidIPQuery);

				echo 'true';
			}else{
				echo 'false';
			}
			die();
		}

		/*
			Checks to see if the front end user is capable of viewing the site.
			@return bool if valid
		*/
		public function wps_check_valid_testing(){
			global $wpdb;
			session_start();

			//If the user is logged in, they can view the site.
			if(!is_user_logged_in()){
				//If the user is at the login, the login page will display.
				if(!$this->wps_check_if_log_in_page()){
					//Checks if plugin is enabled
					if($this->wps_check_plugin_enabled()){
						$ip = $_SERVER['REMOTE_ADDR'];

						$checkValidIPQuery = "SELECT ip FROM ".$wpdb->prefix."wps_coming_soon WHERE ip = '".$ip."'";
						$checkValidIP = $wpdb->get_results($checkValidIPQuery, ARRAY_A);

						//If the preview hash is set, check to make sure it's valid and determine if the user can view the page.
						if(isset($_GET['wp-sandbox-preview']) && $_GET['wp-sandbox-preview'] != ''){
							$hash = $_GET['wp-sandbox-preview'];

							if($this->wps_check_hash($hash)){
								$_SESSION['wp-sandbox-preview-hash'] = $hash;
								return true;
							}else{
								$this->wps_display_coming_soon();
							}
						//If the session is active, check to make sure the hash still is and determine if the user can view the page.
						}else if(isset($_SESSION['wp-sandbox-preview-hash']) && $_SESSION['wp-sandbox-preview-hash'] != ''){
							$hash = $_SESSION['wp-sandbox-preview-hash'];
							
							if($this->wps_check_hash($hash)){
								$_SESSION['wp-sandbox-preview-hash'] = $hash;
								return true;
							}else{
								$_SESSION['wp-sandbox-preview-hash'] = '';
								$this->wps_display_coming_soon();
							}
						//If the IP is valid, the user can view the site.
						}else if(!empty($checkValidIP)){
							return true;
						//Check IP Ranges
						}else if($this->wps_check_ip_valid_range($ip)){
							return true;
						}else if($this->wps_check_ip_facebook($ip)){

						}else if($this->wps_check_ip_subnet($ip)){
							return true;
						}else{
							$this->wps_display_coming_soon();
						}
						

						//Check Subnet Mask
					}
				}
			}
		}

		/*
			Checks to see if the IP address is in a valid range of IPs.
			Help from: http://stackoverflow.com/questions/18336908/php-check-if-ip-address-is-in-a-range-of-ip-addresses
		*/
		private function wps_check_ip_valid_range($ipAddress){
			global $wpdb;

			$getIPRangeQuery = "SELECT * FROM ".$wpdb->prefix."wps_ip_ranges";
			$ipRanges = $wpdb->get_results($getIPRangeQuery, ARRAY_A);

			foreach($ipRanges as $ipRange){
		        $min    = ip2long($ipRange['start_ip']);
        		$max    = ip2long($ipRange['end_ip']);
        		$needle = ip2long($ipAddress);  

        		if(($needle >= $min) AND ($needle <= $max)){
        			return true;
        		}
			}
			return false;
		}
		/*
			Checks to see if the IP address is Facebook
		*/
		private function wps_check_ip_facebook($ipAddress){

		}
		/*
			Checks to see if the IP address is in an allowed subnet
		*/
		private function wps_check_ip_subnet($ipAddress){
			global $wpdb;

			$getSubnetsQuery = "SELECT * FROM ".$wpdb->prefix."wps_subnets";
			$subnets = $wpdb->get_results($getSubnetsQuery, ARRAY_A);

			foreach($subnets as $subnet){
				$subnetParts = explode('.', $subnet['start_ip']);
				
				$firstOctet = $subnetParts[0];
				$secondOctet = $subnetParts[1];
				$thirdOctet = $subnetParts[2];
				$fourthOctet = $subnetParts[3];

				$newFirstOctet = '';
				$newSecondOctet = '';
				$newThirdOctet = '';
				$newFourthOctet = '';

				if($subnet['subnet'] <= 8){
					echo '<font color="red">Start IP: </font>'.$subnet['start_ip'];

					if(($firstOctet + $subnet['subnet']) > 255){
						$newFirstOctet = 255;
					}else{
						$newFirstOctet = $firstOctet + $subnet['subnet'];
					}

					echo '<font color="red">End IP: </font>'.$newFirstOctet.'.255.255.254';

					$min    = ip2long($subnet['start_ip']);
	        		$max    = ip2long($newFirstOctet.'.255.255.254');
	        		$needle = ip2long($ipAddress);  

	        		if(($needle >= $min) AND ($needle <= $max)){
	        			return true;
	        		}
				}else if(($subnet['subnet'] > 8) && ($subnet['subnet'] <= 16)){
					echo '<font color="red">Start IP: </font>'.$subnet['start_ip'];

					if(($secondOctet + $subnet['subnet']) > 255){
						$newSecondOctet = ($secondOctet + $subnet['subnet']) - 255;
						$newFirstOctet = $firstOctet + 1;
					}else{
						$newFirstOctet = $firstOctet;
						$newSecondOctet = $secondOctet + $subnet['subnet'];
					}

					echo '<font color="red">End IP: </font>'.$newFirstOctet.'.'.$newSecondOctet.'.255.254';

					$min    = ip2long($subnet['start_ip']);
	        		$max    = ip2long($newFirstOctet.'.255.255.254');
	        		$needle = ip2long($ipAddress);  

	        		if(($needle >= $min) AND ($needle <= $max)){
	        			return true;
	        		}
				}else if(($subnet['subnet'] > 16) && ($subnet['subnet'] <= 24)){
					echo '<font color="red">Start IP: </font>'.$subnet['start_ip'];

					if(($thirdOctet + $subnet['subnet']) > 255){
						$newThirdOctet = ($thirdOctet + $subnet['subnet']) - 255;
						$newSecondOctet = $secondOctet + 1;
						$newFirstOctet = $firstOctet;
					}else{
						$newSecondOctet = $secondOctet;
						$newThirdOctet = $thirdOctet + $subnet['subnet'];
						$newFirstOctet = $firstOctet;
					}

					echo '<font color="red">End IP: </font>'.$newFirstOctet.'.'.$newSecondOctet.'.'.$newThirdOctet.'.254';

					$min    = ip2long($subnet['start_ip']);
	        		$max    = ip2long($newFirstOctet.'.255.255.254');
	        		$needle = ip2long($ipAddress);  

	        		if(($needle >= $min) AND ($needle <= $max)){
	        			return true;
	        		}
				}else if($subnet['subnet'] > 24){
					echo '<font color="red">Start IP: </font>'.$subnet['start_ip'];

					if(($fourthOctet + $subnet['subnet']) > 255){
						$newFourthOctet = ($fourthOctet + $subnet['subnet']) - 255;
						$newThirdOctet = $thirdOctet + 1;
						$newSecondOctet = $secondOctet;
						$newFirstOctet = $firstOctet;
					}else{
						$newFourthOctet = $fourthOctet + $subnet['subnet'];
						$newSecondOctet = $secondOctet;
						$newThirdOctet = $thirdOctet;
						$newFirstOctet = $firstOctet;
					}

					echo '<font color="red">End IP: </font>'.$newFirstOctet.'.'.$newSecondOctet.'.'.$newThirdOctet.'.'.$newFourthOctet;

					$min    = ip2long($subnet['start_ip']);
	        		$max    = ip2long($newFirstOctet.'.255.255.254');
	        		$needle = ip2long($ipAddress);  

	        		if(($needle >= $min) AND ($needle <= $max)){
	        			return true;
	        		}
				}
			}
		}
		/*
			Checks to see if the plugin is enabled
		*/
		private function wps_check_plugin_enabled(){
			global $wpdb;

			$checkPluginEnabledQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Enabled'";
			$pluginEnabled = $wpdb->get_results($checkPluginEnabledQuery, ARRAY_A);

			if($pluginEnabled[0]['setting_value'] == '1'){
				return true;
			}else{
				return false;
			}
		}
		/*
			Checks to see if the $_SESSION or $_GET hash is valid and allows access to the site.
			@return bool
		*/
		private function wps_check_hash($hash){
			global $wpdb;
			$checkHashQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Preview Hash'";
			$hashCheck = $wpdb->get_results($checkHashQuery, ARRAY_A);

			if($hashCheck[0]['setting_value'] == $hash){
				return true;
			}else{
				return false;
			}
		}

		/*
			Displays the coming soon page of the Admin's choice.
		*/
		private function wps_display_coming_soon(){
			global $wpdb;
			$checkDefaultWPSPageQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Default Page'";
			$checkDefaultWPSPage = $wpdb->get_results($checkDefaultWPSPageQuery, ARRAY_A);

			//If there is no page set, search for the 404 template.  If there is no template, throw a 404 error.
			if($checkDefaultWPSPage[0]['setting_value'] == ''){
				header("HTTP/1.0 404 Not Found - Archive Empty");
				$locate_template = locate_template( '404.php' );
					if (!empty($locate_template)){
						require TEMPLATEPATH.'/404.php';
					}
				exit;
			//If the setting value is blank, throw a blank page.
			}else if($checkDefaultWPSPage[0]['setting_value'] == 'blank'){
				header("HTTP/1.0 404 Not Found - Archive Empty");
				exit;
			}else{
				//If a page is set, display the page, and throw a 'wps' parameter in the GET so there are no infinite redirects.
				if(!isset($_GET['wps']) || $_GET['wps'] != 'true'){
					$url = $checkDefaultWPSPage[0]['setting_value'].'?wps=true';
					wp_redirect($url);
					exit;
				}
			}
		}

		/*
			Saves the administrator settings.
			CALLED THROUGH AJAX
		*/
		public function wps_save_admin_settings(){
			$setting = $_POST['setting'];

			switch($setting){
				case 'default_page':
					$this->wps_save_default_page_setting();
					echo 'true';
				break;
				case 'default_expire_time':
					$this->wps_save_default_expire_time();
					echo 'true';
				break;
			}
			die();
		}

		/*
			Removes a user from the database and reloads the table of valid users
			CALLED THROUGH AJAX
		*/
		public function wps_remove_user(){
			global $wpdb;

			$userID = $_POST['wps_user_id'];
			$ip = $_POST['wps_ip'];

			$deleteUserQuery = "DELETE FROM ".$wpdb->prefix."wps_coming_soon WHERE user_id = '".$userID."' AND ip = '".$ip."'";
			$wpdb->query($deleteUserQuery);

			$getAllValidatedUsersQuery = "SELECT * FROM ".$wpdb->prefix."wps_coming_soon";
			$allValidatedUsers = $wpdb->get_results($getAllValidatedUsersQuery, ARRAY_A);

			foreach($allValidatedUsers as $user){
				$userInfo = get_userdata($user['user_id']);
				echo '<tr>';
					echo '<td>'.$userInfo->user_login.'</td>';
					echo '<td>'.$user['ip'].'</td>';
					echo '<td>'.$user['last_login'].'</td>';
					
					if($user['expires'] == '0000-00-00 00:00:00'){
						echo '<td>Never</td>';
					}else{
						echo '<td>'.$user['expires'].'</td>';
					}

					echo '<td><span class="wps-remove" onclick="wps_remove_user('.$user['user_id'].', \''.$user['ip'].'\')"></span></td>';
				echo '</tr>';
			}
			die();
		}

		/*
			Reloads the users table on the administration side.
			CALLED THROUGH AJAX
		*/
		public function wps_reload_users(){
			global $wpdb;
			$getAllValidatedUsersQuery = "SELECT * FROM ".$wpdb->prefix."wps_coming_soon";
			$allValidatedUsers = $wpdb->get_results($getAllValidatedUsersQuery, ARRAY_A);

			foreach($allValidatedUsers as $user){
				$userInfo = get_userdata($user['user_id']);
				echo '<tr>';
					echo '<td>'.$userInfo->user_login.'</td>';
					echo '<td>'.$user['ip'].'</td>';
					echo '<td>'.$user['last_login'].'</td>';
					
					if($user['expires'] == '0000-00-00 00:00:00'){
						echo '<td>Never</td>';
					}else{
						echo '<td>'.$user['expires'].'</td>';
					}

					echo '<td><span class="wps-remove" onclick="wps_remove_user('.$user['user_id'].', \''.$user['ip'].'\')"></span></td>';
				echo '</tr>';
			}
			die();
		}

		/*
			Saves the IP Ranges
			CALLED THROUGH AJAX
		*/
		public function wps_save_ip_ranges(){
			global $wpdb;

			for($i=0; $i<count($_POST['start_range']); $i++){
				$insertIPRangeQuery = "INSERT INTO ".$wpdb->prefix."wps_ip_ranges (start_ip, end_ip) VALUES ('".mysql_real_escape_string($_POST['start_range'][$i])."', '".mysql_real_escape_string($_POST['end_range'][$i])."')";
				$wpdb->query($insertIPRangeQuery);
			}
			die();
		}
		/*
			Saves the default page setting.
			Called from wps_save_admin_settings() which is CALLED THROUGH AJAX
		*/
		private function wps_save_default_page_setting(){
			global $wpdb;

			$defaultPage = $_POST['default_page'];

			$saveDefaultPageQuery = "UPDATE ".$wpdb->prefix."wps_coming_soon_settings SET setting_value = '".$defaultPage."' WHERE setting_name = 'Default Page'";
			$wpdb->query($saveDefaultPageQuery);
		}

		/*
			Checks the database to make sure the IP isn't already white-listed.
		*/
		private function wps_check_existing_ip($ip){
			global $wpdb;

			$checkExistingUserQuery = "SELECT ip FROM ".$wpdb->prefix."wps_coming_soon WHERE ip = '".$ip."'";
			$existingUser = $wpdb->get_results($checkExistingUserQuery, ARRAY_A);

			if(empty($existingUser)){
				return false;
			}else{
				return true;
			}
		}

		/*
			Saves the default expiration time.
			Called from wps_save_admin_settings() which is CALLED THROUGH AJAX
		*/
		private function wps_save_default_expire_time(){
			global $wpdb;

			$defaultExpireTime = $_POST['default_expire_time'];

			$saveDefaultExpireTime = "UPDATE ".$wpdb->prefix."wps_coming_soon_settings SET setting_value = '".$defaultExpireTime."' WHERE setting_name = 'Default Expiration Time'";
			$wpdb->query($saveDefaultExpireTime);
		}

		/*
			Checks if the page is a log in page on Wordpress. Allows front-end users to log in.
			@return bool
		*/
		private function wps_check_if_log_in_page(){
			if(is_admin()){
				return true;
			}else{
    			return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
    		}
		}

		/*
			Builds tables to house settings and users upon install
		*/
		private function wps_build_tables(){
			global $wpdb;

			$wps_login_table_name = $wpdb->prefix."wps_coming_soon";

			$wps_login_table = 'CREATE TABLE IF NOT EXISTS `'.$wps_login_table_name.'` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `user_id` int(11) NOT NULL,
			  `ip` varchar(25) NOT NULL,
			  `expires` DATETIME NOT NULL,
			  `last_login` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
			
			$wpdb->query($wps_login_table);

			$wps_settings_name = $wpdb->prefix."wps_coming_soon_settings";

			$wps_settings_table = 'CREATE TABLE IF NOT EXISTS `'.$wps_settings_name.'` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `setting_name` varchar(50) NOT NULL,
			  `setting_value` text NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';

			$wpdb->query($wps_settings_table);

			$wps_ip_range_name = $wpdb->prefix."wps_ip_ranges";

			$wps_ip_range_table = 'CREATE TABLE IF NOT EXISTS `'.$wps_ip_range_name.'` (
			  `start_ip` varchar(20) NOT NULL,
			  `end_ip` varchar(20) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

			$wpdb->query($wps_ip_range_table);

			$wps_subnet_name = $wpdb->prefix."wps_subnets";

			$wps_subnet_table = 'CREATE TABLE IF NOT EXISTS `'.$wps_subnet_table.'` (
			  `start_ip` varchar(20) NOT NULL,
			  `subnet` varchar(2) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
	
			$wpdb->query($wps_subnet_table);

			$wps_facebook_subnets_name = $wpdb->prefix."wps_facebook_subnets";

			$wps_facebook_subnets_table = 'CREATE TABLE IF NOT EXISTS `'.$wps_facebook_subnets_name.'` (
			  `start_ip` varchar(20) NOT NULL,
			  `subnet` varchar(2) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

			$wpdb->query($wps_facebook_subnets_table);

			$checkDefaultWPSPageQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Default Page'";
			$checkDefaultWPSPage = $wpdb->get_results($checkDefaultWPSPageQuery, ARRAY_A);

			if(empty($checkDefaultWPSPage)){
				$addDefaultPageSettingOption = "INSERT INTO ".$wpdb->prefix."wps_coming_soon_settings (setting_name) VALUES ('Default Page')";
				$wpdb->query($addDefaultPageSettingOption);
			}

			$checkDefaultWPSExpireQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Default Expiration Time'";
			$checkDefaultWPSExpire = $wpdb->get_results($checkDefaultWPSExpireQuery, ARRAY_A);

			if(empty($checkDefaultWPSExpire)){
				$addDefaultExpireOption = "INSERT INTO ".$wpdb->prefix."wps_coming_soon_settings (setting_name, setting_value) VALUES ('Default Expiration Time', 'never')";
				$wpdb->query($addDefaultExpireOption);
			}

			$checkDefaultWPSHashQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Preview Hash'";
			$checkDefaultWPSHash = $wpdb->get_results($checkDefaultWPSHashQuery, ARRAY_A);

			if(empty($checkDefaultWPSHash)){
				$hash = $this->wps_generate_preview_hash();
				$addDefaultHash = "INSERT INTO ".$wpdb->prefix."wps_coming_soon_settings (setting_name, setting_value) VALUES ('Preview Hash', '".$hash."')";
				$wpdb->query($addDefaultHash);
			}

			$checkEnabledQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Enabled'";
			$checkEnabled = $wpdb->get_results($checkEnabledQuery, ARRAY_A);

			if(empty($checkEnabled)){
				$addDefaultEnabled = "INSERT INTO ".$wpdb->prefix."wps_coming_soon_settings (setting_name, setting_value) VALUES ('Enabled', '1')";
				$wpdb->query($addDefaultEnabled);
			}
		}

		/*
			Destroys tables upon un-install
		*/
		private function wps_destroy_tables(){
			global $wpdb;

			$destroyWPSQuery = "DROP TABLE ".$wpdb->prefix."wps_coming_soon";
			$destroyWPSSettingsQuery = "DROP TABLE ".$wpdb->prefix."wps_coming_soon_settings";

			$wpdb->query($destroyWPSQuery);
			$wpdb->query($destroyWPSSettingsQuery);
		}

		/*
			Generates a preview hash url. 
			CALLED THROUGH AJAX
		*/
		public function wps_generate_preview_hash_url(){
			global $wpdb;

			$hash = $this->wps_generate_preview_hash();

			$updateHashQuery = "UPDATE ".$wpdb->prefix."wps_coming_soon_settings SET setting_value = '".$hash."' WHERE setting_name = 'Preview Hash'";
			$wpdb->query($updateHashQuery);

			echo home_url('/').'?wp-sandbox-preview='.$hash;
			die();
		}

		/*
			Generates the actual hash
			Called from wps_generate_preview_hash_url() CALLED THROUGH AJAX.
		*/
		private function wps_generate_preview_hash(){
			global $wpdb;

			$hash = md5(uniqid(rand(), true));

			return substr($hash, 0, 15);
		}

		/*
			Enables and disables the plugin
		*/
		public function wps_enable_plugin(){
			global $wpdb;

			$enabled = $_POST['enabled'];

			$enablePluginQuery = "UPDATE ".$wpdb->prefix."wps_coming_soon_settings SET setting_value = '".$enabled."' WHERE setting_name = 'Enabled'";
			$wpdb->query($enablePluginQuery);

			die();

		}

		/*
			Deletes an IP Range
		*/
		public function wps_delete_ip_range(){
			global $wpdb;

			$start = mysql_real_escape_string($_POST['start']);
			$end = mysql_real_escape_string($_POST['end']);

			$deleteIPRangeQuery = "DELETE FROM ".$wpdb->prefix."wps_ip_ranges WHERE start_ip = '".$start."' AND end_ip = '".$end."'";
			$wpdb->query($deleteIPRangeQuery);

			die();
		}

		/*
			Reloads IP Table
		*/
		public function wps_reload_ip_range_table(){
			global $wpdb;

			$getIPRangesQuery = "SELECT * FROM ".$wpdb->prefix."wps_ip_ranges";
			$ipRanges = $wpdb->get_results($getIPRangesQuery, ARRAY_A);

			foreach($ipRanges as $ipRange){
				echo '<tr>';
					echo '<td>'.$ipRange['start_ip'].'</td>';
					echo '<td>'.$ipRange['end_ip'].'</td>';
					echo '<td><span class="wps-remove" onclick="wps_remove_range(\''.$ipRange['start_ip'].'\', \''.$ipRange['end_ip'].'\')"></span></td>';
				echo '</tr>';
			}
			die();
		}

		/*
			Save Subnets
		*/
		public function wps_save_subnets(){
			global $wpdb;

			for($i=0; $i < count($_POST['ips']); $i++){
				$insertSubnetQuery = "INSERT INTO ".$wpdb->prefix."wps_subnets (start_ip, subnet) VALUES ('".mysql_real_escape_string($_POST['ips'][$i])."', '".mysql_real_escape_string($_POST['subnets'][$i])."')";
				$wpdb->query($insertSubnetQuery);
			}
			die();
		}

		/*
			Delete a subnet
		*/
		public function wps_remove_subnet(){
			global $wpdb;

			$deleteSubnetQuery = "DELETE FROM ".$wpdb->prefix."wps_subnets WHERE start_ip = '".mysql_real_escape_string($_POST['start_ip'])."' AND subnet = '".mysql_real_escape_string($_POST['subnet_extension'])."'";
			$wpdb->query($deleteSubnetQuery);

			die();
		}

		/*
			Reload Subnet Table
		*/
		public function wps_reload_subnet_table(){
			global $wpdb;

			$getSubnetsQuery = "SELECT * FROM ".$wpdb->prefix."wps_subnets";
			$subnets = $wpdb->get_results($getSubnetsQuery, ARRAY_A);

			foreach($subnets as $subnet){
				echo '<tr>';
					echo '<td>'.$subnet['start_ip'].'/'.$subnet['subnet'].'</td>';
					echo '<td><span class="wps-remove" onclick="wps_remove_subnet(\''.$subnet['start_ip'].'\', \''.$subnet['subnet'].'\')"></span></td>';
				echo '</tr>';
			}
			die();
		}
		//-------------------------------------------------------//
		/* MULTISITE FUNCTIONALITY */

		/*
			Functions specific for using the plugin in a Multi-site environment
		*/

	}
	$wpSandbox = new WPSandbox();
?>