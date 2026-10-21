<?php
/**
 * City archive: /{county}/{city}/
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$term      = get_queried_object();
$county    = $term instanceof WP_Term ? get_term_by( 'slug', p10_city_county_slug( $term ), 'p10_county' ) : null;
$siblings  = $term instanceof WP_Term ? p10_get_cities( $term->parent ? '' : ( $county ? $county->slug : '' ) ) : array();
$nearby    = array();

foreach ( $siblings as $sibling ) {
	if ( $sibling->term_id === $term->term_id ) {
		continue;
	}
	$nearby[] = $sibling;
	if ( count( $nearby ) >= 12 ) {
		break;
	}
}

get_template_part( 'template-parts/page-head', null, array( 'kicker' => __( 'Destination', 'perfect10' ) ) );
?>

<section class="p10-section p10-section--tight">
	<div class="p10-shell">
		<?php get_template_part( 'template-parts/directory' ); ?>
	</div>
</section>

<?php if ( ! empty( $nearby ) ) : ?>
	<section class="p10-section p10-section--panel p10-section--gold-line">
		<div class="p10-shell">
			<?php
			get_template_part( 'template-parts/section-head', null, array(
				'kicker'     => __( 'Nearby', 'perfect10' ),
				'title'      => __( 'Other', 'perfect10' ),
				'accent'     => __( 'Destinations', 'perfect10' ),
				'action'     => $county ? $county->name : '',
				'action_url' => $county ? get_term_link( $county ) : '',
			) );
			?>
			<ul class="p10-city-index__list">
				<?php foreach ( $nearby as $sibling ) : ?>
					<li>
						<a href="<?php echo esc_url( p10_term_url( $sibling ) ); ?>">
							<?php echo esc_html( $sibling->name ); ?>
							<span><?php echo esc_html( number_format_i18n( $sibling->count ) ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
<?php endif; ?>

<?php
get_template_part( 'template-parts/callband' );
get_footer();
