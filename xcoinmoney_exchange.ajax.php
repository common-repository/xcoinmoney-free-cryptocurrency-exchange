<?php
if(isset($_POST['action'])){
  require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
  $plugin_prefix_root = plugin_dir_path( __FILE__ );
  $action = $_POST['action'];

  switch($action) {
    case 'get_ways' :
      $plugin_prefix_filename = "{$plugin_prefix_root}/xcoinmoney_exchange.forms.php";
      include_once $plugin_prefix_filename;
      $cbfObj = new XCoinmoneyExchangeForms();
      $ways = $cbfObj->xcoinmoney_exchange_get_ways_by_select($_POST['pid']);
      echo json_encode($ways);
      die();
  }
}