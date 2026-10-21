<?php
/**
 * Destination / city tiles.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( '\Elementor\Widget_Base' ) ) {
	exit;
}

/**
 * Cities widget.
 */
class P10_Elementor_Cities extends P10_Elementor_Widget {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'p10-cities';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Perfect 10 · Destinations', 'perfect10' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-map-pin';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->add_heading_controls( 'heading', array(), 'p10_heading', 'cities' );

		$this->start_controls_section(
			'query',
			array(
				'label' => __( 'Cities & Layout', 'perfect10' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'source',
			array(
				'label'   => __( 'Which cities', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'theme',
				'options' => array(
					'theme'  => __( 'Featured cities (theme order)', 'perfect10' ),
					'custom' => __( 'Choose below', 'perfect10' ),
				),
			)
		);
		$this->add_control(
			'county',
			array(
				'label'     => __( 'County', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'options'   => p10_term_choices( 'p10_county' ),
				'condition' => array( 'source' => 'custom' ),
			)
		);
		$this->add_control(
			'limit',
			array(
				'label'     => __( 'Number of tiles', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 48,
				'default'   => 9,
				'condition' => array( 'source' => 'custom' ),
			)
		);
		$this->add_control(
			'featured_only',
			array(
				'label'        => __( 'Featured cities only', 'perfect10' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'condition'    => array( 'source' => 'custom' ),
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
		$this->add_control(
			'card_layout',
			array(
				'label'   => __( 'Card layout', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'overlay',
				'options' => array(
					'overlay' => __( 'Image with overlaid copy', 'perfect10' ),
					'stacked' => __( 'Image above copy on a panel', 'perfect10' ),
				),
			)
		);
		$this->add_term_card_media_controls( 'p10_city' );
		$this->add_control(
			'anchor_id',
			array(
				'label' => __( 'Anchor ID', 'perfect10' ),
				'type'  => \Elementor\Controls_Manager::TEXT,
			)
		);
		p10_register_grid_controls( $this, '{{WRAPPER}} .p10-grid--cities' );
		$this->end_controls_section();

		$this->add_heading_style_controls();
		$this->add_card_style_controls( '{{WRAPPER}} .p10-city' );
		p10_register_spacing_controls( $this, '{{WRAPPER}} .p10-section' );
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();

		if ( ! isset( $s['source'] ) || 'custom' !== $s['source'] ) {
			$terms = p10_get_featured_cities( (int) p10_option( 'cities_count', 9 ) );
			$counties = p10_get_counties();
			$action = $counties ? p10_term_url( $counties[0] ) : p10_utility_url( 'cities' );
		} else {
			$county = isset( $s['county'] ) ? sanitize_title( $s['county'] ) : '';
			$terms  = p10_get_cities( $county );
			if ( ! empty( $s['featured_only'] ) && 'yes' === $s['featured_only'] ) {
				$featured = wp_list_pluck( p10_get_featured_cities( 48 ), 'term_id' );
				$terms    = array_filter( $terms, static function ( $term ) use ( $featured ) {
					return in_array( (int) $term->term_id, array_map( 'intval', $featured ), true );
				} );
			}
			$terms = array_slice( array_values( $terms ), 0, isset( $s['limit'] ) ? (int) $s['limit'] : 9 );
			if ( $county ) {
				$term = get_term_by( 'slug', $county, 'p10_county' );
				$action = $term ? p10_term_url( $term ) : p10_utility_url( 'cities' );
			} else {
				$action = p10_utility_url( 'cities' );
			}
		}

		$style  = isset( $s['section_style'] ) ? $s['section_style'] : '';
		$anchor = isset( $s['anchor_id'] ) ? sanitize_title( $s['anchor_id'] ) : '';
		$class  = 'p10-section';
		if ( in_array( $style, array( 'panel', 'panel-gold' ), true ) ) {
			$class .= ' p10-section--panel';
		}
		if ( in_array( $style, array( 'gold-line', 'panel-gold' ), true ) ) {
			$class .= ' p10-section--gold-line';
		}
		$heading = $this->get_heading_html( $s, 'heading', 'cities', $action );
		?>
		<section class="<?php echo esc_attr( $class ); ?>"<?php echo $anchor ? ' id="' . esc_attr( $anchor ) . '"' : ''; ?>>
			<div class="p10-shell">
				<?php echo $heading; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php if ( $terms ) : ?>
					<div class="p10-grid p10-grid--cities p10-dynamic-grid">
						<?php p10_output_term_cards( $terms, 'p10_city', ! p10_option( 'reduced_motion' ) && ! $this->is_editor_preview(), isset( $s['card_layout'] ) ? $s['card_layout'] : 'overlay', $this->term_card_args( $s ) ); ?>
					</div>
				<?php else : ?>
					<?php echo p10_empty_state( __( 'No destination cities found. Import the location tree or add cities under Profiles → Cities.', 'perfect10' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
