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