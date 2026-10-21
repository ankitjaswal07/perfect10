<?php
/**
 * Sidebar, chosen by context.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sidebar = is_singular( 'p10_profile' ) ? 'p10-profile' : 'p10-directory';

if ( ! is_active_sidebar( $sidebar ) ) {
	return;
}
?>
<aside class="p10-sidebar" role="complementary">
	<?php dynamic_sidebar( $sidebar ); ?>
</aside>
