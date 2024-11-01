<?php
$plugin_prefix_root = plugin_dir_path( __FILE__ );
$plugin_prefix_filename = "{$plugin_prefix_root}/xcoinmoney_exchange.base.php";
include_once $plugin_prefix_filename;

class XCoinmoneyExchangeCallbac extends XCoinmoneyExchangeBase{
  function process($name){
    $paymentSystem = $this->get_payment_system_by_name($name);
    $paymentSystemObj = $this->get_object_payment_system($paymentSystem->id);

    $paymentSystemObj['object']->callback();
  }
}