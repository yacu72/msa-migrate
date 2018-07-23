<?php

if ( ! class_exists( 'Msa_Migrate_Users' ) ):
  class Msa_Migrate_Users {
  	
    function __construct() {
      //add_action( 'hook_name', array( &$this, 'my_hook_implementation' ) );
    }

    function my_hook_implementation() {
      // does something

    }

    public function msa_migrate_list_users_preview( $uid = NULL, $phase = NULL, $year = NULL ) {

      global $wpdb;

      $users = array();
      $where = '';
      $out = '';

      // Convert year to unix time 
      $start_year = $year ? strtotime( $year .'-01-01 00:00:00') : '';
      
      // Convert to unix time the one year period from year passed
      $end_year = $year ? strtotime( ($year + 1 ) .'-01-01 00:00:00') : '';

      if ( $year && !$uid) {
          $where .= ' AND d5u.created > '. $start_year .' AND d5u.created < '. $end_year;
      }

      if ( $phase && !$uid) {
          $where .= ' AND d5msa.phase = '. $phase;
      }

      // query when uid is porvided( uid && year can't be used aat the same time)
      if ( $uid ) {

          $where .= " AND d5u.uid = ". $uid;
      }

      $sql = "SELECT d5u.*, d5msa.*, d5pv.fid, d5pv.value firstname, d5pv3.value midname, d5pv1.value surname, d5pv2.value IP, wpu.ID ID
      FROM d5_users d5u
          LEFT JOIN wp_users wpu ON wpu.old_uid = d5u.uid
          LEFT JOIN d5_profile_values d5pv3 ON (d5pv3.uid = d5u.uid AND d5pv3.fid  = 5)
          LEFT JOIN d5_profile_values d5pv2 ON (d5pv2.uid = d5u.uid AND d5pv2.fid  = 61)
          LEFT JOIN d5_profile_values d5pv1 ON (d5pv1.uid = d5u.uid AND d5pv1.fid  = 2)
          LEFT JOIN d5_profile_values d5pv ON (d5pv.uid = d5u.uid AND d5pv.fid  = 1)
      INNER JOIN d5_msa d5msa ON d5u.uid = d5msa.uid
      WHERE d5u.status = 1 $where ";



      $res = $wpdb->get_results( $sql );

      foreach ( $res as $key => $user) {
        // variable passed to table constructor
        $data[$user->uid] = array(
          'phase' => $user->phase,
          'id'   => $user->uid,
          'name' => $user->firstname .' '. $user->surname,
          'email' => $user->mail,
          'created' => date('Y-m-d H:i:s', $user->created ),
          'last_login' => date('Y-m-d H:i:s', $user->login ),
          'access' => date('Y-m-d H:i:s', $user->access ),
          'ip'     => $user->IP,
          'migrated' => isset($user->ID) ? $user->ID : __('Pending', 'usersmigrate'),
        );
      }

      // class that handles table output
      //$users_list = new Msa_Users_List_Table();
      //$out .= $users_list->msa_users_table_page( $data );

      //return $out; 
      return $data;     
    }


    /**
     * Migrate D5 users to wordpress
     */
    public static function migrate_users_query( $uid = NULL, $phase = NULL, $year = NULL ) {

    	global $wpdb;

    	$users = array();
    	$where = '';
    	$new_user_id = '';

    	// Convert year to unix time 
    	$start_year = $year ? strtotime( $year .'-01-01 00:00:00') : '';
    	
    	// Convert to unix time the one year period from year passed
    	$end_year = $year ? strtotime( ($year + 1 ) .'-01-01 00:00:00') : '';

    	if ( $year && !$uid) {
    		$where .= ' AND d5u.created > '. $start_year .' AND d5u.created < '. $end_year;
    	}

    	if ( $phase && !$uid) {
    		$where .= ' AND d5msa.phase = '. $phase;
    	}

    	// query when uid is porvided( uid && year can't be used aat the same time)
    	if ( $uid ) {

    		$where .= " AND d5u.uid = ". $uid;
    	}

    	$sql = "SELECT d5u.*, d5msa.*, d5pv.fid, d5pv.value firstname, d5pv3.value midname, d5pv1.value surname, d5pv2.value IP
    	FROM d5_users d5u
			LEFT JOIN d5_profile_values d5pv3 ON (d5pv3.uid = d5u.uid AND d5pv3.fid  = 5)
			LEFT JOIN d5_profile_values d5pv2 ON (d5pv2.uid = d5u.uid AND d5pv2.fid  = 61)
			LEFT JOIN d5_profile_values d5pv1 ON (d5pv1.uid = d5u.uid AND d5pv1.fid  = 2)
			LEFT JOIN d5_profile_values d5pv ON (d5pv.uid = d5u.uid AND d5pv.fid  = 1)
    	INNER JOIN d5_msa d5msa ON d5u.uid = d5msa.uid
    	WHERE d5u.status = 1 $where ";

    	$res = $wpdb->get_results( $sql );


    	foreach ( $res as $key => $user) {

    		$user_id = username_exists( $user->name );

    		if (!$user_id) {//if user already exists don't migrate again

	    		// Create user in wp database
	    		$new_user_id = wp_create_user( $user->name, $user->pass, $user->mail );

	    		// Change role to client
					$user_object = get_user_by( 'id', $new_user_id );

					$user_object->remove_role( 'subscriber' );

					$user_object->add_role( 'client' ); 
					
					// Migrate old uid && registration date
					$registered = date('Y-m-d H:i:s', $user->created );
					$last_activity = date('Y-m-d H:i:s', $user->access );
					
					$wpdb->update($wpdb->users, array('user_registered' => $registered , 'old_uid' => $user->uid), array('ID' => $new_user_id));

					// Migrating Meta Data
					update_user_meta($user_id, 'first_name', $user->firstname);
					update_user_meta($user_id, 'last_name', $user->surname);

					update_user_meta($user_id, 'last_ip', $user->IP);

					update_user_meta($user_id, 'last_activity', $last_activity);

          //add last activity data to bo table
          bp_update_user_last_activity( $user_id, $last_activity );



	    		// variable passed to table constructor
	    		$data[$user->uid] = array(
	    			'phase' => $user->phase,
	    			'id'   => $user->uid,
	    			'name' => $user->firstname .' '. $user->surname,
	    			'email' => $user->mail,
	    			'created' => date('Y-m-d H:i:s', $user->created ),
	    			'last_login' => date('Y-m-d H:i:s', $user->login ),
	    			'access' => date('Y-m-d H:i:s', $user->access ),
	    			'ip'     => $user->IP,
	    		);

          return $new_user_id;

	    	} else {

	    		return $user_id;

	    	}
    	}
    }
  }

endif;

