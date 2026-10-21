<?php
/**
 * 18+ age gate. Cookie driven, rendered once in the footer, dismissed by the
 * front-end script. Servers can also read the cookie via p10_age_confirmed().
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cookie name.
 *
 * @return string
 */
function p10_age_cookie_name() {
	return apply_filters( 'p10_age_cookie_name', 'p10_age_ok' );
}

/**
 * Has this visitor confirmed they are 18+?
 *
 * @return bool
 */
function p10_age_confirmed() {
	if ( ! p10_option( 'age_gate_enabled', 1 ) ) {
		return true;
	}
	return ! empty( $_COOKIE[ p10_age_cookie_name() ] );
}

/**
 * Should the gate be shown on the current request?
 *
 * @return bool
 */
function p10_show_age_gate() {
	if ( ! p10_option( 'age_gate_enabled', 1 ) ) {
		return false;
	}
	if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return false;
	}
	return ! p10_age_confirmed();
}

/**
 * Server-side confirmation endpoint. Sets the cookie without JavaScript.
 */
function p10_handle_age_gate() {
	if ( ! isset( $_GET['p10_age'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}

	$decision = sanitize_key( $_GET['p10_age'] ); // phpcs:ignore WordPress.Security.NonceVerification
	$target   = isset( $_GET['p10_age_to'] ) ? esc_url_raw( wp_unslash( $_GET['p10_age_to'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

	if ( ! $target || 0 !== strpos( $target, home_url() ) ) {
		$target = home_url( add_query_arg( array(), $GLOBALS['wp']->request ? '/' . $GLOBALS['wp']->request . '/' : '/' ) );
	}

	if ( 'accept' === $decision ) {
		$max_age = (int) apply_filters( 'p10_age_cookie_lifetime', 30 * DAY_IN_SECONDS );
		if ( ! headers_sent() ) {
			setcookie( p10_age_cookie_name(), '1', time() + $max_age, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), false );
		}
		$_COOKIE[ p10_age_cookie_name() ] = '1';
	}

	if ( ! headers_sent() ) {
		wp_safe_redirect( remove_query_arg( array( 'p10_age', 'p10_age_to' ), $target ) );
		exit;
	}
}
add_action( 'init', 'p10_handle_age_gate', 1 );

/**
 * Print the age gate markup.
 */
function p10_render_age_gate() {
	if ( ! p10_show_age_gate() ) {
		return;
	}

	$accept_url = add_query_arg(
		array(
			'p10_age'    => 'accept',
			'p10_age_to' => rawurlencode( p10_current_url() ),
		),
		home_url( '/' )
	);
	$exit_url = (string) p10_option( 'age_gate_exit_url', 'https://www.google.com' );
	?>
	<div class="p10-agegate" id="p10-agegate" role="dialog" aria-modal="true" aria-labelledby="p10-agegate-title" data-p10-agegate>
		<div class="p10-agegate__scrim" data-p10-agegate-scrim></div>
		<div class="p10-agegate__panel">
			<p class="p10-agegate__brand">
				<span class="p10-agegate__mark"><?php echo esc_html( p10_option( 'brand_name', 'Perfect 10' ) ); ?></span>
				<span class="p10-agegate__sub"><?php echo esc_html( p10_option( 'brand_suffix', '' ) ); ?></span>
			</p>
			<h2 class="p10-agegate__title" id="p10-agegate-title"><?php echo esc_html( p10_option( 'age_gate_title', __( 'Adults only', 'perfect10' ) ) ); ?></h2>
			<p class="p10-agegate__copy"><?php echo esc_html( p10_option( 'age_gate_copy', '' ) ); ?></p>
			<div class="p10-agegate__actions">
				<button type="button" class="p10-btn p10-btn--gold p10-btn--block" data-p10-agegate-accept>
					<?php echo esc_html( p10_option( 'age_gate_enter', __( 'I am 18 or older — Enter', 'perfect10' ) ) ); ?>
				</button>
				<a class="p10-btn p10-btn--ghost p10-btn--block" href="<?php echo esc_url( $exit_url ); ?>">
					<?php echo esc_html( p10_option( 'age_gate_exit', __( 'Exit', 'perfect10' ) ) ); ?>
				</a>
			</div>
			<p class="p10-agegate__fallback">
				<a href="<?php echo esc_url( $accept_url ); ?>"><?php esc_html_e( 'Continue without JavaScript', 'perfect10' ); ?></a>
			</p>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'p10_render_age_gate', 5 );

/**
 * Lock scrolling while the gate is open.
 */
function p10_age_gate_body_class( $classes ) {
	if ( p10_show_age_gate() ) {
		$classes[] = 'p10-agegate-open';
	}
	return $classes;
}
add_filter( 'body_class', 'p10_age_gate_body_class' );

/**
 * Current request URL.
 *
 * @return string
 */
function p10_current_url() {
	$protocol = is_ssl() ? 'https://' : 'http://';
	$host     = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : wp_parse_url( home_url(), PHP_URL_HOST );
	$path     = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';

	return esc_url_raw( $protocol . $host . $path );
}

/**
 * Blur directory imagery until the gate is accepted (optional hard gate).
 */
function p10_age_gate_head() {
	if ( ! p10_show_age_gate() ) {
		return;
	}
	// Image indexing stays enabled: the gate is a cookie-based confirmation,
	// not a paywall, and noimageindex would remove every image from search.
	if ( apply_filters( 'p10_age_gate_noimageindex', false ) ) {
		echo '<meta name="robots" content="noimageindex">' . "\n";
	}
}
add_action( 'wp_head', 'p10_age_gate_head', 30 );
