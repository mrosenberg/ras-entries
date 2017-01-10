<?php


class RAS_GF_Prefills {



	public function __construct() {

		add_filter( 'gform_field_value_company_name',        array( $this, 'company_name_pre_populate'           ) );
		add_filter( 'gform_field_value_checkout_entry_ids',  array( $this, 'checkout_cart_entry_ids_prepopulate' ) );
		add_filter( 'gform_field_value_checkout_cart_total', array( $this, 'checkout_cart_total_pre_populate'    ) );
    add_filter( 'gform_field_value_checkout_do_paypal',  array( $this, 'checkout_cart_do_paypal_flag'        ) );
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


	private function open_entries() {
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

		return GFAPI::get_entries( 0, $filter );
	}



	public function checkout_cart_entry_ids_prepopulate() {
		$entries = $this->open_entries();
		$ids     = array();

		foreach( $entries as $entry ) {
			$ids[] = $entry[ 'id' ];
		}

		return implode( ',', $ids );
	}



  /**
   ** Check if user is student elligbile for reduced rate
   ** @private
   ** @return bool
  **/
  private function is_student() {
    $user = wp_get_current_user();
    $type = get_user_meta( $user->ID, 'registration_type', true );

    return 'student' === $type;
  }

	/**
	 ** Fill in the proper cart total before checkout
	 ** @public
	 ** @return string
	**/
	public function checkout_cart_total_pre_populate() {
    $is_student = $this->is_student();
		$entries    = $this->open_entries();
    $purchases  = array();

		foreach ( $entries as $entry ) {
			$purchases[] = $entry[6];
		}

    asort( $purchases );

    if( $is_student ) {

      $purchases = array_slice( $purchases, 3 );
    }

		$purchase_total  = array_reduce( $purchases, array( $this, 'purchase_total' ), 0 );

		return $purchase_total;
	}


  public function checkout_cart_do_paypal_flag() {
    $total = $this->checkout_cart_total_pre_populate();

    return $total > 0 ? 1 : 0;
  }
}


if( class_exists( 'GFAPI' ) ) {

	new RAS_GF_Prefills;
}
