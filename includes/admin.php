<?php
/**
 * Bulletin editor and administration list.
 *
 * @package ParishBulletins
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds the focused date/PDF workflow to the Bulletin editor.
 */
function parish_bulletins_add_meta_box() {
	if ( use_block_editor_for_post_type( 'parish_bulletin' ) ) {
		return;
	}

	add_meta_box(
		'parish-bulletin-details',
		__( 'Bulletin Details', 'parish-bulletins' ),
		'parish_bulletins_render_meta_box',
		'parish_bulletin',
		'normal',
		'high'
	);
}

/**
 * Renders the Bulletin Details meta box.
 *
 * @param WP_Post $post Current bulletin.
 */
function parish_bulletins_render_meta_box( $post ) {
	$date = get_post_meta( $post->ID, '_parish_bulletin_date', true );
	$pdf  = parish_bulletins_get_pdf( $post );

	wp_nonce_field( 'parish_bulletins_save_details', 'parish_bulletins_nonce' );
	?>
	<div class="parish-bulletin-fields">
		<p class="description parish-bulletin-intro">
			<?php esc_html_e( 'Choose the Sunday or publication date and its PDF. The excerpt and featured image are optional; the main editor can hold future e-bulletin content.', 'parish-bulletins' ); ?>
		</p>
		<p>
			<label for="parish-bulletin-date"><strong><?php esc_html_e( 'Bulletin date', 'parish-bulletins' ); ?></strong></label><br>
			<input type="date" id="parish-bulletin-date" name="parish_bulletin_date" value="<?php echo esc_attr( $date ); ?>" required>
		</p>
		<div class="parish-bulletin-pdf-field">
			<strong><?php esc_html_e( 'Bulletin PDF', 'parish-bulletins' ); ?></strong>
			<input type="hidden" id="parish-bulletin-pdf-id" name="parish_bulletin_pdf_id" value="<?php echo esc_attr( $pdf ? $pdf->ID : 0 ); ?>">
			<p id="parish-bulletin-pdf-name" class="parish-bulletin-file-name">
				<?php echo $pdf ? esc_html( wp_basename( get_attached_file( $pdf->ID ) ) ) : esc_html__( 'No PDF selected.', 'parish-bulletins' ); ?>
			</p>
			<p>
				<button type="button" class="button button-secondary" id="parish-bulletin-select-pdf">
					<?php echo $pdf ? esc_html__( 'Replace PDF', 'parish-bulletins' ) : esc_html__( 'Select or Upload PDF', 'parish-bulletins' ); ?>
				</button>
				<button type="button" class="button button-link-delete<?php echo $pdf ? '' : ' hidden'; ?>" id="parish-bulletin-remove-pdf">
					<?php esc_html_e( 'Remove PDF', 'parish-bulletins' ); ?>
				</button>
			</p>
		</div>
	</div>
	<?php
}

/**
 * Loads the WordPress media picker only on Bulletin editing screens.
 *
 * @param string $hook_suffix Current admin screen hook.
 */
function parish_bulletins_enqueue_admin_assets( $hook_suffix ) {
	if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'parish_bulletin' !== $screen->post_type ) {
		return;
	}
	if ( use_block_editor_for_post_type( 'parish_bulletin' ) ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_style(
		'parish-bulletins-admin',
		PARISH_BULLETINS_URL . 'assets/admin.css',
		array(),
		PARISH_BULLETINS_VERSION
	);
	wp_enqueue_script(
		'parish-bulletins-admin',
		PARISH_BULLETINS_URL . 'assets/admin.js',
		array( 'jquery' ),
		PARISH_BULLETINS_VERSION,
		true
	);
	wp_localize_script(
		'parish-bulletins-admin',
		'parishBulletinsAdmin',
		array(
			'title'        => __( 'Select a bulletin PDF', 'parish-bulletins' ),
			'button'       => __( 'Use this PDF', 'parish-bulletins' ),
			'empty'        => __( 'No PDF selected.', 'parish-bulletins' ),
			'selectLabel'  => __( 'Select or Upload PDF', 'parish-bulletins' ),
			'replaceLabel' => __( 'Replace PDF', 'parish-bulletins' ),
		)
	);
}

/**
 * Adds the focused Bulletin Details panel to the block editor sidebar.
 */
function parish_bulletins_enqueue_editor_assets() {
	$screen = get_current_screen();
	if ( ! $screen || 'parish_bulletin' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_script(
		'parish-bulletins-editor',
		PARISH_BULLETINS_URL . 'assets/editor.js',
		array( 'wp-block-editor', 'wp-components', 'wp-data', 'wp-editor', 'wp-element', 'wp-i18n', 'wp-plugins' ),
		PARISH_BULLETINS_VERSION,
		true
	);
}

/**
 * Saves and validates Bulletin Details.
 *
 * @param int $post_id Bulletin post ID.
 */
function parish_bulletins_save_details( $post_id ) {
	if (
		! isset( $_POST['parish_bulletins_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['parish_bulletins_nonce'] ) ), 'parish_bulletins_save_details' ) ||
		( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
		! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}

	$date = isset( $_POST['parish_bulletin_date'] )
		? parish_bulletins_sanitize_date( wp_unslash( $_POST['parish_bulletin_date'] ) )
		: '';

	if ( $date ) {
		update_post_meta( $post_id, '_parish_bulletin_date', $date );
	} else {
		delete_post_meta( $post_id, '_parish_bulletin_date' );
	}

	$pdf_id = isset( $_POST['parish_bulletin_pdf_id'] ) ? absint( $_POST['parish_bulletin_pdf_id'] ) : 0;
	if ( $pdf_id && 'attachment' === get_post_type( $pdf_id ) && 'application/pdf' === get_post_mime_type( $pdf_id ) ) {
		update_post_meta( $post_id, '_parish_bulletin_pdf_id', $pdf_id );
	} else {
		delete_post_meta( $post_id, '_parish_bulletin_pdf_id' );
	}
}

/**
 * Adds useful date and PDF columns to the Bulletin list.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function parish_bulletins_admin_columns( $columns ) {
	$new_columns = array();

	foreach ( $columns as $key => $label ) {
		$new_columns[ $key ] = $label;
		if ( 'title' === $key ) {
			$new_columns['bulletin_date'] = __( 'Bulletin Date', 'parish-bulletins' );
			$new_columns['bulletin_pdf']  = __( 'PDF', 'parish-bulletins' );
		}
	}

	return $new_columns;
}

/**
 * Renders custom Bulletin list columns.
 *
 * @param string $column  Column key.
 * @param int    $post_id Bulletin post ID.
 */
function parish_bulletins_admin_column_content( $column, $post_id ) {
	if ( 'bulletin_date' === $column ) {
		echo esc_html( parish_bulletins_get_date( $post_id, get_option( 'date_format' ) ) );
	}

	if ( 'bulletin_pdf' === $column ) {
		$pdf = parish_bulletins_get_pdf( $post_id );
		if ( $pdf ) {
			printf(
				'<a href="%1$s" target="_blank" rel="noopener">%2$s<span class="screen-reader-text">: %3$s</span></a>',
				esc_url( wp_get_attachment_url( $pdf->ID ) ),
				esc_html__( 'View PDF', 'parish-bulletins' ),
				esc_html( get_the_title( $post_id ) )
			);
		} else {
			echo '<span aria-hidden="true">—</span><span class="screen-reader-text">' . esc_html__( 'No PDF', 'parish-bulletins' ) . '</span>';
		}
	}
}

/**
 * Makes the custom Bulletin Date column sortable.
 *
 * @param array $columns Sortable columns.
 * @return array
 */
function parish_bulletins_sortable_columns( $columns ) {
	$columns['bulletin_date'] = 'bulletin_date';
	return $columns;
}

/**
 * Applies Bulletin Date sorting in the admin list.
 *
 * @param WP_Query $query Current query.
 */
function parish_bulletins_admin_order( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || 'parish_bulletin' !== $query->get( 'post_type' ) ) {
		return;
	}

	if ( 'bulletin_date' === $query->get( 'orderby' ) ) {
		$query->set( 'meta_key', '_parish_bulletin_date' );
		$query->set( 'orderby', 'meta_value' );
		$query->set( 'meta_type', 'DATE' );
	}
}

add_action( 'add_meta_boxes_parish_bulletin', 'parish_bulletins_add_meta_box' );
add_action( 'admin_enqueue_scripts', 'parish_bulletins_enqueue_admin_assets' );
add_action( 'enqueue_block_editor_assets', 'parish_bulletins_enqueue_editor_assets' );
add_action( 'save_post_parish_bulletin', 'parish_bulletins_save_details' );
add_filter( 'manage_parish_bulletin_posts_columns', 'parish_bulletins_admin_columns' );
add_action( 'manage_parish_bulletin_posts_custom_column', 'parish_bulletins_admin_column_content', 10, 2 );
add_filter( 'manage_edit-parish_bulletin_sortable_columns', 'parish_bulletins_sortable_columns' );
add_action( 'pre_get_posts', 'parish_bulletins_admin_order' );
