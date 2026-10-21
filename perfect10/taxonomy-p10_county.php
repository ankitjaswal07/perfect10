<?php
/**
 * County archive: /{county}/
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$term   = get_queried_object();
$cities = $term instanceof WP_Term ? p10_get_cities( $term->slug ) : array();

/* Group the county's cities alphabetically for an A–Z index. */
$grouped = array();
foreach ( $cities as $city ) {
	$letter            = strtoupper( mb_substr( $city->name, 0, 1 ) );
	$grouped[ $letter ][] = $city;
}
ksort( $grouped );

get_template_part( 'template-parts/page-head', null, array( 'kicker' => __( 'County', 'perfect10' ) ) );
?>

<section class="p10-section p10-section--tight">
	<div class="p10-shell">
		<?php get_template_part( 'template-parts/directory' ); ?>
	</div>
</section>

<?php if ( ! empty( $grouped ) ) : ?>
	<section class="p10-section p10-section--panel p10-section--gold-line">
		<div class="p10-shell">
			<?php
			get_template_part( 'template-parts/section-head', null, array(
				'kicker' => __( 'All Locations', 'perfect10' ),
				'title'  => __( 'Cities in', 'perfect10' ),
				'accent' => $term instanceof WP_Term ? $term->name : '',
			) );
			?>
			<div class="p10-city-index">
				<?php foreach ( $grouped as $letter => $letter_cities ) : ?>
					<div class="p10-city-index__group">
						<h3 class="p10-city-index__letter"><?php echo esc_html( $letter ); ?></h3>
						<ul class="p10-city-index__list">
							<?php foreach ( $letter_cities as $city ) : ?>
								<li>
									<a href="<?php echo esc_url( p10_term_url( $city ) ); ?>">
										<?php echo esc_html( $city->name ); ?>
										<span><?php echo esc_html( number_format_i18n( $city->count ) ); ?></span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php
get_template_part( 'template-parts/callband' );
get_footer();
