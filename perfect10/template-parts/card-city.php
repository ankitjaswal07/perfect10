<?php
/**
 * City card.
 *
 * Expected $args:
 *   term   WP_Term A p10_city term.
 *   index  int     1-based position.
 *   reveal bool    Apply the scroll-reveal class.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = isset( $args ) ? $args : array();
$term = isset( $args['term'] ) ? $args['term'] : null;

if ( ! $term instanceof WP_Term ) {
	return;
}

$link = get_term_link( $term );
if ( is_wp_error( $link ) ) {
	return;
}

$index        = isset( $args['index'] ) ? (int) $args['index'] : 0;
$reveal       = ! isset( $args['reveal'] ) || $args['reveal'];
$delay        = $index ? ( ( $index - 1 ) % 6 ) : 0;
$county_slug  = p10_city_county_slug( $term );
$county       = $county_slug ? get_term_by( 'slug', $county_slug, 'p10_county' ) : null;
$profile_count = (int) $term->count;
$layout        = isset( $args['layout'] ) && 'stacked' === $args['layout'] ? ' p10-city--stacked' : '';
$classes       = ( $reveal ? 'p10-city p10-reveal' : 'p10-city' ) . $layout;
$blurb         = (string) p10_term_meta( $term, 'blurb', '' );
$show_blurb    = ! isset( $args['show_blurb'] ) || $args['show_blurb'];
$show_count    = ! isset( $args['show_count'] ) || $args['show_count'];
$overlay       = isset( $args['overlay'] ) ? max( 0, min( 90, (int) $args['overlay'] ) ) / 100 : 0;
$ratio         = isset( $args['ratio'] ) ? (string) $args['ratio'] : '';
$image_args    = array(
	'override'      => isset( $args['image'] ) ? $args['image'] : null,
	'loading'       => 'lazy',
	'fetchpriority' => isset( $args['fetchpriority'] ) ? $args['fetchpriority'] : '',
);
$style_vars    = array();
if ( $overlay ) {
	$style_vars[] = '--p10-card-overlay-opacity:' . $overlay;
}
if ( $ratio ) {
	$style_vars[] = '--p10-card-ratio:' . $ratio;
}
if ( isset( $args['focal'] ) && $args['focal'] ) {
	$style_vars[] = '--p10-card-pos:' . sanitize_text_field( (string) $args['focal'] );
}
$img_style = $ratio || isset( $args['focal'] ) ? implode( ';', array_filter( array(
	$ratio ? 'aspect-ratio:' . $ratio : '',
	isset( $args['focal'] ) && $args['focal'] ? 'object-position:' . sanitize_text_field( (string) $args['focal'] ) : '',
) ) ) : '';
if ( $img_style ) {
	$image_args['style'] = $img_style;
}
?>
<a class="<?php echo esc_attr( $classes ); ?>"<?php echo $reveal ? ' data-delay="' . esc_attr( $delay ) . '"' : ''; ?><?php echo $style_vars ? ' style="' . esc_attr( implode( ';', $style_vars ) ) . '"' : ''; ?> href="<?php echo esc_url( $link ); ?>">
	<?php echo p10_term_image_tag( $term, 'p10-card-wide', 'p10-city__img', '', '(max-width: 767px) 100vw, 33vw', $image_args ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper. ?>
	<?php if ( $index ) : ?>
		<span class="p10-city__num"><?php echo esc_html( p10_number( $index ) ); ?></span>
	<?php endif; ?>
	<span class="p10-city__inner">
		<?php if ( $county ) : ?>
			<span class="p10-city__county"><?php echo esc_html( $county->name ); ?></span>
		<?php endif; ?>
		<span class="p10-city__name"><?php echo esc_html( $term->name ); ?></span>
		<?php if ( $show_blurb && $blurb ) : ?>
			<span class="p10-city__blurb"><?php echo esc_html( wp_trim_words( $blurb, 16 ) ); ?></span>
		<?php endif; ?>
		<?php if ( $show_count ) : ?>
		<span class="p10-city__count">
			<?php
			echo esc_html( sprintf(
				/* translators: %s: number of profiles. */
				_n( '%s profile', '%s profiles', $profile_count, 'perfect10' ),
				number_format_i18n( $profile_count )
			) );
			?>
		</span>
		<?php endif; ?>
	</span>
</a>
