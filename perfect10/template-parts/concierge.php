<?php
/**
 * Floating website assistant.
 *
 * The launcher and window are progressive enhancement: with JavaScript off the
 * button does nothing harmful and the page stays fully readable. Appearance is
 * driven by the Website Assistant settings, with every colour optional so the
 * global design tokens show through by default.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) || ! p10_chat_is_visible() ) {
	return;
}

$chat = p10_chat_ui();
$pos  = 'left' === $chat['position'] ? ' p10-chat--left' : ' p10-chat--right';
?>
<div class="p10-concierge p10-chat<?php echo esc_attr( $pos ); ?>" data-p10-chat style="<?php echo esc_attr( p10_chat_style_attr() ); ?>">

	<button type="button" class="p10-concierge__btn p10-chat__launcher" data-chat-open aria-haspopup="dialog" aria-controls="p10-chat-dialog" aria-expanded="false" aria-label="<?php echo esc_attr( $chat['title'] ); ?>">
		<span class="p10-chat__launcher-icon" aria-hidden="true">
			<?php
			if ( $chat['icon'] ) {
				echo '<img src="' . esc_url( $chat['icon'] ) . '" alt="" width="32" height="32" loading="lazy" decoding="async">';
			} else {
				echo $chat['icon_svg']; // phpcs:ignore WordPress.Security.EscapeOutput -- fixed inline SVG.
			}
			?>
		</span>
	</button>

	<dialog id="p10-chat-dialog" class="p10-chat-dialog" aria-labelledby="p10-chat-title" aria-describedby="p10-chat-subtitle">
		<header class="p10-chat__header">
			<div class="p10-chat__identity">
				<span class="p10-chat__mark" aria-hidden="true">◆</span>
				<div>
					<h2 id="p10-chat-title" class="p10-chat__title"><?php echo esc_html( $chat['title'] ); ?></h2>
					<?php if ( $chat['subtitle'] ) : ?>
						<p id="p10-chat-subtitle" class="p10-chat__subtitle"><?php echo esc_html( $chat['subtitle'] ); ?></p>
					<?php endif; ?>
				</div>
			</div>
			<div class="p10-chat__header-actions">
				<button type="button" class="p10-chat__icon-btn" data-chat-minimize aria-label="<?php esc_attr_e( 'Minimize assistant', 'perfect10' ); ?>" aria-expanded="true" aria-controls="p10-chat-panel">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14"/></svg>
				</button>
				<button type="button" class="p10-chat__icon-btn" data-chat-close aria-label="<?php esc_attr_e( 'Close assistant', 'perfect10' ); ?>">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
				</button>
			</div>
		</header>

		<div class="p10-chat__panel" id="p10-chat-panel" data-chat-panel>
			<p class="p10-chat-notice"><?php esc_html_e( 'Published website information only. Chats are not saved by this theme. Do not enter sensitive personal details. Voice input may use your browser’s external speech service; no audio is sent by this theme to an AI provider.', 'perfect10' ); ?></p>
			<p class="p10-chat-provider" data-chat-provider></p>

			<div class="p10-chat-log" role="log" aria-live="polite" aria-relevant="additions" tabindex="0" aria-label="<?php esc_attr_e( 'Conversation', 'perfect10' ); ?>" data-chat-log></div>

			<div class="p10-chat-suggestions" data-chat-suggestions aria-label="<?php esc_attr_e( 'Suggested questions', 'perfect10' ); ?>">
				<?php foreach ( $chat['suggestions'] as $suggestion ) : ?>
					<button type="button" class="p10-chat__suggestion" data-chat-suggestion><?php echo esc_html( $suggestion ); ?></button>
				<?php endforeach; ?>
			</div>

			<p class="p10-chat__status" role="status" data-chat-status></p>

			<form class="p10-chat__form" data-chat-form>
				<label class="screen-reader-text" for="p10-chat-input"><?php esc_html_e( 'Your message', 'perfect10' ); ?></label>
				<div class="p10-chat-input">
					<input id="p10-chat-input" name="message" maxlength="600" autocomplete="off" required placeholder="<?php esc_attr_e( 'Ask about published profiles, cities or the site…', 'perfect10' ); ?>">
					<button type="submit" class="p10-chat__send">
						<span><?php esc_html_e( 'Send', 'perfect10' ); ?></span>
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m4 12 16-8-6 8 6 8-16-8Z"/></svg>
					</button>
				</div>
			</form>

			<div class="p10-chat-tools" aria-label="<?php esc_attr_e( 'Assistant controls', 'perfect10' ); ?>">
				<button type="button" data-chat-mic hidden><?php esc_html_e( 'Use microphone', 'perfect10' ); ?></button>
				<button type="button" data-chat-mute aria-pressed="true"><?php esc_html_e( 'Voice muted', 'perfect10' ); ?></button>
				<button type="button" data-chat-stop><?php esc_html_e( 'Stop voice', 'perfect10' ); ?></button>
				<button type="button" data-chat-repeat><?php esc_html_e( 'Repeat response', 'perfect10' ); ?></button>
				<button type="button" data-chat-reset><?php esc_html_e( 'Reset', 'perfect10' ); ?></button>
				<button type="button" data-chat-follow hidden><?php esc_html_e( 'Request follow-up', 'perfect10' ); ?></button>
			</div>
			<p class="p10-chat-notice p10-chat-notice--voice" data-chat-voice-note><?php esc_html_e( 'Text chat works without voice. Speech recognition requires a supported browser, microphone permission and usually HTTPS.', 'perfect10' ); ?></p>
		</div>
	</dialog>
</div>
