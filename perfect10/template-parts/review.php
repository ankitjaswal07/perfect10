<?php
/**
 * Guest review card.
 *
 * Expected $args:
 *   review WP_Post A p10_review post.
 *   index  int     1-based position.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args   = isset( $args ) ? $args : array();
$review = isset( $args['review'] ) ? get_post( $args['review'] ) : get_post();

if ( ! $review || 'p10_review' !== $review->post_type ) {
	return;
}

$index    = isset( $args['index'] ) ? (int) $args['index'] : 0;
$name     = (string) p10_get_meta( $review->ID, 'guest_name', __( 'Verified Guest', 'perfect10' ) );
$location = (string) p10_get_meta( $review->ID, 'guest_location', '' );
$label    = (string) p10_get_meta( $review->ID, 'guest_label', __( 'Verified Guest', 'perfect10' ) );
$rating   = max( 1, min( 5, (int) p10_get_meta( $review->ID, 'rating', 5 ) ) );
$delay    = $index ? ( ( $index - 1 ) % 3 ) : 0;
?>
<figure class="p10-review p10-reveal" data-delay="<?php echo esc_attr( $delay ); ?>">

	<span class="p10-review__mark" aria-hidden="true">&ldquo;</span>

	<?php if ( $rating ) : ?>
		<span class="p10-review__stars" role="img" aria-label="<?php echo esc_attr( sprintf( __( '%d out of 5', 'perfect10' ), $rating ) ); ?>">
			<?php echo esc_html( str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating ) ); ?>
		</span>
	<?php endif; ?>

	<blockquote class="p10-review__text"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt( $review ) ) ); ?></blockquote>

	<figcaption class="p10-review__foot">
		<span class="p10-review__name"><?php echo esc_html( $name ); ?><?php echo p10_review_sample_badge( $review->ID ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		<span class="p10-review__meta">
			<?php
			$meta = array_filter( array( $location, $label ) );
			echo esc_html( implode( ' · ', $meta ) );
			?>
		</span>
	</figcaption>

</figure>
