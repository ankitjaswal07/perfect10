<?php
/**
 * Theme setup: supports, menus, image sizes, sidebars.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'p10_setup' ) ) {
	/**
	 * Register theme support and navigation locations.
	 */
	function p10_setup() {
		load_theme_textdomain( 'perfect10', P10_DIR . 'languages' );

		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'custom-logo', array(
			'height'      => 96,
			'width'       => 320,
			'flex-height' => true,
			'flex-width'  => true,
		) );
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		) );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		// Elementor Free can use the theme's content area and native Canvas/Full
		// Width page templates without introducing a Pro dependency.
		add_theme_support( 'elementor' );
		// Elementor Pro's Theme Builder is optional; free Elementor remains the
		// baseline. The custom location bridge in inc/elementor.php is used when
		// Pro is not present.
		add_theme_support( 'header-footer-elementor' );
		add_theme_support( 'editor-styles' );
		add_theme_support( 'customize-selective-refresh-widgets' );

		add_editor_style( 'assets/css/editor.css' );

		register_nav_menus( array(
			'primary' => __( 'Primary Menu (header)', 'perfect10' ),
			'footer'  => __( 'Footer Menu', 'perfect10' ),
			'legal'   => __( 'Legal Menu (footer base)', 'perfect10' ),
		) );

		/*
		 * Image sizes used across the directory. Named sizes keep the markup
		 * predictable and let WordPress generate responsive srcsets.
		 */
		add_image_size( 'p10-hero', 2400, 1400, true );
		add_image_size( 'p10-card', 900, 1200, true );
		add_image_size( 'p10-card-wide', 1400, 800, true );
		add_image_size( 'p10-gallery', 1600, 1200, false );
		add_image_size( 'p10-thumb', 480, 640, true );
		add_image_size( 'p10-avatar', 160, 160, true );
		add_image_size( 'p10-editorial', 1600, 900, true );
		add_image_size( 'p10-og', 1200, 630, true );
	}
}
add_action( 'after_setup_theme', 'p10_setup' );

/**
 * Set the content width.
 */
function p10_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'p10_content_width', 1240 );
}
add_action( 'after_setup_theme', 'p10_content_width', 0 );

/**
 * Register sidebars.
 */
function p10_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Profile Sidebar', 'perfect10' ),
		'id'            => 'p10-profile',
		'description'   => __( 'Shown beside a single profile.', 'perfect10' ),
		'before_widget' => '<section id="%1$s" class="p10-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="p10-widget__title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Directory Sidebar', 'perfect10' ),
		'id'            => 'p10-directory',
		'description'   => __( 'Shown beside directory and taxonomy archives.', 'perfect10' ),
		'before_widget' => '<section id="%1$s" class="p10-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="p10-widget__title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Footer Column 1', 'perfect10' ),
		'id'            => 'p10-footer-1',
		'before_widget' => '<section id="%1$s" class="p10-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="p10-widget__title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Footer Column 2', 'perfect10' ),
		'id'            => 'p10-footer-2',
		'before_widget' => '<section id="%1$s" class="p10-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="p10-widget__title">',
		'after_title'   => '</h3>',
	) );
}
add_action( 'widgets_init', 'p10_widgets_init' );

/**
 * Add the theme image sizes to the media dropdown.
 *
 * @param array $sizes Existing sizes.
 * @return array
 */
function p10_image_size_names( $sizes ) {
	return array_merge( $sizes, array(
		'p10-hero'      => __( 'P10 Hero (2400×1400)', 'perfect10' ),
		'p10-card'      => __( 'P10 Profile Card (900×1200)', 'perfect10' ),
		'p10-card-wide' => __( 'P10 City Card (1400×800)', 'perfect10' ),
		'p10-gallery'   => __( 'P10 Gallery (1600×1200)', 'perfect10' ),
		'p10-thumb'     => __( 'P10 Thumbnail (480×640)', 'perfect10' ),
		'p10-editorial' => __( 'P10 Editorial (1600×900)', 'perfect10' ),
		'p10-og'        => __( 'P10 Social Share (1200×630)', 'perfect10' ),
	) );
}
add_filter( 'image_size_names_choose', 'p10_image_size_names' );

/**
 * Body classes that drive theme-wide visual state.
 *
 * @param array $classes Body classes.
 * @return array
 */
function p10_body_classes( $classes ) {
	$classes[] = 'p10-theme';
	$classes[] = p10_option( 'reduced_motion' ) ? 'p10-reduced-motion' : 'p10-motion';

	if ( is_singular( 'p10_profile' ) ) {
		$classes[] = 'p10-single-profile';
	}
	if ( p10_is_directory() ) {
		$classes[] = 'p10-directory';
	}
	if ( is_front_page() ) {
		$classes[] = 'p10-front';
	}
	if ( is_singular() && p10_is_built_with_elementor( get_queried_object_id() ) ) {
		$classes[] = 'p10-elementor-document';
	}
	return $classes;
}
add_filter( 'body_class', 'p10_body_classes' );

/**
 * Use a themed excerpt length.
 *
 * @return int
 */
function p10_excerpt_length() {
	return 26;
}
add_filter( 'excerpt_length', 'p10_excerpt_length' );

/**
 * Use a diamond as the excerpt more string to match the brand.
 *
 * @return string
 */
function p10_excerpt_more() {
	return '&nbsp;◆';
}
add_filter( 'excerpt_more', 'p10_excerpt_more' );

/**
 * Stop WordPress from wrapping iframes/emojis in ways that break the layout.
 */
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
