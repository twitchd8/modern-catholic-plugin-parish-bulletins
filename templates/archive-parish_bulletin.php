<?php
/**
 * Public Bulletin archive.
 *
 * @package ParishBulletins
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
parish_bulletins_render_page_header();
?>

<main id="primary" class="parish-bulletins-shell">
	<header class="parish-bulletins-hero">
		<p class="parish-bulletins-eyebrow"><?php esc_html_e( 'Parish Life', 'parish-bulletins' ); ?></p>
		<h1><?php post_type_archive_title(); ?></h1>
		<p><?php esc_html_e( 'Read the latest parish news, schedules, and announcements.', 'parish-bulletins' ); ?></p>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="parish-bulletins-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				$pdf           = parish_bulletins_get_pdf( get_the_ID() );
				$display_title = parish_bulletins_get_display_title( get_the_ID() );
				$thumbnail     = parish_bulletins_get_thumbnail_html( get_the_ID() );
				?>
				<article <?php post_class( 'parish-bulletin-card' ); ?>>
					<a class="parish-bulletin-card__primary" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Open %s', 'parish-bulletins' ), $display_title ) ); ?>">
						<?php if ( $thumbnail ) : ?>
							<div class="parish-bulletin-card__image"><?php echo wp_kses_post( $thumbnail ); ?></div>
						<?php else : ?>
							<div class="parish-bulletin-card__document" aria-hidden="true"><span>PDF</span></div>
						<?php endif; ?>
						<div class="parish-bulletin-card__body">
							<time datetime="<?php echo esc_attr( parish_bulletins_get_date( get_the_ID() ) ); ?>">
								<?php echo esc_html( parish_bulletins_get_date( get_the_ID(), get_option( 'date_format' ) ) ); ?>
							</time>
							<h2><?php echo esc_html( $display_title ); ?></h2>
							<?php if ( has_excerpt() ) : ?>
								<div class="parish-bulletin-card__excerpt"><?php the_excerpt(); ?></div>
							<?php endif; ?>
						</div>
					</a>
					<div class="parish-bulletin-actions">
						<a href="<?php the_permalink(); ?>"><?php esc_html_e( 'View bulletin', 'parish-bulletins' ); ?></a>
						<?php if ( $pdf ) : ?>
							<a href="<?php echo esc_url( wp_get_attachment_url( $pdf->ID ) ); ?>" download><?php esc_html_e( 'Download PDF', 'parish-bulletins' ); ?></a>
						<?php endif; ?>
					</div>
				</article>
			<?php endwhile; ?>
		</div>

		<nav class="parish-bulletins-pagination" aria-label="<?php esc_attr_e( 'Bulletin pages', 'parish-bulletins' ); ?>">
			<?php the_posts_pagination(); ?>
		</nav>
	<?php else : ?>
		<div class="parish-bulletins-empty">
			<h2><?php esc_html_e( 'No bulletins have been published yet.', 'parish-bulletins' ); ?></h2>
			<p><?php esc_html_e( 'Please check back soon.', 'parish-bulletins' ); ?></p>
		</div>
	<?php endif; ?>
</main>

<?php parish_bulletins_render_page_footer(); ?>
