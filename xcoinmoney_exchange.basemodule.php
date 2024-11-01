<?php
abstract class XCoinmoneyExchangeBasemodule{

  function updateExchangeRate() {

  }

  function validate_form($direction) {
    return array(
      'validate' => true,
      'error' => array()
    );
  }


  function get_transaction_fields($transaction_id) {
    global $wpdb;

    $query = "SELECT * FROM ". $wpdb->prefix ."xcm_transaction_details
     WHERE transaction_id=".$transaction_id;

    $fields = $wpdb->get_results($query);

    return $fields;
  }

}