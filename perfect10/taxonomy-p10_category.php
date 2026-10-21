<?php
/**
 * Profile type archive: /browse/{type}/
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

get_template_part( 'template-parts/page-head', null, array( 'kicker' => __( 'Profile Type', 'perfect10' ) ) );
?>

<section class="p10-section p10-section--tight">
	<div class="p10-shell">
		<?php get_template_part( 'template-parts/directory' ); ?>
	</div>
</section>

<?php
$other_types = array();
foreach ( p10_get_categories() as $category ) {
	if ( $category->term_id !== get_queried_object_id() ) {
		$other_types[] = $category;
	}
}

if ( ! empty( $other_types ) ) :
	?>
	<section class="p10-section p10-section--panel p10-section--gold-line">
		<div class="p10-shell">
			<?php
			get_template_part( 'template-parts/section-head', null, array(
				'kicker' => __( 'Keep browsing', 'perfect10' ),
				'title'  => __( 'Other', 'perfect10' ),
				'accent' => __( 'Profile Types', 'perfect10' ),
			) );
			?>
			<div class="p10-grid p10-grid--types">
				<?php foreach ( array_slice( $other_types, 0, 6 ) as $i => $category ) : ?>
					<?php
					get_template_part( 'template-parts/card-type', null, array(
						'term'  => $category,
						'index' => $i + 1,
					) );
					?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php
get_template_part( 'template-parts/callband' );
get_footer();
