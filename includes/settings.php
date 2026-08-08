<?php
/**
 * Bulletin settings.
 *
 * @package ParishBulletins
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PARISH_BULLETINS_SETTINGS_OPTION', 'parish_bulletins_settings' );
define( 'PARISH_BULLETINS_DEFAULT_RETENTION_MONTHS', 12 );
define( 'PARISH_BULLETINS_MAX_RETENTION_MONTHS', 1200 );

/**
 * Gets validated plugin settings with defaults applied.
 *
 * @return array
 */
function parish_bulletins_get_settings() {
	$saved = get_option( PARISH_BULLETINS_SETTINGS_OPTION, array() );
	$saved = is_array( $saved ) ? $saved : array();

	$mode   = isset( $saved['retention_mode'] ) && 'months' === $saved['retention_mode'] ? 'months' : 'all';
	$months = isset( $saved['retention_months'] ) ? absint( $saved['retention_months'] ) : PARISH_BULLETINS_DEFAULT_RETENTION_MONTHS;
	$months = min( PARISH_BULLETINS_MAX_RETENTION_MONTHS, max( 1, $months ) );

	return array(
		'retention_mode'   => $mode,
		'retention_months' => $months,
	);
}

/**
 * Gets the configured retention period.
 *
 * @return int Number of months, or zero when all Bulletins are kept.
 */
function parish_bulletins_get_retention_months() {
	$settings = parish_bulletins_get_settings();

	return 'all' === $settings['retention_mode'] ? 0 : $settings['retention_months'];
}

/**
 * Sanitizes settings before saving.
 *
 * @param mixed $input Submitted settings.
 * @return array
 */
function parish_bulletins_sanitize_settings( $input ) {
	$input  = is_array( $input ) ? $input : array();
	$mode   = isset( $input['retention_mode'] ) && 'months' === $input['retention_mode'] ? 'months' : 'all';
	$months = isset( $input['retention_months'] ) ? absint( $input['retention_months'] ) : PARISH_BULLETINS_DEFAULT_RETENTION_MONTHS;
	$months = min( PARISH_BULLETINS_MAX_RETENTION_MONTHS, max( 1, $months ) );

	return array(
		'retention_mode'   => $mode,
		'retention_months' => $months,
	);
}

/**
 * Registers Bulletin settings.
 */
function parish_bulletins_register_settings() {
	register_setting(
		'parish_bulletins_settings',
		PARISH_BULLETINS_SETTINGS_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'parish_bulletins_sanitize_settings',
			'default'           => array(
				'retention_mode'   => 'all',
				'retention_months' => PARISH_BULLETINS_DEFAULT_RETENTION_MONTHS,
			),
		)
	);
}

/**
 * Adds the settings screen beneath Bulletins.
 */
function parish_bulletins_add_settings_page() {
	add_submenu_page(
		'edit.php?post_type=parish_bulletin',
		__( 'Bulletin Settings', 'parish-bulletins' ),
		__( 'Settings', 'parish-bulletins' ),
		'manage_options',
		'parish-bulletins-settings',
		'parish_bulletins_render_settings_page'
	);
}

/**
 * Renders the settings screen.
 */
function parish_bulletins_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings = parish_bulletins_get_settings();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Parish Bulletins Settings', 'parish-bulletins' ); ?></h1>
		<form action="options.php" method="post">
			<?php settings_fields( 'parish_bulletins_settings' ); ?>
			<h2><?php esc_html_e( 'Bulletin retention', 'parish-bulletins' ); ?></h2>
			<p><?php esc_html_e( 'Choose how long published and unpublished Bulletin records remain on the website.', 'parish-bulletins' ); ?></p>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Retention policy', 'parish-bulletins' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="radio" name="<?php echo esc_attr( PARISH_BULLETINS_SETTINGS_OPTION ); ?>[retention_mode]" value="all" <?php checked( $settings['retention_mode'], 'all' ); ?>>
								<?php esc_html_e( 'Keep all Bulletins', 'parish-bulletins' ); ?>
							</label>
							<br>
							<label>
								<input type="radio" name="<?php echo esc_attr( PARISH_BULLETINS_SETTINGS_OPTION ); ?>[retention_mode]" value="months" <?php checked( $settings['retention_mode'], 'months' ); ?>>
								<?php esc_html_e( 'Keep Bulletins for a set number of months', 'parish-bulletins' ); ?>
							</label>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="parish-bulletins-retention-months"><?php esc_html_e( 'Months to keep', 'parish-bulletins' ); ?></label></th>
					<td>
						<input id="parish-bulletins-retention-months" class="small-text" type="number" min="1" max="<?php echo esc_attr( PARISH_BULLETINS_MAX_RETENTION_MONTHS ); ?>" step="1" name="<?php echo esc_attr( PARISH_BULLETINS_SETTINGS_OPTION ); ?>[retention_months]" value="<?php echo esc_attr( $settings['retention_months'] ); ?>">
						<p class="description"><?php esc_html_e( 'For example, enter 36 to keep three years. This value is ignored when Keep all Bulletins is selected.', 'parish-bulletins' ); ?></p>
					</td>
				</tr>
			</table>
			<div class="notice notice-warning inline">
				<p><strong><?php esc_html_e( 'Deletion is permanent.', 'parish-bulletins' ); ?></strong> <?php esc_html_e( 'When a month limit is active, older Bulletin records and unshared PDF files are removed during automatic cleanup.', 'parish-bulletins' ); ?></p>
			</div>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Updates the cleanup schedule after the retention setting changes.
 */
function parish_bulletins_retention_setting_updated() {
	parish_bulletins_clear_retention_schedule();
	parish_bulletins_schedule_retention();
}

add_action( 'admin_init', 'parish_bulletins_register_settings' );
add_action( 'admin_menu', 'parish_bulletins_add_settings_page' );
add_action( 'add_option_' . PARISH_BULLETINS_SETTINGS_OPTION, 'parish_bulletins_retention_setting_updated' );
add_action( 'update_option_' . PARISH_BULLETINS_SETTINGS_OPTION, 'parish_bulletins_retention_setting_updated' );
