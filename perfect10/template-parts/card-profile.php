<?php
/**
 * Profile card.
 *
 * Expected $args:
 *   profile WP_Post The profile (falls back to the global post).
 *   index   int     1-based position, renders the "№ 01" badge.
 *   variant string  '' | 'premium'.
 *   reveal  bool    Apply the scroll-reveal class.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args    = isset( $args ) ? $args : array();
$profile = isset( $args['profile'] ) ? get_post( $args['profile'] ) : get_post();

if ( ! $profile || 'p10_profile' !== $profile->post_type ) {
	return;
}

$index        = isset( $args['index'] ) ? (int) $args['index'] : 0;
$variant      = isset( $args['variant'] ) ? $args['variant'] : '';
$reveal       = ! isset( $args['reveal'] ) || $args['reveal'];
$delay        = $index ? ( ( $index - 1 ) % 6 ) : 0;

$permalink    = get_permalink( $profile );
$is_premium   = (bool) p10_meta( $profile, 'premium' );
$is_verified  = (bool) p10_meta( $profile, 'verified' );
$availability = p10_profile_availability( $profile );
$location     = p10_profile_location_line( $profile );
$stats        = p10_profile_stats_line( $profile );
$phone        = (string) p10_meta( $profile, 'phone', '' );

$classes = array( 'p10-card' );
if ( $is_premium ) {
	$classes[] = 'p10-card--premium';
}
if ( $reveal ) {
	$classes[] = 'p10-reveal';
}
?>
<article class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"<?php echo $reveal ? ' data-delay="' . esc_attr( $delay ) . '"' : ''; ?>>

	<a
		class="p10-card__link"
		href="<?php echo esc_url( $permalink ); ?>"
		tabindex="-1"
		aria-label="<?php echo esc_attr( sprintf( __( 'View profile: %s', 'perfect10' ), get_the_title( $profile ) ) ); ?>">
		<div class="p10-card__media">
			<img
				class="p10-card__img"
				src="<?php echo esc_url( p10_profile_image( $profile, 'p10-card' ) ); ?>"
				alt="<?php echo esc_attr( get_the_title( $profile ) ); ?>"
				loading="lazy"
				decoding="async"
				width="900"
				height="1200">
			<?php if ( ! has_post_thumbnail( $profile ) ) : ?>
				<span class="p10-card__placeholder"><?php esc_html_e( 'Photo unavailable · placeholder', 'perfect10' ); ?></span>
			<?php endif; ?>
		</div>
	</a>

	<?php if ( $index ) : ?>
		<span class="p10-card__num"><?php echo esc_html( sprintf( '№ %s', p10_number( $index ) ) ); ?></span>
	<?php endif; ?>

	<div class="p10-card__badges">
		<?php if ( $is_premium ) : ?>
			<span class="p10-badge p10-badge--premium"><?php esc_html_e( 'Premium', 'perfect10' ); ?></span>
		<?php endif; ?>
		<span class="p10-dot p10-dot--<?php echo esc_attr( $availability['slug'] ); ?>">
			<?php echo esc_html( $availability['label'] ); ?>
		</span>
	</div>

	<div class="p10-card__body">
		<?php if ( $is_verified ) : ?>
			<span class="p10-card__verified"><?php esc_html_e( 'Verified Profile', 'perfect10' ); ?></span>
		<?php endif; ?>

		<h3 class="p10-card__title">
			<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( get_the_title( $profile ) ); ?></a>
		</h3>

		<?php if ( $location ) : ?>
			<p class="p10-card__loc"><?php echo esc_html( $location ); ?></p>
		<?php endif; ?>

		<?php if ( $stats ) : ?>
			<p class="p10-card__stats"><?php echo esc_html( $stats ); ?></p>
		<?php endif; ?>

		<div class="p10-card__foot">
			<a class="p10-link" href="<?php echo esc_url( $permalink ); ?>">
				<?php esc_html_e( 'View Profile', 'perfect10' ); ?>
				<span class="p10-link__arrow" aria-hidden="true">→</span>
			</a>

			<?php if ( $phone && p10_option( 'show_phone_on_card' ) ) : ?>
				<a class="p10-phone" href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ); ?>">
					<span class="p10-phone__icon" aria-hidden="true">☏</span>
					<span class="p10-sr"><?php esc_html_e( 'Call', 'perfect10' ); ?></span>
				</a>
			<?php endif; ?>
		</div>
	</div>

	<div class="p10-card__overlay">
		<a class="p10-btn p10-btn--gold p10-btn--sm" href="<?php echo esc_url( $permalink ); ?>">
			<?php esc_html_e( 'View Profile', 'perfect10' ); ?>
		</a>
	</div>

</article>
