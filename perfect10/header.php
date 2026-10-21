<?php
/**
 * Site header.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sticky  = (bool) p10_option( 'sticky_header', 1 );
$brand   = (string) p10_option( 'brand_name', get_bloginfo( 'name' ) );
$suffix  = (string) p10_option( 'brand_suffix', '' );
$logo_id = get_theme_mod( 'custom_logo' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="p10-skip p10-sr" href="#p10-main"><?php esc_html_e( 'Skip to content', 'perfect10' ); ?></a>

<?php if ( ! p10_elementor_location( 'header' ) ) : ?>
<header
	class="p10-header<?php echo $sticky ? '' : ' p10-header--static'; ?>"
	<?php echo $sticky ? 'data-p10-header' : ''; ?>
	role="banner">

	<div class="p10-header__inner">

		<a class="p10-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php if ( $logo_id ) : ?>
				<?php echo wp_get_attachment_image( $logo_id, 'full', false, array( 'class' => 'p10-brand__logo', 'alt' => esc_attr( $brand ) ) ); ?>
			<?php else : ?>
				<span class="p10-brand__name"><?php echo esc_html( $brand ); ?></span>
				<?php if ( $suffix ) : ?>
					<span class="p10-brand__sub"><?php echo esc_html( $suffix ); ?></span>
				<?php endif; ?>
			<?php endif; ?>
		</a>

		<nav class="p10-nav" aria-label="<?php esc_attr_e( 'Primary', 'perfect10' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'p10-nav__list',
					'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
					'walker'         => new P10_Nav_Walker(),
					'depth'          => 2,
					'fallback_cb'    => 'p10_default_nav',
				) );
			} else {
				p10_default_nav();
			}
			?>
		</nav>

		<div class="p10-header__actions">
			<?php if ( p10_phone_display() ) : ?>
				<?php p10_phone_link( '', 'p10-header__phone' ); ?>
			<?php endif; ?>

			<a class="p10-btn p10-btn--ghost p10-btn--sm" href="<?php echo esc_url( p10_utility_url( 'booking' ) ); ?>">
				<?php esc_html_e( 'Reserve', 'perfect10' ); ?>
			</a>

			<button
				type="button"
				class="p10-burger"
				data-p10-burger
				aria-expanded="false"
				aria-controls="p10-drawer"
				aria-label="<?php esc_attr_e( 'Open menu', 'perfect10' ); ?>">
				<span></span><span></span><span></span>
			</button>
		</div>

	</div>
</header>

<div class="p10-drawer" id="p10-drawer" data-p10-drawer aria-hidden="true">
	<nav aria-label="<?php esc_attr_e( 'Mobile', 'perfect10' ); ?>">
		<ul class="p10-drawer__list">
			<?php
			$drawer_items = p10_drawer_items();
			foreach ( $drawer_items as $i => $item ) :
				?>
				<li<?php echo ! empty( $item['child'] ) ? ' class="p10-drawer__subitem"' : ''; ?>>
					<a href="<?php echo esc_url( $item['url'] ); ?>" data-num="<?php echo esc_attr( p10_number( $i + 1 ) ); ?>">
						<?php echo esc_html( $item['label'] ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>

	<div class="p10-drawer__foot">
		<?php if ( p10_phone_display() ) : ?>
			<a class="p10-btn p10-btn--gold p10-btn--block" href="<?php echo esc_url( p10_phone_href() ); ?>">
				<?php echo esc_html( p10_phone_display() ); ?>
			</a>
		<?php endif; ?>
		<a class="p10-btn p10-btn--ghost p10-btn--block" href="<?php echo esc_url( p10_utility_url( 'booking' ) ); ?>">
			<?php esc_html_e( 'Reservation Form', 'perfect10' ); ?>
		</a>
		<a class="p10-btn p10-btn--ghost p10-btn--block" href="<?php echo esc_url( p10_directory_url() ); ?>">
			<?php esc_html_e( 'Browse Directory', 'perfect10' ); ?>
		</a>
	</div>
</div>

<?php endif; ?>
<main id="p10-main" class="p10-main" role="main">
