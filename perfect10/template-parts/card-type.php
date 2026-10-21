<?php
/**
 * Profile-type tile.
 *
 * Expected $args:
 *   term   WP_Term A p10_category term.
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

$index  = isset( $args['index'] ) ? (int) $args['index'] : 0;
$reveal = ! isset( $args['reveal'] ) || $args['reveal'];
$delay  = $index ? ( ( $index - 1 ) % 6 ) : 0;

$link    = get_term_link( $term );
$number  = (string) p10_term_meta( $term, 'number', '' );
$short   = (string) p10_term_meta( $term, 'short', '' );
$label   = $short ? $short : $term->name;
$layout  = isset( $args['layout'] ) && 'stacked' === $args['layout'] ? ' p10-type--stacked' : '';
$classes = ( $reveal ? 'p10-type p10-reveal' : 'p10-type' ) . $layout;
$blurb      = (string) p10_term_meta( $term, 'blurb', '' );
$show_blurb = ! isset( $args['show_blurb'] ) || $args['show_blurb'];
$show_count = ! isset( $args['show_count'] ) || $args['show_count'];
$overlay    = isset( $args['overlay'] ) ? max( 0, min( 90, (int) $args['overlay'] ) ) / 100 : 0;
$ratio      = isset( $args['ratio'] ) ? (string) $args['ratio'] : '';
$focal      = isset( $args['focal'] ) ? sanitize_text_field( (string) $args['focal'] ) : '';
$image_args = array(
	'override' => isset( $args['image'] ) ? $args['image'] : null,
	'loading'  => 'lazy',
);
$style_vars = array();
if ( $overlay ) {
	$style_vars[] = '--p10-card-overlay-opacity:' . $overlay;
}
if ( $ratio ) {
	$style_vars[] = '--p10-card-ratio:' . $ratio;
}
$img_style = implode( ';', array_filter( array(
	$ratio ? 'aspect-ratio:' . $ratio : '',
	$focal ? 'object-position:' . $focal : '',
) ) );
if ( $img_style ) {
	$image_args['style'] = $img_style;
}

if ( is_wp_error( $link ) ) {
	return;
}
?>
<a class="<?php echo esc_attr( $classes ); ?>"<?php echo $reveal ? ' data-delay="' . esc_attr( $delay ) . '"' : ''; ?><?php echo $style_vars ? ' style="' . esc_attr( implode( ';', $style_vars ) ) . '"' : ''; ?> href="<?php echo esc_url( $link ); ?>">
	<?php echo p10_term_image_tag( $term, 'p10-card', 'p10-type__img', '', '(max-width: 767px) 50vw, 20vw', $image_args ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper. ?>
	<span class="p10-type__inner">
		<?php if ( $number || $index ) : ?>
			<span class="p10-type__num"><?php echo esc_html( $number ? $number : p10_number( $index ) ); ?></span>
		<?php endif; ?>
		<span class="p10-type__label"><?php echo esc_html( $label ); ?></span>
		<?php if ( $show_blurb && $blurb ) : ?>
			<span class="p10-type__blurb"><?php echo esc_html( wp_trim_words( $blurb, 12 ) ); ?></span>
		<?php endif; ?>
		<?php if ( $show_count && $term->count ) : ?>
			<span class="p10-type__count">
				<?php
				echo esc_html( sprintf(
					/* translators: %s: number of profiles. */
					_n( '%s profile', '%s profiles', (int) $term->count, 'perfect10' ),
					number_format_i18n( $term->count )
				) );
				?>
			</span>
		<?php endif; ?>
	</span>
</a>
