<?php
$plugin_prefix_root = plugin_dir_path( __FILE__ );
$plugin_prefix_filename = "{$plugin_prefix_root}/../xcoinmoney_exchange.base.php";
include_once $plugin_prefix_filename;

class XCoinmoneyExchangeXCoinmoneyModule extends XCoinmoneyExchangeBase{
  private $_paymentSystems = array(
    array('name'=> 'xcm_usd', 'label' => 'XCoinMoney USD', 'currency' => 'USD'),
    array('name'=> 'xcm_btc', 'label' => 'XCoinMoney BTC', 'currency' => 'BTC'),
    array('name'=> 'xcm_ltc', 'label' => 'XCoinMoney LTC', 'currency' => 'LTC')
  );

  private $_api;

  function __construct() {
    $this->_api = $this->get_api();
  }

  public function install(){

  }

  public function unistall() {

  }

  public function form_step2_to($paymentSystem){
    if(isset($_POST['xcoinmoney_account_to'])){
      $xcoinmoneyAccountTo = $_POST['xcoinmoney_account_to'];
    }else{
      $xcoinmoneyAccountTo = '';
    }

    $html = '<div>
        <h5>'. $paymentSystem->label .'</h5>
        <input type="hidden" name="system_to" value="'. $paymentSystem->id .'">
        <div class="payment-system-field-block">
          <div class="payment-system-field-label">Your xCoinMoney account</div>
          <div class="payment-system-field-option">
            <input type="text" name="xcoinmoney_account_to" value="'.$xcoinmoneyAccountTo.'">
          </div>
        </div>
      </div>';

    return $html;
  }

  public function form_step2_from($paymentSystem){
    if(isset($_POST['xcoinmoney_account_from'])){
      $xcoinmoneyAccountFrom = $_POST['xcoinmoney_account_from'];
    }else{
      $xcoinmoneyAccountFrom = '';
    }

    $html = '<div>
        <h5>'. $paymentSystem->label .'</h5>
        <input type="hidden" name="system_from" value="'. $paymentSystem->id .'">
        <div class="payment-system-field-block">
          <div class="payment-system-field-label">Your xCoinMoney account</div>
          <div class="payment-system-field-option">
              <input type="text" name="xcoinmoney_account_from" value="'.$xcoinmoneyAccountFrom.'">
          </div>
        </div>
      </div>';

    return $html;
  }

  function render_process_details_to_system($transactionId = NULL) {
    $data = array();

    if (!empty($transactionId)) {
      $accountFrom = $this->get_transaction_field($transactionId, 'xcoinmoney_account_to');
    } else {
      $accountFrom = $_POST['xcoinmoney_account_to'];
    }

    $data[0]['label'] = 'xCoinMoney account';
    $data[0]['value'] = $accountFrom;

    return $data;
  }

  function validate_form($direction) {
    $validFlag = true;
    $error = array();
    if(empty($_POST['xcoinmoney_account_'.$direction])) {
      $error[] = 'Enter your account';
      $validFlag = false;
    }
    return array(
      'validate' => $validFlag,
      'error' => $error
    );
  }

  function process_to($transaction_id) {

    $paymentSystem = $this->get_transaction_field($transaction_id, 'system_to');
    $accountRecipient = $this->get_transaction_field($transaction_id, 'xcoinmoney_account_to');

    $accountOwner = $this->get_account($paymentSystem);

    $transaction = $this->get_transaction($transaction_id);

    //generate details string
    $paymentSystemFromObj = $this->get_payment_system($transaction->system_from);
    $paymentSystemToObj = $this->get_payment_system($transaction->system_to);

    $amountTo = $this->round_amount($transaction->amount_to,  $paymentSystemToObj->currency_name);
    $amountFrom = $this->round_amount($transaction->amount_from,  $paymentSystemFromObj->currency_name);
    $fee = $this->round_amount($transaction->profit, $paymentSystemFromObj->currency_name);

    $currencyFrom = $this->get_currency_abbr($paymentSystemFromObj->currency_name);
    $currencyTo = $this->get_currency_abbr($paymentSystemToObj->currency_name);


    $description = "You Pay: ". $amountFrom ." ". $currencyFrom .",
       You get: ". $amountTo ." ". $currencyTo .",
       Fee: ". $fee ." ". $currencyFrom .",
       Rate: ". $transaction->exchange_rate."
       Order ID: ". $transaction->uuid;

    if ($this->_api->transactionPayout($amountTo,$accountOwner['account_id'] , $accountRecipient, XCoinmoneyApi::INTERNAL, $description)) {
      $result = $this->_api->getResult();
      $data = array(
        'status' => 'success',
        'fee' => $result['fee'],
        'transaction_to' => $result['transaction_id'],
        'transaction_id' => $transaction_id
      );
    }
    else {
      $errors = $this->_api->getErrorMessages();
      $data = array(
        'status' => 'error',
        'result' => $errors,
        'transaction_id' => $transaction_id
      );
    }
    return $data;

  }

  public function get_currency_abbr($currency) {
    if ($currency == 'USD') {
      return 'DXX';
    }

    return $currency;
  }

}
