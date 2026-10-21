<?php
/**
 * Statistics band.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( '\Elementor\Widget_Base' ) ) {
	exit;
}

/**
 * Stats widget.
 */
class P10_Elementor_Stats extends P10_Elementor_Widget {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'p10-stats';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Perfect 10 · Statistics', 'perfect10' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-counter';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'stats',
			array(
				'label' => __( 'Statistics', 'perfect10' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'source',
			array(
				'label'   => __( 'Content source', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'theme',
				'options' => array(
					'theme'  => __( 'Live WordPress counts (recommended)', 'perfect10' ),
					'custom' => __( 'Custom values', 'perfect10' ),
				),
				'description' => __( 'Live counts come from published profiles and the location tree, so the numbers cannot go stale.', 'perfect10' ),
			)
		);

		$repeater = new \Elementor\Repeater();
		$repeater->add_control(
			'value',
			array(
				'label' => __( 'Value', 'perfect10' ),
				'type'  => \Elementor\Controls_Manager::TEXT,
			)
		);
		$repeater->add_control(
			'label',
			array(
				'label' => __( 'Label', 'perfect10' ),
				'type'  => \Elementor\Controls_Manager::TEXT,
			)
		);
		$this->add_control(
			'items',
			array(
				'label'       => __( 'Statistics', 'perfect10' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ value }}} — {{{ label }}}',
				'condition'   => array( 'source' => 'custom' ),
			)
		);

		$this->add_control(
			'section_style',
			array(
				'label'   => __( 'Section treatment', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''           => __( 'Plain', 'perfect10' ),
					'panel'      => __( 'Panel background', 'perfect10' ),
					'gold-line'  => __( 'Gold hairline', 'perfect10' ),
					'panel-gold' => __( 'Panel + gold hairline', 'perfect10' ),
				),
			)
		);
		$this->end_controls_section();

		p10_register_spacing_controls( $this, '{{WRAPPER}} .p10-section' );
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();

		if ( ! isset( $s['source'] ) || 'custom' !== $s['source'] ) {
			$items = p10_get_stats();
		} else {
			$items = isset( $s['items'] ) ? (array) $s['items'] : array();
		}

		if ( ! $items ) {
			echo p10_empty_state( __( 'No statistics available yet.', 'perfect10' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			return;
		}

		$style = isset( $s['section_style'] ) ? $s['section_style'] : '';
		$class = 'p10-section p10-section--tight';
		if ( in_array( $style, array( 'panel', 'panel-gold' ), true ) ) {
			$class .= ' p10-section--panel';
		}
		if ( in_array( $style, array( 'gold-line', 'panel-gold' ), true ) ) {
			$class .= ' p10-section--gold-line';
		}
		?>
		<section class="<?php echo esc_attr( $class ); ?>">
			<div class="p10-shell">
				<div class="p10-el-stats">
					<?php foreach ( $items as $item ) : ?>
						<div class="p10-stat">
							<span class="p10-stat__value"><?php echo esc_html( $item['value'] ); ?></span>
							<span class="p10-stat__label"><?php echo esc_html( $item['label'] ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php
	}
}
