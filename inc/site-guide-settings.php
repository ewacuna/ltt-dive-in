<?php
/**
 * Site Guide: theme settings screens.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin menu groups that settings guides are listed under, in display order.
 *
 * @return string[]
 */
function ltt_dive_in_settings_guide_groups() {
	return array(
		'appearance' => __( 'Appearance', 'ltt-dive-in' ),
		'settings'   => __( 'Settings', 'ltt-dive-in' ),
		'posts'      => __( 'Posts', 'ltt-dive-in' ),
	);
}

/**
 * Load every settings guide from guides/settings/*.php, keyed by file slug.
 *
 * @return array[]
 */
function ltt_dive_in_get_settings_guides() {
	static $guides = null;

	if ( null !== $guides ) {
		return $guides;
	}

	$guides = array();

	foreach ( (array) glob( LTT_DIVE_IN_DIR . '/guides/settings/*.php' ) as $file ) {
		$guide = include $file;

		if ( ! is_array( $guide ) || empty( $guide['title'] ) || empty( $guide['screen']['slug'] ) ) {
			continue;
		}

		// Screens owned by a plugin are only documented where that plugin runs.
		if ( isset( $guide['available'] ) && is_callable( $guide['available'] ) && ! call_user_func( $guide['available'] ) ) {
			continue;
		}

		$guide['slug'] = sanitize_key( basename( $file, '.php' ) );
		$guides[ $guide['slug'] ] = $guide;
	}

	uasort(
		$guides,
		static function ( $a, $b ) {
			$order = array_keys( ltt_dive_in_settings_guide_groups() );
			$group = array_search( $a['group'] ?? '', $order, true ) <=> array_search( $b['group'] ?? '', $order, true );

			return 0 !== $group ? $group : strnatcasecmp( $a['title'], $b['title'] );
		}
	);

	return $guides;
}

/**
 * Get the URL of a settings guide.
 *
 * @param string $slug Guide slug, or empty for the list.
 * @return string
 */
function ltt_dive_in_settings_guide_url( $slug = '' ) {
	$args = array( 'page' => 'ltt-dive-in-settings-guide' );

	if ( $slug ) {
		$args['setting'] = $slug;
	}

	return add_query_arg( $args, admin_url( 'admin.php' ) );
}

/**
 * Get the admin URL of the settings screen a guide documents.
 *
 * @param array $guide Settings guide.
 * @return string
 */
function ltt_dive_in_settings_guide_screen_url( $guide ) {
	$parent = isset( $guide['screen']['parent'] ) ? (string) $guide['screen']['parent'] : 'admin.php';

	return add_query_arg( 'page', $guide['screen']['slug'], admin_url( $parent ) );
}

/**
 * Whether the current user can open and change a guide's settings screen.
 *
 * @param array $guide Settings guide.
 * @return bool
 */
function ltt_dive_in_settings_guide_can_edit( $guide ) {
	return current_user_can( isset( $guide['screen']['capability'] ) ? (string) $guide['screen']['capability'] : 'manage_options' );
}

/**
 * Run a guide's status checks.
 *
 * Checks report only whether something is set, never a saved value.
 *
 * @param array $guide Settings guide.
 * @return array[] Rows with label, state (ok, warning, off), and text.
 */
function ltt_dive_in_settings_guide_status( $guide ) {
	$rows = array();

	foreach ( (array) ( $guide['status'] ?? array() ) as $check ) {
		if ( empty( $check['label'] ) || ! isset( $check['check'] ) || ! is_callable( $check['check'] ) ) {
			continue;
		}

		$result = call_user_func( $check['check'] );

		if ( ! is_array( $result ) || empty( $result['text'] ) ) {
			continue;
		}

		$rows[] = array(
			'label' => (string) $check['label'],
			'state' => in_array( $result['state'] ?? '', array( 'ok', 'warning', 'off' ), true ) ? $result['state'] : 'off',
			'text'  => (string) $result['text'],
		);
	}

	return $rows;
}

/**
 * Status check for an API key, reporting only whether one is available.
 *
 * @param string        $key          The key value from its getter (never displayed).
 * @param bool          $is_constant  Whether the key comes from wp-config.php.
 * @param string        $missing_text What breaks while the key is missing.
 * @return array{state:string,text:string}
 */
function ltt_dive_in_settings_guide_key_check( $key, $is_constant, $missing_text ) {
	if ( '' === trim( (string) $key ) ) {
		return array(
			'state' => 'warning',
			/* translators: %s: what breaks without the key. */
			'text'  => sprintf( __( 'Missing. %s', 'ltt-dive-in' ), $missing_text ),
		);
	}

	return array(
		'state' => 'ok',
		'text'  => $is_constant ? __( 'Set in the server configuration', 'ltt-dive-in' ) : __( 'Set', 'ltt-dive-in' ),
	);
}

/**
 * Status check for the last request to an outside service.
 *
 * Reads the status record the theme already keeps for that service.
 *
 * @param string $option Option holding an array with "status" and "time".
 * @return array{state:string,text:string}
 */
function ltt_dive_in_settings_guide_last_request_check( $option ) {
	$status = get_option( $option, array() );

	if ( ! is_array( $status ) || empty( $status['status'] ) ) {
		return array(
			'state' => 'off',
			'text'  => __( 'No request yet', 'ltt-dive-in' ),
		);
	}

	$when = ! empty( $status['time'] ) ? wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) $status['time'] ) : '';

	if ( 'ok' === $status['status'] ) {
		return array(
			'state' => 'ok',
			/* translators: %s: date and time. */
			'text'  => $when ? sprintf( __( 'Working (checked %s)', 'ltt-dive-in' ), $when ) : __( 'Working', 'ltt-dive-in' ),
		);
	}

	return array(
		'state' => 'warning',
		/* translators: %s: date and time. */
		'text'  => $when ? sprintf( __( 'Failed (checked %s). See the details on the settings screen.', 'ltt-dive-in' ), $when ) : __( 'Failed. See the details on the settings screen.', 'ltt-dive-in' ),
	);
}

/**
 * Render the status badge text for a settings card.
 *
 * @param array[] $rows Status rows.
 * @return array{state:string,text:string}|null
 */
function ltt_dive_in_settings_guide_status_summary( $rows ) {
	if ( ! $rows ) {
		return null;
	}

	$warnings = count(
		array_filter(
			$rows,
			static function ( $row ) {
				return 'warning' === $row['state'];
			}
		)
	);

	if ( $warnings ) {
		return array(
			'state' => 'warning',
			/* translators: %d: number of items. */
			'text'  => sprintf( _n( '%d item needs attention', '%d items need attention', $warnings, 'ltt-dive-in' ), $warnings ),
		);
	}

	$all_off = ! array_filter(
		$rows,
		static function ( $row ) {
			return 'off' !== $row['state'];
		}
	);

	return $all_off
		? array(
			'state' => 'off',
			'text'  => __( 'Off', 'ltt-dive-in' ),
		)
		: array(
			'state' => 'ok',
			'text'  => __( 'Set up', 'ltt-dive-in' ),
		);
}

/**
 * Render the Settings section of the Site Guide.
 */
function ltt_dive_in_render_settings_guide_page() {
	if ( ! current_user_can( ltt_dive_in_block_guide_capability() ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to view this page.', 'ltt-dive-in' ) );
	}

	$guides = ltt_dive_in_get_settings_guides();
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only view selection.
	$current = isset( $_GET['setting'] ) ? sanitize_key( wp_unslash( $_GET['setting'] ) ) : '';

	echo '<div class="wrap ltt-block-guide">';

	if ( $current && isset( $guides[ $current ] ) ) {
		ltt_dive_in_render_settings_guide_detail( $guides[ $current ] );
	} else {
		ltt_dive_in_render_settings_guide_index( $guides );
	}

	echo '</div>';
}

/**
 * Render the list of settings guides, grouped by admin menu.
 *
 * @param array[] $guides Settings guides.
 */
function ltt_dive_in_render_settings_guide_index( $guides ) {
	?>
	<h1><?php esc_html_e( 'Settings', 'ltt-dive-in' ); ?></h1>
	<p class="ltt-block-guide__lead">
		<?php esc_html_e( 'These screens control parts of the site that are not edited page by page, like the header, the footer, alerts, and connections to outside services. Open one to see what it controls and what each setting does.', 'ltt-dive-in' ); ?>
	</p>

	<?php if ( ! $guides ) : ?>
		<p><?php esc_html_e( 'No settings guides have been written yet.', 'ltt-dive-in' ); ?></p>
		<?php
		return;
	endif;

	foreach ( ltt_dive_in_settings_guide_groups() as $group => $group_label ) :
		$in_group = array_filter(
			$guides,
			static function ( $guide ) use ( $group ) {
				return ( $guide['group'] ?? '' ) === $group;
			}
		);

		if ( ! $in_group ) {
			continue;
		}
		?>
		<section class="ltt-block-guide__section" aria-labelledby="ltt-settings-group-<?php echo esc_attr( $group ); ?>">
			<h2 id="ltt-settings-group-<?php echo esc_attr( $group ); ?>"><?php echo esc_html( $group_label ); ?></h2>
			<ul class="ltt-block-guide__grid">
				<?php foreach ( $in_group as $guide ) : ?>
					<?php
					$summary = ltt_dive_in_settings_guide_can_edit( $guide ) ? ltt_dive_in_settings_guide_status_summary( ltt_dive_in_settings_guide_status( $guide ) ) : null;
					$updated = ltt_dive_in_block_guide_updated( $guide );
					?>
					<li class="ltt-block-guide__card">
						<span class="ltt-block-guide__card-icon dashicons dashicons-<?php echo esc_attr( $guide['icon'] ?? 'admin-generic' ); ?>" aria-hidden="true"></span>
						<h3 class="ltt-block-guide__card-title">
							<a href="<?php echo esc_url( ltt_dive_in_settings_guide_url( $guide['slug'] ) ); ?>"><?php echo esc_html( $guide['title'] ); ?></a>
						</h3>
						<?php if ( ! empty( $guide['menu'] ) ) : ?>
							<p class="ltt-block-guide__card-menu"><?php echo esc_html( $guide['menu'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $guide['short'] ) ) : ?>
							<p class="ltt-block-guide__card-description"><?php echo esc_html( $guide['short'] ); ?></p>
						<?php endif; ?>
						<?php if ( $summary ) : ?>
							<ul class="ltt-block-guide__badges">
								<li class="ltt-block-guide__status<?php echo 'off' === $summary['state'] ? '' : ' is-' . esc_attr( 'ok' === $summary['state'] ? 'ready' : 'warning' ); ?>"><?php echo esc_html( $summary['text'] ); ?></li>
							</ul>
						<?php endif; ?>
						<?php if ( $updated ) : ?>
							<p class="ltt-block-guide__card-updated">
								<?php
								/* translators: %s: date. */
								echo esc_html( sprintf( __( 'Updated %s', 'ltt-dive-in' ), $updated ) );
								?>
							</p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
		<?php
	endforeach;
}

/**
 * Render one settings guide.
 *
 * @param array $guide Settings guide.
 */
function ltt_dive_in_render_settings_guide_detail( $guide ) {
	$can_edit = ltt_dive_in_settings_guide_can_edit( $guide );
	$status   = $can_edit ? ltt_dive_in_settings_guide_status( $guide ) : array();
	$updated  = ltt_dive_in_block_guide_updated( $guide );
	?>
	<p class="ltt-block-guide__back">
		<a href="<?php echo esc_url( ltt_dive_in_settings_guide_url() ); ?>">
			<span aria-hidden="true">&larr;</span> <?php esc_html_e( 'All settings', 'ltt-dive-in' ); ?>
		</a>
	</p>

	<header class="ltt-block-guide__header">
		<span class="ltt-block-guide__header-icon dashicons dashicons-<?php echo esc_attr( $guide['icon'] ?? 'admin-generic' ); ?>" aria-hidden="true"></span>
		<div>
			<h1><?php echo esc_html( $guide['title'] ); ?></h1>
			<?php if ( ! empty( $guide['summary'] ) ) : ?>
				<p class="ltt-block-guide__lead"><?php echo wp_kses_post( $guide['summary'] ); ?></p>
			<?php endif; ?>
			<?php if ( $updated ) : ?>
				<p class="ltt-block-guide__updated">
					<?php
					/* translators: %s: date. */
					echo esc_html( sprintf( __( 'Last updated: %s', 'ltt-dive-in' ), $updated ) );
					?>
				</p>
			<?php endif; ?>
		</div>
	</header>

	<div class="ltt-block-guide__where ltt-block-guide__screen">
		<p>
			<strong><?php esc_html_e( 'Where to find it:', 'ltt-dive-in' ); ?></strong>
			<?php echo esc_html( $guide['menu'] ?? '' ); ?>
			<?php if ( ! $can_edit ) : ?>
				<br><?php esc_html_e( 'Only administrators can change these settings. Ask an administrator if something here needs to change.', 'ltt-dive-in' ); ?>
			<?php endif; ?>
		</p>
		<?php if ( $can_edit ) : ?>
			<a class="button button-primary" href="<?php echo esc_url( ltt_dive_in_settings_guide_screen_url( $guide ) ); ?>"><?php esc_html_e( 'Open settings', 'ltt-dive-in' ); ?></a>
		<?php endif; ?>
	</div>

	<?php if ( $status ) : ?>
		<section class="ltt-block-guide__section" aria-labelledby="ltt-settings-status">
			<h2 id="ltt-settings-status"><?php esc_html_e( 'Current status', 'ltt-dive-in' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Only administrators see this. It shows whether each item is set up, never the saved values.', 'ltt-dive-in' ); ?></p>
			<ul class="ltt-block-guide__checks">
				<?php foreach ( $status as $row ) : ?>
					<li class="is-<?php echo esc_attr( $row['state'] ); ?>">
						<span class="dashicons dashicons-<?php echo esc_attr( 'ok' === $row['state'] ? 'yes-alt' : ( 'warning' === $row['state'] ? 'warning' : 'marker' ) ); ?>" aria-hidden="true"></span>
						<strong><?php echo esc_html( $row['label'] ); ?>:</strong>
						<?php echo esc_html( $row['text'] ); ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>

	<?php
	ltt_dive_in_render_site_guide_sections(
		$guide,
		'ltt-settings',
		array( 'fields' => __( 'Settings on this screen', 'ltt-dive-in' ) )
	);
}
