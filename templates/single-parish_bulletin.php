<?php
/**
 * Public single Bulletin view.
 *
 * @package ParishBulletins
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

the_post();
$pdf     = parish_bulletins_get_pdf( get_the_ID() );
$pdf_url = $pdf ? wp_get_attachment_url( $pdf->ID ) : '';
$display_title = parish_bulletins_get_display_title( get_the_ID() );
parish_bulletins_render_page_header();
?>

<main id="primary" class="parish-bulletins-shell parish-bulletin-single">
	<nav class="parish-bulletin-back" aria-label="<?php esc_attr_e( 'Bulletin navigation', 'parish-bulletins' ); ?>">
		<a href="<?php echo esc_url( get_post_type_archive_link( 'parish_bulletin' ) ); ?>">&larr; <?php esc_html_e( 'All bulletins', 'parish-bulletins' ); ?></a>
	</nav>

	<article <?php post_class(); ?>>
		<header class="parish-bulletin-single__header">
			<p class="parish-bulletins-eyebrow"><?php esc_html_e( 'Parish Bulletin', 'parish-bulletins' ); ?></p>
			<h1><?php echo esc_html( $display_title ); ?></h1>
			<time datetime="<?php echo esc_attr( parish_bulletins_get_date( get_the_ID() ) ); ?>">
				<?php echo esc_html( parish_bulletins_get_date( get_the_ID(), get_option( 'date_format' ) ) ); ?>
			</time>
			<?php if ( has_excerpt() ) : ?>
				<div class="parish-bulletin-single__summary"><?php the_excerpt(); ?></div>
			<?php endif; ?>
		</header>

		<?php if ( $pdf_url ) : ?>
			<div class="parish-bulletin-actions parish-bulletin-actions--single">
				<a class="parish-bulletin-button parish-bulletin-button--primary" href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" rel="noopener">
					<?php esc_html_e( 'View PDF', 'parish-bulletins' ); ?>
				</a>
				<a class="parish-bulletin-button" href="<?php echo esc_url( $pdf_url ); ?>" download>
					<?php esc_html_e( 'Download PDF', 'parish-bulletins' ); ?>
				</a>
			</div>

			<div
				class="parish-bulletin-pdf-viewer"
				data-pdf-url="<?php echo esc_url( $pdf_url ); ?>"
				data-loading-message="<?php esc_attr_e( 'Loading bulletin…', 'parish-bulletins' ); ?>"
				data-error-message="<?php esc_attr_e( 'The embedded viewer could not load this bulletin.', 'parish-bulletins' ); ?>"
			>
				<div class="parish-bulletin-viewer-toolbar" role="toolbar" aria-label="<?php esc_attr_e( 'PDF viewer controls', 'parish-bulletins' ); ?>">
					<div class="parish-bulletin-viewer-pages">
						<button type="button" data-pdf-action="previous" aria-label="<?php esc_attr_e( 'Previous page', 'parish-bulletins' ); ?>" disabled>&larr;</button>
						<label>
							<span class="screen-reader-text"><?php esc_html_e( 'Current page', 'parish-bulletins' ); ?></span>
							<input type="number" data-pdf-page value="1" min="1" inputmode="numeric" disabled>
						</label>
						<span aria-hidden="true">/</span>
						<span data-pdf-page-count aria-label="<?php esc_attr_e( 'Total pages', 'parish-bulletins' ); ?>">—</span>
						<button type="button" data-pdf-action="next" aria-label="<?php esc_attr_e( 'Next page', 'parish-bulletins' ); ?>" disabled>&rarr;</button>
					</div>
					<div class="parish-bulletin-viewer-zoom">
						<button type="button" data-pdf-action="zoom-out" aria-label="<?php esc_attr_e( 'Zoom out', 'parish-bulletins' ); ?>" disabled>&minus;</button>
						<span data-pdf-zoom aria-live="polite">100%</span>
						<button type="button" data-pdf-action="zoom-in" aria-label="<?php esc_attr_e( 'Zoom in', 'parish-bulletins' ); ?>" disabled>+</button>
					</div>
				</div>
				<div class="parish-bulletin-viewer-status" data-pdf-status role="status">
					<?php esc_html_e( 'Loading bulletin…', 'parish-bulletins' ); ?>
				</div>
				<div class="parish-bulletin-viewer-canvas" data-pdf-canvas-wrap tabindex="0" aria-label="<?php echo esc_attr( sprintf( __( 'Document pages for %s', 'parish-bulletins' ), $display_title ) ); ?>">
					<canvas data-pdf-canvas hidden></canvas>
				</div>
				<p class="parish-bulletin-viewer-fallback">
					<?php esc_html_e( 'Having trouble with the viewer?', 'parish-bulletins' ); ?>
					<a href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Open the PDF in a new tab.', 'parish-bulletins' ); ?></a>
				</p>
			</div>
		<?php else : ?>
			<div class="parish-bulletins-empty parish-bulletins-empty--notice">
				<p><?php esc_html_e( 'A PDF has not been attached to this bulletin.', 'parish-bulletins' ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( trim( get_the_content() ) ) : ?>
			<section class="parish-bulletin-content" aria-labelledby="parish-bulletin-content-heading">
				<h2 id="parish-bulletin-content-heading"><?php esc_html_e( 'In this bulletin', 'parish-bulletins' ); ?></h2>
				<?php the_content(); ?>
			</section>
		<?php endif; ?>
	</article>
</main>

<?php parish_bulletins_render_page_footer(); ?>
