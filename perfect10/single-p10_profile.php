<?php
/**
 * Single profile.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$post_id      = get_the_ID();
	$availability = p10_profile_availability( $post );
	$is_premium   = (bool) p10_meta( $post, 'premium' );
	$is_verified  = (bool) p10_meta( $post, 'verified' );
	$city         = p10_get_primary_term( $post_id, 'p10_city' );
	$county       = p10_get_primary_term( $post_id, 'p10_county' );
	$type         = p10_get_primary_term( $post_id, 'p10_category' );
	$gallery      = p10_profile_gallery( $post );
	$specs        = p10_profile_spec_list( $post );
	$services     = p10_profile_tags( $post, 'services' );
	$flags        = p10_profile_flags( $post );
	$phone        = (string) p10_meta( $post, 'phone', '' );
	if ( ! $phone ) {
		$phone = p10_phone_display();
	}
	$currency = (string) p10_meta( $post, 'currency', p10_option( 'currency', 'USD' ) );
	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'p10-profile' ); ?>>

		<header class="p10-profile-hero">
			<div class="p10-shell">
				<?php p10_breadcrumb(); ?>

				<div class="p10-profile-head">
					<div class="p10-profile-head__main">
						<h1 class="p10-profile-head__title"><?php the_title(); ?></h1>

						<div class="p10-profile-head__sub">
							<?php if ( p10_profile_location_line( $post ) ) : ?>
								<span><?php echo esc_html( p10_profile_location_line( $post ) ); ?></span>
							<?php endif; ?>
							<?php if ( p10_profile_stats_line( $post ) ) : ?>
								<span aria-hidden="true">◆</span>
								<span><?php echo esc_html( p10_profile_stats_line( $post ) ); ?></span>
							<?php endif; ?>
						</div>
					</div>

					<div class="p10-profile-head__badges">
						<?php if ( $is_premium ) : ?>
							<span class="p10-badge p10-badge--premium"><?php esc_html_e( 'Premium', 'perfect10' ); ?></span>
						<?php endif; ?>
						<?php if ( $is_verified ) : ?>
							<span class="p10-badge p10-badge--outline p10-badge--verified"><?php esc_html_e( 'Verified Profile', 'perfect10' ); ?></span>
						<?php endif; ?>
						<span class="p10-dot p10-dot--<?php echo esc_attr( $availability['slug'] ); ?>">
							<?php echo esc_html( $availability['label'] ); ?>
						</span>
					</div>
				</div>
			</div>
		</header>

		<div class="p10-shell">
			<div class="p10-profile-layout">

				<div class="p10-profile-main">

					<?php if ( ! empty( $gallery ) ) : ?>
						<div class="p10-gallery" data-p10-gallery tabindex="0" role="group" aria-label="<?php esc_attr_e( 'Profile gallery', 'perfect10' ); ?>">
							<div class="p10-gallery__main" data-p10-gallery-main>
								<?php
								echo wp_get_attachment_image(
									$gallery[0],
									'p10-gallery',
									false,
									array(
										'alt'   => esc_attr( get_the_title() ),
										'class' => '',
									)
								);
								?>
								<?php if ( count( $gallery ) > 1 ) : ?>
									<button type="button" class="p10-gallery__nav p10-gallery__nav--prev" data-p10-gallery-prev aria-label="<?php esc_attr_e( 'Previous image', 'perfect10' ); ?>">←</button>
									<button type="button" class="p10-gallery__nav p10-gallery__nav--next" data-p10-gallery-next aria-label="<?php esc_attr_e( 'Next image', 'perfect10' ); ?>">→</button>
								<?php endif; ?>
							</div>

							<?php if ( count( $gallery ) > 1 ) : ?>
								<div class="p10-gallery__thumbs">
									<?php foreach ( $gallery as $i => $attachment_id ) : ?>
										<?php
										$full = wp_get_attachment_image_url( $attachment_id, 'p10-gallery' );
										$alt  = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
										?>
										<button
											type="button"
											class="p10-gallery__thumb"
											data-p10-gallery-thumb
											data-full="<?php echo esc_url( $full ); ?>"
											data-alt="<?php echo esc_attr( $alt ? $alt : get_the_title() ); ?>"
											aria-current="<?php echo 0 === $i ? 'true' : 'false'; ?>"
											aria-label="<?php echo esc_attr( sprintf( __( 'Image %d', 'perfect10' ), $i + 1 ) ); ?>">
											<?php echo wp_get_attachment_image( $attachment_id, 'p10-thumb', false, array( 'alt' => '' ) ); ?>
										</button>
									<?php endforeach; ?>
								</div>
								<p class="p10-small" data-p10-gallery-count style="margin-top:calc(var(--p10-fluid-space) * 0.9)">
									<?php echo esc_html( '1 / ' . count( $gallery ) ); ?>
								</p>
							<?php endif; ?>
						</div>
					<?php else : ?>
						<figure class="p10-gallery">
							<img src="<?php echo esc_url( P10_URI . 'assets/images/placeholder-profile.svg' ); ?>" alt="<?php esc_attr_e( 'Placeholder — profile photos unavailable', 'perfect10' ); ?>" width="900" height="1200">
							<figcaption><?php esc_html_e( 'Photos unavailable — placeholder, not an imported gallery.', 'perfect10' ); ?></figcaption>
						</figure>
					<?php endif; ?>

					<?php if ( p10_meta( $post, 'bio_heading' ) ) : ?>
						<h2 class="p10-display-md" style="margin:calc(var(--p10-fluid-space) * 2.4) 0 calc(var(--p10-fluid-space) * 1)">
							<?php echo esc_html( p10_meta( $post, 'bio_heading' ) ); ?>
						</h2>
					<?php endif; ?>

					<?php if ( p10_meta( $post, 'quote' ) ) : ?>
						<blockquote style="margin:calc(var(--p10-fluid-space) * 1.6) 0 calc(var(--p10-fluid-space) * 2);padding-left:calc(var(--p10-fluid-space) * 1.5);border-left:2px solid var(--p10-gold);font-family:var(--p10-display);font-style:italic;font-size:calc(var(--p10-fluid-type) * 1.25);line-height:1.6">
							<?php echo esc_html( p10_meta( $post, 'quote' ) ); ?>
						</blockquote>
					<?php endif; ?>

					<div class="p10-profile-body p10-content">
						<?php
						the_content();
						wp_link_pages();
						?>
					</div>

					<?php if ( ! empty( $services ) ) : ?>
						<div class="p10-panel" style="margin-top:calc(var(--p10-fluid-space) * 1.8)">
							<h2 class="p10-panel__title"><?php esc_html_e( 'Services & Interests', 'perfect10' ); ?></h2>
							<ul class="p10-chips">
								<?php foreach ( $services as $service ) : ?>
									<li class="p10-chip"><?php echo esc_html( $service ); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $specs ) ) : ?>
						<div class="p10-panel" style="margin-top:calc(var(--p10-fluid-space) * 1.2)">
							<h2 class="p10-panel__title"><?php esc_html_e( 'Profile Details', 'perfect10' ); ?></h2>
							<table class="p10-spec">
								<tbody>
									<?php foreach ( $specs as $label => $value ) : ?>
										<tr>
											<th scope="row"><?php echo esc_html( $label ); ?></th>
											<td><?php echo esc_html( (string) $value ); ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					<?php endif; ?>

					<?php
					$disclaimer = (string) p10_meta( $post, 'disclaimer', '' );
					if ( ! $disclaimer ) {
						$disclaimer = (string) p10_option( 'profile_disclaimer', '' );
					}
					if ( $disclaimer ) :
						?>
						<p class="p10-disclaimer"><?php echo wp_kses_post( $disclaimer ); ?></p>
					<?php endif; ?>

					<?php if ( p10_meta( $post, 'video_url' ) ) : ?>
						<div class="p10-panel" style="margin-top:calc(var(--p10-fluid-space) * 1.8)">
							<h2 class="p10-panel__title"><?php esc_html_e( 'Video', 'perfect10' ); ?></h2>
							<?php echo wp_oembed_get( (string) p10_meta( $post, 'video_url' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- trusted oEmbed. ?>
						</div>
					<?php endif; ?>

				</div>

				<aside class="p10-profile-aside">

					<div class="p10-panel p10-panel--sticky">

						<h2 class="p10-panel__title"><?php esc_html_e( 'Rates', 'perfect10' ); ?></h2>

						<div class="p10-pricebox">
							<?php
							$rate_rows = array(
								'rate'           => __( 'Per hour', 'perfect10' ),
								'rate_dinner'    => __( 'Dinner date', 'perfect10' ),
								'rate_overnight' => __( 'Overnight', 'perfect10' ),
							);
							$has_rate = false;
							foreach ( $rate_rows as $key => $label ) :
								$amount = p10_meta( $post, $key );
								if ( ! $amount ) {
									continue;
								}
								$has_rate = true;
								?>
								<div class="p10-pricebox__row">
									<span class="p10-pricebox__label"><?php echo esc_html( $label ); ?></span>
									<span class="p10-pricebox__value"><?php echo esc_html( p10_format_money( $amount, $currency ) ); ?></span>
								</div>
							<?php endforeach; ?>

							<?php if ( ! $has_rate ) : ?>
								<p class="p10-pricebox__label" style="padding-bottom:0;border:0">
									<?php esc_html_e( 'Rates on request', 'perfect10' ); ?>
								</p>
							<?php endif; ?>
						</div>

						<?php if ( ! empty( $flags ) ) : ?>
							<ul class="p10-chips" style="margin-bottom:calc(var(--p10-fluid-space) * 1.5)">
								<?php foreach ( $flags as $flag ) : ?>
									<li class="p10-chip"><?php echo esc_html( $flag ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>

						<?php if ( $phone ) : ?>
							<a class="p10-btn p10-btn--gold p10-btn--block" href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ); ?>" style="margin-bottom:calc(var(--p10-fluid-space) * 0.7)">
								<?php echo esc_html( $phone ); ?>
							</a>
						<?php endif; ?>

						<a class="p10-btn p10-btn--ghost p10-btn--block" href="<?php echo esc_url( p10_utility_url( 'booking' ) ); ?>" style="margin-bottom:calc(var(--p10-fluid-space) * 1.5)">
							<?php esc_html_e( 'Reservation Form', 'perfect10' ); ?>
						</a>

						<?php if ( $is_verified ) : ?>
							<p class="p10-form__hint">
								<?php
								$verified_on = (string) p10_meta( $post, 'verified_on', '' );
								printf(
									/* translators: %s: verification date. */
									esc_html__( 'Identity and photos verified%s. All profiles are independent adults aged 18+.', 'perfect10' ),
									$verified_on ? ' ' . esc_html( $verified_on ) : ''
								);
								?>
							</p>
						<?php endif; ?>

						<?php if ( p10_meta( $post, 'contact_note' ) || p10_option( 'contact_note' ) ) : ?>
							<p class="p10-form__hint" style="margin-top:calc(var(--p10-fluid-space) * 1)">
								<?php echo esc_html( p10_meta( $post, 'contact_note', p10_option( 'contact_note', '' ) ) ); ?>
							</p>
						<?php endif; ?>

					</div>

					<div class="p10-panel">
						<h2 class="p10-panel__title"><?php esc_html_e( 'Request a Referral', 'perfect10' ); ?></h2>
						<?php p10_form_status( 'profile' ); ?>
						<?php
						p10_the_form( 'profile', array(
							'profile_id' => $post_id,
							'redirect'   => get_permalink( $post ),
							'submit'     => __( 'Send enquiry', 'perfect10' ),
							'class'      => 'p10-form--compact',
						) );
						?>
					</div>

					<?php if ( ( $city || $county || $type ) ) : ?>
						<div class="p10-panel">
							<h2 class="p10-panel__title"><?php esc_html_e( 'Listed under', 'perfect10' ); ?></h2>
							<ul class="p10-chips">
								<?php if ( $county ) : ?>
									<li class="p10-chip"><a href="<?php echo esc_url( get_term_link( $county ) ); ?>"><?php echo esc_html( $county->name ); ?></a></li>
								<?php endif; ?>
								<?php if ( $city ) : ?>
									<li class="p10-chip"><a href="<?php echo esc_url( get_term_link( $city ) ); ?>"><?php echo esc_html( $city->name ); ?></a></li>
								<?php endif; ?>
								<?php if ( $type ) : ?>
									<li class="p10-chip"><a href="<?php echo esc_url( get_term_link( $type ) ); ?>"><?php echo esc_html( $type->name ); ?></a></li>
								<?php endif; ?>
								<?php
								$nationality = p10_get_primary_term( $post_id, 'p10_nationality' );
								if ( $nationality ) :
									?>
									<li class="p10-chip"><a href="<?php echo esc_url( get_term_link( $nationality ) ); ?>"><?php echo esc_html( $nationality->name ); ?></a></li>
								<?php endif; ?>
							</ul>
						</div>
					<?php endif; ?>

					<?php if ( is_active_sidebar( 'p10-profile' ) ) : ?>
						<div class="p10-panel">
							<?php dynamic_sidebar( 'p10-profile' ); ?>
						</div>
					<?php endif; ?>

				</aside>

			</div>
		</div>

		<?php
		$related = p10_get_related_profiles( $post, 3 );
		if ( ! empty( $related ) ) :
			?>
			<section class="p10-related">
				<div class="p10-shell">
					<?php
					get_template_part( 'template-parts/section-head', null, array(
						'kicker' => __( 'You may also like', 'perfect10' ),
						'title'  => __( 'Similar', 'perfect10' ),
						'accent' => __( 'Profiles', 'perfect10' ),
						'action' => $city ? sprintf(
							/* translators: %s: city name. */
							__( 'More in %s', 'perfect10' ),
							$city->name
						) : '',
						'action_url' => $city ? get_term_link( $city ) : '',
					) );
					?>
					<div class="p10-grid p10-grid--profiles">
						<?php foreach ( $related as $related_profile ) : ?>
							<?php
							get_template_part( 'template-parts/card-profile', null, array(
								'profile' => $related_profile,
								'index'   => 0,
							) );
							?>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

	</article>

	<?php
	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}

endwhile;

get_footer();
