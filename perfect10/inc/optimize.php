<?php
/**
 * Conservative front-end performance work.
 *
 * Nothing here changes content, routes or the visual design. Every optimisation
 * can be disabled with a filter so a site owner can opt out from a child theme.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Preload the hero image used as the front page LCP.
 *
 * Only the image that is actually visible on a given viewport is preloaded:
 * the mobile fallback below 768px, the poster above it. A video hero never
 * preloads the video file itself — it is attached by main.js after the
 * reduced-motion and viewport checks.
 */
function p10_preload_hero_image() {
	if ( ! is_front_page() || apply_filters( 'p10_disable_hero_preload', false ) ) {
		return;
	}

	$bg       = p10_hero_background_settings();
	$desktop  = ! empty( $bg['poster'] ) ? $bg['poster'] : (string) p10_hero_image();
	$mobile   = ! empty( $bg['mobile_image'] ) ? $bg['mobile_image'] : '';
	$is_video = 'video' === ( $bg['type'] ?? 'image' );

	if ( $is_video && $mobile ) {
		// The desktop poster is not shown on phones; do not download it there.
		printf(
			'<link rel="preload" as="image" href="%s" media="(min-width: 768px)" fetchpriority="high">' . "\n",
			esc_url( $desktop )
		);
		printf(
			'<link rel="preload" as="image" href="%s" media="(max-width: 767px)" fetchpriority="high">' . "\n",
			esc_url( $mobile )
		);
		return;
	}

	if ( ! $desktop ) {
		return;
	}

	printf(
		'<link rel="preload" as="image" href="%s" fetchpriority="high">' . "\n",
		esc_url( $desktop )
	);
}
add_action( 'wp_head', 'p10_preload_hero_image', 2 );

/**
 * Remove the WordPress generator tag.
 *
 * This is a small information-disclosure and payload win; it has no visual or
 * editorial effect.
 */
function p10_remove_generator() {
	remove_action( 'wp_head', 'wp_generator' );
}
add_action( 'init', 'p10_remove_generator' );

/**
 * Disable the WordPress emoji detector and its extra scripts.
 *
 * The theme ships its own iconography and does not rely on emoji replacement.
 */
function p10_disable_emoji_assets() {
	if ( apply_filters( 'p10_disable_emoji_assets', true ) ) {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	}
}
add_action( 'init', 'p10_disable_emoji_assets' );
