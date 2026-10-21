<?php
/**
 * Template Name: P10 — Directory
 * Template Post Type: page
 *
 * Full directory with the filter sidebar. Uses p10_directory_query() so the
 * page works exactly like the profile archive.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$p10_query = p10_directory_query();

while ( have_posts() ) :
	the_post();

	get_template_part( 'template-parts/page-head', null, array(
		'kicker' => __( 'The Directory', 'perfect10' ),
		'title'  => get_the_title(),
		'blurb'  => has_excerpt() ? get_the_excerpt() : p10_option( 'directory_blurb', '' ),
	) );
	?>
	<section class="p10-section p10-section--tight">
		<div class="p10-shell">
			<?php get_template_part( 'template-parts/directory', null, array( 'query' => $p10_query ) ); ?>
		</div>
	</section>

	<?php if ( trim( (string) get_the_content() ) ) : ?>
		<section class="p10-section p10-section--tight">
			<div class="p10-shell">
				<div class="p10-content" style="max-width:78ch"><?php the_content(); ?></div>
			</div>
		</section>
	<?php endif; ?>

	<?php
endwhile;

wp_reset_postdata();

get_template_part( 'template-parts/callband' );
get_footer();
