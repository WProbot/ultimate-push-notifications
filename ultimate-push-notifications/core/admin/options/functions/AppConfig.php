<?php namespace UltimatePushNotifications\admin\options\functions;

/**
 * Database Actions handler for App Config
 *
 * @package Functions
 * @since 1.0.0
 * @author M.Tuhin <info@codesolz.net>
 */

if ( ! defined( 'CS_UPN_VERSION' ) ) {
	exit;
}

use UltimatePushNotifications\lib\Util;

if ( ! \class_exists( 'AppConfig' ) ) {

	class AppConfig {

		/**
		 * Add Configuration key
		 *
		 * @var string
		 */
		private static $app_config_key = 'cs_upn_app_config';

		/**
		 * Save App Config
		 *
		 * @return void
		 */
		public function save( $user_query ){

			$user_app_config = Util::check_evil_script( $user_query['cs_app_config'] );
			
			//check empty
			$is_empty = false;
			if( $user_app_config ){
				foreach( $user_app_config as  $key => $val ){
					if( empty( $val ) ){
						$is_empty = true;
						break;
					}
				}
			}
			
			if( true === $is_empty ){
				return wp_send_json(
					array(
						'status'       => false,
						'title'        => 'Error!',
						'text'         => __( "One or more field is empty. All fields are required.", 'ultimate-push-notifications' )
						)
				);	
			}
				
			update_option( self::$app_config_key, $user_app_config );
			$resMsg = isset( $user_query['cs_app_config_update']['id'] ) ? 'updated' : 'saved';

			return wp_send_json(
				array(
					'status'       => true,
					'title'        => 'Success!',
					'text'         => __( "Thank you! app configuration {$resMsg} successfully.", 'ultimate-push-notifications' )
					// 'redirect_url' => admin_url( 'admin.php?page=cs-all-masking-rules' ),
				)
			);

		}

		/**
		 * Get App Configuration
		 *
		 * @return void
		 */
		public static function get_config(){
			return get_option( self::$app_config_key );
		}


		/**
		 * Save / update token
		 *
		 * @param [type] $user_input
		 * @return void
		 */
		public function cs_update_token( $user_input ){
			global $wpdb;

			$current_user = Util::check_evil_script( $user_input['current_user'] );
			$token = Util::check_evil_script( $user_input['gen_token'] );
			$device_id = Util::check_evil_script( $user_input['device_id'] );

			$is_exists = $wpdb->get_var(
				$wpdb->prepare(
					"select id from `{$wpdb->prefix}upn_user_devices` where device_id = %s ", $device_id
				)
			);

			if( $is_exists ){
				$wpdb->update(
					"{$wpdb->prefix}upn_user_devices",
					array(
						'token' => $token
					),
					array(
						'id' => $is_exists
					)
				);
			}else{
				$wpdb->update(
					"{$wpdb->prefix}upn_user_devices",
					array(
						'user_id' => $current_user,
						'token' => $token,
						'device_id' => $device_id
					)
				);
			}

			return wp_send_json(
				array(
					'status'       => true,
					'title'        => 'Success!',
					'text'         => __( "Device token saved successfully", 'ultimate-push-notifications' )
				)
			);
		}


	}

}

