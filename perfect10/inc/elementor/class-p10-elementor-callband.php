<?php
/**
 * "Requests within 24 hours?" call band.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( '\Elementor\Widget_Base' ) ) {
	exit;
}

/**
 * Call band widget.
 */
class P10_Elementor_Callband extends P10_Elementor_Widget {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'p10-callband';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Perfect 10 · Call Band', 'perfect10' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-call-to-action';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'band',
			array(
				'label' => __( 'Call Band', 'perfect10' ),
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
			'kicker',
			array(
				'label'     => __( 'Kicker', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'condition' => array( 'content_source' => 'custom' ),
			)
		);
		$this->add_control(
			'title',
			array(
				'label'     => __( 'Heading', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'condition' => array( 'content_source' => 'custom' ),
			)
		);
		$this->add_control(
			'accent',
			array(
				'label'     => __( 'Italic accent', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'condition' => array( 'content_source' => 'custom' ),
			)
		);
		$this->add_control(
			'copy',
			array(
				'label'     => __( 'Copy', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXTAREA,
				'rows'      => 3,
				'condition' => array( 'content_source' => 'custom' ),
			)
		);
		$this->add_control(
			'cta',
			array(
				'label'     => __( 'Button label', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => __( 'Reservation Form', 'perfect10' ),
				'condition' => array( 'content_source' => 'custom' ),
			)
		);
		$this->add_control(
			'cta_url',
			array(
				'label'     => __( 'Button link', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::URL,
				'condition' => array( 'content_source' => 'custom' ),
			)
		);
		$this->add_control(
			'note',
			array(
				'label'     => __( 'Small note', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'condition' => array( 'content_source' => 'custom' ),
			)
		);
		$this->add_control(
			'show_phone',
			array(
				'label'        => __( 'Show phone number', 'perfect10' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
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
			get_template_part( 'template-parts/callband' );
			return;
		}

		$url = p10_el_url( isset( $s['cta_url'] ) ? $s['cta_url'] : array(), p10_utility_url( 'booking' ) );
		?>
		<section class="p10-callband">
			<div class="p10-shell">
				<div class="p10-callband__inner">
					<div class="p10-callband__main p10-reveal">
						<?php p10_section_eyebrow( '', isset( $s['kicker'] ) ? $s['kicker'] : '' ); ?>
						<?php echo p10_get_heading_html( isset( $s['title'] ) ? $s['title'] : '', isset( $s['accent'] ) ? $s['accent'] : '', 'h2', 'p10-callband__title' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php if ( ! empty( $s['copy'] ) ) : ?>
							<p class="p10-callband__copy"><?php echo esc_html( $s['copy'] ); ?></p>
						<?php endif; ?>
					</div>
					<div class="p10-callband__actions p10-reveal" data-delay="2">
						<?php if ( p10_phone_display() && ( ! isset( $s['show_phone'] ) || 'yes' === $s['show_phone'] ) ) : ?>
							<a class="p10-callband__number" href="<?php echo esc_url( p10_phone_href() ); ?>"><?php echo esc_html( p10_phone_display() ); ?></a>
						<?php endif; ?>
						<a class="p10-btn p10-btn--ghost p10-btn--block" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( isset( $s['cta'] ) ? $s['cta'] : __( 'Reservation Form', 'perfect10' ) ); ?></a>
						<?php if ( ! empty( $s['note'] ) ) : ?>
							<p class="p10-callband__note"><?php echo esc_html( $s['note'] ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</section>
		<?php
	}
}
