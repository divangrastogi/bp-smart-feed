<?php
/**
 * Plugin Name: BP Smart Feed Curator
 * Plugin URI: https://wbcomdesigns.com/
 * Description: Intelligently re-ranks BuddyPress activity feeds based on user engagement metrics and personalized interests. A premium-quality plugin for enhanced social networking experience.
 * Version: 1.0.0
 * Author: WBCom Designs
 * Author URI: https://wbcomdesigns.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: bp-smart-feed-curator
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * BuddyPress: 10.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Define plugin constants.
define( 'BPSFC_VERSION', '1.0.0' );
define( 'BPSFC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BPSFC_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'BPSFC_PLUGIN_FILE', __FILE__ );
define( 'BPSFC_TEXT_DOMAIN', 'bp-smart-feed-curator' );
define( 'BPSFC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Include the core plugin class.
require_once BPSFC_PLUGIN_PATH . 'includes/class-core.php';
// Include activation and deactivation classes.
require_once BPSFC_PLUGIN_PATH . 'includes/class-activator.php';
require_once BPSFC_PLUGIN_PATH . 'includes/class-deactivator.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function bpsfc_run_plugin() {
    $plugin = new BPSFC_Core();
    $plugin->run();
}
bpsfc_run_plugin();
// Register activation and deactivation hooks.
register_activation_hook( __FILE__, array( 'BPSFC_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'BPSFC_Deactivator', 'deactivate' ) );
