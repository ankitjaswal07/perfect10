<?php
/**
 * Profile-type tiles.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( '\Elementor\Widget_Base' ) ) {
	exit;
}

/**
 * Types widget.
 */
class P10_Elementor_Types extends P10_Elementor_Widget {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'p10-types';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Perfect 10 · Profile Types', 'perfect10' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->add_heading_controls( 'heading', array(), 'p10_heading', 'types' );

		$this->start_controls_section(
			'query',
			array(
				'label' => __( 'Types & Layout', 'perfect10' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'source',
			array(
				'label'   => __( 'Which types', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'theme',
				'options' => array(
					'theme'  => __( 'Theme order (all profile types)', 'perfect10' ),
					'custom' => __( 'Choose below', 'perfect10' ),
				),
			)
		);
		$this->add_control(
			'limit',
			array(
				'label'     => __( 'Number of tiles', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 24,
				'default'   => 6,
				'condition' => array( 'source' => 'custom' ),
			)
		);
		$this->add_control(
			'orderby',
			array(
				'label'     => __( 'Order by', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'count',
				'options'   => array(
					'count'      => __( 'Most profiles', 'perfect10' ),
					'name'       => __( 'Name', 'perfect10' ),
					'menu_order' => __( 'Theme order', 'perfect10' ),
					'include'    => __( 'Selected order', 'perfect10' ),
				),
				'condition' => array( 'source' => 'custom' ),
			)
		);
		$this->add_control(
			'include',
			array(
				'label'       => __( 'Only these types', 'perfect10' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => p10_term_choices( 'p10_category', false ),
				'condition'   => array( 'source' => 'custom' ),
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
		$this->add_term_card_media_controls( 'p10_category' );
		$this->add_control(
			'anchor_id',
			array(
				'label' => __( 'Anchor ID', 'perfect10' ),
				'type'  => \Elementor\Controls_Manager::TEXT,
			)
		);
		p10_register_grid_controls( $this, '{{WRAPPER}} .p10-grid--types' );
		$this->end_controls_section();

		$this->add_heading_style_controls();
		$this->add_card_style_controls( '{{WRAPPER}} .p10-type' );
		p10_register_spacing_controls( $this, '{{WRAPPER}} .p10-section' );
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();

		if ( ! isset( $s['source'] ) || 'custom' !== $s['source'] ) {
			$terms = array_slice( p10_get_categories(), 0, (int) p10_option( 'types_count', 6 ) );
			$action = p10_directory_url();
		} else {
			$args = array(
				'taxonomy'   => 'p10_category',
				'hide_empty' => false,
				'number'     => isset( $s['limit'] ) ? (int) $s['limit'] : 6,
			);
			$orderby = isset( $s['orderby'] ) ? $s['orderby'] : 'count';
			if ( 'name' === $orderby ) {
				$args['orderby'] = 'name';
				$args['order']   = 'ASC';
			} elseif ( 'menu_order' === $orderby ) {
				$args['orderby'] = 'meta_value_num';
				$args['meta_key'] = 'p10_order'; // phpcs:ignore WordPress.DB.SlowDBQuery
			} elseif ( 'include' === $orderby ) {
				$args['orderby'] = 'include';
			} else {
				$args['orderby'] = 'count';
				$args['order']   = 'DESC';
			}
			if ( ! empty( $s['include'] ) ) {
				$args['include'] = array_map( 'absint', (array) $s['include'] );
			}
			$terms = get_terms( $args );
			$terms = is_wp_error( $terms ) ? array() : $terms;
			$action = p10_directory_url();
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
		$heading = $this->get_heading_html( $s, 'heading', 'types', $action );
		?>
		<section class="<?php echo esc_attr( $class ); ?>"<?php echo $anchor ? ' id="' . esc_attr( $anchor ) . '"' : ''; ?>>
			<div class="p10-shell">
				<?php echo $heading; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php if ( $terms ) : ?>
					<div class="p10-grid p10-grid--types p10-dynamic-grid">
						<?php p10_output_term_cards( $terms, 'p10_category', ! p10_option( 'reduced_motion' ) && ! $this->is_editor_preview(), isset( $s['card_layout'] ) ? $s['card_layout'] : 'overlay', $this->term_card_args( $s ) ); ?>
					</div>
				<?php else : ?>
					<?php echo p10_empty_state( __( 'No profile types are published yet. Add them under Profiles → Profile Types.', 'perfect10' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
