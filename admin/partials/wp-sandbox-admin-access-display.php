<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://521dimensions.com
 * @since      1.0.0
 *
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<img class="wps-header-logo" src="<?php echo plugins_url(); ?>/wp-sandbox/images/wp-sandbox-logo-large.png"/>
<br>Version: <?php echo $version; ?><br>

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