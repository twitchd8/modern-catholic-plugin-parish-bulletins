<?php
/**
 * Plugin Name: Modern Catholic – Parish Bulletins
 * Plugin URI: https://github.com/twitchd8/modern-catholic-plugin-parish-bulletins
 * Description: Publishes dated parish bulletin PDFs for Modern Catholic parish websites with a future-ready home for e-bulletin content.
 * Version: 1.5.2
 * Author: Andrew T. Schmitt
 * License: GPL-3.0-only
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: parish-bulletins
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PARISH_BULLETINS_VERSION', '1.5.2' );
define( 'PARISH_BULLETINS_SCHEMA_VERSION', '1.0.0' );
define( 'PARISH_BULLETINS_FILE', __FILE__ );
define( 'PARISH_BULLETINS_DIR', plugin_dir_path( __FILE__ ) );
define( 'PARISH_BULLETINS_URL', plugin_dir_url( __FILE__ ) );

require_once PARISH_BULLETINS_DIR . 'includes/post-type.php';
require_once PARISH_BULLETINS_DIR . 'includes/settings.php';
require_once PARISH_BULLETINS_DIR . 'includes/retention.php';
require_once PARISH_BULLETINS_DIR . 'includes/admin.php';
require_once PARISH_BULLETINS_DIR . 'includes/frontend.php';

/**
 * Migrates legacy bulletin posts to the Modern Catholic post type key.
 */
function parish_bulletins_maybe_migrate_post_type() {
	global $wpdb;

	if ( PARISH_BULLETINS_SCHEMA_VERSION === get_option( 'parish_bulletins_schema_version' ) ) {
		return;
	}

	$wpdb->update(
		$wpdb->posts,
		array( 'post_type' => 'mc_bulletin' ),
		array( 'post_type' => 'parish_bulletin' ),
		array( '%s' ),
		array( '%s' )
	);

	update_option( 'parish_bulletins_schema_version', PARISH_BULLETINS_SCHEMA_VERSION, false );
	update_option( 'parish_bulletins_flush_rewrite', 1, false );
}

/**
 * Refreshes rewrite rules once after an in-place post type migration.
 */
function parish_bulletins_maybe_flush_rewrite_rules() {
	if ( get_option( 'parish_bulletins_flush_rewrite' ) ) {
		flush_rewrite_rules( false );
		delete_option( 'parish_bulletins_flush_rewrite' );
	}
}

/**
 * Registers content before flushing the new rewrite rules.
 */
function parish_bulletins_activate() {
	parish_bulletins_maybe_migrate_post_type();
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
add_action( 'plugins_loaded', 'parish_bulletins_maybe_migrate_post_type', 5 );
add_action( 'init', 'parish_bulletins_maybe_flush_rewrite_rules', 99 );
