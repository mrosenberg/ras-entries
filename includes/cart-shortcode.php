<?php

class RAS_Cart_Shortcode {


	static function init() {


		add_shortcode( 'ras_cart', array( __CLASS__, 'ras_do_cart' ) );
	}


	public function ras_do_cart() {
		$user      = wp_get_current_user();		
		$purchases = array();
		$filter    = array();

		if( isset( $_REQUEST['action'] ) && 'remove' === $_REQUEST['action'] ) {
			return self::remove_entry();
		}

		$filter[ 'status' ]          = 'active';
		$filter[ 'field_filters' ][] = array(
			'key'   => 'created_by', 
			'value' => $user->ID				
		);
		$filter[ 'field_filters' ][] = array(
			'key'   => 7, 
			'value' => 'open'		
		);		

		$entries = GFAPI::get_entries( 0, $filter );

		if( $entries ) {

			$html  = '<table>';
			$html .= '<thead>';
			$html .= '<tr>';
			$html .= '<th>Name</th>';
			$html .= '<th>Description</th>';
			$html .= '<th>Category</th>';
			$html .= '<th>Price</th>';
			$html .= '<th>Remove</th>';
			$html .= '</tr>';
			$html .= '</thead>';
			$html .= '<tbody>';


			foreach( $entries as $entry ) {
				$category = explode( '|', $entry[5] );
				$price    = number_format( $entry[6], 2 );
				$button   = self::entry_removal_button( $entry['id'] );

				$html .= '<tr>';
				$html .= sprintf( '<td>%s</td>', $entry[2] );
				$html .= sprintf( '<td>%s</td>', $entry[3] );
				$html .= sprintf( '<td>%s</td>',  $category[0] );
				$html .= sprintf( '<td>$%s</td>', $price );
				$html .= sprintf( '<td>%s</td>', $button );
				$html .= '</tr>';

			}			

			$html .= '<tr colspan="5">';
			$html .= '<td><a href="/add-entry">add entry</a>';			
			$html .= '<td><a href="/checkout">checkout</a>';
			$html .= '</tr>';

			$html .= '</tbody>';
			$html .= '</table>';
		}
		else {

			$html = '<h1>Your cart is empty</h1>';
		}

		return $html;
	}


	private static function entry_removal_button( $entry_id ) {

		$html  = '<form method="POST" action="?action=remove">';
		$html .= '<input type="hidden" name="entry_id" value="'.$entry_id.'">';
		$html .= wp_nonce_field( 'delete_entry', 'delete_entry_nonce', true, false );
		$html .= '<input type="submit" value="X">';
		$html .= '</form>';

		return $html;
	}


	private static function remove_entry() {
		$redirect = $_REQUEST[ '_wp_http_referer' ];
		$entry_id = $_REQUEST[ 'entry_id' ];


		if ( 
		    ! isset( $_POST['delete_entry_nonce'] ) 
		    || ! wp_verify_nonce( $_POST['delete_entry_nonce'], 'delete_entry' ) 
		) {

		   print 'Sorry, you&apos;re not authorized to delete content entries.';
		   exit;

		} else {

			GFAPI::update_entry_property( $entry_id, 'status', 'trash' );

			wp_redirect( $redirect );

			exit;			
		}	
	}
}

if( class_exists( 'GFAPI' ) ) {

	RAS_Cart_Shortcode::init();	
}