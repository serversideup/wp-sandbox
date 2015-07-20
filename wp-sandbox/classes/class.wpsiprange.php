<?php
	class WPSIPRange{
		/*------------------------------------------------
			Retrieves all ip ranges
		------------------------------------------------*/
		public static function getIPRanges(){
			global $wpdb;

			if( is_multisite() ){
				$currentBlogID = get_current_blog_id();

				global $switched;
				switch_to_blog(1);

				/*
					Retrieves all of the ip ranges with
					respect to the current site.
				*/
				$ranges = $wpdb->get_results( $wpdb->prepare(
					"SELECT * FROM ".$wpdb->prefix."wps_ip_ranges
					WHERE blog_id = '%d'",
					$currentBlogID
				), ARRAY_A );

				restore_current_blog();
			}else{
				/*
					Retrieves all of the ip ranges.
				*/
				$ranges = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."wps_ip_ranges", ARRAY_A );
			}

			return $ranges;
		}
		/*------------------------------------------------
			Adds a range.
		------------------------------------------------*/
		public static function addRange( $startIP, $endIP, $expiration, $userID ){
			global $wpdb;

			/*
				Checks if it's multisite and grabs
				the blog ID if needed.
			*/
			if( is_multisite() ){
				$currentBlogID = get_current_blog_id();

				global $switched;
				switch_to_blog(1);

				/*
					Clenses and adds the rule
					to the database.
				*/
				$wpdb->query( $wpdb->prepare(
					"INSERT INTO ".$wpdb->prefix."wps_ip_ranges
					( blog_id, added_by, start_ip, end_ip, expires )
					VALUES( %d, %d, %s, %s, '".$expiration."')",
					$currentBlogID,
					$userID,
					$startIP,
					$endIP
				) );

				$rangeID = $wpdb->insert_id;

				restore_current_blog();
			}else{
				/*
					Clenses and adds the rule
					to the database.
				*/
				$wpdb->query( $wpdb->prepare(
					"INSERT INTO ".$wpdb->prefix."wps_ip_ranges
					( added_by, start_ip, end_ip, expires )
					VALUES( %d, %s, %s, '".$expiration."')",
					$userID,
					$startIP,
					$endIP
				) );

				$rangeID = $wpdb->insert_id;
			}

			/*
				Returns the newly added ID.
			*/
			return $rangeID;
		}

		/*------------------------------------------------
			Deletes a range
		------------------------------------------------*/
		public static function deleteRange( $rangeID ){
			global $wpdb;
			
			global $switched;
			switch_to_blog(1);

			$wpdb->query( $wpdb->prepare(
				"DELETE FROM ".$wpdb->prefix."wps_ip_ranges
				WHERE id = '%d'",
				$rangeID
			) );

			restore_current_blog();
		}

		/*------------------------------------------------
			Checks to see if the IP address is in a valid range of IPs.
			Help from: http://stackoverflow.com/questions/18336908/php-check-if-ip-address-is-in-a-range-of-ip-addresses
		------------------------------------------------*/
		public static function checkIPValidRange( $ipAddress ){
			global $wpdb;

			if( is_multisite() ){

				$currentBlogID = get_current_blog_id();

				global $switched;
				switch_to_blog(1);

				/*
					Gets IP ranges
				*/
				$ipRanges = $wpdb->get_results( $wpdb->prepare(
					"SELECT * 
					 FROM ".$wpdb->prefix."wps_ip_ranges
					 WHERE blog_id = '%d'",
					 $currentBlogID
				), ARRAY_A );
				
				restore_current_blog();
			}else{
				/*
					Gets IP ranges
				*/
				$ipRanges = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."wps_ip_ranges", ARRAY_A );

			}
			
			/*
				Loops through all of the IP Ranges
				to see if the ip address is within
				range.
			*/
			foreach($ipRanges as $ipRange){
		        $min    = ip2long($ipRange['start_ip']);
        		$max    = ip2long($ipRange['end_ip']);
        		$needle = ip2long($ipAddress);  

        		if( ( $needle >= $min ) AND ( $needle <= $max ) ){
        			return true;
        		}
			}

			/*
				Returns false if the ip is not in range.
			*/
			return false;
		}

		/*------------------------------------------------
			Gets all network authenticated ips. This 
			is only called from a multisite instance so 
			we know it's multisite.
		------------------------------------------------*/
		public static function getNetworkAuthenticatedIPRanges(){
			global $wpdb;
			global $switched;

			switch_to_blog(1);

			$authenticatedIPRanges = $wpdb->get_results(
				"SELECT *
				 FROM ".$wpdb->prefix."wps_ip_ranges",
			ARRAY_A );

			restore_current_blog();

			return $authenticatedIPRanges;
		}
	}
?>