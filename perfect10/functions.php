<?php
/**
 * Perfect 10 theme bootstrap.
 *
 * Las10Perfect.com — design and data model from the original Perfect 10 theme.
 * Backend organisation, Elementor integration, administration and demo import
 * patterns are adapted from the Arctic Resto reference architecture, without
 * carrying over any of its business content or visual styling.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'P10_VERSION', '2.0.0' );
define( 'P10_DIR', trailingslashit( get_template_directory() ) );
define( 'P10_URI', trailingslashit( get_template_directory_uri() ) );
define( 'P10_DEMO_ORIGIN', 'perfect10-demo' );
define( 'P10_DEMO_MANIFEST_OPTION', 'p10_demo_manifest' );

/**
 * Load a theme include if it exists.
 *
 * @param string $file File name relative to /inc.
 */
function p10_require( $file ) {
	$path = P10_DIR . 'inc/' . $file;
	if ( is_readable( $path ) ) {
		require_once $path;
	}
}

/*
 * Core modules. The existing module names and load order are preserved so
 * third-party snippets that remove or replace a module with p10_loaded keep
 * working. New modules are added around them, not in place of them.
 */
p10_require( 'helpers.php' );
p10_require( 'setup.php' );
p10_require( 'post-types.php' );
p10_require( 'meta.php' );
p10_require( 'template-tags.php' );
p10_require( 'design-system.php' );
p10_require( 'hero.php' );
p10_require( 'nav.php' );
p10_require( 'customizer.php' );
p10_require( 'assets.php' );
p10_require( 'query.php' );
p10_require( 'forms.php' );
p10_require( 'enquiries.php' );
p10_require( 'age-gate.php' );
p10_require( 'seo.php' );
p10_require( 'shortcodes.php' );
p10_require( 'widgets.php' );
p10_require( 'optimize.php' );

// Elementor integration and the Perfect 10 widget library.
p10_require( 'elementor.php' );
p10_require( 'elementor-widgets.php' );

// Demo importer (extends the original demo-content factory, never replaces it).
p10_require( 'demo-content.php' );
p10_require( 'demo-importer.php' );

// Preserved authorised source-import workflow and assistant.
p10_require( 'source-import.php' );
p10_require( 'chatbot.php' );

if ( is_admin() ) {
	p10_require( 'admin.php' );
	p10_require( 'admin-ui.php' );
}

/**
 * Fire an action once every Perfect 10 module has been loaded.
 */
do_action( 'p10_loaded' );
