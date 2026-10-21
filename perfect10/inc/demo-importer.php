<?php
/**
 * Batched, idempotent demo importer.
 *
 * The original p10_run_demo_import() in inc/demo-content.php remains the
 * content factory. This module adds:
 *
 * - a step runner so a shared host can import one small batch per request;
 * - a manifest of every post, term, attachment and menu the importer owns;
 * - an Elementor homepage built from the same dynamic Perfect 10 widgets, so
 *   the homepage is editable without duplicating sample cards in HTML;
 * - guarded reset and homepage-rebuild actions.
 *
 * Nothing here deletes content unless an administrator types the confirmation
 * phrase and the record is present in the manifest.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'P10_DEMO_STATE_OPTION', 'p10_demo_import_state' );
define( 'P10_DEMO_LOCK', 'p10_demo_import_lock' );
define( 'P10_DEMO_HOME_REVISION', '2.0.0.1' );

/**
 * Ordered import steps.
 *
 * @return array
 */
function p10_demo_steps() {
	return array(
		'locations' => __( 'Counties, cities & districts', 'perfect10' ),
		'taxonomies' => __( 'Profile types & nationalities', 'perfect10' ),
		'profiles'  => __( 'Sample profiles (demo)', 'perfect10' ),
		'packages'  => __( 'Premium packages', 'perfect10' ),
		'reviews'   => __( 'Sample reviews (clearly labelled)', 'perfect10' ),
		'pages'     => __( 'Utility pages', 'perfect10' ),
		'elementor-home' => __( 'Elementor homepage', 'perfect10' ),
		'menus'     => __( 'Menu & reading settings', 'perfect10' ),
		'finalize'  => __( 'Permalinks & manifest', 'perfect10' ),
	);
}

/**
 * Default importer state.
 *
 * @return array
 */
function p10_demo_state() {
	$state = get_option( P10_DEMO_STATE_OPTION, array() );
	$state = is_array( $state ) ? $state : array();

	return wp_parse_args(
		$state,
		array(
			'status'    => 'idle',
			'run_id'    => '',
			'started'   => '',
			'updated'   => '',
			'current'   => '',
			'completed' => array(),
			'log'       => array(),
			'errors'    => array(),
			'home_built'=> false,
		)
	);
}

/**
 * Persist state.
 *
 * @param array $state State.
 */
function p10_demo_save_state( $state ) {
	$state['updated'] = current_time( 'mysql' );
	update_option( P10_DEMO_STATE_OPTION, $state, false );
}

/**
 * Append a log line to the state and return it.
 *
 * @param array  $state State.
 * @param string $line  Line.
 * @param string $level Level: info|success|warning|error.
 * @return string
 */
function p10_demo_log( &$state, $line, $level = 'info' ) {
	$line = trim( wp_strip_all_tags( (string) $line ) );
	if ( '' === $line ) {
		return '';
	}
	$state['log'][] = array(
		'time'  => current_time( 'H:i:s' ),
		'level' => sanitize_key( $level ),
		'text'  => $line,
	);
	$state['log'] = array_slice( $state['log'], -200 );
	return $line;
}

/**
 * Start or resume a run.
 *
 * @param string $run_id Optional run identifier.
 * @return array
 */
function p10_demo_begin_run( $run_id = '' ) {
	$state = p10_demo_state();

	if ( $run_id && $state['run_id'] === $run_id ) {
		return $state;
	}

	$state['run_id']    = $run_id ? $run_id : wp_generate_password( 12, false, false );
	// A new run supersedes a stale lock left by an interrupted request.
	delete_transient( P10_DEMO_LOCK );
	$state['status']    = 'running';
	$state['started']   = $state['started'] ? $state['started'] : current_time( 'mysql' );
	$state['completed'] = array();
	$state['errors']    = array();
	p10_demo_save_state( $state );
	return $state;
}

/**
 * Fetch slug => ID for the standard pages, without assuming they exist.
 *
 * @return array
 */
function p10_demo_page_map() {
	$slugs = array(
		'home'                         => 'home',
		'all-independent-escorts'      => 'all-independent-escorts',
		'booking'                      => 'booking',
		'register-independent-profile' => 'register-independent-profile',
		'premium-packages'             => 'premium-packages',
		'all-cities'                   => 'all-cities',
	);

	$map = array();
	foreach ( $slugs as $key => $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			$map[ $key ] = (int) $page->ID;
		}
	}
	return $map;
}

/**
 * Locate a page created by the demo importer.
 *
 * @param string $slug Page slug.
 * @return int
 */
function p10_demo_page_id( $slug ) {
	$page = get_page_by_path( $slug );
	return $page ? (int) $page->ID : 0;
}

/**
 * Run one import step.
 *
 * @param string $step Step slug.
 * @param array  $args Optional args (run_id, force).
 * @return array|WP_Error
 */
function p10_demo_run_step( $step, $args = array() ) {
	$steps = p10_demo_steps();
	$step  = sanitize_key( $step );

	if ( ! isset( $steps[ $step ] ) ) {
		return new WP_Error( 'p10_demo_unknown_step', __( 'Unknown import step.', 'perfect10' ) );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return new WP_Error( 'p10_demo_forbidden', __( 'You do not have permission to import demo content.', 'perfect10' ) );
	}

	$state = p10_demo_begin_run( isset( $args['run_id'] ) ? sanitize_text_field( $args['run_id'] ) : '' );

	if ( 'running' !== $state['status'] ) {
		$state['status'] = 'running';
	}

	$lock = get_transient( P10_DEMO_LOCK );
	if ( $lock && $lock !== $state['run_id'] ) {
		return new WP_Error( 'p10_demo_busy', __( 'Another importer request is still running. Wait a moment and retry this step.', 'perfect10' ) );
	}
	set_transient( P10_DEMO_LOCK, $state['run_id'], 5 * MINUTE_IN_SECONDS );

	$state['current'] = $step;
	p10_demo_save_state( $state );

	$lines = array();

	try {
		switch ( $step ) {
			case 'locations':
				p10_raise_time_limit();
				$counties = p10_import_counties( $lines );
				p10_import_cities( $counties, $lines );
				$lines[] = sprintf(
					/* translators: 1: county count, 2: city count. */
					__( 'Location tree: %1$d counties and %2$d cities/districts.', 'perfect10' ),
					count( $counties ),
					count( p10_get_cities() )
				);
				break;

			case 'taxonomies':
				p10_import_categories( $lines );
				p10_import_nationalities( $lines );
				break;

			case 'profiles':
				$counties = array();
				foreach ( p10_get_counties() as $county ) {
					$counties[ $county->slug ] = (int) $county->term_id;
				}
				$types = array();
				foreach ( p10_get_categories() as $type ) {
					$types[ $type->slug ] = (int) $type->term_id;
				}
				p10_import_profiles( $counties, $types, $lines );
				break;

			case 'packages':
				p10_import_packages( $lines );
				break;

			case 'reviews':
				p10_import_reviews( $lines );
				break;

			case 'pages':
				$pages   = p10_import_pages( $lines );
				$lines[] = sprintf(
					/* translators: %d: page count. */
					__( 'Utility pages ready: %d.', 'perfect10' ),
					count( $pages )
				);
				break;

			case 'elementor-home':
				$result = p10_build_elementor_home(
					array(
						'force' => ! empty( $args['force'] ),
					)
				);
				if ( is_wp_error( $result ) ) {
					if ( 'p10_demo_elementor_inactive' === $result->get_error_code() ) {
						$state['home_built'] = false;
						p10_demo_log( $state, __( 'Elementor is not active — the homepage keeps using the theme’s PHP layout. Activate Elementor, then build the homepage from the Elementor tab.', 'perfect10' ), 'warning' );
						break;
					}
					throw new Exception( $result->get_error_message() );
				}
				$state['home_built'] = true;
				$lines[] = $result;
				break;

			case 'menus':
				$pages = p10_demo_page_map();
				p10_import_menus( $pages );
				p10_import_home_settings( $pages, $lines );
				$lines[] = __( 'Primary menu assigned (existing menu items were left untouched).', 'perfect10' );
				break;

			case 'finalize':
				p10_add_nested_rules();
				flush_rewrite_rules();

				$manifest = p10_demo_manifest();
				$manifest['version']      = P10_VERSION;
				$manifest['completed_at'] = current_time( 'mysql' );
				$manifest['steps']        = array_keys( $steps );
				$manifest['home_built']   = ! empty( $state['home_built'] ) || (bool) get_post_meta( p10_demo_page_id( 'home' ), '_p10_elementor_home', true );
				update_option( P10_DEMO_MANIFEST_OPTION, $manifest, false );

				$lines[] = __( 'Permalinks flushed and manifest saved.', 'perfect10' );
				break;
		}
	} catch ( Exception $e ) {
		delete_transient( P10_DEMO_LOCK );
		$state['status']   = 'error';
		$state['errors'][] = $e->getMessage();
		p10_demo_log( $state, $e->getMessage(), 'error' );
		p10_demo_save_state( $state );
		return new WP_Error( 'p10_demo_step_failed', $e->getMessage(), array( 'step' => $step ) );
	}

	foreach ( $lines as $line ) {
		p10_demo_log( $state, $line, 'success' );
	}

	if ( ! in_array( $step, (array) $state['completed'], true ) ) {
		$state['completed'][] = $step;
	}

	$keys     = array_keys( $steps );
	$position = array_search( $step, $keys, true );
	$next     = isset( $keys[ $position + 1 ] ) ? $keys[ $position + 1 ] : '';

	if ( 'finalize' === $step ) {
		$state['status']  = 'complete';
		$state['current'] = '';
	} else {
		$state['status'] = 'running';
	}

	if ( 'finalize' === $step || 'error' === $state['status'] ) {
		delete_transient( P10_DEMO_LOCK );
	} else {
		set_transient( P10_DEMO_LOCK, $state['run_id'], 5 * MINUTE_IN_SECONDS );
	}

	p10_demo_save_state( $state );

	return array(
		'step'        => $step,
		'label'       => $steps[ $step ],
		'done'        => 'finalize' === $step,
		'next'        => $next,
		'completed'   => $state['completed'],
		'progress'    => (int) round( ( count( $state['completed'] ) / count( $keys ) ) * 100 ),
		'log'         => $state['log'],
		'home_built'  => ! empty( $state['home_built'] ),
		'status'      => $state['status'],
	);
}

/**
 * Run every step synchronously.
 *
 * Used by WP-CLI-style maintenance and by the test harness. The admin UI uses
 * one request per step instead.
 *
 * @param array $args Args.
 * @return array
 */
function p10_demo_run_all( $args = array() ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return new WP_Error( 'p10_demo_forbidden', __( 'You do not have permission to import demo content.', 'perfect10' ) );
	}

	$run_id = wp_generate_password( 12, false, false );
	$out    = array( 'run_id' => $run_id, 'steps' => array() );

	foreach ( array_keys( p10_demo_steps() ) as $step ) {
		$result = p10_demo_run_step(
			$step,
			array_merge(
				$args,
				array( 'run_id' => $run_id )
			)
		);
		if ( is_wp_error( $result ) ) {
			$out['steps'][ $step ] = $result->get_error_message();
			break;
		}
		$out['steps'][ $step ] = $result;
	}

	return $out;
}

/* -------------------------------------------------------------------------
 * Elementor homepage
 * ---------------------------------------------------------------------- */

/**
 * Build one Elementor element.
 *
 * @param string $type      Element type.
 * @param array  $settings  Settings.
 * @param array  $elements  Children.
 * @param string $widget    Widget type.
 * @return array
 */
function p10_demo_el_node( $type, $settings = array(), $elements = array(), $widget = '' ) {
	$node = array(
		'id'       => substr( md5( uniqid( '', true ) . wp_rand() ), 0, 7 ),
		'elType'   => $type,
		'settings' => $settings,
		'elements' => $elements,
	);
	if ( 'widget' === $type ) {
		$node['widgetType'] = $widget;
		$node['elements']   = array();
	}
	return $node;
}

/**
 * Full-width Elementor section containing one Perfect 10 widget.
 *
 * @param string $widget_type Widget type.
 * @param array  $settings    Widget settings.
 * @param array  $section_settings Extra section settings.
 * @return array
 */
function p10_demo_el_section( $widget_type, $settings = array(), $section_settings = array() ) {
	$widget = p10_demo_el_node( 'widget', $settings, array(), $widget_type );

	$column = p10_demo_el_node(
		'column',
		array(
			'_column_size'  => 100,
			'_inline_size'  => null,
			'space_between_widgets' => 0,
		),
		array( $widget )
	);

	$section = array(
		'id'       => substr( md5( uniqid( '', true ) . wp_rand() ), 0, 7 ),
		'elType'   => 'section',
		'settings' => array_merge(
			array(
				'layout'              => 'full_width',
				'content_width'       => 'full_width',
				'stretch_section'     => 'section-stretched',
				'gap'                 => 'no',
				'padding'             => array(
					'unit'     => 'px',
					'top'      => '0',
					'right'    => '0',
					'bottom'   => '0',
					'left'     => '0',
					'isLinked' => false,
				),
				'css_classes'         => 'p10-el-section',
			),
			$section_settings
		),
		'elements' => array( $column ),
		'isInner'  => false,
	);

	return $section;
}

/**
 * The homepage element tree.
 *
 * Order mirrors front-page.php: hero, ticker, search, 01 featured, 02 premium,
 * 03 types, 04 destinations, packages, call band, about, 05 reviews,
 * advertisers.
 *
 * @return array
 */
function p10_demo_home_tree() {
	$tree = array();

	$tree[] = p10_demo_el_section( 'p10-hero', array( 'content_source' => 'theme' ) );
	$tree[] = p10_demo_el_section( 'p10-marquee', array( 'content_source' => 'theme' ) );
	$tree[] = p10_demo_el_section( 'p10-search', array( 'content_source' => 'theme', 'anchor' => 'p10-next' ) );
	$tree[] = p10_demo_el_section( 'p10-profiles', array( 'theme_section' => 'muses' ) );
	$tree[] = p10_demo_el_section( 'p10-profiles', array( 'theme_section' => 'premium' ) );
	$tree[] = p10_demo_el_section( 'p10-types', array( 'source' => 'theme', 'heading_source' => 'theme' ) );
	$tree[] = p10_demo_el_section( 'p10-cities', array( 'source' => 'theme', 'heading_source' => 'theme' ) );
	$tree[] = p10_demo_el_section(
		'p10-packages',
		array(
			'heading_show'        => 'yes',
			'heading_source'      => 'custom',
			'heading_kicker'      => __( 'Exclusive Placement', 'perfect10' ),
			'heading_title'       => __( 'Premium', 'perfect10' ),
			'heading_accent'      => __( 'Packages', 'perfect10' ),
			'heading_action'      => __( 'Compare placement', 'perfect10' ),
			'heading_action_url'  => array( 'url' => p10_utility_url( 'packages' ) ),
			'limit'               => 3,
		)
	);
	$tree[] = p10_demo_el_section( 'p10-callband', array( 'content_source' => 'theme' ) );
	$tree[] = p10_demo_el_section( 'p10-about', array( 'content_source' => 'theme' ) );
	$tree[] = p10_demo_el_section( 'p10-reviews', array( 'limit' => 3, 'featured_only' => 'yes', 'heading_source' => 'theme' ) );
	$tree[] = p10_demo_el_section( 'p10-advertisers', array( 'content_source' => 'theme' ) );

	return $tree;
}

/**
 * Validate the generated Elementor tree before it is saved.
 *
 * @param array $tree Element tree.
 * @param array $errors Errors, by reference.
 * @param array $ids   Seen IDs, by reference.
 * @return bool
 */
function p10_demo_validate_tree( $tree, &$errors = array(), &$ids = array() ) {
	$allowed = array( 'section', 'column', 'container', 'widget' );

	foreach ( (array) $tree as $node ) {
		if ( ! is_array( $node ) || empty( $node['elType'] ) ) {
			$errors[] = __( 'Malformed Elementor element.', 'perfect10' );
			continue;
		}
		if ( ! in_array( $node['elType'], $allowed, true ) ) {
			$errors[] = sprintf( __( 'Unknown Elementor element type: %s', 'perfect10' ), $node['elType'] );
		}
		$id = isset( $node['id'] ) ? (string) $node['id'] : '';
		if ( ! preg_match( '/^[a-z0-9]{7}$/', $id ) ) {
			$errors[] = sprintf( __( 'Invalid Elementor element ID: %s', 'perfect10' ), $id );
		} elseif ( isset( $ids[ $id ] ) ) {
			$errors[] = sprintf( __( 'Duplicate Elementor element ID: %s', 'perfect10' ), $id );
		} else {
			$ids[ $id ] = true;
		}
		if ( 'widget' === $node['elType'] ) {
			if ( empty( $node['widgetType'] ) ) {
				$errors[] = __( 'A widget is missing its widgetType.', 'perfect10' );
			} elseif ( class_exists( '\Elementor\Plugin' ) ) {
				$manager = \Elementor\Plugin::$instance->widgets_manager;
				if ( ! $manager->get_widget_types( $node['widgetType'] ) ) {
					$errors[] = sprintf( __( 'Elementor widget not registered: %s', 'perfect10' ), $node['widgetType'] );
				}
			}
		}
		if ( ! empty( $node['elements'] ) ) {
			p10_demo_validate_tree( $node['elements'], $errors, $ids );
		}
	}

	return empty( $errors );
}

/**
 * Build (or rebuild) the Elementor homepage document.
 *
 * @param array $args force, mark_owned.
 * @return string|WP_Error Success message or error.
 */
function p10_build_elementor_home( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'force'      => false,
			'mark_owned' => true,
		)
	);

	if ( ! p10_elementor_active() ) {
		return new WP_Error( 'p10_demo_elementor_inactive', __( 'Elementor is not active. The homepage is using the theme’s built-in PHP layout instead.', 'perfect10' ) );
	}

	$page_id = p10_demo_page_id( 'home' );
	if ( ! $page_id ) {
		return new WP_Error( 'p10_demo_home_missing', __( 'The Home page does not exist yet. Run the “Utility pages” step first.', 'perfect10' ) );
	}

	$built_revision = (string) get_post_meta( $page_id, '_p10_elementor_home_rev', true );
	$has_elementor  = p10_is_built_with_elementor( $page_id );

	if ( $has_elementor && $built_revision === P10_DEMO_HOME_REVISION && ! $args['force'] ) {
		return __( 'The Elementor homepage is already up to date.', 'perfect10' );
	}

	if ( $has_elementor && ! p10_is_demo_owned( $page_id ) && ! $args['force'] ) {
		return new WP_Error( 'p10_demo_home_not_owned', __( 'This Home page was built or edited outside the demo importer. It was left untouched. Use “Rebuild homepage” only after confirming that replacing its Elementor content is acceptable.', 'perfect10' ) );
	}

	$content       = (string) get_post_field( 'post_content', $page_id );
	$looks_seeded  = ! p10_is_demo_owned( $page_id ) && '' === trim( $content ) && ! $has_elementor;

	$tree   = p10_demo_home_tree();
	$errors = array();
	$ids    = array();
	if ( ! p10_demo_validate_tree( $tree, $errors, $ids ) ) {
		return new WP_Error( 'p10_demo_invalid_tree', implode( ' ', array_unique( $errors ) ) );
	}

	$json = wp_json_encode( $tree, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	if ( ! $json ) {
		return new WP_Error( 'p10_demo_encode', __( 'Could not encode the Elementor document.', 'perfect10' ) );
	}

	update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $page_id, '_elementor_template_type', 'wp-page' );
	update_post_meta( $page_id, '_elementor_version', p10_elementor_version() );
	update_post_meta( $page_id, '_elementor_data', wp_slash( $json ) );
	update_post_meta( $page_id, '_p10_elementor_home_rev', P10_DEMO_HOME_REVISION );
	update_post_meta( $page_id, '_p10_elementor_home_built', current_time( 'mysql' ) );

	if ( $args['mark_owned'] && ( $looks_seeded || p10_is_demo_owned( $page_id ) ) ) {
		p10_demo_mark_post( $page_id, 'page' );
	}

	// Force Elementor to regenerate its CSS for this document.
	delete_post_meta( $page_id, '_elementor_css' );
	delete_post_meta( $page_id, '_elementor_page_assets' );

	// Point WordPress at the demo homepage only when no other homepage is set.
	$show_on    = get_option( 'show_on_front' );
	$front_id   = (int) get_option( 'page_on_front' );
	if ( 'page' !== $show_on || ! $front_id ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $page_id );
	} elseif ( $front_id !== $page_id && ! p10_is_demo_owned( $front_id ) ) {
		return __( 'The Elementor homepage was built and saved, but WordPress already uses a different front page. Change Settings → Reading if you want this page at the site root.', 'perfect10' );
	}

	if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	p10_sync_elementor_kit();

	$manifest = p10_demo_manifest();
	if ( ! in_array( $page_id, (array) $manifest['posts'], true ) ) {
		$manifest['posts'][] = $page_id;
	}
	$manifest['home_id']   = $page_id;
	$manifest['home_rev']  = P10_DEMO_HOME_REVISION;
	$manifest['home_built'] = true;
	update_option( P10_DEMO_MANIFEST_OPTION, $manifest, false );

	$state = p10_demo_state();
	$state['home_built'] = true;
	p10_demo_save_state( $state );

	$edit = p10_elementor_edit_url( $page_id );
	return sprintf(
		/* translators: %s: Elementor edit URL. */
		__( 'Elementor homepage built with %1$d native widgets. Edit it here: %2$s', 'perfect10' ),
		count( $tree ),
		$edit
	);
}

/* -------------------------------------------------------------------------
 * AJAX
 * ---------------------------------------------------------------------- */

/**
 * Verify an admin AJAX request.
 *
 * @return bool
 */
function p10_demo_verify_ajax() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'You do not have permission to do that.', 'perfect10' ) ), 403 );
	}
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'p10_admin' ) ) {
		wp_send_json_error( array( 'message' => __( 'The security token expired. Reload the page and try again.', 'perfect10' ) ), 403 );
	}
	return true;
}

/**
 * AJAX: run one step.
 */
function p10_demo_ajax_step() {
	p10_demo_verify_ajax();

	$step   = isset( $_POST['step'] ) ? sanitize_key( $_POST['step'] ) : '';
	$run_id = isset( $_POST['run_id'] ) ? sanitize_text_field( wp_unslash( $_POST['run_id'] ) ) : '';
	$force  = ! empty( $_POST['force'] );

	$result = p10_demo_run_step( $step, array( 'run_id' => $run_id, 'force' => $force ) );

	if ( is_wp_error( $result ) ) {
		$state = p10_demo_state();
		wp_send_json_error(
			array(
				'message' => $result->get_error_message(),
				'state'   => $state,
			),
			500
		);
	}

	wp_send_json_success(
		array(
			'result' => $result,
			'state'  => p10_demo_state(),
			'status' => p10_demo_status(),
		)
	);
}
add_action( 'wp_ajax_p10_demo_step', 'p10_demo_ajax_step' );

/**
 * AJAX: reset demo-owned content.
 */
function p10_demo_ajax_reset() {
	p10_demo_verify_ajax();

	$confirm = isset( $_POST['confirm'] ) ? strtoupper( trim( sanitize_text_field( wp_unslash( $_POST['confirm'] ) ) ) ) : '';
	if ( 'RESET DEMO CONTENT' !== $confirm ) {
		wp_send_json_error( array( 'message' => __( 'Type RESET DEMO CONTENT to confirm. No content was removed.', 'perfect10' ) ), 400 );
	}

	$counts = p10_demo_reset();
	delete_option( P10_DEMO_STATE_OPTION );
	delete_transient( P10_DEMO_LOCK );
	flush_rewrite_rules();

	wp_send_json_success(
		array(
			'message' => sprintf(
				/* translators: 1: posts, 2: terms, 3: attachments, 4: menus. */
				__( 'Removed %1$d posts/pages, %2$d terms, %3$d attachments and %4$d menus. User-created content was not touched.', 'perfect10' ),
				$counts['posts'],
				$counts['terms'],
				$counts['attachments'],
				$counts['menus']
			),
			'counts'  => $counts,
		)
	);
}
add_action( 'wp_ajax_p10_demo_reset', 'p10_demo_ajax_reset' );

/**
 * AJAX: importer status.
 */
function p10_demo_ajax_status() {
	p10_demo_verify_ajax();
	wp_send_json_success( p10_demo_status() );
}
add_action( 'wp_ajax_p10_demo_status', 'p10_demo_ajax_status' );

/**
 * AJAX: build/rebuild the Elementor homepage.
 */
function p10_demo_ajax_build_home() {
	p10_demo_verify_ajax();

	$result = p10_build_elementor_home(
		array(
			'force' => ! empty( $_POST['force'] ),
		)
	);

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
	}

	wp_send_json_success(
		array(
			'message' => $result,
			'status'  => p10_demo_status(),
		)
	);
}
add_action( 'wp_ajax_p10_build_home', 'p10_demo_ajax_build_home' );

/**
 * Importer status for the admin UI.
 *
 * @return array
 */
function p10_demo_status() {
	$state    = p10_demo_state();
	$steps    = p10_demo_steps();
	$manifest = p10_demo_manifest();
	$home_id  = p10_demo_page_id( 'home' );

	return array(
		'state'            => $state,
		'steps'            => $steps,
		'total_steps'      => count( $steps ),
		'completed_steps'  => count( (array) $state['completed'] ),
		'progress'         => (int) round( ( count( (array) $state['completed'] ) / max( 1, count( $steps ) ) ) * 100 ),
		'elementor_active' => p10_elementor_active(),
		'elementor_version'=> p10_elementor_version(),
		'home_id'          => $home_id,
		'home_edit_url'    => $home_id ? p10_elementor_edit_url( $home_id ) : '',
		'home_view_url'    => $home_id ? get_permalink( $home_id ) : '',
		'home_built'       => $home_id ? p10_is_built_with_elementor( $home_id ) : false,
		'home_owned'       => $home_id ? p10_is_demo_owned( $home_id ) : false,
		'manifest'         => array(
			'posts'       => count( (array) $manifest['posts'] ),
			'terms'       => count( (array) $manifest['terms'] ),
			'attachments' => count( (array) $manifest['attachments'] ),
			'menus'       => count( (array) $manifest['menus'] ),
			'version'     => $manifest['version'],
			'completed_at'=> isset( $manifest['completed_at'] ) ? $manifest['completed_at'] : '',
		),
	);
}
