<?php


/**
 * Adds RAS_Cart_Total_Widget widget.
 */
class RAS_Cart_Total_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'ras_car_total_widget', // Base ID
			esc_html__( 'Cart Total', 'text_domain' ), // Name
			array( 'description' => esc_html__( 'Displays the user&aposs cart total', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$user      = wp_get_current_user();		
		$purchases = array();
		$filter    = array();

		$filter[ 'status' ]        = 'active';
		$filter[ 'field_filters' ] = array(
			array( 
				'key'   => 'created_by', 
				'value' => $user->ID				
			),
			array( 
				'key'   => 7, 
				'value' => 'open'
			)			
		);

		$entries = GFAPI::get_entries( 0, $filter );

		foreach ( $entries as $entry ) {
			$purchases[] = $entry[6];
		}

		$total_purchases = count( $purchases );

		$purchase_total  = array_reduce( $purchases, array( $this, 'purchase_total' ), 0 );

		$html = $args['before_widget'];


		if ( ! empty( $instance['title'] ) ) {
			$html .= $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}


		$html .= sprintf( '<h3>$%s</h3>', number_format( $purchase_total, 2 ) );
		$html .= sprintf( '<p>Item Entries: %s</p>', $total_purchases );
		$html .= '<ul>';
		$html .= '<li><a href="/cart">edit</a></li>';
		$html .= '<li><a href="/checkout">checkout</a></li>';
		$html .= '</ul>';

		$html .= $args['after_widget'];

		echo $html;
	}



	private function purchase_total( $carry, $item ) {

		$carry += $item;
		return $carry;
	} 



	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'New title', 'text_domain' );
		?>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'text_domain' ); ?></label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

}


if( class_exists( 'GFAPI' ) ) {

	add_action( 'widgets_init', 'register_ras_cart_total_widget' );
}
function register_ras_cart_total_widget() {
	
    register_widget( 'RAS_Cart_Total_Widget' );
}
