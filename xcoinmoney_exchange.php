<?php
/*
Plugin Name: xCoinMoney Exchange
Version: 1.0
*/
if (!class_exists('XCoinmoneyExchange')) {
  class XCoinmoneyExchange {
    static function install() {
      $plugin_prefix_root = plugin_dir_path( __FILE__ );
      $plugin_prefix_filename = "{$plugin_prefix_root}/xcoinmoney_exchange.install.php";
      include_once $plugin_prefix_filename;
      xcoinmoney_exchange_install();
      xcoinmoney_exchange_install_data();
      exchange_rate_function();
    }
    static function uninstall() {
      $plugin_prefix_root = plugin_dir_path( __FILE__ );
      $plugin_prefix_filename = "{$plugin_prefix_root}/xcoinmoney_exchange.install.php";
      include_once $plugin_prefix_filename;
      xcoinmoney_exchange_uninstall();
    }
  }
  $xcoinmoneyexchange = new XCoinmoneyExchange();
}


if ( isset($xcoinmoneyexchange) && function_exists('register_activation_hook') ){

  register_activation_hook( __FILE__, array('xCoinmoneyExchange', 'install'));
  register_uninstall_hook( __FILE__, array('xCoinmoneyExchange', 'uninstall'));
  add_action( 'admin_menu', 'xcoinmoney_exchange_main_page', 1 );
  add_shortcode( 'xcoinmoney_exchange', 'xcoinmoney_exchange_entrypoint_shortcode' );
  add_shortcode( 'exchange_rates', 'xcoinmoney_exchange_ways_shortcode' );
  add_shortcode( 'available_balance', 'xcoinmoney_exchange_balance_shortcode' );

  if (!wp_next_scheduled('exchange_rate_hook')) {
    wp_schedule_event( time(), 'hourly', 'exchange_rate_hook' );
  }

  add_action( 'exchange_rate_hook', 'exchange_rate_function' );
}

function xcoinmoney_exchange_main_page() {
  add_menu_page( 'xCoinMoney Exchange', 'xCoinMoney Exchange', 8, 'xcoinmoney_exchange_dashboard', 'xcoinmoney_exchange_admin_menu_dashboard');
  add_submenu_page('xcoinmoney_exchange_dashboard', __('xCoinMoney Exchange Systems'), __('Systems'), 8, 'xcoinmoney_exchange_systems',  'xcoinmoney_exchange_admin_menu_systems');
  add_submenu_page('xcoinmoney_exchange_dashboard', __('xCoinMoney Exchange Ways'), __('Ways'), 8, 'xcoinmoney_exchange_ways',  'xcoinmoney_exchange_admin_menu_ways');
  add_submenu_page('xcoinmoney_exchange_dashboard', __('xCoinMoney Exchange Transactions'), __('Transactions'), 8, 'xcoinmoney_exchange_transactions',  'xcoinmoney_exchange_admin_menu_transactions');
}

function xcoinmoney_exchange_admin_menu_dashboard() {
  $plugin_prefix_root = plugin_dir_path( __FILE__ );
  $plugin_prefix_filename = "{$plugin_prefix_root}/admin/xcoinmoney_exchange.admin.php";
  include_once $plugin_prefix_filename;
  $adminObj = new XCoinmoneyExchangeAdmin();
  $adminObj->xcoinmoney_exchange_admin_dashboard();
}

function xcoinmoney_exchange_admin_menu_currencies() {
  $plugin_prefix_root = plugin_dir_path( __FILE__ );
  $plugin_prefix_filename = "{$plugin_prefix_root}/admin/xcoinmoney_exchange.admin.php";
  include_once $plugin_prefix_filename;
  $adminObj = new XCoinmoneyExchangeAdmin();
  $adminObj->xcoinmoney_exchange_admin_currencies();
}

function xcoinmoney_exchange_admin_menu_systems() {
  $plugin_prefix_root = plugin_dir_path( __FILE__ );
  $plugin_prefix_filename = "{$plugin_prefix_root}/admin/xcoinmoney_exchange.admin.php";
  include_once $plugin_prefix_filename;
  $adminObj = new XCoinmoneyExchangeAdmin();
  $adminObj->xcoinmoney_exchange_admin_systems();
}

function xcoinmoney_exchange_admin_menu_ways() {
  $plugin_prefix_root = plugin_dir_path( __FILE__ );
  $plugin_prefix_filename = "{$plugin_prefix_root}/admin/xcoinmoney_exchange.admin.php";
  include_once $plugin_prefix_filename;
  $adminObj = new XCoinmoneyExchangeAdmin();
  $adminObj->xcoinmoney_exchange_admin_ways();
}

function xcoinmoney_exchange_admin_menu_transactions() {
  $plugin_prefix_root = plugin_dir_path( __FILE__ );
  $plugin_prefix_filename = "{$plugin_prefix_root}/admin/xcoinmoney_exchange.admin.php";
  include_once $plugin_prefix_filename;
  $adminObj = new XCoinmoneyExchangeAdmin();
  $adminObj->xcoinmoney_exchange_admin_transactions();
}

function xcoinmoney_exchange_entrypoint_shortcode(){
  $plugin_prefix_root = plugin_dir_path( __FILE__ );
  $plugin_prefix_filename = "{$plugin_prefix_root}/xcoinmoney_exchange.forms.php";
  include_once $plugin_prefix_filename;

  $cbfObj = new XCoinmoneyExchangeForms();
  $cbfObj->xcoinmoney_exchange_main_form();
}

function xcoinmoney_exchange_ways_shortcode(){

  $plugin_prefix_root = plugin_dir_path( __FILE__ );
  $plugin_prefix_filename = "{$plugin_prefix_root}/xcoinmoney_exchange.forms.php";
  include_once $plugin_prefix_filename;
  $cbfObj = new XCoinmoneyExchangeForms();
  $cbfObj->xcoinmoney_exchange_ways_shortcode_form();
}

function xcoinmoney_exchange_balance_shortcode(){
  $plugin_prefix_root = plugin_dir_path( __FILE__ );
  $plugin_prefix_filename = "{$plugin_prefix_root}/xcoinmoney_exchange.forms.php";
  include_once $plugin_prefix_filename;
  $cbfObj = new XCoinmoneyExchangeForms();
  $cbfObj->xcoinmoney_exchange_balance_shortcode_form();
}

function include_all_modules() {
  $plugin_prefix_root = plugin_dir_path( __FILE__ );
  $modelesDir =  "{$plugin_prefix_root}/modules/";
  $catalog = opendir($modelesDir);

  while ($filename = readdir($catalog ))
  {
    $filename = $modelesDir.$filename;
    if(is_file($filename)) include_once($filename);
  }

  closedir($catalog);
}

function exchange_rate_function() {
  $plugin_prefix_root = plugin_dir_path( __FILE__ );
  $plugin_prefix_filename = "{$plugin_prefix_root}/xcoinmoney_exchange.rates.php";
  include_once $plugin_prefix_filename;
  $xcoinmoney_exchange_rates_object = new XCoinmoneyExchangeRates();
  $xcoinmoney_exchange_rates_object->process();
}


function xcoinmoney_invoice_callback(){
  if(isset($_GET['callback'])) {
    $paymentSystemName = $_GET['callback'];
    $plugin_prefix_root = plugin_dir_path( __FILE__ );
    $plugin_prefix_filename = "{$plugin_prefix_root}/xcoinmoney_exchange.callback.php";
    include_once $plugin_prefix_filename;
    $xcoinmoney_exchange_callback_object = new XCoinmoneyExchangeCallbac();
    $xcoinmoney_exchange_callback_object->process($paymentSystemName);
  }
}
add_action('init', 'xcoinmoney_invoice_callback');


