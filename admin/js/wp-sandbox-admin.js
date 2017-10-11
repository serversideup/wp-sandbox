(function( $ ) {
	'use strict';

	var fileFrame;
	var setToPostID = '';

	var primaryBackground = '';
	var secondaryBackground = '';

	/*
		Configure the Admin Settings screen.
	*/
	$(document).ready(function(){
		if( settings != undefined ){
			/*
				Binds the event handlers for the page.
			*/
			wpSandboxBindEventHandlers();

			primaryBackground = settings.background_color_1;
			secondaryBackground = settings.background_color_2;

			/*
				Sets the background color for the icon to select the background color.
			*/
			$('#background-color-1').css('background-color', settings.background_color_1 );
			$('#background-color-2').css('background-color', settings.background_color_2 );

			/*
				Set the background colors for coming soon.
			*/
			wpSandboxSetComingSoonBackground();

			/*
				Defaults the color picker to the background color 1
			*/
			$('#background-color-1').ColorPicker({
				color: settings.background_color_1,
				onChange: function (hsb, hex, rgb) {
					primaryBackground = '#'+hex;
					$('#background-color-1').css('backgroundColor', '#' + hex);
					wpSandboxSetComingSoonBackground();
				}
			});

			/*
				Defaults the color picker to the background color 2
			*/
			$('#background-color-2').ColorPicker({
				color: settings.background_color_2,
				onChange: function (hsb, hex, rgb) {
					secondaryBackground = '#'+hex;
					$('#background-color-2').css('backgroundColor', '#' + hex);
					wpSandboxSetComingSoonBackground();
				}
			});
		}
	});

	/*
		Binds the event handlers
	*/
	function wpSandboxBindEventHandlers(){
		/*------------------------------------------------
			CHANGE HANDLER
			Determines if Sandbox is enabled or disabled
		------------------------------------------------*/
		$('#wp-sandbox-on-off-switch-checkbox').on('change', function(){
			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'wp_sandbox_save_enabled',
					enabled: $('#wp-sandbox-on-off-switch-checkbox').is(':checked')
				}
			}).done( function( response ){
				if( $('#wp-sandbox-on-off-switch-checkbox').is(':checked') ){
					$('#wp-sandbox-disabled').hide();
					$('#wp-admin-bar-wp-sandbox-admin-bar-notification').removeClass('wp-sandbox-admin-bar-disabled');
					$('#wp-admin-bar-wp-sandbox-admin-bar-notification').addClass('wp-sandbox-admin-bar-enabled');
					$('#wp-admin-bar-wp-sandbox-admin-bar-notification a').html('WP Sandbox Enabled');
				}else{
					$('#wp-sandbox-disabled').show();
					$('#wp-admin-bar-wp-sandbox-admin-bar-notification').removeClass('wp-sandbox-admin-bar-enabled');
					$('#wp-admin-bar-wp-sandbox-admin-bar-notification').addClass('wp-sandbox-admin-bar-disabled');
					$('#wp-admin-bar-wp-sandbox-admin-bar-notification a').html('WP Sandbox Disabled');
				}
			});
		});

		/*------------------------------------------------
			CLICK HANDLER
			Toggles the access/desgin display
		------------------------------------------------*/
		$('.wp-sandbox-settings-link').on('click', function(){
			var id = $(this).attr('id');
			wpSandboxToggleDisplay( id );
		});

		/*------------------------------------------------
			CLICK HANDLER
			Handles a click on the upload image button
		------------------------------------------------*/
		$('#upload-image-button').on('click', function(){
			var send_attachment_bkp = wp.media.editor.send.attachment;
			var button = $(this);

			/*
				When the attachment is uploaded/selected, set the display sections.
			*/
			wp.media.editor.send.attachment = function( props, attachment ) {
				$('#image-preview').attr('src', attachment.url );
				$('#wp-sandbox-coming-soon-template-preview-logo').attr('src', attachment.url );
				$('#wp-sandbox-image-attachment-url').val( attachment.url );
				wp.media.editor.send.attachment = send_attachment_bkp;
				$('#wp-sandbox-design-logo').val( attachment.url );
				$('#image-preview-wrapper').show();
			}

			/*
				Open the media editor
			*/
			wp.media.editor.open();
			return false;
		});

		/*------------------------------------------------
			CLICK HANDLER
			Handles the deletion of an access rule
		------------------------------------------------*/
		$('.wp-sandbox-remove-access-rule').click(function(){
			var ruleID = $(this).attr('data-attr-id');
			var type = $(this).attr('data-attr-type');

			wpSandboxRemoveAccessRule( ruleID, type );
		});

		/*------------------------------------------------
			CLICK HANDLER
			Regenerates the preview url
		------------------------------------------------*/
		$(function(){
			$('#wp-sandbox-regenerate-url').click(function(){
				wpSandboxRegenerateURL();
			});
		});

		/*------------------------------------------------
			CLICK HANDLER
			Adds an access rule
		------------------------------------------------*/
		$('#wp-sandbox-add-access-rule').on('click', function(){
			var type = '';
			var valid = false;

			var accessSubject = $('#wp-sandbox-new-access-rule').val().replace(/\s/g, '');

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
				if( wpSandboxCheckValidRange( accessSubject ) ){
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
				var expiration = jQuery('#wp-sandbox-access-rule-expires').val();

				wpSandboxAddAccessRule( type, accessSubject, expiration );

				/*
					Reset styles
				*/
				$('#wp-sandbox-new-access-rule').css('border', '1px solid #ddd');
				$('#wp-sandbox-access-rule-validation').hide();
			}else{
				$('#wp-sandbox-new-access-rule').css('border', '1px solid red');
				$('#wp-sandbox-access-rule-validation').show();
			}
		});

		/*------------------------------------------------
			KEY UP HANDLER
			When the user enters a new image, update the preview
		------------------------------------------------*/
		$('#wp-sandbox-design-logo').on('keyup', function(){
			if( $(this).val().length > 0 ){
				$('#image-preview-wrapper').show();
				$('wp-sandbox-coming-soon-template-preview-logo').attr( 'src', $(this).val() );
			}else{
				$('#image-preview-wrapper').hide();
				$('#wp-sandbox-coming-soon-template-preview-logo').hide();
			}
		});

		/*------------------------------------------------
			CHANGE HANDLER
			If the user toggles the login checkbox, update the preview
		------------------------------------------------*/
		$('#wp-sandbox-show-login-switch-checkbox').on('change', function(){
			if( $(this).is(':checked') ){
				$('#wp-sandbox-coming-soon-template-preview-login').show();
			}else{
				$('#wp-sandbox-coming-soon-template-preview-login').hide();
			}
		});

		/*------------------------------------------------
			KEY UP HANDLER
			When the user enters a new main title, update the preview
		------------------------------------------------*/
		$('#wp-sandbox-design-main-title').on('keyup', function(){
			if( $(this).val().length > 0 ){
				$('#wp-sandbox-coming-soon-template-preview-header').html( $(this).val() );
			}else{
				$('#wp-sandbox-coming-soon-template-preview-header').html('This awesome site is coming soon!');
			}
		});

		/*------------------------------------------------
			KEY UP HANDLER
			When the user enters a new subtitle title, update the preview
		------------------------------------------------*/
		$('#wp-sandbox-design-sub-title').on('keyup', function(){
			if( $(this).val().length > 0 ){
				$('#wp-sandbox-coming-soon-template-preview-sub-header').html( $(this).val() );
			}else{
				$('#wp-sandbox-coming-soon-template-preview-sub-header').html('Please excuse the dust, we will be launching soon.');
			}
		});

		/*------------------------------------------------
			KEY UP HANDLER
			When the user enters a Twitter URL show the icon
		------------------------------------------------*/
		$('#wp-sandbox-design-twitter').on('keyup', function(){
			if( $(this).val().length > 0 ){
				$('#wp-sandbox-social-icon-container #twitter').show();
			}else{
				$('#wp-sandbox-social-icon-container #twitter').hide();
			}
		});

		/*------------------------------------------------
			KEY UP HANDLER
			When the user enters a Instagram URL show the icon
		------------------------------------------------*/
		$('#wp-sandbox-design-instagram').on('keyup', function(){
			if( $(this).val().length > 0 ){
				$('#wp-sandbox-social-icon-container #instagram').show();
			}else{
				$('#wp-sandbox-social-icon-container #instagram').hide();
			}
		});

		/*------------------------------------------------
			KEY UP HANDLER
			When the user enters a Google Plus URL show the icon
		------------------------------------------------*/
		$('#wp-sandbox-design-google-plus').on('keyup', function(){
			if( $(this).val().length > 0 ){
				$('#wp-sandbox-social-icon-container #google-plus').show();
			}else{
				$('#wp-sandbox-social-icon-container #google-plus').hide();
			}
		});

		/*------------------------------------------------
			KEY UP HANDLER
			When the user enters a Dribbble URL show the icon
		------------------------------------------------*/
		$('#wp-sandbox-design-dribbble').on('keyup', function(){
			if( $(this).val().length > 0 ){
				$('#wp-sandbox-social-icon-container #dribbble').show();
			}else{
				$('#wp-sandbox-social-icon-container #dribbble').hide();
			}
		});

		/*------------------------------------------------
			KEY UP HANDLER
			When the user enters a Vimeo URL show the icon
		------------------------------------------------*/
		$('#wp-sandbox-design-vimeo').on('keyup', function(){
			if( $(this).val().length > 0 ){
				$('#wp-sandbox-social-icon-container #vimeo').show();
			}else{
				$('#wp-sandbox-social-icon-container #vimeo').hide();
			}
		});

		/*------------------------------------------------
			KEY UP HANDLER
			When the user enters a YouTube URL show the icon
		------------------------------------------------*/
		$('#wp-sandbox-design-youtube').on('keyup', function(){
			if( $(this).val().length > 0 ){
				$('#wp-sandbox-social-icon-container #youtube').show();
			}else{
				$('#wp-sandbox-social-icon-container #youtube').hide();
			}
		});

		/*------------------------------------------------
			KEY UP HANDLER
			When the user enters a Facebook URL show the icon
		------------------------------------------------*/
		$('#wp-sandbox-design-facebook').on('keyup', function(){
			if( $(this).val().length > 0 ){
				$('#wp-sandbox-social-icon-container #facebook').show();
			}else{
				$('#wp-sandbox-social-icon-container #facebook').hide();
			}
		});

		/*------------------------------------------------
			CLICK HANDLER
			Submits the design update form
		------------------------------------------------*/
		$('#wp-sandbox-update-design').on('click', function(){
			if( !validateDesignChanges() ){
				wpSandboxUpdateDesign();
			}
		});
	}

	/*------------------------------------------------
		Validate the design changes
	------------------------------------------------*/
	function validateDesignChanges(){
		var errors = false;

		if( $('#wp-sandbox-design-twitter').val() != ''
		&& !$('#wp-sandbox-design-twitter').val().match(/(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g) ){
			errors = true;
			$('#twitter-url-validation').show();
		}else{
			$('#twitter-url-validation').hide();
		}

		if( $('#wp-sandbox-design-facebook').val() != ''
		&& !$('#wp-sandbox-design-facebook').val().match(/(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g) ){
			errors = true;
			$('#facebook-url-validation').show();
		}else{
			$('#facebook-url-validation').hide();
		}

		if( $('#wp-sandbox-design-instagram').val() != ''
		&& !$('#wp-sandbox-design-instagram').val().match(/(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g) ){
			errors = true;
			$('#instagram-url-validation').show();
		}else{
			$('#instagram-url-validation').hide();
		}

		if( $('#wp-sandbox-design-vimeo').val() != ''
		&& !$('#wp-sandbox-design-vimeo').val().match(/(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g) ){
			errors = true;
			$('#vimeo-url-validation').show();
		}else{
			$('#vimeo-url-validation').hide();
		}

		if( $('#wp-sandbox-design-dribbble').val() != ''
		&& !$('#wp-sandbox-design-dribbble').val().match(/(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g) ){
			errors = true;
			$('#dribbble-url-validation').show();
		}else{
			$('#dribbble-url-validation').hide();
		}

		if( $('#wp-sandbox-design-youtube').val() != ''
		&& !$('#wp-sandbox-design-youtube').val().match(/(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g) ){
			errors = true;
			$('#youtube-url-validation').show();
		}else{
			$('#youtube-url-validation').hide();
		}

		return errors;
	}

	/*------------------------------------------------
		Changes the Coming Soon Background to match
		the colors selected
	------------------------------------------------*/
	function wpSandboxSetComingSoonBackground(){
		$('#wp-sandbox-coming-soon-template-preview').css('background', primaryBackground );
		$('#wp-sandbox-coming-soon-template-preview').css('background', '-moz-linear-gradient(45deg, '+primaryBackground+' 0%, '+secondaryBackground+' 100%');
		$('#wp-sandbox-coming-soon-template-preview').css('background', '-webkit-linear-gradient(45deg, '+primaryBackground+' 0%, '+secondaryBackground+' 100%');
		$('#wp-sandbox-coming-soon-template-preview').css('background', 'linear-gradient(45deg, '+primaryBackground+' 0%, '+secondaryBackground+' 100%');
		$('#wp-sandbox-coming-soon-template-preview').css('filter', 'progid:DXImageTransform.Microsoft.gradient( startColorstr=\''+primaryBackground+'\', endColorstr=\''+secondaryBackground+'\',GradientType=1' );
	}

	/*------------------------------------------------
		Toggle the display from access to design
	------------------------------------------------*/
	function wpSandboxToggleDisplay( id ){
		$('.wp-sandbox-settings-link').removeClass('wp-sandbox-active-settings-link');

		switch( id ){
			case 'wp-sandbox-access-link':
				$('#wp-sandbox-access-link').addClass('wp-sandbox-active-settings-link');
				$('#wp-sandbox-access-list-page').show();
				$('#wp-sandbox-design-page').hide();
			break;
			case 'wp-sandbox-design-link':
				$('#wp-sandbox-design-link').addClass('wp-sandbox-active-settings-link');
				$('#wp-sandbox-design-page').show();
				$('#wp-sandbox-access-list-page').hide();
			break;
		}
	}

	/*------------------------------------------------
		Regenerates the Sandbox URL
	------------------------------------------------*/
	function wpSandboxRegenerateURL(){
		/*
			Confirms that the user wants to regenerate the URL.
		*/
		if( confirm('Are you sure you want to regenerate the preview url? This will invalidate and block all access for people with the previous url.') ){
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'wp_sandbox_regenerate_url'
				}
			}).done( function( response ){
				/*
					Sets the new url to be copied.
				*/
				jQuery('#wp-sandbox-share-url').val( response.preview_url );
			});
		}
	}

	/*------------------------------------------------
		Checks to see if the IP range is valid
		meaning that the first IP is less than
		the second IP.
	------------------------------------------------*/
	function wpSandboxCheckValidRange( ipRange ){
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
		Adds an access rule to the plugin.
	------------------------------------------------*/
	function wpSandboxAddAccessRule( type, rule, expiration ){
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'wp_sandbox_add_rule',
				type: type,
				rule: rule,
				expiration: expiration
			}
		}).done( function( response ){
			/*
				Clear the new rule data from the
				form.
			*/
			$('#wp-sandbox-new-access-rule').val('');

			/*
				Append the new rule to the table
			*/
			$('table#wp-sandbox-access-rule-table tbody')
				.append(
					'<tr id="user-'+response.rule_id+'">'+
						'<td>' + response.type + '</td>' +
						'<td>' + response.rule + '</td>' +
						'<td>' + response.added_by + '</td>' +
						'<td>' + response.expiration + '</td>' +
						'<td><a class="wp-sandbox-remove-access-rule" data-attr-type="user" data-attr-id="' + response.rule_id + '">Remove Access</a></td>' +
					'</tr>'
				);

				/*
					Unbinds existing click
					handlers and rebinds the
					newly added row's click
					handlers
				*/
				$('.wp-sandbox-remove-access-rule').unbind('click');

				$('.wp-sandbox-remove-access-rule').click(function(){
					var ruleID = $(this).attr('data-attr-id');
					var type = $(this).attr('data-attr-type');

					wpSandboxRemoveAccessRule( ruleID, type );
				});
		});
	}

	/*------------------------------------------------
		Removes an access rule
	------------------------------------------------*/
	function wpSandboxRemoveAccessRule( ruleID, type ){
		/*
			Confirms that the user wants to delete
			the access rule.
		*/
		if( confirm( 'Are you sure you want to remove this access rule?' ) ){
			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'wp_sandbox_remove_rule',
					type: type,
					rule: ruleID
				}
			}).done( function( response ){
				/*
					When completed, remove the rule from the
					table.
				*/
				if( response.success ){
					$('#'+type+'-'+ruleID).fadeOut( 500, function(){
						$(this).remove();
					});
				}
			});
		}
	}

	/*------------------------------------------------
		Update the WP Sandbox Design for the default page
	------------------------------------------------*/
	function wpSandboxUpdateDesign(){
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'wp_sandbox_update_design',
				logo: $('#wp-sandbox-design-logo').val(),
				main_title: $('#wp-sandbox-design-main-title').val(),
				sub_title: $('#wp-sandbox-design-sub-title').val(),
				show_login_link: $('#wp-sandbox-show-login-switch-checkbox').val(),
				background_color_1: primaryBackground,
				background_color_2: secondaryBackground,
				twitter: $('#wp-sandbox-design-twitter').val(),
				facebook: $('#wp-sandbox-design-facebook').val(),
				google_plus: $('#wp-sandbox-design-google-plus').val(),
				instagram: $('#wp-sandbox-design-instagram').val(),
				vimeo: $('#wp-sandbox-design-vimeo').val(),
				dribbble: $('#wp-sandbox-design-dribbble').val(),
				youtube: $('#wp-sandbox-design-youtube').val()
			}
		}).done( function( response ){
			$('.design-settings-updated').show();
		});
	}
})( jQuery );
