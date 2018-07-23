<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://mauro.com
 * @since             1.0.0
 * @package           Msa_Migrate
 *
 * @wordpress-plugin
 * Plugin Name:       MSA Migrate
 * Plugin URI:        http://msamigrate.com
 * Description:       This plugin handles several function related to the migration porcess.
 * Version:           1.0.0
 * Author:            Mauro
 * Author URI:        http://mauro.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       msa-migrate
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGIN_NAME_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-msa-migrate-activator.php
 */
function activate_msa_migrate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-msa-migrate-activator.php';
	Msa_Migrate_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-msa-migrate-deactivator.php
 */
function deactivate_msa_migrate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-msa-migrate-deactivator.php';
	Msa_Migrate_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_msa_migrate' );
register_deactivation_hook( __FILE__, 'deactivate_msa_migrate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-msa-migrate.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_msa_migrate() {

	$plugin = new Msa_Migrate();
	$plugin->run();

}
run_msa_migrate();
