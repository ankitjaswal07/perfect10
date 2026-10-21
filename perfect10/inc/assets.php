<?php
/**
 * Front-end and admin assets.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Fonts families used by the theme.
 *
 * @return array
 */
function p10_font_families() {
	$available = array(
		'Playfair Display'   => 'Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700',
		'Cormorant Garamond' => 'Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600',
		'Bodoni Moda'        => 'Bodoni+Moda:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,600',
		'Libre Baskerville'  => 'Libre+Baskerville:ital,wght@0,400;0,700;1,400',
		'Inter'              => 'Inter:wght@300;400;500;600;700',
		'Jost'               => 'Jost:wght@300;400;500;600',
		'Manrope'            => 'Manrope:wght@300;400;500;600;700',
		'Source Sans 3'      => 'Source+Sans+3:wght@300;400;500;600;700',
	);

	$display  = (string) p10_option( 'font_display', 'Playfair Display' );
	$body     = (string) p10_option( 'font_body', 'Inter' );
	$families = array();

	foreach ( array( $display, 'Jost', $body ) as $family ) {
		if ( isset( $available[ $family ] ) && ! in_array( $available[ $family ], $families, true ) ) {
			$families[] = $available[ $family ];
		}
	}

	return apply_filters( 'p10_font_families', array(
		'family'  => $families,
		'display' => 'swap',
	) );
}

/**
 * Google Fonts URL.
 *
 * @return string
 */
function p10_fonts_url() {
	$families = p10_font_families();
	if ( empty( $families['family'] ) ) {
		return '';
	}
	$query = array( 'display' => isset( $families['display'] ) ? $families['display'] : 'swap' );
	return add_query_arg(
		array_merge( $query, array( 'family' => implode( '&family=', $families['family'] ) ) ),
		'https://fonts.googleapis.com/css2'
	);
}

/**
 * Preconnect to the font origins.
 */
function p10_font_preconnect() {
	if ( apply_filters( 'p10_disable_google_fonts', false ) ) {
		return;
	}
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}
add_action( 'wp_head', 'p10_font_preconnect', 1 );

/**
 * Enqueue front-end assets.
 */
function p10_enqueue_assets() {
	if ( ! apply_filters( 'p10_disable_google_fonts', false ) ) {
		$fonts_url = p10_fonts_url();
		if ( $fonts_url ) {
			wp_enqueue_style( 'p10-fonts', $fonts_url, array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		}
	}

	wp_enqueue_style( 'perfect10', get_stylesheet_uri(), array(), P10_VERSION );
	wp_enqueue_style( 'p10-main', P10_URI . 'assets/css/main.css', array( 'perfect10' ), P10_VERSION );
	wp_enqueue_style( 'p10-widgets', P10_URI . 'assets/css/widgets.css', array( 'p10-main' ), P10_VERSION );

	wp_enqueue_script( 'p10-main', P10_URI . 'assets/js/main.js', array(), P10_VERSION, true );

	wp_localize_script( 'p10-main', 'p10Config', p10_js_config() );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'p10_enqueue_assets' );

/**
 * Data handed to the front-end script.
 *
 * @return array
 */
function p10_js_config() {
	return apply_filters( 'p10_js_config', array(
		'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
		'restUrl'      => esc_url_raw( rest_url( 'perfect10/v1' ) ),
		'nonce'        => wp_create_nonce( 'p10_public' ),
		'restNonce'    => wp_create_nonce( 'wp_rest' ),
		'homeUrl'      => home_url( '/' ),
		'directoryUrl' => p10_directory_url(),
		'phoneHref'    => p10_phone_href(),
		'citiesByCounty' => p10_city_map_for_js(),
		'i18n'         => array(
			'loading'    => __( 'Loading…', 'perfect10' ),
			'allCities'  => __( 'All Cities', 'perfect10' ),
			'allCounties' => __( 'All Counties', 'perfect10' ),
			'noResults'  => __( 'No profiles match those filters.', 'perfect10' ),
			'error'      => __( 'Something went wrong. Please try again.', 'perfect10' ),
			'copied'     => __( 'Copied', 'perfect10' ),
		),
		'ageGate'      => (bool) p10_option( 'age_gate_enabled', 1 ),
		'blurUntilEntered' => (bool) p10_option( 'blur_until_entered', 0 ),
		'reducedMotion' => (bool) p10_option( 'reduced_motion', 0 ),
	) );
}

/**
 * Compact county → city map for the front end.
 *
 * @return array
 */
function p10_city_map_for_js() {
	$map = array();
	foreach ( p10_get_cities_by_county() as $county_slug => $group ) {
		$map[ $county_slug ] = array(
			'label'  => $group['label'],
			'cities' => array(),
		);
		foreach ( $group['cities'] as $city ) {
			$map[ $county_slug ]['cities'][] = array(
				'slug' => $city->slug,
				'name' => $city->name,
			);
		}
	}
	return $map;
}

/**
 * Inline the critical above-the-fold CSS so the hero paints immediately.
 */
function p10_critical_css() {
	$file = P10_DIR . 'assets/css/critical.css';
	if ( ! is_readable( $file ) ) {
		return;
	}
	$css = wp_strip_all_tags( (string) file_get_contents( $file ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	echo '<style id="p10-critical">' . $css . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- stripped CSS from a theme-owned file.
}
add_action( 'wp_head', 'p10_critical_css', 21 );

/**
 * Data for the theme's admin screens.
 *
 * @return array
 */
function p10_admin_js_config() {
	$status = function_exists( 'p10_demo_status' ) ? p10_demo_status() : array();
	$home_id = isset( $status['home_id'] ) ? (int) $status['home_id'] : 0;

	return array(
		'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
		'nonce'        => wp_create_nonce( 'p10_admin' ),
		'elementor'    => p10_elementor_active(),
		'steps'        => function_exists( 'p10_demo_steps' ) ? p10_demo_steps() : array(),
		'state'        => isset( $status['state'] ) ? $status['state'] : array(),
		'homeId'       => $home_id,
		'homeEditUrl'  => $home_id ? p10_elementor_edit_url( $home_id ) : '',
		'homeViewUrl'  => $home_id ? get_permalink( $home_id ) : '',
		'resetPhrase'  => 'RESET DEMO CONTENT',
		'i18n'         => array(
			'working'      => __( 'Working…', 'perfect10' ),
			'done'         => __( 'Import complete. The Elementor homepage is ready to edit.', 'perfect10' ),
			'error'        => __( 'Something went wrong. Check the log and try again.', 'perfect10' ),
			'confirm'      => __( 'This removes only content created by the Perfect 10 demo importer. Continue?', 'perfect10' ),
			'resetPrompt'  => __( 'Type RESET DEMO CONTENT to enable the reset button.', 'perfect10' ),
			'resetDone'    => __( 'Demo content removed. Reloading…', 'perfect10' ),
			'stepFailed'   => __( 'Step failed:', 'perfect10' ),
			'homeWorking'  => __( 'Building the Elementor document…', 'perfect10' ),
			'homeBuilt'    => __( 'Homepage built. Opening the editor link is now possible.', 'perfect10' ),
			'homeFailed'   => __( 'The homepage could not be built:', 'perfect10' ),
		),
	);
}

/**
 * Enqueue admin assets on the screens that need them.
 *
 * @param string $hook Current admin page.
 */
function p10_admin_assets( $hook ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	$needs = in_array( $hook, array( 'post.php', 'post-new.php' ), true )
		|| ( $screen && in_array( $screen->taxonomy, array( 'p10_county', 'p10_city', 'p10_category', 'p10_nationality' ), true ) );

	if ( $needs ) {
		wp_enqueue_media();
		wp_enqueue_style( 'p10-admin', P10_URI . 'assets/css/admin.css', array(), P10_VERSION );
		wp_enqueue_script( 'p10-admin', P10_URI . 'assets/js/admin.js', array( 'jquery' ), P10_VERSION, true );
	}

	if ( false !== strpos( (string) $hook, 'p10-' ) ) {
		wp_enqueue_style( 'p10-admin-ui', P10_URI . 'assets/css/admin-ui.css', array(), P10_VERSION );
		wp_enqueue_script( 'p10-admin-ui', P10_URI . 'assets/js/admin-ui.js', array( 'jquery' ), P10_VERSION, true );
		wp_localize_script( 'p10-admin-ui', 'P10Admin', p10_admin_js_config() );
	}
}
add_action( 'admin_enqueue_scripts', 'p10_admin_assets' );

/**
 * Editor styling.
 */
function p10_editor_assets() {
	wp_enqueue_style( 'p10-editor', P10_URI . 'assets/css/editor.css', array(), P10_VERSION );
}
add_action( 'enqueue_block_editor_assets', 'p10_editor_assets' );

/**
 * Let SVG uploads through for administrators (used for brand marks and icons).
 *
 * @param array $mimes Allowed mime types.
 * @return array
 */
function p10_allow_svg( $mimes ) {
	if ( current_user_can( 'manage_options' ) ) {
		$mimes['svg']  = 'image/svg+xml';
		$mimes['svgz'] = 'image/svg+xml';
	}
	return $mimes;
}
add_filter( 'upload_mimes', 'p10_allow_svg' );

/**
 * Add theme-specific TinyMCE formats for the editorial typography.
 *
 * @param array $init TinyMCE settings.
 * @return array
 */
function p10_tiny_mce_styles( $init ) {
	$styles = array(
		array(
			'title'   => __( 'Gold italic accent', 'perfect10' ),
			'selector' => 'em, i, span',
			'classes' => 'p10-accent',
		),
		array(
			'title'   => __( 'Section kicker', 'perfect10' ),
			'block'   => 'p',
			'classes' => 'p10-eyebrow',
		),
		array(
			'title'   => __( 'Gold button', 'perfect10' ),
			'selector' => 'a',
			'classes' => 'p10-btn p10-btn--gold',
		),
		array(
			'title'   => __( 'Ghost button', 'perfect10' ),
			'selector' => 'a',
			'classes' => 'p10-btn p10-btn--ghost',
		),
	);

	$existing = isset( $init['style_formats'] ) ? json_decode( $init['style_formats'], true ) : array();
	$init['style_formats'] = wp_json_encode( array_merge( (array) $existing, $styles ) );

	return $init;
}
add_filter( 'tiny_mce_before_init', 'p10_tiny_mce_styles' );

/**
 * Fallback favicon so the theme looks finished before a site icon is set.
 */
function p10_favicon() {
	if ( has_site_icon() ) {
		return;
	}

	printf(
		'<link rel="icon" href="%s" type="image/svg+xml">' . "\n",
		esc_url( P10_URI . 'assets/images/favicon.svg' )
	);
}
add_action( 'wp_head', 'p10_favicon', 1 );


/**
 * Load theme styles inside the Elementor editor preview.
 */
function p10_elementor_preview_styles() {
	wp_enqueue_style( 'p10-main-preview', P10_URI . 'assets/css/main.css', array(), P10_VERSION );
	wp_enqueue_style( 'p10-widgets-preview', P10_URI . 'assets/css/widgets.css', array(), P10_VERSION );
}
add_action( 'elementor/preview/enqueue_styles', 'p10_elementor_preview_styles' );

/**
 * Load theme UI styles in the Elementor editor panel.
 */
function p10_elementor_editor_styles() {
	wp_enqueue_style( 'p10-editor-elementor', P10_URI . 'assets/css/editor.css', array(), P10_VERSION );
}
add_action( 'elementor/editor/after_enqueue_styles', 'p10_elementor_editor_styles' );
