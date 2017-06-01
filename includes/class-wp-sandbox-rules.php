<?php

/**
 * Fired during plugin activation
 *
 * @link       https://521dimensions.com
 * @since      1.0.0
 *
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/includes
 * @author     521 Dimensions <dan@521dimensions.com>
 */
class WP_Sandbox_Rules {
	public function wp_sandbox_add_rule(){
		/*
			Defines the variables to pass to the function
			that would add to the database.
		*/
		$type 			= $_POST['type'];
		$rule 			= $_POST['rule'];
		$expiration 	= $this->wpsGetExpirationTime( $_POST['expiration'] );

		$user 			= get_userdata( get_current_user_id() );

		/*
			Determines type and adds the
			rule.
		*/
		switch( $type ){
			case 'single':
				$ruleID = WP_Sandbox_IP::addIP( $rule, $expiration, $user->ID );
			break;
			case 'range':
				$ipParts = explode( '-', $rule );

				$ruleID = WP_Sandbox_IP_Range::addRange( $ipParts[0], $ipParts[1], $expiration, $user->ID );
			break;
			case 'subnet':
				$subnetParts = explode( '/', $rule );

				$ruleID = WP_Sandbox_Subnet::addSubnet( $subnetParts[0], $subnetParts[1], $expiration, $user->ID );
			break;
		}

		$typeDisplay 	= $this->wpsGetTypeDisplay( $type );
		/*
			Returns the new information so it
			can be added to the table.
		*/
		wp_send_json( array( 
			'type' => $typeDisplay, 
			'rule' => $rule, 
			'expiration' => $expiration != '' ? date('m-d-Y H:i:s', strtotime( $expiration ) ) : 'Never', 
			'added_by' =>  $user->user_login,
			'rule_id' => $ruleID ) );

		die();
	}

	/*------------------------------------------------
		Returns the pretty display for the addition
		of a new rule.
	------------------------------------------------*/
	private function wpsGetTypeDisplay( $type ){
		switch( $type ){
			case 'single':
				return 'Single IP';
			break;
			case 'range':
				return 'IP Range';
			break;
			case 'subnet':
				return 'Network';
			break;
		}
	}

	public function wp_sandbox_remove_rule(){
		$type 		= $_POST['type'];
		$ruleID 	= $_POST['rule'];

		/*
			Calls appropriate remove function for
			the type of rule.
		*/
		switch( $type ){
			case 'user':
				WP_Sandbox_Authenticated_Users::deleteAuthenticatedUser( $ruleID );
			break;
			case 'single':
				WP_Sandbox_IP::deleteIP( $ruleID );
			break;
			case 'range':
				WP_Sandbox_IP_Range::deleteRange( $ruleID );
			break;
			case 'subnet':
				WP_Sandbox_Subnet::deleteSubnet( $ruleID );
			break;
		}

		wp_send_json( array(
			'success' => true
		) );
	}
}