<?php
/**
 * Homepage hero.
 *
 * Default mode renders the original PHP hero part, so the Elementor homepage
 * stays identical to the classic homepage. Custom mode exposes every content
 * field plus image/video background controls. The background can also be
 * overridden while keeping the Customizer copy, which avoids duplicating the
 * same headline into every Elementor document.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( '\Elementor\Widget_Base' ) ) {
	exit;
}

/**
 * Hero widget.
 */
class P10_Elementor_Hero extends P10_Elementor_Widget {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'p10-hero';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Perfect 10 · Hero', 'perfect10' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-banner';
	}

	/**
	 * Build background arguments from Elementor settings.
	 *
	 * @param array $s Settings.
	 * @return array
	 */
	protected function background_args( $s ) {
		if ( ! isset( $s['background_source'] ) || 'custom' !== $s['background_source'] ) {
			return array();
		}

		$type    = isset( $s['background_type'] ) ? $s['background_type'] : 'image';
		$video   = isset( $s['video'] ) ? p10_media_url( $s['video'], 'full' ) : '';
		$overlay = isset( $s['overlay']['size'] ) ? (float) $s['overlay']['size'] : null;

		return array(
			'type'          => 'video' === $type ? 'video' : 'image',
			'image'         => isset( $s['image'] ) ? p10_media_url( $s['image'], 'p10-hero' ) : '',
			'video_url'     => $video ? $video : ( isset( $s['video_url'] ) ? (string) $s['video_url'] : '' ),
			'poster'        => isset( $s['poster'] ) ? p10_media_url( $s['poster'], 'p10-hero' ) : '',
			'mobile_image'  => isset( $s['mobile_image'] ) ? p10_media_url( $s['mobile_image'], 'p10-hero' ) : '',
			'position'      => isset( $s['bg_position'] ) ? $s['bg_position'] : 'center 30%',
			'overlay_color' => isset( $s['overlay_color'] ) ? $s['overlay_color'] : '',
			'overlay'       => null === $overlay ? null : $overlay / 100,
			'align'         => isset( $s['align'] ) ? $s['align'] : 'left',
		);
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'hero_content',
			array(
				'label' => __( 'Hero Content', 'perfect10' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'content_source',
			array(
				'label'   => __( 'Content source', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'theme',
				'options' => array(
					'theme'  => __( 'Perfect 10 Customizer (single source of truth)', 'perfect10' ),
					'custom' => __( 'Override for this page', 'perfect10' ),
				),
			)
		);
		$this->add_control(
			'theme_note',
			array(
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => '<p class="p10-elementor-note">' . esc_html__( 'Heading, copy and buttons are inherited from Appearance → Customize → Perfect 10 → Hero. Switch “Content source” only when this page needs different copy.', 'perfect10' ) . '</p>',
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				'condition'       => array( 'content_source' => 'theme' ),
			)
		);

		$custom = array( 'content_source' => 'custom' );

		$this->add_control(
			'eyebrow',
			array(
				'label'     => __( 'Eyebrow', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => '',
				'condition' => $custom,
			)
		);
		$this->add_control(
			'title_before',
			array(
				'label'     => __( 'Headline', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => '',
				'condition' => $custom,
			)
		);
		$this->add_control(
			'title_accent',
			array(
				'label'     => __( 'Italic gold accent', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => '',
				'condition' => $custom,
			)
		);
		$this->add_control(
			'tagline',
			array(
				'label'       => __( 'Tagline', 'perfect10' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'description' => __( 'Separate phrases with the ◆ diamond to reproduce the original rhythm.', 'perfect10' ),
				'condition'   => $custom,
			)
		);
		$this->add_control(
			'copy',
			array(
				'label'     => __( 'Intro copy', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXTAREA,
				'rows'      => 3,
				'default'   => '',
				'condition' => $custom,
			)
		);
		$this->add_control(
			'cta_primary',
			array(
				'label'     => __( 'Primary button label', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => '',
				'condition' => $custom,
			)
		);
		$this->add_control(
			'cta_primary_url',
			array(
				'label'         => __( 'Primary button link', 'perfect10' ),
				'type'          => \Elementor\Controls_Manager::URL,
				'default'       => array(),
				'condition'     => $custom,
				'description'   => __( 'Leave blank to link to the directory.', 'perfect10' ),
			)
		);
		$this->add_control(
			'show_phone',
			array(
				'label'        => __( 'Show phone button', 'perfect10' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);
		$this->add_control(
			'cta_secondary',
			array(
				'label'     => __( 'Phone button prefix', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => '',
				'condition' => $custom,
			)
		);
		$this->add_control(
			'show_stats',
			array(
				'label'        => __( 'Show statistics', 'perfect10' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);
		$this->add_control(
			'scroll_label',
			array(
				'label'     => __( 'Scroll cue label', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => '',
				'condition' => $custom,
			)
		);
		$this->add_control(
			'align',
			array(
				'label'   => __( 'Text alignment', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'default' => 'left',
				'options' => array(
					'left'   => array(
						'title' => __( 'Left', 'perfect10' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'perfect10' ),
						'icon'  => 'eicon-text-align-center',
					),
				),
			)
		);

		$this->end_controls_section();

		/* Background ---------------------------------------------------- */
		$this->start_controls_section(
			'hero_background',
			array(
				'label' => __( 'Background', 'perfect10' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'background_source',
			array(
				'label'   => __( 'Background source', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'theme',
				'options' => array(
					'theme'  => __( 'Perfect 10 Customizer', 'perfect10' ),
					'custom' => __( 'Override for this page', 'perfect10' ),
				),
			)
		);
		$this->add_control(
			'background_type',
			array(
				'label'     => __( 'Background type', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'image',
				'options'   => array(
					'image' => __( 'Image', 'perfect10' ),
					'video' => __( 'Video', 'perfect10' ),
				),
				'condition' => array( 'background_source' => 'custom' ),
			)
		);
		$this->add_control(
			'image',
			array(
				'label'       => __( 'Background image', 'perfect10' ),
				'type'        => \Elementor\Controls_Manager::MEDIA,
				'default'     => array(),
				'description' => __( 'Also used as the poster/fallback for a video background.', 'perfect10' ),
				'condition'   => array( 'background_source' => 'custom' ),
			)
		);
		$this->add_control(
			'video',
			array(
				'label'       => __( 'Upload video (MP4/WebM)', 'perfect10' ),
				'type'        => \Elementor\Controls_Manager::MEDIA,
				'media_types' => array( 'video' ),
				'default'     => array(),
				'condition'   => array(
					'background_source' => 'custom',
					'background_type'   => 'video',
				),
			)
		);
		$this->add_control(
			'video_url',
			array(
				'label'       => __( 'Or external video URL', 'perfect10' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => 'https://example.com/film.mp4',
				'description' => __( 'Direct MP4/WebM, YouTube or Vimeo. YouTube/Vimeo load only after JavaScript confirms the visitor has not requested reduced motion.', 'perfect10' ),
				'condition'   => array(
					'background_source' => 'custom',
					'background_type'   => 'video',
				),
			)
		);
		$this->add_control(
			'poster',
			array(
				'label'     => __( 'Video poster image', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::MEDIA,
				'default'   => array(),
				'condition' => array(
					'background_source' => 'custom',
					'background_type'   => 'video',
				),
			)
		);
		$this->add_control(
			'mobile_image',
			array(
				'label'       => __( 'Mobile fallback image', 'perfect10' ),
				'type'        => \Elementor\Controls_Manager::MEDIA,
				'default'     => array(),
				'description' => __( 'Videos are not downloaded below 768px; this image is used instead.', 'perfect10' ),
				'condition'   => array( 'background_source' => 'custom' ),
			)
		);
		$this->add_control(
			'bg_position',
			array(
				'label'     => __( 'Background focus', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'center 30%',
				'options'   => array(
					'center 30%'   => __( 'Centre, slightly up', 'perfect10' ),
					'center center' => __( 'Centre', 'perfect10' ),
					'center top'   => __( 'Top', 'perfect10' ),
					'center bottom' => __( 'Bottom', 'perfect10' ),
					'left center'  => __( 'Left', 'perfect10' ),
					'right center' => __( 'Right', 'perfect10' ),
				),
				'condition' => array( 'background_source' => 'custom' ),
			)
		);
		$this->add_control(
			'overlay_color',
			array(
				'label'     => __( 'Overlay colour', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'condition' => array( 'background_source' => 'custom' ),
			)
		);
		$this->add_control(
			'overlay',
			array(
				'label'      => __( 'Overlay opacity (%)', 'perfect10' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( '%' ),
				'range'      => array(
					'%' => array(
						'min' => 0,
						'max' => 95,
					),
				),
				'default'    => array(
					'unit' => '%',
					'size' => 62,
				),
				'selectors'  => array(
					'{{WRAPPER}} .p10-hero' => '--p10-hero-overlay:calc({{SIZE}} / 100);',
				),
			)
		);
		$this->end_controls_section();

		/* Style --------------------------------------------------------- */
		$this->start_controls_section(
			'hero_style',
			array(
				'label' => __( 'Hero Style', 'perfect10' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'min_height',
			array(
				'label'      => __( 'Minimum height', 'perfect10' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'vh', 'px' ),
				'range'      => array(
					'vh' => array(
						'min' => 40,
						'max' => 100,
					),
					'px' => array(
						'min' => 400,
						'max' => 1100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .p10-hero' => 'min-height:{{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Headline colour', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .p10-hero__title' => 'color:{{VALUE}};',
				),
			)
		);
		$this->add_responsive_control(
			'content_max_width',
			array(
				'label'      => __( 'Content width', 'perfect10' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 480,
						'max' => 1400,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .p10-hero__inner' => 'max-width:{{SIZE}}px;',
				),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s          = $this->get_settings_for_display();
		$background = $this->background_args( $s );
		$align      = isset( $s['align'] ) ? (string) $s['align'] : 'left';

		if ( ! isset( $s['content_source'] ) || 'custom' !== $s['content_source'] ) {
			get_template_part(
				'template-parts/hero',
				null,
				array(
					'background' => $background,
					'align'      => $align,
				)
			);
			return;
		}

		$url = p10_el_url( isset( $s['cta_primary_url'] ) ? $s['cta_primary_url'] : array(), p10_directory_url() );

		if ( empty( $background ) ) {
			$background = p10_hero_background_settings();
		}

		$stats = $this->is_editor_preview() || p10_is_yes( isset( $s['show_stats'] ) ? $s['show_stats'] : '' ) ? p10_get_stats() : array();

		$style = '';
		if ( ! empty( $background['overlay_color'] ) ) {
			$style .= '--p10-hero-overlay-color:' . sanitize_hex_color( $background['overlay_color'] ) . ';';
		}
		if ( isset( $background['overlay'] ) && is_numeric( $background['overlay'] ) ) {
			$style .= '--p10-hero-overlay:' . max( 0, min( 1, (float) $background['overlay'] ) ) . ';';
		}
		?>
		<section class="p10-hero p10-hero--align-<?php echo esc_attr( 'center' === $align ? 'center' : 'left' ); ?>" data-p10-hero<?php echo $style ? ' style="' . esc_attr( $style ) . '"' : ''; ?>>
			<?php echo p10_hero_media_html( $background ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped media markup. ?>
			<div class="p10-hero__scrim"></div>

			<div class="p10-hero__inner">
				<?php if ( ! empty( $s['eyebrow'] ) ) : ?>
					<p class="p10-hero__eyebrow" data-p10-hero-eyebrow><?php echo esc_html( $s['eyebrow'] ); ?></p>
				<?php endif; ?>

				<h1 class="p10-hero__title">
					<span data-p10-hero-before><?php echo esc_html( isset( $s['title_before'] ) ? $s['title_before'] : '' ); ?></span>
					<?php if ( ! empty( $s['title_accent'] ) ) : ?>
						<em data-p10-hero-accent><?php echo esc_html( $s['title_accent'] ); ?></em>
					<?php endif; ?>
				</h1>

				<?php if ( ! empty( $s['tagline'] ) ) : ?>
					<?php $parts = array_values( array_filter( array_map( 'trim', preg_split( '/\s*◆\s*/u', (string) $s['tagline'] ) ) ) ); ?>
					<p class="p10-hero__tagline" data-p10-hero-tagline>
						<?php foreach ( $parts as $i => $part ) : ?>
							<?php if ( $i > 0 ) : ?><i aria-hidden="true">◆</i><?php endif; ?>
							<span><?php echo esc_html( $part ); ?></span>
						<?php endforeach; ?>
					</p>
				<?php endif; ?>

				<?php if ( ! empty( $s['copy'] ) ) : ?>
					<p class="p10-hero__copy"><?php echo esc_html( $s['copy'] ); ?></p>
				<?php endif; ?>

				<div class="p10-hero__cta">
					<?php if ( ! empty( $s['cta_primary'] ) ) : ?>
						<a class="p10-btn p10-btn--gold p10-btn--lg" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $s['cta_primary'] ); ?></a>
					<?php endif; ?>
					<?php if ( p10_phone_display() && ( ! isset( $s['show_phone'] ) || 'yes' === $s['show_phone'] ) ) : ?>
						<a class="p10-btn p10-btn--ghost p10-btn--lg" href="<?php echo esc_url( p10_phone_href() ); ?>">
							<?php
							$secondary = ! empty( $s['cta_secondary'] ) ? $s['cta_secondary'] . ' ' : '';
							echo esc_html( $secondary . p10_phone_display() );
							?>
						</a>
					<?php endif; ?>
				</div>

				<?php if ( $stats ) : ?>
					<div class="p10-hero__stats">
						<?php foreach ( $stats as $stat ) : ?>
							<div class="p10-stat">
								<span class="p10-stat__value"><?php echo esc_html( $stat['value'] ); ?></span>
								<span class="p10-stat__label"><?php echo esc_html( $stat['label'] ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $s['scroll_label'] ) ) : ?>
				<a class="p10-hero__cue" href="#p10-next" aria-label="<?php esc_attr_e( 'Scroll to content', 'perfect10' ); ?>">
					<span><?php echo esc_html( $s['scroll_label'] ); ?></span>
				</a>
			<?php endif; ?>
		</section>
		<?php
	}
}
