<?php
/**
 * Theme widgets.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base widget with shared update/render plumbing.
 */
abstract class P10_Widget_Base extends WP_Widget {

	/**
	 * Field schema for the widget admin form.
	 *
	 * @return array
	 */
	abstract protected function fields();

	/**
	 * Render the widget.
	 *
	 * @param array $args     Sidebar args.
	 * @param array $instance Widget instance.
	 */
	abstract public function render( $args, $instance );

	/**
	 * WP_Widget entry point — delegates to render().
	 *
	 * @param array $args     Sidebar args.
	 * @param array $instance Widget instance.
	 */
	public function widget( $args, $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->defaults() );
		$this->render( $args, $instance );
	}

	/**
	 * Default values.
	 *
	 * @return array
	 */
	protected function defaults() {
		$defaults = array();
		foreach ( $this->fields() as $key => $field ) {
			$defaults[ $key ] = isset( $field['default'] ) ? $field['default'] : '';
		}
		return $defaults;
	}

	/**
	 * Sanitise on save.
	 *
	 * @param array $new_instance New values.
	 * @param array $old_instance Old values.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		foreach ( $this->fields() as $key => $field ) {
			$type = isset( $field['type'] ) ? $field['type'] : 'text';
			if ( 'checkbox' === $type ) {
				$instance[ $key ] = ! empty( $new_instance[ $key ] ) ? 1 : 0;
			} elseif ( 'number' === $type ) {
				$instance[ $key ] = isset( $new_instance[ $key ] ) ? (int) $new_instance[ $key ] : 0;
			} else {
				$instance[ $key ] = isset( $new_instance[ $key ] ) ? sanitize_text_field( $new_instance[ $key ] ) : '';
			}
		}
		return $instance;
	}

	/**
	 * Render the admin form.
	 *
	 * @param array $instance Saved values.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->defaults() );

		foreach ( $this->fields() as $key => $field ) {
			$value = isset( $instance[ $key ] ) ? $instance[ $key ] : '';
			$id    = $this->get_field_id( $key );
			$name  = $this->get_field_name( $key );
			$type  = isset( $field['type'] ) ? $field['type'] : 'text';

			echo '<p>';

			if ( 'checkbox' === $type ) {
				printf(
					'<label for="%1$s"><input class="checkbox" type="checkbox" id="%1$s" name="%2$s" value="1"%3$s /> %4$s</label>',
					esc_attr( $id ),
					esc_attr( $name ),
					checked( (bool) $value, true, false ),
					esc_html( $field['label'] )
				);
			} else {
				printf(
					'<label for="%1$s">%2$s</label>',
					esc_attr( $id ),
					esc_html( $field['label'] )
				);
				printf(
					'<input class="widefat" id="%1$s" name="%2$s" type="%3$s" value="%4$s" />',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( 'number' === $type ? 'number' : 'text' ),
					esc_attr( $value )
				);
			}

			echo '</p>';
		}
	}
}

/**
 * Popular cities.
 */
class P10_Widget_Cities extends P10_Widget_Base {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'p10_cities',
			__( 'P10 — Cities', 'perfect10' ),
			array( 'description' => __( 'Lists city archives with profile counts.', 'perfect10' ) )
		);
	}

	/**
	 * Fields.
	 *
	 * @return array
	 */
	protected function fields() {
		return array(
			'title'  => array( 'label' => __( 'Title', 'perfect10' ), 'default' => __( 'Popular Cities', 'perfect10' ) ),
			'limit'  => array( 'label' => __( 'Number of cities', 'perfect10' ), 'type' => 'number', 'default' => 8 ),
			'county' => array( 'label' => __( 'County slug (blank = all)', 'perfect10' ), 'default' => '' ),
		);
	}

	/**
	 * Render.
	 *
	 * @param array $args     Sidebar args.
	 * @param array $instance Values.
	 */
	public function render( $args, $instance ) {
		$cities = $instance['county']
			? p10_get_cities( sanitize_title( $instance['county'] ) )
			: p10_get_featured_cities( 40 );

		$cities = array_slice( $cities, 0, max( 1, (int) $instance['limit'] ) );

		if ( empty( $cities ) ) {
			return;
		}

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '<ul class="p10-footer__list">';
		foreach ( $cities as $city ) {
			printf(
				'<li><a href="%1$s">%2$s <span style="color:var(--p10-muted-2);font-size:.75rem">(%3$s)</span></a></li>',
				esc_url( p10_term_url( $city ) ),
				esc_html( $city->name ),
				esc_html( number_format_i18n( $city->count ) )
			);
		}
		echo '</ul>';
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

/**
 * Profile types.
 */
class P10_Widget_Types extends P10_Widget_Base {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'p10_types',
			__( 'P10 — Profile Types', 'perfect10' ),
			array( 'description' => __( 'Lists the directory categories.', 'perfect10' ) )
		);
	}

	/**
	 * Fields.
	 *
	 * @return array
	 */
	protected function fields() {
		return array(
			'title' => array( 'label' => __( 'Title', 'perfect10' ), 'default' => __( 'Browse', 'perfect10' ) ),
		);
	}

	/**
	 * Render.
	 *
	 * @param array $args     Sidebar args.
	 * @param array $instance Values.
	 */
	public function render( $args, $instance ) {
		$categories = p10_get_categories();
		if ( empty( $categories ) ) {
			return;
		}

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '<ul class="p10-footer__list">';
		foreach ( $categories as $category ) {
			printf(
				'<li><a href="%1$s">%2$s</a></li>',
				esc_url( get_term_link( $category ) ),
				esc_html( $category->name )
			);
		}
		echo '</ul>';
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

/**
 * Call to action: phone plus reservation link.
 */
class P10_Widget_CTA extends P10_Widget_Base {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'p10_cta',
			__( 'P10 — Call To Action', 'perfect10' ),
			array( 'description' => __( 'Phone number and reservation button.', 'perfect10' ) )
		);
	}

	/**
	 * Fields.
	 *
	 * @return array
	 */
	protected function fields() {
		return array(
			'title' => array( 'label' => __( 'Heading', 'perfect10' ), 'default' => __( 'Book today', 'perfect10' ) ),
			'text'  => array( 'label' => __( 'Copy', 'perfect10' ), 'default' => '' ),
			'note'  => array( 'label' => __( 'Small print', 'perfect10' ), 'default' => '' ),
		);
	}

	/**
	 * Render.
	 *
	 * @param array $args     Sidebar args.
	 * @param array $instance Values.
	 */
	public function render( $args, $instance ) {
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		if ( ! empty( $instance['text'] ) ) {
			echo '<p style="color:var(--p10-muted);font-size:.875rem;line-height:1.7;margin-bottom:1.2rem">' . esc_html( $instance['text'] ) . '</p>';
		}
		if ( p10_phone_display() ) {
			echo '<a class="p10-btn p10-btn--gold p10-btn--block" href="' . esc_url( p10_phone_href() ) . '" style="margin-bottom:.6rem">' . esc_html( p10_phone_display() ) . '</a>';
		}
		echo '<a class="p10-btn p10-btn--ghost p10-btn--block" href="' . esc_url( p10_utility_url( 'booking' ) ) . '">' . esc_html__( 'Reservation Form', 'perfect10' ) . '</a>';

		if ( ! empty( $instance['note'] ) ) {
			echo '<p class="p10-form__hint" style="margin-top:1rem">' . esc_html( $instance['note'] ) . '</p>';
		}

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

/**
 * Latest profiles.
 */
class P10_Widget_Latest extends P10_Widget_Base {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'p10_latest',
			__( 'P10 — Latest Profiles', 'perfect10' ),
			array( 'description' => __( 'The most recently published profiles.', 'perfect10' ) )
		);
	}

	/**
	 * Fields.
	 *
	 * @return array
	 */
	protected function fields() {
		return array(
			'title' => array( 'label' => __( 'Title', 'perfect10' ), 'default' => __( 'Recently Added', 'perfect10' ) ),
			'limit' => array( 'label' => __( 'Number of profiles', 'perfect10' ), 'type' => 'number', 'default' => 4 ),
		);
	}

	/**
	 * Render.
	 *
	 * @param array $args     Sidebar args.
	 * @param array $instance Values.
	 */
	public function render( $args, $instance ) {
		$profiles = get_posts( array(
			'post_type'      => 'p10_profile',
			'post_status'    => 'publish',
			'posts_per_page' => max( 1, (int) $instance['limit'] ),
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		) );

		if ( empty( $profiles ) ) {
			return;
		}

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput
		}

		echo '<div class="p10-widget-profiles" style="display:grid;gap:.9rem">';
		foreach ( $profiles as $profile ) {
			printf(
				'<a href="%1$s" style="display:grid;grid-template-columns:52px 1fr;gap:.8rem;align-items:center">
					<img src="%2$s" alt="" style="width:52px;height:68px;object-fit:cover;border-radius:2px">
					<span>
						<span style="display:block;font-family:var(--p10-display);font-size:1.05rem">%3$s</span>
						<span style="display:block;font-size:.6875rem;letter-spacing:.1em;color:var(--p10-muted-2)">%4$s</span>
					</span>
				</a>',
				esc_url( get_permalink( $profile ) ),
				esc_url( p10_profile_image( $profile, 'p10-thumb' ) ),
				esc_html( get_the_title( $profile ) ),
				esc_html( p10_profile_location_line( $profile ) )
			);
		}
		echo '</div>';

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

/**
 * County / city quick search.
 */
class P10_Widget_Search extends P10_Widget_Base {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'p10_search',
			__( 'P10 — Location Search', 'perfect10' ),
			array( 'description' => __( 'Cascading county and city lookup.', 'perfect10' ) )
		);
	}

	/**
	 * Fields.
	 *
	 * @return array
	 */
	protected function fields() {
		return array(
			'title' => array( 'label' => __( 'Title', 'perfect10' ), 'default' => __( 'Where are you looking?', 'perfect10' ) ),
		);
	}

	/**
	 * Render.
	 *
	 * @param array $args     Sidebar args.
	 * @param array $instance Values.
	 */
	public function render( $args, $instance ) {
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput
		}

		$home     = trailingslashit( home_url( '/' ) );
		$template = $home . '__county__/__city__/';
		?>
		<form class="p10-form" method="get" action="<?php echo esc_url( p10_directory_url() ); ?>" data-p10-search-form data-url-template="<?php echo esc_attr( $template ); ?>" style="gap:.9rem">
			<div class="p10-form__field">
				<label class="p10-field__label" for="p10-w-county"><?php esc_html_e( 'County', 'perfect10' ); ?></label>
				<select class="p10-select" id="p10-w-county" name="county" data-p10-county>
					<option value=""><?php esc_html_e( 'All Counties', 'perfect10' ); ?></option>
					<?php foreach ( p10_get_counties() as $county ) : ?>
						<option value="<?php echo esc_attr( $county->slug ); ?>"><?php echo esc_html( $county->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="p10-form__field">
				<label class="p10-field__label" for="p10-w-city"><?php esc_html_e( 'City', 'perfect10' ); ?></label>
				<select class="p10-select" id="p10-w-city" name="city" data-p10-city>
					<option value=""><?php esc_html_e( 'All Cities', 'perfect10' ); ?></option>
					<?php foreach ( p10_get_cities() as $city ) : ?>
						<option value="<?php echo esc_attr( $city->slug ); ?>"><?php echo esc_html( $city->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<button class="p10-btn p10-btn--gold p10-btn--block" type="submit"><?php esc_html_e( 'Search', 'perfect10' ); ?></button>
		</form>
		<?php
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

/**
 * Register the widgets.
 */
function p10_register_widgets() {
	register_widget( 'P10_Widget_Cities' );
	register_widget( 'P10_Widget_Types' );
	register_widget( 'P10_Widget_CTA' );
	register_widget( 'P10_Widget_Latest' );
	register_widget( 'P10_Widget_Search' );
}
add_action( 'widgets_init', 'p10_register_widgets' );
