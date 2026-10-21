<?php
/**
 * "For Advertisers" band.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( '\Elementor\Widget_Base' ) ) {
	exit;
}

/**
 * Advertisers band widget.
 */
class P10_Elementor_Advertisers extends P10_Elementor_Widget {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'p10-advertisers';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Perfect 10 · Advertisers Band', 'perfect10' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-image-box';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'ads',
			array(
				'label' => __( 'Advertisers Band', 'perfect10' ),
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
			'cta_1',
			array(
				'label'     => __( 'Primary button label', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'condition' => array( 'content_source' => 'custom' ),
			)
		);
		$this->add_control(
			'cta_1_url',
			array(
				'label'     => __( 'Primary button link', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::URL,
				'condition' => array( 'content_source' => 'custom' ),
			)
		);
		$this->add_control(
			'cta_2',
			array(
				'label'     => __( 'Secondary button label', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'condition' => array( 'content_source' => 'custom' ),
			)
		);
		$this->add_control(
			'cta_2_url',
			array(
				'label'     => __( 'Secondary button link', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::URL,
				'condition' => array( 'content_source' => 'custom' ),
			)
		);
		$this->add_control(
			'tags',
			array(
				'label'     => __( 'Small print', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'condition' => array( 'content_source' => 'custom' ),
			)
		);
		$this->add_control(
			'image',
			array(
				'label'     => __( 'Image', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::MEDIA,
				'condition' => array( 'content_source' => 'custom' ),
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
			get_template_part( 'template-parts/advertisers' );
			return;
		}

		$image = p10_el_url( isset( $s['image'] ) ? $s['image'] : array(), '' );
		$url_1 = p10_el_url( isset( $s['cta_1_url'] ) ? $s['cta_1_url'] : array(), p10_utility_url( 'register' ) );
		$url_2 = p10_el_url( isset( $s['cta_2_url'] ) ? $s['cta_2_url'] : array(), p10_utility_url( 'packages' ) );
		?>
		<section class="p10-section p10-section--panel">
			<div class="p10-shell">
				<div class="p10-ads">
					<div class="p10-ads__main p10-reveal">
						<?php p10_section_eyebrow( '', isset( $s['kicker'] ) ? $s['kicker'] : '' ); ?>
						<?php echo p10_get_heading_html( isset( $s['title'] ) ? $s['title'] : '', isset( $s['accent'] ) ? $s['accent'] : '', 'h2', 'p10-display-lg' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php if ( ! empty( $s['copy'] ) ) : ?>
							<p class="p10-lede"><?php echo esc_html( $s['copy'] ); ?></p>
						<?php endif; ?>
						<div class="p10-ads__actions">
							<?php if ( ! empty( $s['cta_1'] ) ) : ?>
								<a class="p10-btn p10-btn--gold" href="<?php echo esc_url( $url_1 ); ?>"><?php echo esc_html( $s['cta_1'] ); ?></a>
							<?php endif; ?>
							<?php if ( ! empty( $s['cta_2'] ) ) : ?>
								<a class="p10-btn p10-btn--ghost" href="<?php echo esc_url( $url_2 ); ?>"><?php echo esc_html( $s['cta_2'] ); ?></a>
							<?php endif; ?>
						</div>
						<?php if ( ! empty( $s['tags'] ) ) : ?>
							<p class="p10-ads__tags"><?php echo esc_html( $s['tags'] ); ?></p>
						<?php endif; ?>
					</div>
					<?php if ( $image ) : ?>
						<div class="p10-ads__media p10-reveal" data-delay="2">
							<img src="<?php echo esc_url( $image ); ?>" alt="" loading="lazy" decoding="async">
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php
	}
}
