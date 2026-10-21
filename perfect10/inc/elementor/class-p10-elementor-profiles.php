<?php
/**
 * Dynamic Perfect 10 profile grid.
 *
 * The original widget name (`p10-profiles`) is intentionally preserved so the
 * documents created by the previous theme version keep working. The control
 * surface and markup have been expanded to cover every directory query used by
 * the homepage and the archive templates.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( '\Elementor\Widget_Base' ) ) {
	exit;
}

/**
 * Profile grid widget.
 */
class P10_Elementor_Profiles extends P10_Elementor_Widget {

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'p10-profiles';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Perfect 10 · Profile Grid', 'perfect10' );
	}

	/**
	 * Panel icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-posts-grid';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'theme_section',
			array(
				'label' => __( 'Homepage Section', 'perfect10' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'theme_section',
			array(
				'label'       => __( 'Section preset', 'perfect10' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'custom',
				'options'     => array(
					'custom'  => __( 'Custom grid (use the controls below)', 'perfect10' ),
					'muses'   => __( '01 · The Featured Edit (theme data)', 'perfect10' ),
					'premium' => __( '02 · The Premium Collection (theme data)', 'perfect10' ),
				),
				'description' => __( 'The two presets read the section order, count, heading and action link from Appearance → Customize. No copy is duplicated into this page.', 'perfect10' ),
			)
		);
		$this->end_controls_section();

		$this->add_heading_controls(
			'heading',
			array(
				'show_heading' => 'yes',
				'title'        => '',
			),
			'p10_heading',
			''
		);

		p10_register_query_controls( $this );

		$this->start_controls_section(
			'p10_layout',
			array(
				'label' => __( 'Grid & Section', 'perfect10' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'section_style',
			array(
				'label'   => __( 'Section treatment', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''             => __( 'Plain', 'perfect10' ),
					'panel'        => __( 'Panel background', 'perfect10' ),
					'gold-line'    => __( 'Gold hairline', 'perfect10' ),
					'panel-gold'   => __( 'Panel + gold hairline', 'perfect10' ),
				),
			)
		);
		$this->add_control(
			'anchor_id',
			array(
				'label'       => __( 'Anchor ID', 'perfect10' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'description' => __( 'Optional #anchor for navigation. Letters, numbers, hyphens and underscores only.', 'perfect10' ),
			)
		);
		$this->add_control(
			'card_variant',
			array(
				'label'   => __( 'Card variant', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''        => __( 'Standard', 'perfect10' ),
					'premium' => __( 'Premium collection', 'perfect10' ),
				),
			)
		);
		p10_register_grid_controls( $this, '{{WRAPPER}} .p10-grid--profiles' );
		$this->end_controls_section();

		$this->add_heading_style_controls();
		$this->add_card_style_controls();
		p10_register_spacing_controls( $this, '{{WRAPPER}} .p10-section' );
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s       = $this->get_settings_for_display();
		$preset  = isset( $s['theme_section'] ) ? $s['theme_section'] : 'custom';
		$premium_quote = '';

		if ( 'muses' === $preset ) {
			$posts   = p10_get_featured_profiles( (int) p10_option( 'muses_count', 6 ) );
			$heading = $this->heading_is_visible( $s ) ? p10_theme_section_head( 'muses', p10_directory_url() ) : '';
			$variant = '';
			$class   = 'p10-section p10-section--panel p10-section--gold-line';
			$anchor  = isset( $s['anchor_id'] ) && $s['anchor_id'] ? sanitize_title( $s['anchor_id'] ) : 'p10-featured';
		} elseif ( 'premium' === $preset ) {
			$posts   = p10_get_premium_profiles( (int) p10_option( 'premium_count', 6 ) );
			$heading = $this->heading_is_visible( $s ) ? p10_theme_section_head( 'premium', p10_directory_url( array( 'premium' => 1 ) ) ) : '';
			$variant = 'premium';
			$class   = 'p10-section';
			$anchor  = isset( $s['anchor_id'] ) && $s['anchor_id'] ? sanitize_title( $s['anchor_id'] ) : 'p10-premium';
			if ( p10_option( 'premium_quote' ) ) {
				$premium_quote = (string) p10_option( 'premium_quote' );
			}
		} else {
			$filters = array(
				'limit'         => isset( $s['limit'] ) ? $s['limit'] : 6,
				'orderby'       => isset( $s['orderby'] ) ? $s['orderby'] : 'featured',
				'county'        => isset( $s['county'] ) ? $s['county'] : '',
				'city'          => isset( $s['city'] ) ? $s['city'] : '',
				'category'      => isset( $s['category'] ) ? $s['category'] : '',
				'nationality'   => isset( $s['nationality'] ) ? $s['nationality'] : '',
				'premium'       => isset( $s['premium'] ) ? $s['premium'] : '',
				'verified'      => isset( $s['verified'] ) ? $s['verified'] : '',
				'available'     => isset( $s['available'] ) ? $s['available'] : '',
				'featured_only' => isset( $s['featured_only'] ) ? $s['featured_only'] : '',
				'context'       => isset( $s['context'] ) ? $s['context'] : 'yes',
			);
			$query   = p10_query_profiles( $filters );
			$posts   = $query->posts;
			$style   = isset( $s['section_style'] ) ? $s['section_style'] : '';
			$anchor  = isset( $s['anchor_id'] ) ? sanitize_title( $s['anchor_id'] ) : '';
			$class   = 'p10-section';
			$heading = $this->get_heading_html( $s );
			$variant = isset( $s['card_variant'] ) ? $s['card_variant'] : '';
			if ( in_array( $style, array( 'panel', 'panel-gold' ), true ) ) {
				$class .= ' p10-section--panel';
			}
			if ( in_array( $style, array( 'gold-line', 'panel-gold' ), true ) ) {
				$class .= ' p10-section--gold-line';
			}
		}
		?>
		<section class="<?php echo esc_attr( $class ); ?>"<?php echo $anchor ? ' id="' . esc_attr( $anchor ) . '"' : ''; ?>>
			<div class="p10-shell">
				<?php echo $heading; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped template part. ?>
				<?php if ( $posts ) : ?>
					<div class="p10-grid p10-grid--profiles p10-dynamic-grid">
						<?php
						p10_output_profile_cards(
							$posts,
							$variant,
							! p10_option( 'reduced_motion' ) && ! $this->is_editor_preview()
						);
						?>
					</div>
				<?php else : ?>
					<?php echo p10_empty_state( __( 'No published profiles match these filters yet. Add profiles in WordPress → Profiles or relax the widget filters.', 'perfect10' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
