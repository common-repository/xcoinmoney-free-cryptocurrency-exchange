<?php
$plugin_prefix_root = plugin_dir_path( __FILE__ );
$plugin_prefix_filename = "{$plugin_prefix_root}/../xcoinmoney_exchange.base.php";
include_once $plugin_prefix_filename;

class XCoinmoneyExchangeXCoinmoneyMerchantModule extends XCoinmoneyExchangeBase{
  private $_paymentSystems = array(
    array('name'=> 'xcm_merchant_usd', 'label' => 'XCoinMoney Merchant USD', 'currency' => 'USD'),
    array('name'=> 'xcm_merchant_btc', 'label' => 'XCoinMoney Merchant BTC', 'currency' => 'BTC'),
    array('name'=> 'xcm_merchant_ltc', 'label' => 'XCoinMoney Merchant LTC', 'currency' => 'LTC')
  );

  private $_api;

  function __construct() {
    $this->_api =  $this->get_api();
  }

  public function install(){

  }

  public function unistall() {

  }

  public function form_step2_from($paymentSystem){
    $html = '<div>
        <h5>'. $paymentSystem->label .'</h5>
        <input type="hidden" name="system_from" value="'. $paymentSystem->id .'">
      </div>';
    return $html;
  }

  function process_from($post) {


    $paymentSystemFrom = $post['system_from'];
    $paymentSystemTo = $post['system_to'];
    $paymentSystemFromObj = $this->get_payment_system($paymentSystemFrom);
    $paymentSystemToObj = $this->get_payment_system($paymentSystemTo);

    $amountTo = $this->round_amount($post['amount_to'],  $paymentSystemToObj->currency_name);
    $amountFrom = $this->get_amount_from($paymentSystemFrom, $paymentSystemTo, $amountTo);

    $rate = $this->get_rate($paymentSystemFrom, $paymentSystemTo);
    $fee = $this->get_fee($paymentSystemFrom, $paymentSystemTo,  $amountTo/$rate);

    $amountFrom += $fee;

    $amountFrom = $this->round_amount($amountFrom, $paymentSystemFromObj->currency_name);

    $callbackUrl = get_permalink().'&callback='.$paymentSystemFromObj->name;

    $params = array(
      'amount' => $amountFrom,
      'currency' => $this->get_currency_abbr($paymentSystemFromObj->currency_name),
      'payer_pays_fee' => 0,
      'callback_url' => $callbackUrl
    );
  
    if ($this->_api->makeOrder($params)) {
      $result = $this->_api->getResult();
      $data = array(
        'status' => 'success',
        'action' => 'redirect',
        'url'   =>  $result['url'],
        'fee' => $result['fee'],
        'transaction_from' => $result['order_id'],
      );
    }
    else {
      $errors = array();
      foreach ($this->_api->getErrorMessages() as $error) {
        $errors[] = $error[1];
      }

      $data = array(
        'status' => 'error',
        'result' => $errors,
      );
    }

    return $data;
  }

  function callback(){
      if (empty($_POST) || !isset($_POST['data']) || !isset($_POST['hash'])) {
        header("HTTP/1.0 404 Not Found");
        die();
      }

      $hash = md5(stripcslashes($_POST['data']) . get_option('xcoinmoney_exchange_api_key'));

      if ($hash == $_POST['hash']) {
        $data = json_decode(stripcslashes($_POST['data']));
        
        if(isset($data->order_id)){
          echo 'OK';
          $transaction = $this->get_transaction_by_from_id($data->order_id);

          if ($this->get_transaction_status($transaction->id) == 'pending') {
            $this->update_transaction_status($transaction->id, 'received');
            $this->object_from_process_to($transaction->id);
          }
        }
        die();
      }
  }

  public function get_currency_abbr($currency) {
    if ($currency == 'USD') {
      return 'DXX';
    }

    return $currency;
  }
}
