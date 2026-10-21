<?php
/**
 * Perfect 10 design system.
 *
 * One resolver turns the existing Customizer settings into the CSS custom
 * properties used by every theme template, Elementor widget, admin preview and
 * the chatbot. The legacy tokens (--p10-gold, --p10-ink, --p10-cream,
 * --p10-panel) remain the storage layer; semantic aliases (--p10-color-*)
 * point at them so existing overrides and child themes keep working.
 *
 * No stylesheet is generated here. The values are printed once in wp_head by
 * p10_custom_colors() in inc/customizer.php, which has done that since 1.0.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Optional complete palettes.
 *
 * "inherit" is the default and keeps every existing Customizer value exactly
 * where it is. The named presets are explicit palettes an administrator can
 * opt into; selecting one never changes posts, terms, menus or URLs.
 *
 * @return array
 */
function p10_design_presets() {
	return apply_filters(
		'p10_design_presets',
		array(
			'default' => array(
				'label'  => __( 'Perfect 10 — dark editorial (default)', 'perfect10' ),
				'colors' => array(
					'gold'      => '#d4af37',
					'ink'       => '#0b0a09',
					'ink_2'     => '#100e0b',
					'ink_3'     => '#1a1712',
					'cream'     => '#f5f0e6',
					'panel'     => '#14120e',
					'gold_soft' => '#e9d9a6',
					'gold_deep' => '#a8842a',
					'muted'     => '#9c9484',
					'muted_2'   => '#6f6a5e',
				),
			),
			'ivory' => array(
				'label'  => __( 'Ivory editorial (light)', 'perfect10' ),
				'colors' => array(
					'gold'      => '#9a7420',
					'ink'       => '#f6f1e7',
					'ink_2'     => '#fbf8f1',
					'ink_3'     => '#e9e0d0',
					'cream'     => '#17130d',
					'panel'     => '#ffffff',
					'gold_soft' => '#7a5a12',
					'gold_deep' => '#6b4e0e',
					'muted'     => '#665f52',
					'muted_2'   => '#8b8374',
				),
			),
			'midnight' => array(
				'label'  => __( 'Midnight navy', 'perfect10' ),
				'colors' => array(
					'gold'      => '#c8a96a',
					'ink'       => '#090d14',
					'ink_2'     => '#0c1119',
					'ink_3'     => '#121a26',
					'cream'     => '#eef1f4',
					'panel'     => '#101722',
					'gold_soft' => '#e5d3a6',
					'gold_deep' => '#96783b',
					'muted'     => '#9aa4b1',
					'muted_2'   => '#6b7684',
				),
			),
		)
	);
}

/**
 * The selected palette, resolved.
 *
 * @return array{key:string,label:string,colors:array<string,string>}
 */
function p10_design_palette() {
	$presets = p10_design_presets();
	$key     = (string) p10_option( 'color_preset', 'inherit' );

	$defaults = array(
		'gold'      => sanitize_hex_color( (string) p10_option( 'color_gold', '#d4af37' ) ),
		'ink'       => sanitize_hex_color( (string) p10_option( 'color_ink', '#0b0a09' ) ),
		'ink_2'     => '#100e0b',
		'ink_3'     => sanitize_hex_color( (string) p10_option( 'color_secondary', '#1a1712' ) ),
		'cream'     => sanitize_hex_color( (string) p10_option( 'color_cream', '#f5f0e6' ) ),
		'panel'     => sanitize_hex_color( (string) p10_option( 'color_panel', '#14120e' ) ),
		'gold_soft' => sanitize_hex_color( (string) p10_option( 'color_accent', '#e9d9a6' ) ),
		'gold_deep' => '#a8842a',
		'muted'     => sanitize_hex_color( (string) p10_option( 'color_muted', '#9c9484' ) ),
		'muted_2'   => '#6f6a5e',
	);

	$defaults = array_filter( $defaults );

	if ( 'inherit' !== $key && isset( $presets[ $key ] ) ) {
		$colors = wp_parse_args( (array) $presets[ $key ]['colors'], $defaults );
		$label  = $presets[ $key ]['label'];
	} else {
		$colors = $defaults;
		$label  = __( 'Custom (individual controls)', 'perfect10' );
		$key    = 'inherit';
	}

	return array(
		'key'    => $key,
		'label'  => $label,
		'colors' => $colors,
	);
}

/**
 * Convert a hex colour to an "r g b" triplet for modern rgb() alpha syntax.
 *
 * @param string $hex Hex colour.
 * @return string
 */
function p10_hex_rgb( $hex ) {
	$hex = ltrim( (string) $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex . $hex;
	}
	if ( ! preg_match( '/^[0-9a-fA-F]{6}$/', $hex ) ) {
		return '0 0 0';
	}
	return implode(
		' ',
		array(
			hexdec( substr( $hex, 0, 2 ) ),
			hexdec( substr( $hex, 2, 2 ) ),
			hexdec( substr( $hex, 4, 2 ) ),
		)
	);
}

/**
 * Pick a colour with a fallback and validation.
 *
 * @param string $option   Option key without prefix.
 * @param string $fallback Fallback colour.
 * @return string
 */
function p10_design_color( $option, $fallback ) {
	$value = sanitize_hex_color( (string) p10_option( $option, '' ) );
	return $value ? $value : $fallback;
}

/**
 * All design tokens used by the front end.
 *
 * @return array<string,string>
 */
function p10_design_tokens() {
	$palette = p10_design_palette();
	$colors  = $palette['colors'];

	$gold      = $colors['gold'];
	$ink       = $colors['ink'];
	$cream     = $colors['cream'];
	$panel     = $colors['panel'];
	$muted     = $colors['muted'];
	$gold_soft = $colors['gold_soft'];

	$border   = p10_design_color( 'color_border', '' );
	$link     = p10_design_color( 'color_link', $cream );
	$hover    = p10_design_color( 'color_link_hover', $gold_soft );
	$focus    = p10_design_color( 'color_focus', $gold_soft );
	$btn_bg   = p10_design_color( 'color_button_bg', $gold );
	$btn_text = p10_design_color( 'color_button_text', $ink );
	$btn2     = p10_design_color( 'color_button_secondary_text', $cream );
	$btn2_bd  = p10_design_color( 'color_button_secondary_border', '' );
	$form_bg  = p10_design_color( 'color_form_bg', '' );
	$form_tx  = p10_design_color( 'color_form_text', $cream );
	$form_bd  = p10_design_color( 'color_form_border', '' );

	$display_font  = (string) p10_option( 'font_display', 'Playfair Display' );
	$body_font     = (string) p10_option( 'font_body', 'Inter' );
	$shell_width   = max( 1100, min( 1560, absint( p10_option( 'container_width', 1300 ) ) ) );
	$section_scale = max( 60, min( 160, absint( p10_option( 'section_scale', 100 ) ) ) );
	$radius        = max( 0, min( 40, absint( p10_option( 'radius', 2 ) ) ) );
	$card_radius   = max( 0, min( 40, absint( p10_option( 'card_radius', 2 ) ) ) );
	$type_scale    = max( 85, min( 130, absint( p10_option( 'type_scale', 100 ) ) ) ) / 100;
	$heading_weight = (string) p10_option( 'heading_weight', '500' );
	$heading_weight = in_array( $heading_weight, array( '400', '500', '600', '700' ), true ) ? $heading_weight : '500';
	$heading_track = max( -40, min( 40, (int) p10_option( 'heading_tracking', -15 ) ) ) / 1000;
	$body_line     = max( 140, min( 190, absint( p10_option( 'body_line_height', 165 ) ) ) ) / 100;

	$tokens = array(
		/* Legacy palette tokens (single storage layer). */
		'--p10-gold'         => $gold,
		'--p10-ink'          => $ink,
		'--p10-ink-2'        => $colors['ink_2'],
		'--p10-ink-3'        => $colors['ink_3'],
		'--p10-cream'        => $cream,
		'--p10-panel'        => $panel,
		'--p10-gold-soft'    => $gold_soft,
		'--p10-gold-deep'    => $colors['gold_deep'],
		'--p10-muted'        => $muted,
		'--p10-muted-2'      => $colors['muted_2'],
		'--p10-line'         => $border ? $border : 'rgb(' . p10_hex_rgb( $gold ) . ' / 0.16)',
		'--p10-line-soft'    => 'rgb(' . p10_hex_rgb( $cream ) . ' / 0.09)',
		'--p10-gold-rgb'     => p10_hex_rgb( $gold ),
		'--p10-ink-rgb'      => p10_hex_rgb( $ink ),
		'--p10-cream-rgb'    => p10_hex_rgb( $cream ),
		'--p10-panel-rgb'    => p10_hex_rgb( $panel ),

		/* Semantic aliases. */
		'--p10-color-primary'    => $gold,
		'--p10-color-secondary'  => $colors['ink_3'],
		'--p10-color-accent'     => $gold_soft,
		'--p10-color-text'       => $cream,
		'--p10-color-muted'      => $muted,
		'--p10-color-background' => $ink,
		'--p10-color-surface'    => $panel,
		'--p10-color-border'     => $border ? $border : 'rgb(' . p10_hex_rgb( $gold ) . ' / 0.16)',
		'--p10-color-link'       => $link,
		'--p10-color-link-hover' => $hover,
		'--p10-color-focus'      => $focus,
		'--p10-color-button-bg'  => $btn_bg,
		'--p10-color-button-text' => $btn_text,
		'--p10-color-button-secondary-text'   => $btn2,
		'--p10-color-button-secondary-border' => $btn2_bd ? $btn2_bd : 'rgb(' . p10_hex_rgb( $gold ) . ' / 0.45)',
		'--p10-color-form-bg'     => $form_bg ? $form_bg : 'rgb(' . p10_hex_rgb( $ink ) . ' / 0.72)',
		'--p10-color-form-text'   => $form_tx,
		'--p10-color-form-border' => $form_bd ? $form_bd : 'rgb(' . p10_hex_rgb( $gold ) . ' / 0.28)',

		/* Typography and layout. */
		'--p10-display'        => '"' . $display_font . '","Times New Roman",Georgia,serif',
		'--p10-body'           => '"' . $body_font . '",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif',
		'--p10-type-scale'     => (string) $type_scale,
		'--p10-heading-weight' => $heading_weight,
		'--p10-heading-tracking' => $heading_track . 'em',
		'--p10-body-line'      => (string) $body_line,
		'--p10-shell'          => 'min(97vw,' . $shell_width . 'px)',
		'--p10-shell-wide'     => 'min(97vw,' . $shell_width . 'px)',
		'--p10-radius'         => $radius . 'px',
		'--p10-radius-lg'      => $card_radius . 'px',
		'--p10-section-scale'  => (string) ( $section_scale / 100 ),
		'--p10-section-pad'    => 'calc(clamp(4.5rem,10vw,9rem) * ' . ( $section_scale / 100 ) . ')',
		'--p10-section-pad-tight' => 'calc(clamp(3rem,6vw,5rem) * ' . ( $section_scale / 100 ) . ')',
		'--p10-grid-columns'   => (string) max( 1, min( 4, absint( p10_option( 'directory_columns', 3 ) ) ) ),
	);

	$footer = array(
		'--p10-footer-bg'     => p10_design_color( 'footer_bg_color', $ink ),
		'--p10-footer-text'   => p10_design_color( 'footer_text_color', $muted ),
		'--p10-footer-link'   => p10_design_color( 'footer_link_color', $cream ),
		'--p10-footer-accent' => p10_design_color( 'footer_accent_color', $gold ),
	);

	$overlay_color = sanitize_hex_color( (string) p10_option( 'hero_overlay_color', '' ) );
	$overlay_color = $overlay_color ? $overlay_color : $ink;

	$hero = array(
		'--p10-hero-overlay' => (string) ( max( 0, min( 95, absint( p10_option( 'hero_overlay', 62 ) ) ) ) / 100 ),
		'--p10-hero-overlay-color' => $overlay_color,
		'--p10-hero-overlay-rgb' => p10_hex_rgb( $overlay_color ),
		'--p10-hero-min-height' => max( 50, min( 100, absint( p10_option( 'hero_min_height', 100 ) ) ) ) . 'svh',
		'--p10-marquee-duration' => absint( p10_option( 'marquee_speed', 38 ) ) . 's',
	);

	$tokens = array_merge( $tokens, $footer, $hero );

	return apply_filters( 'p10_design_tokens', $tokens, $palette );
}

/**
 * Sanitized CSS custom-property declarations.
 *
 * @return string
 */
function p10_design_tokens_css() {
	$css = '';
	foreach ( p10_design_tokens() as $property => $value ) {
		if ( ! preg_match( '/^--p10-[a-z0-9-]+$/', $property ) ) {
			continue;
		}
		$value = trim( (string) $value );
		if ( '' === $value ) {
			continue;
		}
		// Declarations are built from validated hex colours, integers, font
		// names and calc()/rgb() fragments generated above. Strip delimiters
		// defensively so no additional declaration can be injected.
		$value = str_replace( array( ';', '}', '{' ), '', $value );
		$css  .= $property . ':' . $value . ';';
	}
	return ':root{' . $css . '}';
}
