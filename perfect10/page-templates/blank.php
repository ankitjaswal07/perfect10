<?php
/**
 * Template Name: P10 — Blank Landing
 * Template Post Type: page
 *
 * Header and footer only; the page content is the whole canvas. Useful for
 * landing pages built with the block editor.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<div class="p10-content p10-shell">
			<?php
			the_content();
			wp_link_pages( array(
				'before' => '<nav class="p10-pagination">',
				'after'  => '</nav>',
			) );
			?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
