<?php
/**
 * Perfect 10 Elementor widget library — stable include point.
 *
 * The widgets themselves live in /inc/elementor/ and are loaded by
 * inc/elementor.php on `elementor/loaded` so they are never instantiated when
 * the plugin is inactive. This file is kept as the single, documented entry
 * point for child themes and for future widget additions.
 *
 * To add a widget:
 *   1. Create inc/elementor/class-p10-elementor-example.php extending
 *      P10_Elementor_Widget.
 *   2. Add the file to p10_load_elementor_widgets() in inc/elementor.php.
 *   3. Add the class name to p10_register_elementor_widgets().
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fires after the widget library entry point has loaded.
 *
 * Elementor may not be active yet; use this hook only for registering further
 * hooks, not for calling Elementor APIs directly.
 */
do_action( 'p10_elementor_widget_library_loaded' );
