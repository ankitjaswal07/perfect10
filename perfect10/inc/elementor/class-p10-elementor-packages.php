<?php
/**
 * Premium package cards.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( '\Elementor\Widget_Base' ) ) {
	exit;
}

/**
 * Packages widget.
 */
class P10_Elementor_Packages extends P10_Elementor_Widget {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'p10-packages';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Perfect 10 · Packages', 'perfect10' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-price-table';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->add_heading_controls(
			'heading',
			array(
				'kicker' => __( 'Exclusive Placement', 'perfect10' ),
				'title'  => __( 'Premium', 'perfect10' ),
				'accent' => __( 'Packages', 'perfect10' ),
				'action' => __( 'Compare placement', 'perfect10' ),
			)
		);

		$this->start_controls_section(
			'query',
			array(
				'label' => __( 'Packages & Layout', 'perfect10' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'limit',
			array(
				'label'   => __( 'Number of packages', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 12,
				'default' => 3,
			)
		);
		$this->add_control(
			'section_style',
			array(
				'label'   => __( 'Section treatment', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'panel',
				'options' => array(
					''           => __( 'Plain', 'perfect10' ),
					'panel'      => __( 'Panel background', 'perfect10' ),
					'gold-line'  => __( 'Gold hairline', 'perfect10' ),
					'panel-gold' => __( 'Panel + gold hairline', 'perfect10' ),
				),
			)
		);
		$this->add_control(
			'anchor_id',
			array(
				'label' => __( 'Anchor ID', 'perfect10' ),
				'type'  => \Elementor\Controls_Manager::TEXT,
			)
		);
		p10_register_grid_controls( $this, '{{WRAPPER}} .p10-grid--packages' );
		$this->end_controls_section();

		$this->add_heading_style_controls();
		p10_register_spacing_controls( $this, '{{WRAPPER}} .p10-section' );
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s        = $this->get_settings_for_display();
		$limit    = isset( $s['limit'] ) ? max( 1, min( 12, (int) $s['limit'] ) ) : 3;
		$packages = array_slice( p10_get_packages(), 0, $limit );
		$style    = isset( $s['section_style'] ) ? $s['section_style'] : '';
		$anchor   = isset( $s['anchor_id'] ) ? sanitize_title( $s['anchor_id'] ) : '';
		$class    = 'p10-section';
		if ( in_array( $style, array( 'panel', 'panel-gold' ), true ) ) {
			$class .= ' p10-section--panel';
		}
		if ( in_array( $style, array( 'gold-line', 'panel-gold' ), true ) ) {
			$class .= ' p10-section--gold-line';
		}
		$heading = $this->get_heading_html( $s, 'heading', '', p10_utility_url( 'packages' ) );
		?>
		<section class="<?php echo esc_attr( $class ); ?>"<?php echo $anchor ? ' id="' . esc_attr( $anchor ) . '"' : ''; ?>>
			<div class="p10-shell">
				<?php echo $heading; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php if ( $packages ) : ?>
					<div class="p10-grid p10-grid--packages p10-dynamic-grid">
						<?php foreach ( $packages as $package ) : ?>
							<?php get_template_part( 'template-parts/card-package', null, array( 'package' => $package ) ); ?>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<?php echo p10_empty_state( __( 'No packages published yet. Add them under Perfect 10 → Packages.', 'perfect10' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
