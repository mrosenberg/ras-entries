<?php


class RAS_Post_Checkout_Processing {


	public function __construct() {

    apply_filters( 'wp_mail_content_type',  array( $this, 'email_content_type' )   );
    add_action( 'gform_after_submission_3', array( $this, 'close_entries' ), 10, 2 );
    add_action( 'gform_after_submission_3', array( $this, 'email_entries' ), 10, 2 );
		add_action( 'gform_paypal_post_ipn',    array( $this, 'process_order' ), 10, 4 );
	}


  public function email_content_type() {

    return 'text/html';
  }


  public function email_entries( $order_entry, $form ) {
    $order      = GFAPI::get_entry( $order_entry );
    $entries    = rgar( $order, 10               );
    $entry_ids  = explode( ',', $entries         );
    $first_name = rgar( $order, '2.3'            );
    $last_name  = rgar( $order, '2.6'            );
    $email      = rgar( $order, '4'              );
    $row        = '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s %s</td></tr>';
    $headers    = array(
      'From: Richmond Ad Club <webmaster@richmondadclub.com>',
      'Content-Type: text/html; charset=UTF-8'
    );


    $html  = file_get_contents( dirname(__FILE__) . '/email_template_parts/header.html' );
    $html .= '<table>';
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th>ID</th><th>Agency</th><th>Entry Title</th><th>Submitted By</th>';
    $html .= '</tr>';
    $html .= '<thead>';
    $html .= '<tbody>';

    foreach ( $entry_ids as $id ) :
      $entry  = GFAPI::get_entry( $id );

      $html .= sprintf(
        $row,
        $id,
        rgar( $entry, '1' ),
        rgar( $entry, '2' ),
        $first_name,
        $last_name
      );

    endforeach;

    $html .= '<tbody>';
    $html .= '</table>';
    $html .= file_get_contents( dirname(__FILE__) . '/email_template_parts/footer.html' );

    $sent = wp_mail(
      $email,
      'Your 2016 Richmond Show Submissions',
      $html,
      $headers
    );
  }


  public function close_entries( $order_entry, $form ) {
    $order     = GFAPI::get_entry( $order_entry );
    $entries   = rgar( $order, 10               );
    $entry_ids = explode( ',', $entries         );

    foreach ( $entry_ids as $id ) :

      $entry = GFAPI::get_entry( $id );

      GFAPI::update_entry_field( $entry['id'], 7, 'closed' );

    endforeach;
  }


	public function process_order( $paypal_ping, $order_entry, $cancel ) {
		$order      = GFAPI::get_entry( $order_entry );
		$entries    = rgar( $order, 10               );
		$entry_ids  = explode( ',', $entries         );
		$txn_id     = $paypal_ping[ 'txn_id'         ];
		$txn_status = $paypal_ping[ 'payment_status' ];
		$txn_date   = $paypal_ping[ 'payment_date'   ];


		foreach ( $entry_ids as $id ) :

			$entry = GFAPI::get_entry( $id );

			GFAPI::update_entry_field( $entry['id'], 'trasaction_id',  $txn_id     );
			GFAPI::update_entry_field( $entry['id'], 'payment_status', $txn_status );
			GFAPI::update_entry_field( $entry['id'], 'payment_date',   $txn_date   );

		endforeach;
	}

}


if( class_exists( 'GFAPI' ) ) {

	new RAS_Post_Checkout_Processing();
}
