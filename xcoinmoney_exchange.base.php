<?php
$plugin_prefix_root = plugin_dir_path( __FILE__ );
$plugin_prefix_filename = "{$plugin_prefix_root}/xcoinmoneyapi.php";
include_once $plugin_prefix_filename;

abstract class XCoinmoneyExchangeBase{
  protected $templateDir = 'templates';
  protected $moduleDir = 'modules';

  function get_template_dir() {
    $plugin_prefix_root = plugin_dir_path( __FILE__ );
    return "{$plugin_prefix_root}/". $this->templateDir. "/";
  }

  function get_modules_dir() {
    $plugin_prefix_root = plugin_dir_path( __FILE__ );
    return "{$plugin_prefix_root}/". $this->moduleDir. "/";
  }

  function get_payment_system($id) {
    global $wpdb;
    $query = "SELECT es.*, ec.name as currency_name
          FROM ". $wpdb->prefix ."xcm_exchange_systems es
          LEFT JOIN ". $wpdb->prefix ."xcm_currencies ec ON es.currency = ec.id
          WHERE es.id=".$id;
    $result = $wpdb->get_row($query);

    return $result;
  }

  function get_payment_system_by_name($name) {
    global $wpdb;
    $query = "SELECT es.*, ec.name as currency_name
          FROM ". $wpdb->prefix ."xcm_exchange_systems es
          LEFT JOIN ". $wpdb->prefix ."xcm_currencies ec ON es.currency = ec.id
          WHERE es.name='".$name."'";
    $result = $wpdb->get_row($query);

    return $result;
  }

  function  get_object_payment_system($id) {
    $paymentSystem = $this->get_payment_system($id);

    if($paymentSystem) {
      include_once $this->get_modules_dir().$paymentSystem->file_name;
      $object = new $paymentSystem->class_name();
      return array('object' => $object, 'result' => $paymentSystem);
    }else{
      return false;
    }
  }

  function get_exchange_way($paymentSystemFrom, $paymentSystemTo) {
    global $wpdb;
    $query = "SELECT ew.*, es_to.label as label_to, es_to.enable_out as enable_out,
              es_to.enable_in as enable_in, es_to.is_active as active_to, es_from.is_active as active_from
              FROM ". $wpdb->prefix ."xcm_exchange_ways ew
              LEFT JOIN ". $wpdb->prefix ."xcm_exchange_systems es_to ON ew.system_to= es_to.`id`
              LEFT JOIN ". $wpdb->prefix ."xcm_exchange_systems es_from ON ew.system_from= es_from.`id`
              WHERE ew.system_from=".$paymentSystemFrom." and ew.system_to=".$paymentSystemTo;

    $result = $wpdb->get_row($query);

    return $result;
  }

  function get_rate($paymentSystemFrom, $paymentSystemTo) {
    $result = $this->get_exchange_way($paymentSystemFrom, $paymentSystemTo);

    if ($result->is_manualy_exchange_rate && !empty($result->manualy_exchange_rate)){
      return $result->manualy_exchange_rate;
    }else{
      return $result->exchange_rate;
    }
  }

  function get_fee($paymentSystemFrom, $paymentSystemTo, $amount) {
    $result = $this->get_exchange_way($paymentSystemFrom, $paymentSystemTo);

    $fee = 0;
    if (!empty($result->fee_percentage) && $result->fee_percentage*$amount/100 > $result->fee_flat){
      $fee = $result->fee_percentage*$amount/100;
    }else{
      $fee = $result->fee_flat;
    }

    return $fee;
  }

  function get_amount_from($paymentSystemFrom, $paymentSystemTo, $amountTo) {
    $rate = $this->get_rate($paymentSystemFrom, $paymentSystemTo);

    $amount = $amountTo / $rate;

    return $amount;
  }

  function xcoinmoney_exchange_get_ways_by_select($pid) {
    global $wpdb;

    $query = "
     SELECT DISTINCT ew.system_to, es.`label`
     FROM ". $wpdb->prefix ."xcm_exchange_ways ew
     LEFT JOIN ". $wpdb->prefix ."xcm_exchange_systems es ON ew.system_to= es.`id`
     WHERE ew.system_from=". $pid  ." AND es.`enable_out`=1";

    $allSystemsOut = $wpdb->get_results($query);

    return $allSystemsOut;
  }

  function create_transaction($post){
    global $wpdb;


    $paymentSystemFrom = $this->get_payment_system($post['system_from']);
    $paymentSystemTo = $this->get_payment_system($post['system_to']);
    $details = array(
      'account_id'=>get_option('xcoinmoney_exchange_user_id')
    );

    $amountFrom = $this->get_amount_from($post['system_from'], $post['system_to'], $post['amount_to']);
    $rate = $this->get_rate($post['system_from'], $post['system_to']);
    $fee = $this->get_fee($post['system_from'], $post['system_to'], $amountFrom);

    $userID = get_current_user_id();



    $options = array(
      'uid' => $userID,
      'uuid' => $post['uuid'],
      'system_from' => $post['system_from'],
      'currency_from' => $paymentSystemFrom->currency,
      'amount_from' => $this->round_amount($amountFrom + $fee, $paymentSystemFrom->currency_name),
      'profit' => $this->round_amount($fee, $paymentSystemFrom->currency_name),
      'transaction_from' => '',
      'system_to' => $post['system_to'],
      'currency_to' => $paymentSystemTo->currency,
      'amount_to' => $this->round_amount($post['amount_to'], $paymentSystemTo->currency_name),
      'transaction_to' => '',
      'exchange_rate' => $rate,
      'status'=> 'pending',
      'details' => serialize($details),
      'created' => date('Y-m-d H:i:s', time())
    );

    $wpdb->insert($wpdb->prefix ."xcm_transactions", $options);
    $transaction_id = $wpdb->insert_id;

    unset($post['step']);
    unset($post['submit']);

    $this->add_transactions_details($transaction_id, $post);

    return $transaction_id;
  }

  public function add_transactions_details($transaction_id, $options) {
    global $wpdb;

    foreach ($options as $k => $v) {
      $tdOption = array(
        'transaction_id' => $transaction_id,
        'name' => $k,
        'value' => $v
      );
      $wpdb->insert($wpdb->prefix ."xcm_transaction_details", $tdOption);
    }
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

  function get_transaction_field($transaction_id, $name) {
    global $wpdb;

    $query = "SELECT * FROM ". $wpdb->prefix ."xcm_transaction_details
     WHERE transaction_id=".$transaction_id." and name='".$name."'";

    $field = $wpdb->get_row($query);

    return $field->value;
  }

  function set_transaction_from($transaction_id, $transaction_from) {
    global $wpdb;

    $query = "UPDATE ". $wpdb->prefix ."xcm_transactions SET transaction_from='".$transaction_from."'
     WHERE id=".$transaction_id;

    $wpdb->query($query);
  }

  function set_transaction_to($transaction_id, $transaction_to) {
    global $wpdb;

    $query = "UPDATE ". $wpdb->prefix ."xcm_transactions SET transaction_to='".$transaction_to."'
     WHERE id=".$transaction_id;

    $wpdb->query($query);
  }

  function object_from_process_to($transaction_id){
    $transaction = $this->get_transaction($transaction_id);

    $objectTo = $this->get_object_payment_system($transaction->system_to);

    $responce = $objectTo['object']->process_to($transaction->id);


    if($responce['status'] == 'success'){
      $this->set_transaction_to($responce['transaction_id'], $responce['transaction_to']);
      if (isset($responce['fee'])) {
        $this->set_system_fee($responce['transaction_id'], $responce['fee']);
      }

      $this->update_transaction_status($responce['transaction_id'], 'processed');

      $this->send_success_email($responce['transaction_id']);
    }

    return $responce;
  }

  function get_transaction($transaction_id) {
    global $wpdb;

    $query = "SELECT * FROM ". $wpdb->prefix ."xcm_transactions
     WHERE id='".$transaction_id."'";

    $transaction = $wpdb->get_row($query);
    return $transaction;
  }

  function get_transaction_by_from_id($transaction_from) {
    global $wpdb;

    $query = "SELECT * FROM ". $wpdb->prefix ."xcm_transactions
     WHERE transaction_from='".$transaction_from."'";

    $transaction = $wpdb->get_row($query);
    return $transaction;
  }

  function get_api() {
    return new XCoinmoneyApi(get_option('xcoinmoney_exchange_user_id'), get_option('xcoinmoney_exchange_api_key'));
  }

  function get_account($system_id){
    $api = $this->get_api();
    $paymentSystem = $this->get_payment_system($system_id);

    if ($api->getBalance()) {
      $balanceAll = $api->getResult();
    }else{
      $error = $api->getErrorMessages();
    }
    if(isset($balanceAll)) {
      foreach($balanceAll['accounts'] as $account){
        if($account['currency'] == $paymentSystem->currency_name) {
          return $account;
        }elseif($paymentSystem->currency_name == 'USD' && $account['currency'] == 'DXX') {
          return $account;
        }
      }
    }
    return false;
  }

  function check_balance(){
    $account_info = $this->get_account($_POST['system_to']);

    if($account_info){
      if($_POST['amount_to'] >= $account_info['amount']){
       return false;
      }
    }else{
      return false;
    }

    return true;
  }

  function update_transaction_status($transaction_id, $status) {
    global $wpdb;

    $query = "UPDATE ". $wpdb->prefix ."xcm_transactions SET status='".$status."'
     WHERE  id='".$transaction_id."'";

    $wpdb->query($query);
  }

  function get_transaction_status($transaction_id){
    global $wpdb;

    $query = "SELECT * FROM ". $wpdb->prefix ."xcm_transactions
     WHERE id='".$transaction_id."'";

    $transaction = $wpdb->get_row($query);

    return $transaction->status;
  }

  function validate_request() {
    $post = $_POST;
    $way = $this->get_exchange_way($post['system_from'], $post['system_to']);
    $rate = $this->get_rate($_POST['system_from'], $_POST['system_to']);

    if ($rate != $_POST['exchange_rate'] && $_POST['step'] == 3) {
      return false;
    }

    if($way->enable_out && $way->enable_in && $way->active_from && $way->active_to && $way->is_active) {
      return true;
    }

    return false;
  }

  function get_list_transactions($dateFilter, $offset, $limit) {
    global $wpdb;

    $query = "SELECT t.*, es1.label as system_from_name, es2.label as system_to_name,
                c1.name as currency_name_from, c2.name as currency_name_to
                FROM ". $wpdb->prefix ."xcm_transactions t
                LEFT JOIN ". $wpdb->prefix ."xcm_exchange_systems es1 ON t.system_from = es1.id
                LEFT JOIN ". $wpdb->prefix ."xcm_exchange_systems es2 ON t.system_to = es2.id
                LEFT JOIN ". $wpdb->prefix ."xcm_currencies c1 ON t.currency_from = c1.id
                LEFT JOIN ". $wpdb->prefix ."xcm_currencies c2 ON t.currency_to = c2.id";
    if (!empty($dateFilter)) {
      $query .= " WHERE $dateFilter ";
    }

    $query .= " ORDER BY t.created DESC LIMIT $offset, $limit";

    return $wpdb->get_results($query);
  }

  function get_total_transactions($dateFilter) {
    global $wpdb;

    $query = "SELECT count(t.id)
                FROM ". $wpdb->prefix ."xcm_transactions t";

    if (!empty($dateFilter)) {
      $query .= " WHERE $dateFilter ";
    }

    return $wpdb->get_var($query);
  }

  function set_system_fee($transaction_id, $fee=0) {
    global $wpdb;

    $query = "UPDATE ". $wpdb->prefix ."xcm_transactions SET fee_system='".$fee."'
     WHERE  id='".$transaction_id."'";

    $wpdb->query($query);
  }

  function redirect($url) {
    echo'<script> window.location="'.$url.'"; </script> ';
  }

  function send_success_email($transactionId) {
    if(!get_option('xcoinmoney_exchange_merchant_email')) {
      return FALSE;
    }

    $message = "Hello!";

    $transaction = $this->get_transaction_details($transactionId);

    $message .= "\n We have received ".$this->round_amount($transaction->amount_from,$transaction->currency_name_from)." ".$transaction->currency_name_from;
    $message .= "\n ".$this->round_amount($transaction->amount_to,$transaction->currency_name_to)." ".$transaction->currency_name_to." have been sent";
    $message .= "\n \n Regards,";
    $message .= "\n ". get_bloginfo( 'name' );

    wp_mail(get_option('xcoinmoney_exchange_merchant_email'), 'Order is processed!', $message);
  }

  function get_transaction_details($transactionId) {
    global $wpdb;

    $query = "SELECT tp.*, es1.label as system_from_name, es2.label as system_to_name,
                c1.name as currency_name_from, c2.name as currency_name_to
                FROM ". $wpdb->prefix ."xcm_transactions tp
                LEFT JOIN ". $wpdb->prefix ."xcm_exchange_systems es1 ON tp.system_from = es1.id
                LEFT JOIN ". $wpdb->prefix ."xcm_exchange_systems es2 ON tp.system_to = es2.id
                LEFT JOIN ". $wpdb->prefix ."xcm_currencies c1 ON tp.currency_from = c1.id
                LEFT JOIN ". $wpdb->prefix ."xcm_currencies c2 ON tp.currency_to = c2.id
                WHERE tp.id=".$transactionId;
    $result = $wpdb->get_row($query);

    return $result;
  }

  public function round_amount($amount, $paymentSystem = 'BTC') {
    if (in_array($paymentSystem, array('USD', 'EUR'))) {
      $precision = 2;
    }
    else {
      $precision = 8;
    }

    $str = round($amount, $precision);

    if (substr($str, -1) == '.') {
      $str .= '00';
    }

    return $str;
  }

  function render_process_details_from_system($transactionId = NULL) {
    return  array();
  }
  function render_process_details_to_system($transactionId = NULL) {
    return  array();
  }

  public static function generateUniqueUUID($name) {
    $uuid_path = plugin_dir_path( __FILE__ );
    $uuid_filename = "{$uuid_path}/uuid.php";
    include_once $uuid_filename;

    $uuid = \Lootils\Uuid\Uuid::createV5(\Lootils\Uuid\Uuid::DNS, __CLASS__ . time() . $name);

    return $uuid->getUuid();
  }
}