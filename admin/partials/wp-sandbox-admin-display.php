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
<img class="wps-header-logo" src="<?php echo plugins_url().'/wp-sandbox/admin/images/wp-sandbox-logo-large.png'; ?>"/>

<br>Version: <?php echo $version; ?><br>

<div id="wps-disabled-banner" <?php echo ( $enabled == '1' ? 'class="wps-disabled-banner-enabled"' : '' ); ?>>
	WP Sandbox is currently <strong>DISABLED</strong>. Public users are able to access <?php echo home_url('/'); ?>
</div>

<div id="wps-settings-saved" class="updated">
	WP Sandbox settings saved
</div>

<div class="wrap">
	<div class="wps-panel">
		<h3>WP Sandbox Settings</h3>
		<hr>

		<label class="wps-label wps-large-label">Public Access: </label>
		<input type="radio" class="wps-radio" name="wps-public-access-setting" value="0" <?php echo ( $enabled == '0' ? 'checked="checked"' : '' ); ?> /><span class="wps-radio-label">Allowed</span>
		<input type="radio" class="wps-radio" name="wps-public-access-setting" value="1" <?php echo ( $enabled == '1' ? 'checked="checked"' : '' ); ?> /><span class="wps-radio-label">Blocked</span>
		
		<br><br>

		<label class="wps-label wps-large-label">Default Page: </label>
		<select name="wps-default-page" id="wps-default-page">
			<option value="blank" <?php echo ( $defaultPage == 'blank' ? 'selected="selected"' : '' ); ?>>Blank</option>
			<option value="404" <?php echo ( $defaultPage == '404' ? 'selected="selected"' : '' ); ?>>404</option>
			<?php foreach( $pages as $page ){ ?>
				<option value="<?php echo get_page_link( $page->ID ); ?>" <?php echo ( $defaultPage == get_page_link( $page->ID ) ? 'selected="selected"' : '' ); ?>><?php echo $page->post_title; ?></option>
			<?php } ?>
		</select>

		<p>This is the page that will display if the user is unauthorized to view a page. Using this feature allows you to create a custom landing page for unauthorized users.</p>

		<br>


		<label class="wps-label wps-large-label">Default Expiration: </label>
		<select id="wps-default-expiration" name="wps-default-expiration">
			<option value="day" <?php echo ( $defaultExpirationTime == 'day' ? 'selected="selected"' : '' ); ?>>Day</option>
			<option value="week" <?php echo ( $defaultExpirationTime == 'week' ? 'selected="selected"' : '' ); ?>>Week</option>
			<option value="twoweeks" <?php echo ( $defaultExpirationTime == 'twoweeks' ? 'selected="selected"' : '' ); ?>>Two Weeks</option>
			<option value="never" <?php echo ( $defaultExpirationTime == 'never' ? 'selected="selected"' : '' ); ?>'>Never</option>
		</select>

		<p>When a user logs into Wordpress, their IP will be authenticated for future access. You can control how long this IP should remain authenticated for.</p>
		
		<a class="button button-primary" id="wps-save-settings"/>Save Changes</a>
	</div>
</div>