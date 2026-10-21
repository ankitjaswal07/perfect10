<?php
/**
 * Site footer and the floating concierge widget.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$brand      = (string) p10_option( 'brand_name', get_bloginfo( 'name' ) );
$city_count = (int) p10_option( 'footer_columns', 12 );
$cities     = $city_count ? array_slice( p10_get_featured_cities( $city_count ), 0, $city_count ) : array();
$categories = p10_get_categories();
$year       = gmdate( 'Y' );
?>
</main><!-- #p10-main -->

<?php if ( ! p10_elementor_location( 'footer' ) ) : ?>
<footer class="p10-footer" role="contentinfo">
	<div class="p10-shell">

		<div class="p10-footer__top">

			<div class="p10-footer__brand">
				<a class="p10-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<span class="p10-brand__name"><?php echo esc_html( $brand ); ?></span>
					<?php if ( p10_option( 'brand_suffix' ) ) : ?>
						<span class="p10-brand__sub"><?php echo esc_html( p10_option( 'brand_suffix' ) ); ?></span>
					<?php endif; ?>
				</a>

				<?php if ( p10_option( 'footer_blurb' ) ) : ?>
					<p class="p10-footer__blurb"><?php echo esc_html( p10_option( 'footer_blurb' ) ); ?></p>
				<?php endif; ?>

				<?php if ( p10_phone_display() ) : ?>
					<p class="p10-footer__phone"><?php p10_phone_link(); ?></p>
				<?php endif; ?>

				<?php if ( p10_option( 'email' ) ) : ?>
					<p class="p10-footer__email">
						<a href="mailto:<?php echo esc_attr( antispambot( (string) p10_option( 'email' ) ) ); ?>">
							<?php echo esc_html( antispambot( (string) p10_option( 'email' ) ) ); ?>
						</a>
					</p>
				<?php endif; ?>

				<?php if ( p10_option( 'address' ) ) : ?>
					<p class="p10-footer__address"><?php echo wp_kses_post( nl2br( (string) p10_option( 'address' ) ) ); ?></p>
				<?php endif; ?>

				<?php if ( p10_option( 'footer_show_tags', 1 ) && p10_option( 'footer_tags' ) ) : ?>
					<p class="p10-footer__tags"><?php echo esc_html( p10_option( 'footer_tags' ) ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( p10_option( 'footer_show_cities', 1 ) && ! empty( $cities ) ) : ?>
				<div class="p10-footer__col">
					<h3 class="p10-footer__title"><?php esc_html_e( 'Popular Cities', 'perfect10' ); ?></h3>
					<ul class="p10-footer__list">
						<?php foreach ( $cities as $city ) : ?>
							<li>
								<a href="<?php echo esc_url( p10_term_url( $city ) ); ?>"><?php echo esc_html( $city->name ); ?></a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( p10_option( 'footer_show_browse', 1 ) && ! empty( $categories ) ) : ?>
				<div class="p10-footer__col">
					<h3 class="p10-footer__title"><?php esc_html_e( 'Browse', 'perfect10' ); ?></h3>
					<ul class="p10-footer__list">
						<?php foreach ( $categories as $category ) : ?>
							<li>
								<a href="<?php echo esc_url( get_term_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a>
							</li>
						<?php endforeach; ?>
						<li>
							<a href="<?php echo esc_url( p10_directory_url( array( 'premium' => 1 ) ) ); ?>">
								<?php esc_html_e( 'Premium Collection', 'perfect10' ); ?>
							</a>
						</li>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( p10_option( 'footer_show_explore', 1 ) ) : ?>
			<div class="p10-footer__col">
				<?php if ( has_nav_menu( 'footer' ) ) : ?>
					<h3 class="p10-footer__title"><?php esc_html_e( 'Explore', 'perfect10' ); ?></h3>
					<?php
					wp_nav_menu( array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'p10-footer__list',
						'depth'          => 1,
						'fallback_cb'    => false,
					) );
					?>
				<?php else : ?>
					<h3 class="p10-footer__title"><?php esc_html_e( 'Explore', 'perfect10' ); ?></h3>
					<ul class="p10-footer__list">
						<li><a href="<?php echo esc_url( p10_directory_url() ); ?>"><?php esc_html_e( 'Full Directory', 'perfect10' ); ?></a></li>
						<li><a href="<?php echo esc_url( p10_utility_url( 'reviews' ) ); ?>"><?php esc_html_e( 'Guest Reviews', 'perfect10' ); ?></a></li>
						<li><a href="<?php echo esc_url( p10_utility_url( 'packages' ) ); ?>"><?php esc_html_e( 'Premium Packages', 'perfect10' ); ?></a></li>
						<li><a href="<?php echo esc_url( p10_utility_url( 'booking' ) ); ?>"><?php esc_html_e( 'Reservation Form', 'perfect10' ); ?></a></li>
						<li><a href="<?php echo esc_url( p10_utility_url( 'register' ) ); ?>"><?php esc_html_e( 'Create a Listing', 'perfect10' ); ?></a></li>
					</ul>
				<?php endif; ?>

				<?php if ( is_active_sidebar( 'p10-footer-1' ) || is_active_sidebar( 'p10-footer-2' ) ) : ?>
					<div class="p10-footer__widgets">
						<?php dynamic_sidebar( 'p10-footer-1' ); ?>
						<?php dynamic_sidebar( 'p10-footer-2' ); ?>
					</div>
				<?php endif; ?>
			</div>
			<?php endif; ?>

		</div>

		<?php if ( p10_option( 'footer_mark', 1 ) ) : ?>
			<div class="p10-footer__mark" aria-hidden="true"><?php echo esc_html( $brand ); ?></div>
		<?php endif; ?>

		<div class="p10-footer__base">
			<p class="p10-footer__copy">
				<?php
				$copyright = (string) p10_option( 'footer_copyright', '' );
				if ( $copyright ) {
					echo esc_html( $copyright );
				} else {
					printf(
						/* translators: 1: year, 2: brand name. */
						esc_html__( '© %1$s %2$s. All rights reserved. 18+ only.', 'perfect10' ),
						esc_html( $year ),
						esc_html( $brand )
					);
				}
				?>
			</p>

			<?php if ( has_nav_menu( 'legal' ) ) : ?>
				<nav class="p10-footer__legal-nav" aria-label="<?php esc_attr_e( 'Legal', 'perfect10' ); ?>">
					<?php
					wp_nav_menu( array(
						'theme_location' => 'legal',
						'container'      => false,
						'items_wrap'     => '%3$s',
						'depth'          => 1,
						'fallback_cb'    => false,
					) );
					?>
				</nav>
			<?php endif; ?>
		</div>

		<?php if ( p10_option( 'footer_show_disclaimer', 1 ) && p10_option( 'footer_legal' ) ) : ?>
			<p class="p10-footer__disclaimer"><?php echo wp_kses_post( (string) p10_option( 'footer_legal' ) ); ?></p>
		<?php endif; ?>

	</div>
</footer>
<?php endif; ?>

<?php get_template_part( 'template-parts/concierge' ); ?>

<?php wp_footer(); ?>
</body>
</html>
