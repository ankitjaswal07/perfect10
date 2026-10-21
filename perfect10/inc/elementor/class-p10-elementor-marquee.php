<?php
/**
 * Scrolling ticker band.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( '\Elementor\Widget_Base' ) ) {
	exit;
}

/**
 * Ticker widget.
 */
class P10_Elementor_Marquee extends P10_Elementor_Widget {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'p10-marquee';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Perfect 10 · Ticker', 'perfect10' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-animation-text';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'ticker',
			array(
				'label' => __( 'Ticker', 'perfect10' ),
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
					'theme'  => __( 'Perfect 10 Customizer', 'perfect10' ),
					'custom' => __( 'Override for this page', 'perfect10' ),
				),
			)
		);
		$this->add_control(
			'items',
			array(
				'label'       => __( 'Items', 'perfect10' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'rows'        => 4,
				'placeholder' => __( 'Independent Companions, Since 2010, 24/7 Booking Agent', 'perfect10' ),
				'condition'   => array( 'content_source' => 'custom' ),
			)
		);
		$this->add_control(
			'speed',
			array(
				'label'      => __( 'Animation duration (seconds)', 'perfect10' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 's' ),
				'range'      => array(
					's' => array(
						'min' => 12,
						'max' => 120,
					),
				),
				'default'    => array(
					'unit' => 's',
					'size' => 38,
				),
				'selectors'  => array(
					'{{WRAPPER}} .p10-marquee' => '--p10-marquee-duration:{{SIZE}}s;',
				),
			)
		);
		$this->add_control(
			'label_text',
			array(
				'label'       => __( 'Accessible label', 'perfect10' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'description' => __( 'The ticker is decorative and hidden from assistive technology by default. Add a label only if you need one.', 'perfect10' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'style',
			array(
				'label' => __( 'Ticker Style', 'perfect10' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'color',
			array(
				'label'     => __( 'Text colour', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .p10-marquee__item' => 'color:{{VALUE}};',
				),
			)
		);
		$this->add_control(
			'background',
			array(
				'label'     => __( 'Background', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .p10-marquee' => 'background-color:{{VALUE}};',
				),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();

		if ( ! isset( $s['content_source'] ) || 'custom' !== $s['content_source'] ) {
			get_template_part( 'template-parts/marquee' );
			return;
		}

		$items = p10_list_from_string( isset( $s['items'] ) ? $s['items'] : '' );
		if ( ! $items ) {
			return;
		}
		$label = isset( $s['label_text'] ) ? (string) $s['label_text'] : '';
		?>
		<div class="p10-marquee" data-p10-marquee <?php echo $label ? 'role="region" aria-label="' . esc_attr( $label ) . '"' : 'aria-hidden="true"'; ?>>
			<div class="p10-marquee__track">
				<div class="p10-marquee__group">
					<?php foreach ( $items as $item ) : ?>
						<span class="p10-marquee__item"><?php echo esc_html( $item ); ?></span>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
