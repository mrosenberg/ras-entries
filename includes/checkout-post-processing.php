<?php


class RAS_Post_Checkout_Processing {



	public function __construct() {

		add_action( 'gform_paypal_fulfillment', array( $this, 'process_order' ), 10, 4 );
	}


	public function process_order( $paypal_entry, $feed, $transaction_id, $amount ) {
		$user     = wp_get_current_user();		
		$filter   = array();
	    $order_id = rgar( $paypal_entry, 'id' );


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

		foreach( $entries as $entry ) {
			$timestamp = new DateTime();

			GFAPI::update_entry_property( $entry['id'], 7, 'closed' );
			GFAPI::update_entry_property( $entry['id'], 'trasaction_id', $transaction_id );
			GFAPI::update_entry_property( $entry['id'], 'payment_status', 'Approved' );
			GFAPI::update_entry_property( $entry['id'], 'payment_date', $timestamp->format( 'c' ) );
		}
	}

}


if( class_exists( 'GFAPI' ) ) {

	new RAS_Post_Checkout_Processing();
}