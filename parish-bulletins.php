<?php
/**
 * Plugin Name: Parish Bulletins
 * Description: Publishes dated parish bulletin PDFs with a future-ready home for e-bulletin content.
 * Version: 1.4.0
 * Author: Andrew T. Schmitt
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: parish-bulletins
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PARISH_BULLETINS_VERSION', '1.4.0' );
define( 'PARISH_BULLETINS_FILE', __FILE__ );
define( 'PARISH_BULLETINS_DIR', plugin_dir_path( __FILE__ ) );
define( 'PARISH_BULLETINS_URL', plugin_dir_url( __FILE__ ) );

require_once PARISH_BULLETINS_DIR . 'includes/post-type.php';
require_once PARISH_BULLETINS_DIR . 'includes/retention.php';
require_once PARISH_BULLETINS_DIR . 'includes/admin.php';
require_once PARISH_BULLETINS_DIR . 'includes/frontend.php';

/**
 * Registers content before flushing the new rewrite rules.
 */
function parish_bulletins_activate() {
	parish_bulletins_register_post_type();
	parish_bulletins_schedule_retention();
	flush_rewrite_rules();
}

/**
 * Clears the plugin's rewrite rules on deactivation.
 */
function parish_bulletins_deactivate() {
	parish_bulletins_clear_retention_schedule();
	flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'parish_bulletins_activate' );
register_deactivation_hook( __FILE__, 'parish_bulletins_deactivate' );
