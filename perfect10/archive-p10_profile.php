<?php
/**
 * Profile archive — the flat directory listing.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

get_template_part( 'template-parts/page-head', null, array(
	'kicker' => __( 'The Directory', 'perfect10' ),
	'title'  => p10_option( 'directory_title', __( 'Independent Companions', 'perfect10' ) ),
	'blurb'  => p10_option( 'directory_blurb', '' ),
) );
?>

<section class="p10-section p10-section--tight">
	<div class="p10-shell">
		<?php get_template_part( 'template-parts/directory' ); ?>
	</div>
</section>

<?php
get_template_part( 'template-parts/callband' );
get_footer();
