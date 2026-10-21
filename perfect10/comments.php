<?php
/**
 * Comments.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}
?>
<section class="p10-comments">
	<div class="p10-shell">

		<?php if ( have_comments() ) : ?>
			<h2 class="p10-display-md" style="margin-bottom:1.8rem">
				<?php
				echo esc_html( sprintf(
					/* translators: %s: comment count. */
					_n( '%s response', '%s responses', get_comments_number(), 'perfect10' ),
					number_format_i18n( get_comments_number() )
				) );
				?>
			</h2>

			<ol class="comment-list">
				<?php
				wp_list_comments( array(
					'style'      => 'ol',
					'short_ping' => true,
					'avatar_size' => 48,
				) );
				?>
			</ol>

			<?php
			the_comments_navigation( array(
				'prev_text' => __( '← Older comments', 'perfect10' ),
				'next_text' => __( 'Newer comments →', 'perfect10' ),
			) );
			?>
		<?php endif; ?>

		<?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
			<p class="p10-lede"><?php esc_html_e( 'Comments are closed.', 'perfect10' ); ?></p>
		<?php endif; ?>

		<?php
		comment_form( array(
			'class_submit'  => 'submit',
			'label_submit'  => __( 'Post comment', 'perfect10' ),
			'title_reply'   => __( 'Leave a response', 'perfect10' ),
			'comment_field' => '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Comment', 'perfect10' ) . '</label><textarea id="comment" name="comment" rows="5" required></textarea></p>',
		) );
		?>

	</div>
</section>
