<?php
$plugin_prefix_root = plugin_dir_path( __FILE__ );
$plugin_prefix_filename = "{$plugin_prefix_root}/xcoinmoney_exchange.base.php";
include_once $plugin_prefix_filename;

class XCoinmoneyExchangeForms extends  XCoinmoneyExchangeBase {
   private $_api;

   function __construct() {
      require_once('xcoinmoneyapi.php');
      $this->_api = $this->get_api();
    }

   function xcoinmoney_exchange_main_form() {
     global $wpdb;
     $viewForm = true;
     $data = array();
     $error = array();
     if($_POST) {
       if($this->check_balance()) {
         if($_POST['step'] == 1 && !empty($_POST['amount_to'])
           && !empty($_POST['system_from']) && !empty($_POST['system_to'])){
           $viewForm = false;
           $this->xcoinmoney_exchange_main_form_step2();
         }elseif($_POST['step'] == 2){
           $viewForm = false;
           $this->xcoinmoney_exchange_main_form_step2();
         }elseif($_POST['step'] == 3){
           $viewForm = false;
           $this->xcoinmoney_exchange_main_form_step3();
         }

       }else{
         $error[] = 'The amount you want to get too much';
       }

     }

     if($viewForm){
       include_once $this->get_template_dir()."xcoinmoney_exchange_main.php";

       $query = "SELECT DISTINCT ew.system_from as id, es.label
        FROM ". $wpdb->prefix ."xcm_exchange_ways ew
        LEFT JOIN ". $wpdb->prefix ."xcm_exchange_systems es ON es.id = ew.system_from
        WHERE es.enable_in=1";

       $allSystemsIn = $wpdb->get_results($query);
       $data['systemsIn'] = $allSystemsIn;
       $data['error'] = $error;
       render_main_form($data);
     }
    }

   function xcoinmoney_exchange_main_form_step2() {

     global $wpdb;
     $data = array();
     $viewForm = true;
     $errorForm = array();

     if(!isset($_POST)) {
       return false;
     }

     $objectFrom  = $this->get_object_payment_system($_POST['system_from']);
     $objectTo  = $this->get_object_payment_system($_POST['system_to']);

     include_once $this->get_template_dir()."xcoinmoney_exchange_main_step2.php";

     if($_POST['step'] == 1){
       $viewForm = true;
     }
     if($_POST['step'] == 2){
       $viewForm = false;

       $validRequest = $this->validate_request();
       if (!$validRequest) {
         $viewForm = false;
         $errorForm[] = 'Unable to perform operation!';
       }

       $validateFrom = $objectFrom['object']->validate_form('from');
       $validateTo = $objectTo['object']->validate_form('to');

       if ( !is_user_logged_in() && get_option('xcoinmoney_exchange_enable_user_register')) {
         $viewForm = false;
         $error_form[] = 'You need to register!';
       }

       if($validateFrom['validate'] && $validateTo['validate'] && $this->validate_request() && !$viewForm){

         $this->xcoinmoney_exchange_main_form_step3();


       }else{
         $viewForm = true;

         if(!empty($validateFrom['error'])){
           $data['error_form_from'] = $validateFrom['error'];
         }

         if(!empty($validateTo['error'])){
           $data['error_form_to'] = $validateTo['error'];
         }
       }
     }


     if($viewForm){
       $data['amount_to'] = $_POST['amount_to'];

       if(isset($objectFrom)){
         $data['form_from'] = $objectFrom['object']->form_step2_from($objectFrom['result']);
       }
       if(isset($objectTo)){
         $data['form_to'] = $objectTo['object']->form_step2_to($objectTo['result']);
       }

       $data['error_form'] = $errorForm;
       render_main_form_step2($data);
     }
   }

   function xcoinmoney_exchange_main_form_step3() {
     $data = array();
     $viewForm = true;
     $errorForm = array();

     if(!isset($_POST)) {
       return false;
     }

     $objectFrom  = $this->get_object_payment_system($_POST['system_from']);
     $objectTo  = $this->get_object_payment_system($_POST['system_to']);

     $dataViewFormObjectFrom = $objectFrom['object']->render_process_details_from_system();
     $dataViewFormObjectTo = $objectTo['object']->render_process_details_to_system();

     if($_POST['step'] == 3) {

       $viewForm = false;

       $validRequest = $this->validate_request();
       if (!$validRequest) {
         $viewForm = true;
         $errorForm[] = 'Unable to perform operation!';
       }

       $validateFrom = $objectFrom['object']->validate_form('from');
       $validateTo = $objectTo['object']->validate_form('to');

       if ( !is_user_logged_in() && get_option('xcoinmoney_exchange_enable_user_register')) {
         $viewForm = true;
         $errorForm[] = 'You need to register!';
       }

       if($validateFrom['validate'] && $validateTo['validate'] && !$viewForm){
         $objectFrom  = $this->get_object_payment_system($_POST['system_from']);

         $post = $_POST;
         $post['uuid'] = $this->generateUniqueUUID('transactions');

         //start process
         $responce = $objectFrom['object']->process_from($post);

         if($responce['status'] == 'success'){
           $transactionId = $this->create_transaction($post);
           $this->set_transaction_from($transactionId, $responce['transaction_from']);

           if (isset($responce['action'])) {
             if ($responce['action'] == 'redirect') {
               $this->redirect($responce['url']);
             }
           }

           $transaction = $this->get_transaction_details($transactionId);

           $data['fields_from'] = $dataViewFormObjectFrom;
           $data['fields_to'] = $dataViewFormObjectTo;

           $transaction = (array)$transaction;
           $transaction['amount_from'] = $this->round_amount($transaction['amount_from'],$transaction['$transaction->currency_name_from']);
           $transaction['amount_to'] = $this->round_amount($transaction['amount_to'],$transaction['$transaction->currency_name_to']);
           $transaction['profit'] = $this->round_amount($transaction['profit'],$transaction['$transaction->currency_name_from']);

           $data['transaction'] = $transaction;

           include_once $this->get_template_dir()."xcoinmoney_exchange_main_thank_you.php";
           render_main_form_thank_you($data);
         } else {
           $viewForm = true;
           $errorForm = array_merge($errorForm, $responce['result']);
         }
       }
     }


     if($viewForm){
       $data['form_data'] = $_POST;
       unset($data['form_data']['step']);
       unset($data['form_data']['submit']);

       $rate = $this->get_rate($_POST['system_from'], $_POST['system_to']);
       $fee = $this->get_fee($_POST['system_from'], $_POST['system_to'], $_POST['amount_to']/$rate);
       $paymentSystemFrom = $this->get_payment_system($_POST['system_from']);
       $paymentSystemTo = $this->get_payment_system($_POST['system_to']);

       $data['system_from_name'] = $paymentSystemFrom->label;
       $data['system_to_name'] = $paymentSystemTo->label;
       $data['exchange_rate'] = $rate;
       $data['fee'] = $this->round_amount($fee, $paymentSystemFrom->currency_name);
       $data['amount_from'] = $this->round_amount($_POST['amount_to']/$rate+$fee, $paymentSystemFrom->currency_name);
       $data['amount_to'] = $this->round_amount($_POST['amount_to'], $paymentSystemTo->currency_name);
       $data['currency_name_from'] = $paymentSystemFrom->currency_name;
       $data['currency_name_to'] = $paymentSystemTo->currency_name;
       $data['form_data']['exchange_rate'] = $rate;


       $data['error_form'] = $errorForm;

       $data['fields_from'] = $dataViewFormObjectFrom;
       $data['fields_to'] = $dataViewFormObjectTo;

       include_once $this->get_template_dir()."xcoinmoney_exchange_main_step3.php";
       render_main_form_step3($data);
     }
   }

   function xcoinmoney_exchange_ways_shortcode_form() {
      global $wpdb;

      include_once $this->get_template_dir()."xcoinmoney_exchange_ways_shortcode.php";
      $query = "SELECT ew.*, es1.label as system_from_name, es2.label as system_to_name,
      c1.name as currency_from_name
      FROM ". $wpdb->prefix ."xcm_exchange_ways ew
      LEFT JOIN ". $wpdb->prefix ."xcm_exchange_systems es1 ON ew.system_from = es1.id
      LEFT JOIN ". $wpdb->prefix ."xcm_exchange_systems es2 ON ew.system_to = es2.id
      LEFT JOIN ". $wpdb->prefix ."xcm_currencies c1 ON es1.currency = c1.id
      WHERE es1.`enable_in`=1 AND es1.`enable_out`=1 AND es2.`enable_in`=1 AND es2.`enable_out`=1
      ";

      $allWays = $wpdb->get_results($query);

      render_ways_shortcode_form(array('allWays' => $allWays));
   }

   function xcoinmoney_exchange_balance_shortcode_form() {
      global $wpdb;

      include_once $this->get_template_dir()."xcoinmoney_exchange_balance_shortcode.php";

      $query = "SELECT * FROM ". $wpdb->prefix ."xcm_currencies WHERE is_active=1";

      $currencies = $wpdb->get_results($query);

      $errors = array();
      if ($this->_api->getBalance()) {
         $balanceAll = $this->_api->getResult();
      }else{
        foreach ($this->_api->getErrorMessages() as $error) {
          $errors[] = $error[1];
        }

        $errors[] = 'Check the settings of the plugin';
      }

     $fullCurrencies  = array();
     $i = 0;
     foreach ($currencies as $currency) {
       $fullCurrencies[$i]['name'] = $currency->name;
      foreach ($balanceAll['accounts'] as $balance) {
        if($currency->name == 'USD' && $balance['currency'] == 'DXX') {
          $fullCurrencies[$i]['amount'] = $balance['amount'];
          break;
        }
        if($currency->name == $balance['currency']) {
          $fullCurrencies[$i]['amount'] = $balance['amount'];
          break;
        }
      }

       $i++;
     }

     render_balance_shortcode_form(array('currencies' => $fullCurrencies, 'error' => $errors));
   }
}