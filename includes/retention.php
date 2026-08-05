<?php
/**
 * Rolling Bulletin retention.
 *
 * @package ParishBulletins
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PARISH_BULLETINS_RETENTION_MONTHS', 12 );
define( 'PARISH_BULLETINS_RETENTION_HOOK', 'parish_bulletins_run_retention' );
define( 'PARISH_BULLETINS_RETENTION_BATCH_SIZE', 100 );

/**
 * Gets the oldest Bulletin date that remains available online.
 *
 * @return string Date in Y-m-d format.
 */
function parish_bulletins_get_retention_cutoff() {
	return current_datetime()
		->modify( '-' . PARISH_BULLETINS_RETENTION_MONTHS . ' months' )
		->format( 'Y-m-d' );
}

/**
 * Schedules the daily cleanup without creating duplicate cron events.
 */
function parish_bulletins_schedule_retention() {
	if ( wp_next_scheduled( PARISH_BULLETINS_RETENTION_HOOK ) ) {
		return;
	}

	wp_schedule_event(
		time() + HOUR_IN_SECONDS,
		'daily',
		PARISH_BULLETINS_RETENTION_HOOK
	);
}

/**
 * Removes every retention event when the plugin is deactivated.
 */
function parish_bulletins_clear_retention_schedule() {
	wp_clear_scheduled_hook( PARISH_BULLETINS_RETENTION_HOOK );
}

/**
 * Checks whether a Bulletin PDF is still used outside the expiring Bulletin.
 *
 * WordPress does not maintain a universal attachment-usage index, so this
 * checks the attachment parent, other Bulletin records, attachment IDs stored
 * in post metadata, and links embedded in other post content before allowing
 * the media file to be removed.
 *
 * @param int $pdf_id      PDF attachment ID.
 * @param int $bulletin_id Bulletin being removed.
 * @return bool
 */
function parish_bulletins_pdf_is_shared( $pdf_id, $bulletin_id ) {
	$attachment = get_post( $pdf_id );
	if ( ! $attachment || 'attachment' !== $attachment->post_type ) {
		return false;
	}

	if ( $attachment->post_parent && (int) $attachment->post_parent !== (int) $bulletin_id ) {
		return true;
	}

	$other_bulletins = get_posts(
		array(
			'post_type'      => 'parish_bulletin',
			'post_status'    => array( 'publish', 'private', 'draft', 'pending', 'future', 'trash' ),
			'post__not_in'   => array( $bulletin_id ),
			'fields'         => 'ids',
			'posts_per_page' => 1,
			'no_found_rows'  => true,
			'meta_query'     => array(
				array(
					'key'     => '_parish_bulletin_pdf_id',
					'value'   => $pdf_id,
					'compare' => '=',
					'type'    => 'NUMERIC',
				),
			),
		)
	);

	if ( $other_bulletins ) {
		return true;
	}

	global $wpdb;

	// Conservatively preserve PDFs referenced by another post's metadata.
	$linked_meta = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id
			FROM {$wpdb->postmeta}
			WHERE post_id NOT IN (%d, %d)
				AND meta_value = %s
			LIMIT 1",
			$bulletin_id,
			$pdf_id,
			(string) $pdf_id
		)
	);

	if ( $linked_meta ) {
		return true;
	}

	$pdf_url = wp_get_attachment_url( $pdf_id );
	if ( ! $pdf_url ) {
		return false;
	}

	$linked_post = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT ID
			FROM {$wpdb->posts}
			WHERE ID <> %d
				AND post_type NOT IN ('attachment', 'revision')
				AND post_content LIKE %s
			LIMIT 1",
			$bulletin_id,
			'%' . $wpdb->esc_like( $pdf_url ) . '%'
		)
	);

	return ! empty( $linked_post );
}

/**
 * Permanently removes Bulletins older than the rolling retention window.
 *
 * @return array Cleanup summary.
 */
function parish_bulletins_apply_retention() {
	$cutoff = parish_bulletins_get_retention_cutoff();
	$result = array(
		'run_at'         => current_time( 'mysql', true ),
		'cutoff'         => $cutoff,
		'deleted'        => 0,
		'pdfs_deleted'   => 0,
		'pdfs_preserved' => 0,
		'failed'         => 0,
	);

	if ( get_transient( 'parish_bulletins_retention_lock' ) ) {
		return $result;
	}

	set_transient( 'parish_bulletins_retention_lock', 1, 15 * MINUTE_IN_SECONDS );

	try {
		$expired = get_posts(
			array(
				'post_type'      => 'parish_bulletin',
				'post_status'    => array( 'publish', 'private', 'draft', 'pending', 'future', 'trash' ),
				'fields'         => 'ids',
				'posts_per_page' => PARISH_BULLETINS_RETENTION_BATCH_SIZE,
				'orderby'        => 'meta_value',
				'order'          => 'ASC',
				'no_found_rows'  => true,
				'meta_key'       => '_parish_bulletin_date',
				'meta_type'      => 'DATE',
				'meta_query'     => array(
					array(
						'key'     => '_parish_bulletin_date',
						'value'   => $cutoff,
						'compare' => '<',
						'type'    => 'DATE',
					),
				),
			)
		);

		foreach ( $expired as $bulletin_id ) {
			$pdf_id    = absint( get_post_meta( $bulletin_id, '_parish_bulletin_pdf_id', true ) );
			$pdf_shared = $pdf_id ? parish_bulletins_pdf_is_shared( $pdf_id, $bulletin_id ) : false;

			if ( ! wp_delete_post( $bulletin_id, true ) ) {
				++$result['failed'];
				continue;
			}

			++$result['deleted'];

			if ( ! $pdf_id ) {
				continue;
			}

			if ( $pdf_shared ) {
				++$result['pdfs_preserved'];
				continue;
			}

			if ( wp_delete_attachment( $pdf_id, true ) ) {
				++$result['pdfs_deleted'];
			} else {
				++$result['failed'];
			}
		}
	} finally {
		delete_transient( 'parish_bulletins_retention_lock' );
	}

	update_option( 'parish_bulletins_last_retention_run', $result, false );

	return $result;
}

/**
 * Displays the retention policy and latest result on Bulletin admin screens.
 */
function parish_bulletins_retention_admin_notice() {
	$screen = get_current_screen();
	if ( ! $screen || 'parish_bulletin' !== $screen->post_type ) {
		return;
	}

	$last_run = get_option( 'parish_bulletins_last_retention_run', array() );
	$class    = ! empty( $last_run['failed'] ) ? 'notice notice-warning' : 'notice notice-info';
	?>
	<div class="<?php echo esc_attr( $class ); ?>">
		<p>
			<strong><?php esc_html_e( 'Rolling 12-month retention is active.', 'parish-bulletins' ); ?></strong>
			<?php esc_html_e( 'Bulletins older than 12 months are permanently removed during daily cleanup. Unshared PDF files and their generated previews are deleted; media still used elsewhere is preserved.', 'parish-bulletins' ); ?>
		</p>
		<?php if ( ! empty( $last_run['run_at'] ) ) : ?>
			<p>
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: number of Bulletins, 2: number of PDFs. */
						__( 'Last cleanup removed %1$d Bulletins and %2$d PDF files.', 'parish-bulletins' ),
						absint( isset( $last_run['deleted'] ) ? $last_run['deleted'] : 0 ),
						absint( isset( $last_run['pdfs_deleted'] ) ? $last_run['pdfs_deleted'] : 0 )
					)
				);
				?>
			</p>
		<?php endif; ?>
	</div>
	<?php
}

add_action( 'init', 'parish_bulletins_schedule_retention', 20 );
add_action( PARISH_BULLETINS_RETENTION_HOOK, 'parish_bulletins_apply_retention' );
add_action( 'admin_notices', 'parish_bulletins_retention_admin_notice' );
