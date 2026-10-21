<?php
/**
 * Template Name: P10 Full Width (header & footer)
 * Template Post Type: page, p10_profile, p10_review, p10_package
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; }
// An assigned template must not blank the original page when Elementor is absent.
if ( ! p10_elementor_document() ) {
	$type     = get_post_type( get_queried_object_id() );
	$fallback = is_front_page() ? 'front-page.php' : ( 'page' === $type ? 'page.php' : 'single-' . $type . '.php' );
	$path     = locate_template( array( $fallback, 'single.php', 'index.php' ) );
	if ( $path ) {
		include $path;
		return; }
}
get_header();
while ( have_posts() ) {
	the_post();
	echo '<article id="post-' . esc_attr( get_the_ID() ) . '" class="p10-editor-content">';
	the_content();
	wp_link_pages();
	echo '</article>';
}
get_footer();
