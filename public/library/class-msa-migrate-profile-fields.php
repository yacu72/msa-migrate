<?php
/**
 * Migrate clients profile fields
 */
if ( ! class_exists( 'Msa_Migrate_Profile_Fields' ) ) :

	class Msa_Migrate_Profile_Fields {

    function __construct() {

    }

    /**
     * Show data to be imported.
     */
    public function msa_profile_show_data( $uid ) {

    	global $wpdb;

    	$is_required = false;
    	
			$sql = "SELECT wpu.ID UID, xpf.type, xpf.id ID, xpf.name NAME, xpf.group_id Tab, d5pv.value
		FROM d5_profile_fields d5pf
		INNER JOIN d5_profile_values d5pv ON d5pv.fid = d5pf.fid
		INNER JOIN wp_bp_xprofile_fields xpf ON xpf.name = d5pf.title
		INNER JOIN wp_users wpu ON wpu.old_uid = d5pv.uid
		WHERE wpu.ID = %d";

			$res = $wpdb->get_results($wpdb->prepare($sql, $uid)); 

    	$output = '<div class="profile-data-wrapper" >';
			
			foreach ($res as $key => $data) {

				if ($data->type == 'datebox') {
					$date = unserialize($data->value);
					$output .= $data->NAME .': '. $date['year'] .'-'. $date['month'] .'-'. $date['day'] .' 00:00:00' .'<br>';
				}	

				if ($data->type == 'selectbox') {
					//$output .= $data->NAME .': '. $data->value .'<br>';

					$options = msa_select_options( $data->ID );

					if ( in_array($data->value, $options) ) {
						$output .= $data->NAME .': '. $data->value .'<br>';
					} 
					else {
						$max_percent = 75;
						$country = '';

						foreach ($options as $option) {

							similar_text($option, $data->value, $percent); 

							if ($percent > $max_percent) {
								//$output .= 'partial: '. $option .'<br>';
								$country = $option;
							}

						}
						$output .= $data->NAME .'(cleaned): '. $country .'<br>';
					}
				}

				if ($data->type == 'textbox') {
					$output .= $data->NAME .': '. $data->value .'<br>';
				}

				if ($data->type == 'textarea') {
					$output .= $data->NAME .': '. $data->value .'<br>';
				}

				if ($data->type == 'telephone') {
					$output .= $data->NAME .': '. $data->value .'<br>';
				}	

				if ($data->type == 'radio') {
					$output .= $data->NAME .': '. $data->value .'<br>';
				}

			} 

			$output .= '</div>';//profile-data-wrapper  

			$output .= '<div class="ajax-wrapper" >';
			$output .= '<button data-uid="'. $uid .'" class="button migrate-button ajax-profile-button" >Migrate Profile Info</button>';	
			$output .= '</div>';

			return $output;

    }	

    /**
     * Migrate Profile data from d5db to wpdb.
     */
    public function msa_profile_migrate_data( $uid ) {

    	global $wpdb;

			$is_required = false; 

			$sql = "SELECT wpu.ID UID, xpf.type, xpf.id ID, xpf.name NAME, xpf.group_id Tab, d5pv.value
		FROM d5_profile_fields d5pf
		INNER JOIN d5_profile_values d5pv ON d5pv.fid = d5pf.fid
		INNER JOIN wp_bp_xprofile_fields xpf ON xpf.name = d5pf.title
		INNER JOIN wp_users wpu ON wpu.old_uid = d5pv.uid
		WHERE wpu.ID = %d";

			$res = $wpdb->get_results($wpdb->prepare($sql, $uid));

			foreach ($res as $key => $data) {

				if ($data->type == 'datebox') {
					$date = unserialize($data->value);
					$date_value = $date['year'] .'-'. $date['month'] .'-'. $date['day'] .' 00:00:00';
					$result = xprofile_set_field_data($data->ID, $uid, $date_value, $is_required);
				}

				if ($data->type == 'selectbox') {
					
					// country and Citizenship fields
					if ($data->ID == 5 || $data->ID == 399) {

						$options = msa_select_options( $data->ID );

						if ( in_array($data->value, $options) ) {
							$result = xprofile_set_field_data($data->ID, $uid, $data->value, $is_required);
						} else {
							$max_percent = 75;
							$country = '';

							foreach ($options as $option) {

								similar_text($option, $data->value, $percent); 

								if ($percent > $max_percent) {
									$country = $option;
								}

							}
							$result = xprofile_set_field_data($data->ID, $uid, $country, $is_required);
						}

						

					} else {

						$result = xprofile_set_field_data($data->ID, $uid, $data->value, $is_required);
		
					}
				}

				if ($data->type == 'textbox') {

					$result = xprofile_set_field_data($data->ID, $uid, $data->value, $is_required);

					if ($data->NAME == 'Name') {
						update_user_meta($data->ID, 'first_name', $data->value);
					}
					if ($data->NAME == 'Surname/Family Name') {
						update_user_meta($data->ID, 'last_name', $data->value);
					}	

				}

				if ($data->type == 'textarea') {
					$result = xprofile_set_field_data($data->ID, $uid, $data->value, $is_required);
				}

				if ($data->type == 'telephone') {
					$result = xprofile_set_field_data($data->ID, $uid, $data->value, $is_required);
				}	

				if ($data->type == 'radio') {
					$result = xprofile_set_field_data($data->ID, $uid, $data->value, $is_required);
				}
			}    	

			add_user_meta( $uid, 'migrated', true );
    }	    	

	}

endif;