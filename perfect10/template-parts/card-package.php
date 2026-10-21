<?php
/**
 * Premium package card.
 *
 * Expected $args:
 *   package WP_Post A p10_package post.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args    = isset( $args ) ? $args : array();
$package = isset( $args['package'] ) ? get_post( $args['package'] ) : get_post();

if ( ! $package || 'p10_package' !== $package->post_type ) {
	return;
}

$price    = p10_get_meta( $package->ID, 'price', '' );
$period   = (string) p10_get_meta( $package->ID, 'period', __( 'per month', 'perfect10' ) );
$badge    = (string) p10_get_meta( $package->ID, 'badge', '' );
$features = (string) p10_get_meta( $package->ID, 'features', '' );
$cta      = (string) p10_get_meta( $package->ID, 'cta_label', __( 'Enquire about this package', 'perfect10' ) );
$featured = (bool) p10_get_meta( $package->ID, 'featured', false );

$feature_list = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $features ) ) ) );
$classes      = $featured ? 'p10-package p10-package--featured p10-reveal' : 'p10-package p10-reveal';
?>
<article class="<?php echo esc_attr( $classes ); ?>">

	<?php if ( $badge ) : ?>
		<span class="p10-package__badge"><?php echo esc_html( $badge ); ?></span>
	<?php endif; ?>

	<h2 class="p10-package__name"><?php echo esc_html( get_the_title( $package ) ); ?></h2>

	<?php if ( '' !== $price ) : ?>
		<div class="p10-package__price">
			<span class="p10-package__amount"><?php echo esc_html( p10_format_money( $price ) ); ?></span>
			<span class="p10-package__period"><?php echo esc_html( $period ); ?></span>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $feature_list ) ) : ?>
		<ul class="p10-package__features">
			<?php foreach ( $feature_list as $feature ) : ?>
				<li><?php echo esc_html( $feature ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php elseif ( has_excerpt( $package ) ) : ?>
		<div class="p10-package__features"><?php echo esc_html( get_the_excerpt( $package ) ); ?></div>
	<?php endif; ?>

	<a class="p10-btn <?php echo $featured ? 'p10-btn--gold' : 'p10-btn--ghost'; ?> p10-btn--block" href="<?php echo esc_url( p10_utility_url( 'register' ) ); ?>">
		<?php echo esc_html( $cta ); ?>
	</a>

</article>
