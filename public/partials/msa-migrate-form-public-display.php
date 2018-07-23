<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://mauro.com
 * @since      1.0.0
 *
 * @package    Msa_Migrate
 * @subpackage Msa_Migrate/public/partials
 */
?>

	<form method = "post" action="">
		<?php wp_nonce_field( 'migrate_users_shortcode_form', 'migrate_users_nonce' ); ?>

		<input class="username" type="text" name="uid" placeholder = "UID" value="<?php echo $_GET['uid'] ?>">
		<input class="user-phase" type="text" name="phase" placeholder = "Phase" value="<?php echo $_GET['phase'] ?>" >
		<input class="user-period" type="text" name="period" placeholder = "Year" value="<?php echo $_GET['period'] ?>" >
		<input class="button button-migrate-users" type = "submit" name = "migrate_user" value = "Migrate Users" >
		<input class="button button-list-users" type = "submit" name = "msa_list_user" value = "Preview Users Migrate" title="List the users to be migrated" >
	</form>

	<div class="user-list-wrapper" ></div>



<?php

	

	//$users_listing = new Msa_Migrate_Users;
	//$table = $users_listing->msa_migrate_list_users_preview( NULL, 6, 2008 );
	//echo $table;
?>
