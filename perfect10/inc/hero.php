<?php
/**
 * Hero media resolver.
 *
 * The original hero only rendered a single <img>. This module keeps that
 * markup and adds an opt-in image/video background with poster, mobile
 * fallback, focus position and overlay colour. It is shared by:
 *
 * - template-parts/hero.php (Customizer and the PHP homepage fallback)
 * - P10_Elementor_Hero (free Elementor)
 * - the Elementor editor preview
 *
 * Playback is progressive: the video source is only attached by main.js when
 * the viewport is wide enough, the visitor has not requested reduced motion,
 * and the hero is on screen. Without JavaScript the poster/image remains.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve a media control value (ID, URL, Elementor array) to a URL.
 *
 * @param mixed  $value Media value.
 * @param string $size  Image size for attachment IDs.
 * @return string
 */
function p10_media_url( $value, $size = 'full' ) {
	if ( is_array( $value ) ) {
		if ( ! empty( $value['url'] ) ) {
			return esc_url_raw( (string) $value['url'] );
		}
		$value = isset( $value['id'] ) ? $value['id'] : 0;
	}
	if ( is_numeric( $value ) && (int) $value > 0 ) {
		$url = wp_get_attachment_image_url( (int) $value, $size );
		return $url ? (string) $url : '';
	}
	if ( is_string( $value ) && '' !== $value ) {
		return esc_url_raw( $value );
	}
	return '';
}

/**
 * Resolve a media control value to an attachment ID.
 *
 * @param mixed $value Media value.
 * @return int
 */
function p10_media_id( $value ) {
	if ( is_array( $value ) ) {
		$value = isset( $value['id'] ) ? $value['id'] : 0;
	}
	return absint( $value );
}

/**
 * Ordered image metadata for responsive markup.
 *
 * @param int|string $value Attachment ID or URL.
 * @param string     $size  Image size.
 * @return array{url:string,id:int,width:int,height:int,srcset:string,alt:string}
 */
function p10_image_data( $value, $size = 'p10-hero' ) {
	$id      = p10_media_id( $value );
	$url     = p10_media_url( $value, $size );
	$data    = array(
		'url'    => $url,
		'id'     => $id,
		'width'  => 0,
		'height' => 0,
		'srcset' => '',
		'alt'    => '',
	);

	if ( $id ) {
		$meta = wp_get_attachment_image_src( $id, $size );
		if ( $meta ) {
			$data['url']    = (string) $meta[0];
			$data['width']  = (int) $meta[1];
			$data['height'] = (int) $meta[2];
		}
		$srcset = wp_get_attachment_image_srcset( $id, $size );
		if ( $srcset ) {
			$data['srcset'] = $srcset;
		}
		$alt = get_post_meta( $id, '_wp_attachment_image_alt', true );
		if ( $alt ) {
			$data['alt'] = (string) $alt;
		}
	}

	return $data;
}

/**
 * Classify a hero video URL.
 *
 * Direct files are rendered in a <video>; YouTube and Vimeo use a no-cookie
 * iframe that is only inserted by JavaScript. Anything else is rejected rather
 * than embedded as arbitrary HTML.
 *
 * @param string $url Video URL.
 * @return array{type:string,url:string,id:string}
 */
function p10_hero_video_source( $url ) {
	$url = trim( (string) $url );
	if ( ! $url ) {
		return array( 'type' => '', 'url' => '', 'id' => '' );
	}

	if ( ! preg_match( '~^https?://~i', $url ) ) {
		return array( 'type' => '', 'url' => '', 'id' => '' );
	}

	if ( preg_match( '~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~i', $url, $match ) ) {
		return array( 'type' => 'youtube', 'url' => $url, 'id' => $match[1] );
	}

	if ( preg_match( '~vimeo\.com/(?:video/)?(\d+)~i', $url, $match ) ) {
		return array( 'type' => 'vimeo', 'url' => $url, 'id' => $match[1] );
	}

	$path = (string) wp_parse_url( $url, PHP_URL_PATH );
	$ext  = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );

	if ( in_array( $ext, array( 'mp4', 'm4v', 'webm', 'ogv', 'ogg' ), true ) ) {
		return array( 'type' => 'file', 'url' => esc_url_raw( $url ), 'id' => '' );
	}

	return array( 'type' => '', 'url' => '', 'id' => '' );
}

/**
 * Normalise the Customizer hero background.
 *
 * @return array
 */
function p10_hero_background_settings() {
	$type        = (string) p10_option( 'hero_background_type', 'image' );
	$video_id    = absint( p10_option( 'hero_video_id', 0 ) );
	$video_file  = $video_id ? (string) wp_get_attachment_url( $video_id ) : '';
	$video_url   = $video_file ? $video_file : (string) p10_option( 'hero_video_url', '' );
	$image       = (int) p10_option( 'hero_image', 0 );
	$image_url   = $image ? (string) wp_get_attachment_image_url( $image, 'p10-hero' ) : '';
	$image_url   = $image_url ? $image_url : p10_hero_image();
	$poster      = (int) p10_option( 'hero_poster', 0 );
	$mobile      = (int) p10_option( 'hero_mobile_image', 0 );
	$position    = (string) p10_option( 'hero_bg_position', 'center 30%' );
	$overlay_col = (string) p10_option( 'hero_overlay_color', '' );

	$positions = array( 'center 30%', 'center center', 'center top', 'center bottom', 'left center', 'right center' );
	if ( ! in_array( $position, $positions, true ) ) {
		$position = 'center 30%';
	}

	return array(
		'type'          => 'video' === $type ? 'video' : 'image',
		'image'         => $image_url,
		'image_id'      => $image,
		'video_url'     => $video_url,
		'poster'        => $poster ? (string) wp_get_attachment_image_url( $poster, 'p10-hero' ) : $image_url,
		'poster_id'     => $poster,
		'mobile_image'  => $mobile ? (string) wp_get_attachment_image_url( $mobile, 'p10-hero' ) : '',
		'mobile_id'     => $mobile,
		'position'      => $position,
		'overlay_color' => $overlay_col ? sanitize_hex_color( $overlay_col ) : '',
		'align'         => 'center' === p10_option( 'hero_text_align', 'left' ) ? 'center' : 'left',
		'height'        => max( 50, min( 100, absint( p10_option( 'hero_min_height', 100 ) ) ) ),
	);
}

/**
 * Render the hero media layer.
 *
 * @param array $args Background arguments. Missing keys fall back to the
 *                    Customizer values.
 * @return string
 */
function p10_hero_media_html( $args = array() ) {
	$defaults = p10_hero_background_settings();
	$args     = wp_parse_args( (array) $args, $defaults );

	$type     = 'video' === ( $args['type'] ?? 'image' ) ? 'video' : 'image';
	$image    = p10_image_data( $args['image'] ?? '', 'p10-hero' );
	$mobile   = p10_image_data( $args['mobile_image'] ?? '', 'p10-hero' );
	$poster   = p10_image_data( $args['poster'] ?? ( $args['image'] ?? '' ), 'p10-hero' );
	$position = (string) ( $args['position'] ?? 'center 30%' );
	$video    = p10_hero_video_source( $args['video_url'] ?? '' );
	$overlay  = (string) ( $args['overlay_color'] ?? '' );
	$overlay  = $overlay ? sanitize_hex_color( $overlay ) : '';
	$style    = '--p10-hero-pos:' . esc_attr( $position ) . ';';
	if ( $overlay ) {
		$style .= '--p10-hero-overlay-color:' . esc_attr( $overlay ) . ';--p10-hero-overlay-rgb:' . esc_attr( p10_hex_rgb( $overlay ) ) . ';';
	}
	if ( isset( $args['overlay'] ) && is_numeric( $args['overlay'] ) ) {
		$style .= '--p10-hero-overlay:' . esc_attr( (string) max( 0, min( 1, (float) $args['overlay'] ) ) ) . ';';
	}

	$still = p10_option( 'hero_motion', 1 ) ? '' : ' p10-hero__img--still';

	$image_attrs = static function ( $data, $class, $fetchpriority = false ) {
		$attrs = ' class="' . esc_attr( $class ) . '" src="' . esc_url( $data['url'] ) . '" alt="" decoding="async"';
		if ( $data['srcset'] ) {
			$attrs .= ' srcset="' . esc_attr( $data['srcset'] ) . '" sizes="100vw"';
		}
		if ( $data['width'] && $data['height'] ) {
			$attrs .= ' width="' . esc_attr( $data['width'] ) . '" height="' . esc_attr( $data['height'] ) . '"';
		}
		if ( $fetchpriority ) {
			$attrs .= ' fetchpriority="high" loading="eager"';
		} else {
			$attrs .= ' loading="lazy"';
		}
		return $attrs;
	};

	ob_start();

	if ( 'video' === $type && $video['type'] ) {
		$poster_url = $poster['url'] ? $poster['url'] : $image['url'];
		echo '<div class="p10-hero__media p10-hero__media--video" data-p10-hero-media data-p10-video-type="' . esc_attr( $video['type'] ) . '" data-p10-video-src="' . esc_url( $video['url'] ) . '" data-p10-video-id="' . esc_attr( $video['id'] ) . '" style="' . $style . '">';

		if ( 'file' === $video['type'] ) {
			$ext  = strtolower( pathinfo( (string) wp_parse_url( $video['url'], PHP_URL_PATH ), PATHINFO_EXTENSION ) );
			$mime = in_array( $ext, array( 'webm' ), true ) ? 'video/webm' : ( in_array( $ext, array( 'ogv', 'ogg' ), true ) ? 'video/ogg' : 'video/mp4' );
			echo '<video class="p10-hero__video" muted loop playsinline preload="none" aria-hidden="true" tabindex="-1"';
			if ( $poster_url ) {
				echo ' poster="' . esc_url( $poster_url ) . '"';
			}
			echo ' data-p10-hero-video><source data-src="' . esc_url( $video['url'] ) . '" type="' . esc_attr( $mime ) . '"></video>';
		} else {
			// The iframe is inserted by main.js so no third-party request is
			// made for reduced-motion visitors, small screens or no-JS users.
			echo '<div class="p10-hero__embed" data-p10-hero-embed></div>';
		}

		// <picture> lets the browser fetch only the poster (desktop) or the
		// mobile fallback, never both.
		if ( $poster_url ) {
			if ( $mobile['url'] ) {
				echo '<picture>';
				echo '<source media="(max-width: 767px)" srcset="' . esc_url( $mobile['url'] ) . '">';
				echo '<img' . $image_attrs( $poster, 'p10-hero__img p10-hero__img--poster', true ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput -- built and escaped above.
				echo '</picture>';
			} else {
				echo '<img' . $image_attrs( $poster, 'p10-hero__img p10-hero__img--poster', true ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput -- built and escaped above.
			}
		} elseif ( $mobile['url'] ) {
			echo '<img' . $image_attrs( $mobile, 'p10-hero__img p10-hero__img--mobile' ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput -- built and escaped above.
		}
		echo '</div>';
	} else {
		echo '<div class="p10-hero__media" data-p10-hero-media style="' . $style . '">';
		if ( $mobile['url'] ) {
			echo '<picture>';
			echo '<source media="(max-width: 767px)" srcset="' . esc_url( $mobile['url'] ) . '">';
			echo '<img' . $image_attrs( $image, 'p10-hero__img' . $still, true ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput -- built and escaped above.
			echo '</picture>';
		} else {
			echo '<img' . $image_attrs( $image, 'p10-hero__img' . $still, true ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput -- built and escaped above.
		}
		echo '</div>';
	}

	return (string) ob_get_clean();
}

/**
 * Does the current homepage use a video background?
 *
 * @return bool
 */
function p10_hero_uses_video() {
	if ( 'video' !== p10_option( 'hero_background_type', 'image' ) ) {
		return false;
	}
	$video = p10_hero_video_source( (string) p10_option( 'hero_video_url', '' ) );
	if ( ! $video['type'] ) {
		$id = absint( p10_option( 'hero_video_id', 0 ) );
		if ( $id ) {
			$video = p10_hero_video_source( (string) wp_get_attachment_url( $id ) );
		}
	}
	return (bool) $video['type'];
}
