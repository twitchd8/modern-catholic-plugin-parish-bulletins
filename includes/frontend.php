<?php
/**
 * Public archive and single-bulletin presentation.
 *
 * @package ParishBulletins
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sorts the public archive by Bulletin Date, newest first.
 *
 * @param WP_Query $query Current query.
 */
function parish_bulletins_archive_order( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'parish_bulletin' ) ) {
		return;
	}

	$query->set( 'posts_per_page', 12 );
	$query->set( 'post_status', 'publish' );
	$query->set( 'meta_key', '_parish_bulletin_date' );
	$query->set( 'orderby', array( 'meta_value' => 'DESC', 'date' => 'DESC' ) );
	$query->set( 'meta_type', 'DATE' );
}

/**
 * Uses the plugin's presentation unless a child theme deliberately overrides it.
 *
 * Themes can provide parish-bulletins/archive-parish_bulletin.php and
 * parish-bulletins/single-parish_bulletin.php to own the final presentation.
 *
 * @param string $template Resolved template path.
 * @return string
 */
function parish_bulletins_template_include( $template ) {
	if ( is_post_type_archive( 'parish_bulletin' ) ) {
		$override = locate_template( 'parish-bulletins/archive-parish_bulletin.php' );
		return $override ? $override : PARISH_BULLETINS_DIR . 'templates/archive-parish_bulletin.php';
	}

	if ( is_singular( 'parish_bulletin' ) ) {
		$override = locate_template( 'parish-bulletins/single-parish_bulletin.php' );
		return $override ? $override : PARISH_BULLETINS_DIR . 'templates/single-parish_bulletin.php';
	}

	return $template;
}

/**
 * Loads styles only on Bulletin views.
 */
function parish_bulletins_enqueue_public_assets() {
	if ( ! is_post_type_archive( 'parish_bulletin' ) && ! is_singular( 'parish_bulletin' ) ) {
		return;
	}

	wp_enqueue_style(
		'parish-bulletins',
		PARISH_BULLETINS_URL . 'assets/public.css',
		array(),
		PARISH_BULLETINS_VERSION
	);
}

/**
 * Opens the page through the active theme's header system.
 */
function parish_bulletins_render_page_header() {
	if ( ! wp_is_block_theme() ) {
		get_header();
		return;
	}

	// Render before wp_head() so blocks can register their scripts and styles.
	$header = do_blocks( '<!-- wp:template-part {"slug":"header","tagName":"header"} /-->' );
	?>
	<!doctype html>
	<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php wp_head(); ?>
	</head>
	<body <?php body_class( 'parish-bulletins-page' ); ?>>
	<?php
	wp_body_open();
	echo $header; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Closes the page through the active theme's footer system.
 */
function parish_bulletins_render_page_footer() {
	if ( ! wp_is_block_theme() ) {
		get_footer();
		return;
	}

	$footer = do_blocks( '<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->' );
	echo $footer; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	wp_footer();
	echo '</body></html>';
}

add_action( 'pre_get_posts', 'parish_bulletins_archive_order' );
add_filter( 'template_include', 'parish_bulletins_template_include', 20 );
add_action( 'wp_enqueue_scripts', 'parish_bulletins_enqueue_public_assets' );
