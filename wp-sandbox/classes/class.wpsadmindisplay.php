<?php
	class WPSAdminDisplay{
		public static function displayNetworkAdminScreen( $version, $sites, $authenticatedUsers, $ips, $ipRanges, $subnets ){
			/* Start top logo display */
			echo '<img class="wps-header-logo" src="'.plugins_url().'/wp-sandbox/images/wp-sandbox-logo-large.png"/>';
			/* End top logo display */
			
			/* Start top version display */
			echo '<br>Version: '.$version.'<br>';
			/* End top version display */

			/* Start sites enabled/disabled banner */
			echo '<div id="wps-settings-saved" class="updated">';
				echo 'WP Sandbox changes saved';
			echo '</div>';
			/* End sites enabled/disabled banner */

			/* Begin Enabled Sites Panel */
			echo '<div class="wrap">';
				echo '<div class="wps-panel">';
					echo '<h3>Enabled Sites</h3>';
					echo '<hr>';

					/* Begin Enabled Sites Table */
					echo '<table class="wps-table" id="wps-enabled-sites-table">';
						echo '<thead>';
							echo '<tr>';
								echo '<td><input type="checkbox" id="wps-toggle-select-deselect-all"/> Enabled</td>';
								echo '<td>Site Name</td>';
								echo '<td>Site URL</td>';
							echo '</tr>';
						echo '</thead>';
						echo '<tbody>';
							foreach( $sites as $site ){
								$blogInfo = get_blog_details( $site['blog_id'] );
								echo '<tr>';
									if( $site['setting_value'] == 1 ){
										echo '<td>';
											echo '<input type="checkbox" class="wps-network-enable-checkbox" value="'.$site['blog_id'].'" checked="checked">';
										echo '</td>';
									}else{
										echo '<td>';
											echo '<input type="checkbox" class="wps-network-enable-checkbox" value="'.$site['blog_id'].'">';
										echo '</td>';
									}
									echo '<td>';
										echo $blogInfo->blogname;
									echo '</td>';
									echo '<td>';
										echo $blogInfo->siteurl;
									echo '</td>';
								echo '</tr>';
							}
						echo '</tbody>';
					echo '</table>';
					/* End Enabled Sites Table */

					echo '<br>';

					echo '<button id="wps-network-enable-sites-save" class="button button-primary">Save Changes</button>';
				echo '</div>';
			echo '</div>';
			/* End Enabled Sites Panel */

			/* Begin Network Access Panel */
			echo '<div class="wrap">';
				echo '<div class="wps-panel">';
					echo '<h3>Network Access</h3>';
					echo '<hr>';

					/* Begin Network Access Table */
					echo '<table class="wps-table" id="wps-network-access-table">';
						echo '<thead>';
							echo '<tr>';
								echo '<td>Type</td>';
								echo '<td>Site</td>';
								echo '<td>Network/IPs</td>';
								echo '<td>Added By</td>';
								echo '<td>Expires</td>';
								echo '<td></td>';
							echo '</tr>';
						echo '</thead>';
						echo '<tbody>';
							/*
								Authenticated Users
							*/
							foreach( $authenticatedUsers as $authenticatedUser ){
								$user = get_userdata( $authenticatedUser['user_id'] );
								$blogInfo = get_blog_details( $authenticatedUser['blog_id'] );

								echo '<tr id="user-'.$authenticatedUser['id'].'">';
									echo '<td>User ('.$user->user_login.')</td>';
									echo '<td>'.$blogInfo->blogname.'</td>';
									echo '<td>'.$authenticatedUser['ip'].'</td>';
									echo '<td>-</td>';
									echo '<td>'.( $authenticatedUser['expires'] == '0000-00-00 00:00:00' ? 'never' : date('m-d-Y H:i:s', strtotime( $authenticatedUser['expires'] ) ) ).'</td>';
									echo '<td><a class="wps-remove-access" data-attr-type="user" data-attr-id="'.$authenticatedUser['id'].'">Remove Access</a></td>';
								echo '</tr>';
							} 
							/*
								IPs
							*/
							foreach( $ips as $ip ){
								$user = get_userdata( $ip['added_by'] );
								$blogInfo = get_blog_details( $ip['blog_id'] );

								echo '<tr id="single-'.$ip['id'].'">';
									echo '<td>Single IP</td>';
									echo '<td>'.$blogInfo->blogname.'</td>';
									echo '<td>'.$ip['ip'].'</td>';
									echo '<td>'.$user->user_login.'</td>';
									echo '<td>'.( $ip['expires'] == '0000-00-00 00:00:00' ? 'never' : date('m-d-Y H:i:s', strtotime( $ip['expires'] ) ) ).'</td>';
									echo '<td><a class="wps-remove-access" data-attr-type="single" data-attr-id="'.$ip['id'].'">Remove Access</a></td>';
								echo '</tr>';
							}
							/*
								IP Range
							*/
							foreach( $ipRanges as $ipRange ){
								$user = get_userdata( $ipRange['added_by'] );
								$blogInfo = get_blog_details( $ip['blog_id'] );

								echo '<tr id="range-'.$ipRange['id'].'">';
									echo '<td>IP Range</td>';
									echo '<td>'.$blogInfo->blogname.'</td>';
									echo '<td>'.$ipRange['start_ip'].'-'.$ipRange['end_ip'].'</td>';
									echo '<td>'.$user->user_login.'</td>';
									echo '<td>'.( $ipRange['expires'] == '0000-00-00 00:00:00' ? 'never' : date('m-d-Y H:i:s', strtotime( $ipRange['expires'] ) ) ).'</td>';
									echo '<td><a class="wps-remove-access" data-attr-type="range" data-attr-id="'.$ipRange['id'].'">Remove Access</a></td>';
								echo '</tr>';
							}

							/*
								Networks
							*/
							foreach( $subnets as $subnet ){
								$user = get_userdata( $subnet['added_by'] );
								$blogInfo = get_blog_details( $ip['blog_id'] );

								echo '<tr id="subnet-'.$subnet['id'].'">';
									echo '<td>Network</td>';
									echo '<td>'.$blogInfo->blogname.'</td>';
									echo '<td>'.$subnet['start_ip'].'/'.$subnet['subnet'].'</td>';
									echo '<td>'.$user->user_login.'</td>';
									echo '<td>'.( $subnet['expires'] == '0000-00-00 00:00:00' ? 'never' : date('m-d-Y H:i:s', strtotime( $subnet['expires'] ) ) ).'</td>';
									echo '<td><a class="wps-remove-access" data-attr-type="subnet" data-attr-id="'.$subnet['id'].'">Remove Access</a></td>';
								echo '</tr>';
							}
						echo '</tbody>';
					echo '</table>';
					/* End Network Access Table */
				echo '</div>';
			echo '</div>';
			/* End Network Access Panel */
		}
		public static function displaySingleSiteSettingsScreen( $version, $defaultPage, $defaultExpirationTime, $previewHash, $enabled, $pages ){
			/* Start top logo display */
			echo '<img class="wps-header-logo" src="'.plugins_url().'/wp-sandbox/images/wp-sandbox-logo-large.png"/>';
			/* End top logo display */

			/* Start top version display */
			echo '<br>Version: '.$version.'<br>';
			/* End top version display */

			/* Start plugin status banner */
			echo '<div id="wps-disabled-banner" '.( $enabled == '1' ? 'class="wps-disabled-banner-enabled"' : '' ).'>';
				echo 'WP Sandbox is currently <strong>DISABLED</strong>. Public users are able to access '.home_url('/');
			echo '</div>';
			/* End plugin status banner */

			/* Start plugin settings saved banner */
			echo '<div id="wps-settings-saved" class="updated">';
				echo 'WP Sandbox settings saved';
			echo '</div>';
			/* End plugin settings saved banner */

			/* Start WPS Settings */
			echo '<div class="wrap">';
				echo '<div class="wps-panel">';
					echo '<h3>WP Sandbox Settings</h3>';
					echo '<hr>';

					/* Allow public access to the site. */
					echo '<label class="wps-label wps-large-label">Public Access: </label>';
					echo '<input type="radio" class="wps-radio" name="wps-public-access-setting" value="0" '.( $enabled == '0' ? 'checked="checked"' : '' ) .'/><span class="wps-radio-label">Allowed</span>';
					echo '<input type="radio" class="wps-radio" name="wps-public-access-setting" value="1" '.( $enabled == '1' ? 'checked="checked"' : '' ) .'/><span class="wps-radio-label">Blocked</span>';
					
					echo '<br><br>';

					/* Set the default page for unauthorized users */
					echo '<label class="wps-label wps-large-label">Default Page: </label>';
					echo '<select name="wps-default-page" id="wps-default-page">';
						echo '<option value="blank" '.( $defaultPage == 'blank' ? 'selected="selected"' : '' ).'>Blank</option>';
						echo '<option value="404" '.( $defaultPage == '404' ? 'selected="selected"' : '' ).'>404</option>';
						foreach( $pages as $page ){
							echo '<option value="'.get_page_link( $page->ID ).'" '.( $defaultPage == get_page_link( $page->ID ) ? 'selected="selected"' : '' ).'>'.$page->post_title.'</option>';
						}
					echo '</select>';
					echo '<p>This is the page that will display if the user is unauthorized to view a page. Using this feature allows you to create a custom landing page for unauthorized users.</p>';

					echo '<br>';

					/* Sets the default expiration time for new access rules*/
					echo '<label class="wps-label wps-large-label">Default Expiration: </label>';
					echo '<select id="wps-default-expiration" name="wps-default-expiration">';
						echo '<option value="day" '.( $defaultExpirationTime == 'day' ? 'selected="selected"' : '' ).'>Day</option>';
						echo '<option value="week" '.( $defaultExpirationTime == 'week' ? 'selected="selected"' : '' ).'>Week</option>';
						echo '<option value="twoweeks" '.( $defaultExpirationTime == 'twoweeks' ? 'selected="selected"' : '' ).'>Two Weeks</option>';
						echo '<option value="never" '.( $defaultExpirationTime == 'never' ? 'selected="selected"' : '' ).'>Never</option>';
					echo '</select>';

					echo '<p>When a user logs into Wordpress, their IP will be authenticated for future access. You can control how long this IP should remain authenticated for.</p>';
					
					echo '<a class="button button-primary" id="wps-save-settings"/>Save Changes</a>';
				echo '</div>';
			echo '</div>';
			/* End WPS Settings */
		}

		public static function displaySingleSiteAccessScreen( $version, $previewURL, $defaultExpirationTime, $authenticatedUsers, $ips, $ipRanges, $subnets ){
			/* Start top logo display */
			echo '<img class="wps-header-logo" src="'.plugins_url().'/wp-sandbox/images/wp-sandbox-logo-large.png"/>';
			/* End top logo display */

			/* Start top version display */
			echo '<br>Version: '.$version.'<br>';
			/* End top version display */

			/* Begin Private URL Panel */
			echo '<div class="wrap">';
				echo '<div class="wps-panel">';
					echo '<h3>Private URL</h3>';
					echo '<hr>';
					echo '<label class="wps-label">Share URL: </label>';
					echo '<input type="text" class="wps-text" name="wps-share-url" id="wps-share-url" value="'.$previewURL.'" readonly/>';
					
					echo '<p>Copy the URL above to share with users who need access without IP authentication. NOTE: Any user with this URL will be able to access the site unless the URL is regenerated.</p>';
					
					echo '<button id="wps-regenerate-url" class="button button-primary">Regenerate URL</button>';
				echo '</div>';
			echo '</div>';
			/* End Private URL Panel */

			/* Begin Add Access Panel */
			echo '<div class="wrap">';
				echo '<div class="wps-panel">';
					echo '<h3>Access List</h3>';
					echo '<hr>';
					echo '<label class="wps-label">Access Rule: </label>';
					echo '<input type="text" class="wps-text" name="wps-access-rule" id="wps-access-rule" value=""/>';

					echo '<br>';

					echo '<label class="wps-label">Expires: </label>';
					echo '<select id="wps-expiration" name="wps-expiration">';
						echo '<option value="day" '.( $defaultExpirationTime == 'day' ? 'selected="selected"' : '' ).'>Day</option>';
						echo '<option value="week" '.( $defaultExpirationTime == 'week' ? 'selected="selected"' : '' ).'>Week</option>';
						echo '<option value="twoweeks" '.( $defaultExpirationTime == 'twoweek' ? 'selected="selected"' : '' ).'>Two Weeks</option>';
						echo '<option value="never" '.( $defaultExpirationTime == 'never' ? 'selected="selected"' : '' ).'>Never</option>';
					echo '</select>';

					echo '<br><br>';
					echo '<div id="wps-access-rule-validation" class="validation">Please enter a valid access rule.</div>';
					echo '<button id="wps-add-access-rule" class="button button-primary">Add Access Rule</button>';
				
					echo '<p>You can add an IP address, IP range, or a subnet by using the input box above. The following methods are supported: <br><br>';
					echo '<strong>Single IP Address:</strong> 192.168.1.100<br>';
					echo '<strong>IP Address Range:</strong> 192.168.1.100-192.168.1.199<br>';
					echo '<strong>Network Address:</strong> 192.168.1.0/24<br>';
				echo '</div>';
			echo '</div>';
			/* End Add Access Panel */

			/* Begin Current Site Access Panel */
			echo '<div class="wrap">';
				echo '<div class="wps-panel">';
					echo '<h3>Current Site Access</h3>';
					echo '<hr>';
					echo '<table class="wps-table" id="wps-current-site-access">';
						echo '<thead>';
							echo '<tr>';
								echo '<td>Type</td>';
								echo '<td>Network/IPs</td>';
								echo '<td>Added By</td>';
								echo '<td>Expires</td>';
								echo '<td></td>';
							echo '</tr>';
						echo '</thead>';
						echo '<tbody>';
							/*
								Authenticated Users
							*/
							foreach( $authenticatedUsers as $authenticatedUser ){
								$user = get_userdata( $authenticatedUser['user_id'] );

								echo '<tr id="user-'.$authenticatedUser['id'].'">';
									echo '<td>User ('.$user->user_login.')</td>';
									echo '<td>'.$authenticatedUser['ip'].'</td>';
									echo '<td>-</td>';
									echo '<td>'.( $authenticatedUser['expires'] == '0000-00-00 00:00:00' ? 'never' : date('m-d-Y H:i:s', strtotime( $authenticatedUser['expires'] ) ) ).'</td>';
									echo '<td><a class="wps-remove-access" data-attr-type="user" data-attr-id="'.$authenticatedUser['id'].'">Remove Access</a></td>';
								echo '</tr>';
							} 
							/*
								IPs
							*/
							foreach( $ips as $ip ){
								$user = get_userdata( $ip['added_by'] );

								echo '<tr id="single-'.$ip['id'].'">';
									echo '<td>Single IP</td>';
									echo '<td>'.$ip['ip'].'</td>';
									echo '<td>'.$user->user_login.'</td>';
									echo '<td>'.( $ip['expires'] == '0000-00-00 00:00:00' ? 'never' : date('m-d-Y H:i:s', strtotime( $ip['expires'] ) ) ).'</td>';
									echo '<td><a class="wps-remove-access" data-attr-type="single" data-attr-id="'.$ip['id'].'">Remove Access</a></td>';
								echo '</tr>';
							}
							/*
								IP Range
							*/
							foreach( $ipRanges as $ipRange ){
								$user = get_userdata( $ipRange['added_by'] );

								echo '<tr id="range-'.$ipRange['id'].'">';
									echo '<td>IP Range</td>';
									echo '<td>'.$ipRange['start_ip'].'-'.$ipRange['end_ip'].'</td>';
									echo '<td>'.$user->user_login.'</td>';
									echo '<td>'.( $ipRange['expires'] == '0000-00-00 00:00:00' ? 'never' : date('m-d-Y H:i:s', strtotime( $ipRange['expires'] ) ) ).'</td>';
									echo '<td><a class="wps-remove-access" data-attr-type="range" data-attr-id="'.$ipRange['id'].'">Remove Access</a></td>';
								echo '</tr>';
							}

							/*
								Networks
							*/
							foreach( $subnets as $subnet ){
								$user = get_userdata( $subnet['added_by'] );
								
								echo '<tr id="subnet-'.$subnet['id'].'">';
									echo '<td>Network</td>';
									echo '<td>'.$subnet['start_ip'].'/'.$subnet['subnet'].'</td>';
									echo '<td>'.$user->user_login.'</td>';
									echo '<td>'.( $subnet['expires'] == '0000-00-00 00:00:00' ? 'never' : date('m-d-Y H:i:s', strtotime( $subnet['expires'] ) ) ).'</td>';
									echo '<td><a class="wps-remove-access" data-attr-type="subnet" data-attr-id="'.$subnet['id'].'">Remove Access</a></td>';
								echo '</tr>';
							}
						echo '</tbody>';
					echo '</table>';
				echo '</div>';
			echo '</div>';
		}
	}
?>