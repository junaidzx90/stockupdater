<?php
ob_start();
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://easeare.com
 * @since             1.0.0
 * @package           Stockupdater
 *
 * @wordpress-plugin
 * Plugin Name:       Stock Updater
 * Plugin URI:        https://github.com/junaidzx90/stockupdater
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Md Junayed
 * Author URI:        http://easeare.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       stockupdater
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
define( 'STOCKUPDATER_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-stockupdater-activator.php
 */
function activate_stockupdater() {
	
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-stockupdater-deactivator.php
 */
function deactivate_stockupdater() {
	wp_clear_scheduled_hook('stockupdater_update_products');
}

register_activation_hook( __FILE__, 'activate_stockupdater' );
register_deactivation_hook( __FILE__, 'deactivate_stockupdater' );

/**
 * XMLSimpler class for handling large data
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-xmlsimpler.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-stockupdater.php';

if(isset($_POST['save_form'])){
	if(isset($_POST['feed_one_prefix'])){
		update_option( 'feed_one_prefix', $_POST['feed_one_prefix'] );
	}
	if(isset($_POST['feed_two_prefix'])){
		update_option( 'feed_two_prefix', $_POST['feed_two_prefix'] );
	}
}
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
add_action( 'plugins_loaded', 'run_stockupdater' );
function run_stockupdater() {
	$plugin = new StockUpdater();
}