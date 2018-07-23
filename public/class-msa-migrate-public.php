<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://mauro.com
 * @since      1.0.0
 *
 * @package    Msa_Migrate
 * @subpackage Msa_Migrate/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Msa_Migrate
 * @subpackage Msa_Migrate/public
 * @author     Mauro <mauro@mojahmedia.net>
 */
class Msa_Migrate_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Msa_Migrate_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Msa_Migrate_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/msa-migrate-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Msa_Migrate_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Msa_Migrate_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/msa-migrate-public.js', array( 'jquery' ), $this->version, false );

    /**
     *  In backend there is global ajaxurl variable defined by WordPress itself.
     *
     * This variable is not created by WP in frontend. It means that if you want to use AJAX calls in frontend, then you have to define such variable by yourself.
     * Good way to do this is to use wp_localize_script.
     *
     * @link http://wordpress.stackexchange.com/a/190299/90212
     */
    wp_localize_script( $this->plugin_name, 'wp_ajax', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );		

	}

	/**
	 * Shortcode that list User
	 *
	 * @return Table with Users.
	 */
	public function shortcode_list_users( $atts ) {



		$args = shortcode_atts (
			array(
				'uid'   => $_GET['uid'] ? $_GET['uid'] : '',
				'phase' => $_GET['phase'] ? $_GET['phase'] : '',
				'year' => $_GET['period'] ? $_GET['period'] : ''
			),
			$atts
		);


		// Test code to show the table
		$myListTable = new Msa_Users_Test_Table();


	  echo '<div class="wrap list-user-table"><h2>Users Listing</h2>'; 
  	echo '<form id="submissions-table" method="GET">';
    echo '<input type="hidden" name="page" value="ttest_list_table">';
	  echo $myListTable->msa_users_table_page( $args );
  	echo '</form></div>'; 

	}

	/**
	 * This shortcode load a form to migrate users.
	 *
	 * The shortcode supports the possibility to migrate users based
	 * on uid: user uid on drupal system, phase: msa phase( msa module ) , and year of registration.
	 *
	 * TODO: See that uid is not compatible with the two others parameters of searching.
	 * Split search parameters in two separate forms, one form that supports uid data (one or many in form of one array)
	 * a second form that allow to search using parameters as phase and year of registration.
	 *
	 */

	public function shortcode_migrate_user( $atts ) {
		
		$out = '';

		$args = shortcode_atts (
			array(
				'uid'   => '',
				'phase' => '',
				'year' => ''
			),
			$atts
		);

		if ( isset( $_POST['msa_list_user']) ){

			$nonce = $_POST['migrate_users_nonce'];

			if ( !wp_verify_nonce($nonce, 'migrate_users_shortcode_form') ) {
			
				wp_die('Our Site is protected!!');
			
			}

			// Remove this if we are using ajax
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/msa-migrate-form-public-display.php';

			echo ('list users');
		}

		elseif ( isset( $_POST['migrate_user'] )) {

			$nonce = $_POST['migrate_users_nonce'];

			if ( !wp_verify_nonce($nonce, 'migrate_users_shortcode_form') ) {
			
				wp_die('Our Site is protected!!');
			
			} 
			//else {

				//$msa_migrate_users = new Msa_Migrate_Users(); // call special class inside library folder.
				//echo $msa_migrate_users->migrate_users_query( $_POST['uid'] , $_POST['phase'] , $_POST['period']);// call to function inside previouslly loaded class.			
		//}
		}



		else {

			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/msa-migrate-form-public-display.php';

		}
	}	

	/**
	 * Migrate button for the header of the profile page.
	 *
	 * @return $html: HTML output for migrate buttons.
	 */

	public function msa_migrate_button() {

		$uid = bp_displayed_user_id();

		$migrated = get_user_meta( $uid, 'migrated', true );

		// Abort if profile was migrated before.
		if ( $migrated )  {
			return;
		}

		$profileData = new Msa_Migrate_Profile_Fields();
		$profile_fields = $profileData->msa_profile_show_data( $uid );

		$html = '';
		//$html .= '<button data-uid ='. $uid .' class="migrate-button generic-button" id="msa-migrate-ajax-button">Migrate Profile</button>';

		$html .= '<button class="button" data-open="exampleModal1">View Profile Data</button>';

		$html .= '<div class="reveal" id="exampleModal1" data-reveal>';
		$html .= '  <h2>Profile Fields Info Preview.</h2>';
		$html .= '  <p class="lead">'. $profile_fields .'</p>';
		$html .= '  <button class="close-button" data-close aria-label="Close modal" type="button">';
		$html .= '    <span aria-hidden="true">&times;</span>';
		$html .= '  </button>';
		$html .= '</div>';

		echo $html;
	}

	/**
	 * Ajax Call to migrate profile fields data.
	 */

	public function msa_migrate_profile_fields_handler() {
		$data = array();

		$id = $_POST['id'];

		$profileData = new Msa_Migrate_Profile_Fields();
		$profile_fields = $profileData->msa_profile_migrate_data( $id );		

		// Call to class that migrate user picture
		$migrate_picture = new Msa_Migrate_User_Picture;
		$file = $migrate_picture->custom_media_sideload_image( '', false, $id );

		$data = array(
			'data' => $id,
		);

		echo json_encode( $data ) ; 

		exit;

	}

	/**
	 * Ajax Call For users listing table
	 */
	public function msa_list_users_handler() {

		$data = array();

		$uid = $_POST['uid'] ? $_POST['uid'] : NULL;
		$phase = $_POST['phase'] ? $_POST['phase'] : NULL;
		$period = $_POST['year'] ? $_POST['year'] : NULL;

		$users_listing = new Msa_Migrate_Users;
		//$table = $users_listing->msa_migrate_list_users_preview( $uid, $phase, $period );

		$data = array(
			'message' => 'Preview Users Listing',
			//'table' => $table,
		);		


		echo json_encode( $data ) ;

		die(); 		

		exit;
	}

	/**
	 * Ajax call to migrate user
	 */
	public function msa_migrate_user_handler() {

		$data = array();

		$uid = $_POST['id'];//value from ajax

		// Call to the class that have the function that migrates the user
		$migrate_user = new Msa_Migrate_Users;
		$id = $migrate_user->migrate_users_query( $uid, NULL, NULL );// migrated user id in wp table


		$button = sprintf('<a href="%s" >Visit profile</a>', bp_core_get_user_domain($id));



		// prepares array that return data to ajax(js file)
		$data = array(
			'uid' => $id,
			'profile_link' => $button,
		);

		echo json_encode( $data ) ;// sending data to ajax

		die(); 		

		exit;				
	}
}
