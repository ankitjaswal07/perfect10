<?php
/**
 * Directory pagination.
 *
 * Expected $args:
 *   query WP_Query Optional query; defaults to the main query.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_query;

$args  = isset( $args ) ? $args : array();
$query = isset( $args['query'] ) && $args['query'] instanceof WP_Query ? $args['query'] : $wp_query;

$pages = (int) $query->max_num_pages;
if ( $pages < 2 ) {
	return;
}

$paged = $query->get( 'paged' ) ? (int) $query->get( 'paged' ) : 1;
?>
<nav class="p10-pagination" aria-label="<?php esc_attr_e( 'Pagination', 'perfect10' ); ?>">
	<?php
	echo paginate_links( array( // phpcs:ignore WordPress.Security.EscapeOutput -- trusted WP output.
		'base'      => esc_url( get_pagenum_link( 1 ) ) . '%_%',
		'format'    => user_trailingslashit( 'page/%#%' ),
		'current'   => max( 1, $paged ),
		'total'     => $pages,
		'mid_size'  => 2,
		'end_size'  => 1,
		'type'      => 'plain',
		'prev_text' => '<span aria-hidden="true">←</span><span class="p10-sr">' . esc_html__( 'Previous page', 'perfect10' ) . '</span>',
		'next_text' => '<span class="p10-sr">' . esc_html__( 'Next page', 'perfect10' ) . '</span><span aria-hidden="true">→</span>',
	) );
	?>
</nav>
