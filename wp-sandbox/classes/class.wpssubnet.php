<?php
	class WPSSubnet{
		/*------------------------------------------------
			Retrieves all current subnets
		------------------------------------------------*/
		public static function getSubnets(){
			global $wpdb;

			if( is_multisite() ){
				$currentBlogID = get_current_blog_id();

				global $switched;
				switch_to_blog(1);

				/*
					Retrieves all of the subnets with
					respect to the current site.
				*/
				$subnets = $wpdb->get_results( $wpdb->prepare(
					"SELECT * FROM ".$wpdb->prefix."wps_subnets
					WHERE blog_id = '%d'",
					$currentBlogID
				), ARRAY_A );

				restore_current_blog();
			}else{
				/*
					Retrieves all of the subnets.
				*/
				$subnets = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."wps_subnets", ARRAY_A );
			}

			return $subnets;
		}

		/*------------------------------------------------
			Adds a subnet
		------------------------------------------------*/
		public static function addSubnet( $ip, $network, $expiration, $userID ){
			global $wpdb;

			if( is_multisite() ){
				$currentBlogID = get_current_blog_id();

				global $switched;
				switch_to_blog(1);

				/*
					Clenses and adds the IP
					to the database.
				*/
				$wpdb->query( $wpdb->prepare(
					"INSERT INTO ".$wpdb->prefix."wps_subnets
					( blog_id, added_by, start_ip, subnet, expires )
					VALUES ( %d, %d, %s, %s, '".$expiration."')",
					$currentBlogID,
					$userID,
					$ip,
					$network
				) );

				$rangeID = $wpdb->insert_id;

				restore_current_blog();
			}else{
				/*
					Clenses and adds the IP
					to the database.
				*/
				$wpdb->query( $wpdb->prepare(
					"INSERT INTO ".$wpdb->prefix."wps_subnets
					( added_by, start_ip, subnet, expires )
					VALUES ( %d, %s, %s, '".$expiration."')",
					$userID,
					$ip,
					$network
				) );

				$rangeID = $wpdb->insert_id;
			}

			/*
				Returns the newly added ID.
			*/
			return $rangeID;
		}

		/*------------------------------------------------
			Deletes a subnet
		------------------------------------------------*/
		public static function deleteSubnet( $subnetID ){
			global $wpdb;

			global $switched;
			switch_to_blog(1);
			
			$wpdb->query( $wpdb->prepare(
				"DELETE FROM ".$wpdb->prefix."wps_subnets
				WHERE id = '%d'",
				$subnetID
			) );

			restore_current_blog();
		}

		/*------------------------------------------------
			Checks to see if an IP is in an allowed
			subnet
		------------------------------------------------*/
		public static function checkIPSubnet( $ipAddress ){
			global $wpdb;

			/*
				Grabs allowed subnets from the 
				current blog.
			*/
			if( is_multisite() ){
				$currentBlogID = get_current_blog_id();

				global $switched;
				switch_to_blog(1);

				$subnets = $wpdb->get_results( $wpdb->prepare(
					"SELECT *
					 FROM ".$wpdb->prefix."wps_subnets
					 WHERE blog_id = '%d'",
					 $currentBlogID
				), ARRAY_A );

				restore_current_blog();
			}else{
				$subnets = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."wps_subnets", ARRAY_A );
			}

			/*
				Iterates through subnets and checks to see
				if the IP address is within the range.
			*/
			foreach( $subnets as $subnet ){
				$subnetParts 	= explode( '.', $subnet['start_ip'] );

				$firstOctet 	= $subnetParts[0];
				$secondOctet 	= $subnetParts[1];
				$thirdOctet 	= $subnetParts[2];
				$fourthOctet 	= $subnetParts[3];

				$newFirstOctet 	= '';
				$newSecondOctet	= '';
				$newThirdOctet 	= '';
				$newFourthOctet = '';

				/*
					Checks from the largest networks
					first down to the smallest.
				*/
				if( $subnet['subnet'] <= 8 ){
					if( ( $firstOctet + $subnet['subnet'] ) > 255 ){
						$newFirstOctet = 255;
					}else{
						$newFirstOctet = $firstOctet + $subnet['subnet'];
					}

					$min 	= ip2long( $subnet['start_ip'] );
					$max 	= ip2long( $newFirstOctet.'.255.255.254' );
					$needle = ip2long( $ipAddress );

					if( ( $needle >= $min ) AND ( $needle <= $max ) ){
						return true;
					}
				}else if( ( $subnet['subnet'] > 8 ) && ( $subnet['subnet'] <= 16 ) ){
					if( ( $secondOctet + $subnet['subnet'] ) > 255 ){
						$newScondOctet = ( $secondOctet + $subnet['subnet'] ) - 255;
						$newFirstOctet = $firstOctet + 1;
					}else{
						$newFirstOctet = $firstOctet;
						$newSecondOctet = $secondOctet + $subnet['subnet'];
					}

					$min    = ip2long( $subnet['start_ip'] );
					$max    = ip2long( $newFirstOctet.'.255.255.254' );
					$needle = ip2long( $ipAddress );  

					if( ( $needle >= $min ) AND ( $needle <= $max ) ){
						return true;
					}
				}else if( ( $subnet['subnet'] > 16 ) && ( $subnet['subnet'] <= 24 ) ){
					if( ( $thirdOctet + $subnet['subnet'] ) > 255 ){
						$newThirdOctet 	= ( $thirdOctet + $subnet['subnet'] ) - 255;
						$newSecondOctet = $secondOctet + 1;
						$newFirstOctet 	= $firstOctet;
					}else{
						$newSecondOctet = $secondOctet;
						$newThirdOctet 	= $thirdOctet + $subnet['subnet'];
						$newFirstOctet 	= $firstOctet;
					}


					$min    = ip2long( $subnet['start_ip'] );
	        		$max    = ip2long( $newFirstOctet.'.255.255.254' );
	        		$needle = ip2long( $ipAddress );  

	        		if( ( $needle >= $min ) AND ( $needle <= $max ) ){
	        			return true;
	        		}
				}else if( $subnet['subnet'] > 24 ){
					if( ( $fourthOctet + $subnet['subnet'] ) > 255 ){
						$newFourthOctet = ( $fourthOctet + $subnet['subnet'] ) - 255;
						$newThirdOctet 	= $thirdOctet + 1;
						$newSecondOctet = $secondOctet;
						$newFirstOctet 	= $firstOctet;
					}else{
						$newFourthOctet = $fourthOctet + $subnet['subnet'];
						$newSecondOctet = $secondOctet;
						$newThirdOctet 	= $thirdOctet;
						$newFirstOctet 	= $firstOctet;
					}


					$min    = ip2long( $subnet['start_ip'] );
	        		$max    = ip2long( $newFirstOctet.'.255.255.254' );
	        		$needle = ip2long( $ipAddress );  

	        		if( ( $needle >= $min ) AND ( $needle <= $max ) ){
	        			return true;
	        		}
				}
			}

			/*
				Only reachable if nothing was returned
				meaning that the IP is NOT in the subnet.
			*/
			return false;
		}

		/*------------------------------------------------
			Gets all network authenticated subnets. This 
			is only called from a multisite instance so 
			we know it's multisite.
		------------------------------------------------*/
		public static function getNetworkAuthenticatedSubnets(){
			global $wpdb;
			global $switched;

			switch_to_blog(1);

			$authenticatedSubnets = $wpdb->get_results(
				"SELECT *
				 FROM ".$wpdb->prefix."wps_subnets",
			ARRAY_A );

			restore_current_blog();

			return $authenticatedSubnets;
		}
	}
?>