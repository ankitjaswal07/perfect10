<?php
/**
 * Nationality archive.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


get_header();

get_template_part( 'template-parts/page-head', null, array( 'kicker' => __( 'Nationality', 'perfect10' ) ) );
?>

<section class="p10-section p10-section--tight">
	<div class="p10-shell">
		<?php get_template_part( 'template-parts/directory' ); ?>
	</div>
</section>

<?php
get_template_part( 'template-parts/callband' );
get_footer();
