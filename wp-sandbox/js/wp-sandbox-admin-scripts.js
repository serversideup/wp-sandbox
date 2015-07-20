/*------------------------------------------------
	CLICK HANDLER
	Handles the validations of adding an access 
	rule.  If the rule is validated, then
	we send it to the server using the
	wpsAddAccessRule( type, rule ) function.
------------------------------------------------*/
jQuery(function(){
	jQuery('#wps-add-access-rule').click(function(){
		var type = '';
		var valid = false;
		
		/*
			Removes spaces from the subject.
		*/
		var accessSubject = jQuery('#wps-access-rule').val().replace(/\s/g, '');


		/*
			Validates a single IP
		*/
		if (/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test( accessSubject ) ) {  
			type = 'single';
			valid = true;
		}

		/*
			Validates an IP Range
		*/
		else if (/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\-(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test( accessSubject ) ) {  
			/*
				Checks to see if the range is valid meaning
				that the first IP is lower than the second IP.
			*/
			if( wpsCheckValidRange( accessSubject ) ){
				type = 'range';
				valid = true;
			}
		}

		/*
			Validates a Network
		*/
		else if (/^(1\d{0,2}|2(?:[0-4]\d{0,1}|[6789]|5[0-5]?)?|[3-9]\d?|0)\.(1\d{0,2}|2(?:[0-4]\d{0,1}|[6789]|5[0-5]?)?|[3-9]\d?|0)\.(1\d{0,2}|2(?:[0-4]\d{0,1}|[6789]|5[0-5]?)?|[3-9]\d?|0)\.(1\d{0,2}|2(?:[0-4]\d{0,1}|[6789]|5[0-5]?)?|[3-9]\d?|0)(\/(?:[012]\d?|3[012]?|[0-9]?)){0,1}$/.test( accessSubject ) ) {
			type = 'subnet';
			valid = true;
		}

		/*
			If everything validates we send the 
			type and the rule to the server.
			Otherwise we display an error message
			to the user.
		*/
		if( valid ){
			var expiration = jQuery('#wps-expiration').val();

			wpsAddAccessRule( type, accessSubject, expiration );

			/*
				Reset styles
			*/
			jQuery('#wps-access-rule').css('border', '1px solid #ddd');
			jQuery('#wps-access-rule-validation').hide();
		}else{
			jQuery('#wps-access-rule').css('border', '1px solid red');
			jQuery('#wps-access-rule-validation').show();
		}

	});
});

/*------------------------------------------------
	CLICK HANDLER
	Handles the deletion of an access rule
------------------------------------------------*/
jQuery(function(){
	jQuery('.wps-remove-access').click(function(){
		var ruleID = jQuery(this).attr('data-attr-id');
		var type = jQuery(this).attr('data-attr-type');

		wpsRemoveAccessRule( ruleID, type );
	});
});

/*------------------------------------------------
	CLICK HANDLER
	Regenerates the preview url
------------------------------------------------*/
jQuery(function(){
	jQuery('#wps-regenerate-url').click(function(){
		wpsRegenerateURL();
	});
})

/*------------------------------------------------
	CLICK HANDLER
	Saves the settings from the settings page
------------------------------------------------*/
jQuery(function(){
	jQuery('#wps-save-settings').click(function(){
		wpsSaveSettings();
	});
});

/*------------------------------------------------
	CLICK HANDLER
	Handles the change of the toggle
	enable/disable checkbox
------------------------------------------------*/
jQuery(function(){
	jQuery('#wps-toggle-select-deselect-all').click(function(){
		wpsSelectDeselectAllBlogs( jQuery(this).is(':checked') );
	});
});

/*------------------------------------------------
	CLICK HANDLER
	Submits enable and disabled sites
------------------------------------------------*/
jQuery(function(){
	jQuery('#wps-network-enable-sites-save').click(function(){
		wpsSubmitEnabledDisabledChanges();
	});
});

/*------------------------------------------------
	Checks to see if the IP range is valid
	meaning that the first IP is less than 
	the second IP.
------------------------------------------------*/
function wpsCheckValidRange( ipRange ){
	var ipParts = ipRange.split('-');

	/*
		Get start and ending parts of the IP
		ranges.
	*/
	var ipStartParts = ipParts[0].split('.');
	var ipEndParts = ipParts[1].split('.');
	
	/*
		Checks the first parts
		of the IP addresses.
	*/
	if( ipEndParts[0] > ipStartParts[0] ){
		return true;
	}else{
		/*
			Checks the second parts
			of the IP addresses.
		*/
		if( ( ipEndParts[0] == ipStartParts[0] ) && ( ipEndParts[1] > ipStartParts[1] ) ){
			return true;
		}else{
			/*
				Checks the third parts
				of the IP addresses.
			*/
			if( ( ipEndParts[0] == ipStartParts[0] ) && ( ipEndParts[1] == ipStartParts[1] ) && ( ipEndParts[2] > ipStartParts[2] ) ){
				return true;
			}else{
				/*
					Checks the fourth parts
					of the IP addresses.
				*/
				if( ( ipEndParts[0] == ipStartParts[0] ) && ( ipEndParts[1] == ipStartParts[1] ) && ( ipEndParts[2] == ipStartParts[2] ) && ( ipEndParts[3] > ipStartParts[3] ) ){
					return true;
				}else{
					/*
						If the second IP address
						is before the first IP address
						then the range is false.
					*/
					return false;
				}
			}
		}
	}
}

/*------------------------------------------------
	Submits the new access rule to the server.
------------------------------------------------*/
function wpsAddAccessRule( type, rule, expiration ){
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {
			action: 'wps_add_rule',
			type: type,
			rule: rule,
			expiration: expiration
		}
	}).done( function( response ){
		/*
			Clear the new rule data from the
			form.
		*/
		jQuery('#wps-access-rule').val('');

		/*
			Append the new rule to the table
		*/
		jQuery('table#wps-current-site-access tbody')
			.append(
				'<tr id="user-'+response.rule_id+'">'+
					'<td>' + response.type + '</td>' +
					'<td>' + response.rule + '</td>' +
					'<td>' + response.added_by + '</td>' +
					'<td>' + response.expiration + '</td>' +
					'<td><a class="wps-remove-access" data-attr-type="user" data-attr-id="' + response.rule_id + '">Remove Access</a></td>' +
				'</tr>'
			);

			/*
				Unbinds existing click
				handlers and rebinds the
				newly added row's click
				handlers
			*/
			jQuery('.wps-remove-access').unbind('click');

			jQuery('.wps-remove-access').click(function(){
				var ruleID = jQuery(this).attr('data-attr-id');
				var type = jQuery(this).attr('data-attr-type');

				wpsRemoveAccessRule( ruleID, type );
			});
	});
}

/*------------------------------------------------
	Removes an access rule
------------------------------------------------*/
function wpsRemoveAccessRule( ruleID, type ){
	/*
		Confirms that the user wants to delete
		the access rule.
	*/
	if( confirm( 'Are you sure you want to remove this access rule?' ) ){
		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'wps_remove_rule',
				type: type,
				rule: ruleID
			}
		}).done( function( response ){
			/*
				When completed, remove the rule from the
				table.
			*/
			if( response.success ){
				jQuery('#'+type+'-'+ruleID).fadeOut( 500, function(){
					jQuery(this).remove();
				});
			}
		});
	}
}

/*------------------------------------------------
	Regenerates the preview URL
------------------------------------------------*/
function wpsRegenerateURL(){
	/*
		Confirms that the user wants to regenerate the URL.
	*/
	if( confirm('Are you sure you want to regenerate the preview url? This will invalidate and block all access for people with the previous url.') ){
		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'wps_regenerate_url'
			}
		}).done( function( response ){
			/*
				Sets the new url to be copied.
			*/
			jQuery('#wps-share-url').val( response.preview_url );
		});
	}
}

/*------------------------------------------------
	Saves the settings from the admin page
------------------------------------------------*/
function wpsSaveSettings(){
	/*
		Grabs all of the user's settings
	*/
	var publicAccess 		= jQuery('input[name="wps-public-access-setting"]:checked').val();
	var defaultPage 		= jQuery('#wps-default-page').val();
	var expirationTime 		= jQuery('#wps-default-expiration').val();

	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {
			action: 'wps_save_settings',
			public_access: publicAccess,
			default_page: defaultPage,
			expiration_time: expirationTime
		}
	}).done( function( response ){
		jQuery('#wps-settings-saved').show();

		/*
			Hides the disabled banner and adjusts
			the admin bar status.
		*/
		if( response.public_access == '0' ){
			jQuery('#wps-disabled-banner').removeClass('wps-disabled-banner-enabled');

			jQuery('#wp-admin-bar-wps-sandbox-admin-bar-notification').removeClass('wps-admin-bar-enabled');
			jQuery('#wp-admin-bar-wps-sandbox-admin-bar-notification').addClass('wps-admin-bar-disabled');
			jQuery('#wp-admin-bar-wps-sandbox-admin-bar-notification .ab-item').html('WP Sandbox Disabled');
		}else{
			jQuery('#wps-disabled-banner').addClass('wps-disabled-banner-enabled');

			jQuery('#wp-admin-bar-wps-sandbox-admin-bar-notification').removeClass('wps-admin-bar-disabled');
			jQuery('#wp-admin-bar-wps-sandbox-admin-bar-notification').addClass('wps-admin-bar-enabled');
			jQuery('#wp-admin-bar-wps-sandbox-admin-bar-notification .ab-item').html('WP Sandbox Enabled');
		}
	});
}

/*------------------------------------------------
	Selects or deselects all the blog checkboxes 
	on the network admin page
------------------------------------------------*/
function wpsSelectDeselectAllBlogs( enabled ){
	if( enabled ){
		jQuery('.wps-network-enable-checkbox').each(function(){
			this.checked = true;
		});
	}else{
		jQuery('.wps-network-enable-checkbox').each(function(){
			this.checked = false;
		});
	}
}

/*------------------------------------------------
	Submits enabled and disabled changes
------------------------------------------------*/
function wpsSubmitEnabledDisabledChanges(){
	var siteStatus = [];

	var counter = 0;

	/*
		Gets all of the site statuses set for
		being sent along with the id of the sites.
	*/
	jQuery('.wps-network-enable-checkbox').each(function(){
		siteStatus[ counter ] = { "id": this.value, "active": this.checked };
		counter++;
	});

	/*
		Submits the changes
	*/
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {
			action: 'wps_enable_disable_blogs',
			status: siteStatus
		}
	}).done( function( response ){
		jQuery('#wps-settings-saved').show();
	});
}