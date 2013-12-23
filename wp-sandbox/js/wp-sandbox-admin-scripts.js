/*
	Enables and disables the plugin
*/
jQuery(document).ready(function(){
	jQuery('#sandbox-enabled').change(function(){
		if(jQuery('#sandbox-enabled').is(':checked')){
			var data = {
				action: 'wps_enable_plugin',
				enabled: '1'
			}
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: data,
				async: false
			}).done(function(response){

			});
		}else{
			var data = {
				action: 'wps_enable_plugin',
				enabled: '0'
			}
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: data,
				async: false
			}).done(function(response){
				
			});
		}
	});
});
/* 
	Saves the default page settings through AJAX
*/
function wps_save_default_page_setting(){
	var data = {
		action: 'wps_save_admin_settings',
		default_page: jQuery('#wps-default-page').val(),
		setting: 'default_page'
	}
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: data,
		async: false

	}).done(function(response){
		if(response == 'true'){
			jQuery('#wps-settings-saved').show();
			jQuery('#wps-settings-saved').fadeOut(3000);
			wps_reload_users_table();
		}
	});
}
/*
	Removes a valid user through AJAX
*/
function wps_remove_user(user_id, ip){
	var data = {
		action: 'wps_remove_user',
		wps_user_id: user_id,
		wps_ip: ip
	}
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: data,
		async: false
	}).done(function(response){
		jQuery('#wps-users-body').html(response);
		jQuery('#wps-user-removed').show();
		jQuery('#wps-user-removed').fadeOut(3000);
	})
}
/*
	Reloads the users table
*/
function wps_reload_users_table(){
	var data = {
		action: 'wps_reload_users'
	}
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: data,
		async: false
	}).done(function(response){
		jQuery('#wps-users-body').html(response);
	})
}

/*
	Saves the default expiration time for users
*/
function wps_save_default_expire_time(){
	var data = {
		action: 'wps_save_admin_settings',
		default_expire_time: jQuery('#wps-default-expire-time').val(),
		setting: 'default_expire_time'
	}
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: data,
		async: false
	}).done(function(response){
		if(response == 'true'){
			jQuery('#wps-settings-saved').show();
			jQuery('#wps-settings-saved').fadeOut(3000);
			wps_reload_users_table();
		}
	})
}

/*
	Allows a static IP
*/
function wps_allow_ip(){
	var data = {
		action: 'wps_allow_ip',
		ip: jQuery('#wps-allowed-ip').val(),
		expires: jQuery('#wps-ip-allowed-expire-time').val()
	}
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: data,
		async: false
	}).done(function(response){
		if(response == 'true'){
			jQuery('#wps-ip-added').html('IP Added');
			jQuery('#wps-ip-added').css('background-color', 'DarkGreen');
			jQuery('#wps-ip-added').show();
			jQuery('#wps-ip-added').fadeOut(3000);
			wps_reload_users_table();
		}
		if(response == 'false'){
			jQuery('#wps-ip-added').html('IP Already Exists');
			jQuery('#wps-ip-added').css('background-color', 'DarkRed');
			jQuery('#wps-ip-added').show();
			jQuery('#wps-ip-added').fadeOut(3000);
		}
	})
}

/*
	Updates a valid preview hash
*/
function wps_update_preview_hash(){
	var data = {
		action: 'wps_generate_preview_hash_url'
	}
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: data,
		async: false
	}).done(function(response){
		jQuery('#wps-preview-hash').val(response);
		wps_reload_users_table();
	});
}

/*
	Adds an additional IP Range
*/
function wps_add_ip_range(){
	jQuery('#wps-additional-ip-range').append('<div class="ip-range-row"><div class="inner-left-range"><strong>Starting IP: </strong><br><input type="text" name="wps-starting-ip[]"/></div><div class="inner-middle-range">to</div><div class="inner-right-range"><strong>Ending IP: </strong><br><input type="text" name="wps-ending-ip[]"/></div></div>');
}

/*
	Saves IP Ranges
*/
function wps_save_ip_ranges(){
	var start_range_array = '';
	jQuery(function(){
	   	start_range_array = jQuery('input[name="wps-starting-ip[]"]').map(function(){
	       return this.value
	   	}).get();
	});

	var end_range_array = '';
	jQuery(function(){
	   	end_range_array = jQuery('input[name="wps-ending-ip[]"]').map(function(){
	       return this.value
	   	}).get();
	});

	var data = {
		action: 'wps_save_ip_ranges',
		start_range: start_range_array,
		end_range: end_range_array
	}
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: data,
		async: false
	}).done(function(response){
		wps_reload_ip_range_table();
	});
}

/*
	Removes an IP Range
*/
function wps_remove_range(start_ip, end_ip){
	var data = {
		action: 'wps_delete_ip_range',
		start: start_ip,
		end: end_ip
	}
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: data,
		async: false
	}).done(function(response){
		wps_reload_ip_range_table();
	});
}

/*
	Reloads the IP Range table
*/
function wps_reload_ip_range_table(){
	var data = {
		action: 'wps_reload_ip_range_table'
	}
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: data,
		async: false
	}).done(function(response){
		jQuery('#wps-ip-ranges-body').html(response);
	});
}

/*
	Add Subnets
*/
function wps_add_subnet(){
	jQuery('#wps-additional-subnets').append('<div class="wps-subnet-row"><div class="inner-left-subnet"><strong>IP: </strong><br><input type="text" name="wps-subnet-ip[]"/></div><div class="inner-middle-subnet">/</div><div class="inner-right-subnet"><strong>Subnet</strong><br><input type="text" name="wps-subnet-subnet[]"/></div></div>');
}

/*
	Saves Subnets
*/
function wps_save_subnets(){
	var ip_array = '';
	jQuery(function(){
	   	ip_array = jQuery('input[name="wps-subnet-ip[]"]').map(function(){
	       return this.value
	   	}).get();
	});

	var subnet_array = '';
	jQuery(function(){
	   	subnet_array = jQuery('input[name="wps-subnet-subnet[]"]').map(function(){
	       return this.value
	   	}).get();
	});

	var data = {
		action: 'wps_save_subnets',
		ips: ip_array,
		subnets: subnet_array
	}
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: data,
		async: false
	}).done(function(response){
		wps_reload_subnet_table();
	});
}

/*
	Removes a Subnet
*/
function wps_remove_subnet(ip, subnet){
	var data = {
		action: 'wps_remove_subnet',
		start_ip: ip,
		subnet_extension: subnet
	}
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: data,
		async: false
	}).done(function(response){
		wps_reload_subnet_table();
	});
}

/*
	Reloads subnet table
*/
function wps_reload_subnet_table(){
	var data = {
		action: 'wps_reload_subnet_table'
	}
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: data,
		async: false
	}).done(function(response){
		jQuery('#wps-subnets-table-body').html(response);
	});
}