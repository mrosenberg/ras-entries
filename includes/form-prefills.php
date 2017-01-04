<?php


class RAS_GF_Prefills {



	public function __construct() {

		add_filter( 'gform_field_value_company_name', array( $this, 'company_name_pre_populate' ) );
		add_filter( 'gform_field_value_checkout_cart_total', array( $this, 'checkout_cart_total_pre_populate' ) );

	}


	/**
	 ** Prefill the company name from when the user registered
	 ** @public 
	 ** @return string
	**/ 
	public function company_name_pre_populate( $value ) {
		$user    = wp_get_current_user();
		$company = get_user_meta( $user->ID, 'company_name', true );

		return $company;
	}



	private function purchase_total( $carry, $item ) {

		$carry += $item;
		return $carry;
	} 


	/**
	 ** Fill in the proper cart total before checkout
	 ** @public 
	 ** @return string
	**/ 
	public function checkout_cart_total_pre_populate( $value ) {
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

		$purchase_total  = array_reduce( $purchases, array( $this, 'purchase_total' ), 0 );

		return $purchase_total;
	}	

}


if( class_exists( 'GFAPI' ) ) {

	new RAS_GF_Prefills;
}