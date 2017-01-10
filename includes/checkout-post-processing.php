<?php


class RAS_Post_Checkout_Processing {


	public function __construct() {

		add_action( 'gform_paypal_post_ipn', array( $this, 'process_order' ), 10, 4 );
	}


	public function process_order( $paypal_ping, $order_entry, $cancel ) {
		$user       = wp_get_current_user();		
		$order      = GFAPI::get_entry( $order_entry );
		$entries    = rgar( $order, 9 ); 
		$entry_ids  = explode( ',', $entries );
		$txn_id     = $paypal_ping[ 'txn_id' ];
		$txn_status = $paypal_ping[ 'payment_status' ];
		$txn_date   = $paypal_ping[ 'payment_date' ];


		foreach ( $entry_ids as $id ) :

			$entry = GFAPI::get_entry( $id );

			GFAPI::update_entry_property( $entry['id'], 7,                'closed'    );
			GFAPI::update_entry_property( $entry['id'], 'trasaction_id',  $txn_id     );
			GFAPI::update_entry_property( $entry['id'], 'payment_status', $txn_status );
			GFAPI::update_entry_property( $entry['id'], 'payment_date',   $txn_date   );	
					
		endforeach;
	}

}


if( class_exists( 'GFAPI' ) ) {

	new RAS_Post_Checkout_Processing();
}