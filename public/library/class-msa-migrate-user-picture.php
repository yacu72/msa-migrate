<?php

// Gives us access to the download_url() and wp_handle_sideload() functions
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once( ABSPATH . 'wp-admin/includes/admin.php' );
	require_once( ABSPATH . 'wp-admin/includes/media.php' );

if ( ! class_exists( 'Msa_Migrate_User_Picture') ): 
	class Msa_Migrate_User_Picture {

    function __construct() {
      //add_action( 'hook_name', array( &$this, 'my_hook_implementation' ) );
    }

    public 	function custom_media_sideload_image( $image_url = '', $post_id = false, $user_id = '' ) {

			global $wpdb;

			$sql = "SELECT d5u.name, d5u.picture
	FROM d5_users d5u 
	INNER JOIN wp_users wpu ON d5u.uid = wpu.old_uid
	WHERE  wpu.ID = %d";

			$res = $wpdb->get_results($wpdb->prepare($sql, $user_id));

			foreach ($res as $key => $data) {
				$pic_path = $data->picture; 
			}


			if (!$pic_path) {
				die;
			}

			$image_url = 'https://clients.medstudentadvisors.com/'. $pic_path;

			$tmp = download_url( $image_url );
			// Set variables for storage
			// fix file filename for query strings
			preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $image_url, $matches );

			// ads bpfull to file name, necessary to be shown in the profile page
			$ext   = pathinfo($matches[0], PATHINFO_EXTENSION);
			$file_array['name'] = basename($matches[0], ".$ext") . '-bpfull.' . $ext;
			$file_array['tmp_name'] = $tmp;


			// If error storing temporarily, unlink
			if ( is_wp_error( $tmp ) ) {
			@unlink($file_array['tmp_name']);
			$file_array['tmp_name'] = '';
			}

			add_filter( 'upload_dir', array( __CLASS__, 'msa_custom_profile_image_dir' ) );

			$file = wp_handle_sideload( $file_array, array('test_form'=>false) );

			remove_filter( 'upload_dir', array( __CLASS__, 'msa_custom_profile_image_dir' ) );



			if ( isset($file['error']) ) {
			return new WP_Error( 'upload_error', $file['error'] );
			}

	 
		  return $file;

		}	

		public 	function msa_custom_profile_image_dir( $pathdata ) {

			//Load user id variable for avatar subdir name, very dirty.TODO: find a cleaner way 
			global $bp;
			$user_data = $bp->displayed_user;
			$user_id = $user_data->id;

	    return array(
	        'path'   => $pathdata['basedir'] . '/avatars/'. $user_id,
	        'url'    => $pathdata['baseurl'] . '/avatars/'. $user_id,
	        'subdir' => '/avatars/' . $user_id,
	    ) + $pathdata;
		}

		public 	function msa_custom_profile_image_finder( $user_id ) {
			global $wpdb;
			$pic_path = '';

			$sql = "SELECT d5u.name, d5u.picture
	FROM d5_users d5u 
	INNER JOIN wp_users wpu ON d5u.uid = wpu.old_uid
	WHERE  wpu.ID = %d";

			$res = $wpdb->get_results($wpdb->prepare($sql, $user_id));

			foreach ($res as $key => $data) {
			
				$pic_path = $data->picture; 
			
			}

			if ( $pic_path != '') {
			
				return $pic_path;
			
			} else {
			
				return;
			
			}
		}

	}
	
endif;