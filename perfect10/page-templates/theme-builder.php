<?php
/** Internal conditional Theme Builder bridge; not a selectable page template. */
if ( ! defined( 'ABSPATH' ) ) {
	exit; }
$matched = false;
$markup  = '';
// Elementor Pro normally implements Search as archive, and 404 as single.
// Try optional dedicated locations first, then the native conditional location.
foreach ( $GLOBALS['p10_elementor_locations'] as $location ) {
	ob_start();
	$matched = p10_elementor_location( $location );
	$buffer  = ob_get_clean();
	if ( $matched ) {
		$markup = $buffer;
		break; }
}
if ( $matched ) {
	get_header();
	echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor renderer output.
	get_footer();
} elseif ( p10_elementor_document() && ( ! is_front_page() || 'page-templates/elementor-full-width.php' === get_page_template_slug() ) ) {
	include P10_DIR . 'page-templates/elementor-full-width.php';
} else {
	include $GLOBALS['p10_elementor_fallback'];
}
