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
parish_bulletins_render_page_header();
?>

<main id="primary" class="parish-bulletins-shell parish-bulletin-single">
	<nav class="parish-bulletin-back" aria-label="<?php esc_attr_e( 'Bulletin navigation', 'parish-bulletins' ); ?>">
		<a href="<?php echo esc_url( get_post_type_archive_link( 'parish_bulletin' ) ); ?>">&larr; <?php esc_html_e( 'All bulletins', 'parish-bulletins' ); ?></a>
	</nav>

	<article <?php post_class(); ?>>
		<header class="parish-bulletin-single__header">
			<p class="parish-bulletins-eyebrow"><?php esc_html_e( 'Parish Bulletin', 'parish-bulletins' ); ?></p>
			<h1><?php the_title(); ?></h1>
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

			<div class="parish-bulletin-pdf-preview">
				<object data="<?php echo esc_url( $pdf_url ); ?>" type="application/pdf" aria-label="<?php echo esc_attr( sprintf( __( 'PDF preview of %s', 'parish-bulletins' ), get_the_title() ) ); ?>">
					<p><?php esc_html_e( 'Your browser cannot display the PDF preview.', 'parish-bulletins' ); ?> <a href="<?php echo esc_url( $pdf_url ); ?>"><?php esc_html_e( 'Open the PDF instead.', 'parish-bulletins' ); ?></a></p>
				</object>
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
