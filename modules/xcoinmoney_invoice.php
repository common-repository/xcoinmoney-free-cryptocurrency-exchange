<?php
$plugin_prefix_root = plugin_dir_path( __FILE__ );
$plugin_prefix_filename = "{$plugin_prefix_root}/../xcoinmoney_exchange.base.php";
include_once $plugin_prefix_filename;

class XCoinmoneyExchangeXCoinmoneyInvoiceModule extends XCoinmoneyExchangeBase{
  private $_paymentSystems = array(
    array('name'=> 'xcm_invoice_usd', 'label' => 'XCoinMoney Invoice USD', 'currency' => 'USD'),
    array('name'=> 'xcm_invoice_btc', 'label' => 'XCoinMoney Invoice BTC', 'currency' => 'BTC'),
    array('name'=> 'xcm_invoice_ltc', 'label' => 'XCoinMoney Invoice LTC', 'currency' => 'LTC')
  );

  private $_api;

  function __construct() {
    $this->_api =  $this->get_api();
  }

  public function install(){

  }

  public function unistall() {

  }

  public function form_step2_to(){

  }

  public function form_step2_from($paymentSystem) {
    if(isset($_POST['xcoinmoney_invoice_account_owner_from'])){
      $xcoinmoneyInvoiceAccountOwnerFrom = $_POST['xcoinmoney_invoice_account_owner_from'];
    }else{
      $xcoinmoneyInvoiceAccountOwnerFrom = '';
    }

    $html = '<div>
        <h5>'. $paymentSystem->label .'</h5>
        <input type="hidden" name="system_from" value="'. $paymentSystem->id .'">
        <div class="payment-system-field-block">
          <div class="payment-system-field-label">Your xCoinMoney account</div>
          <div class="payment-system-field-option">
            <input type="text" name="xcoinmoney_invoice_account_owner_from" value="'.$xcoinmoneyInvoiceAccountOwnerFrom .'">
          </div>
        </div>
      </div>';
    return $html;
  }

  function validate_form($direction) {
    $validFlag = true;
    $error = array();
    if(empty($_POST['xcoinmoney_invoice_account_owner_'.$direction])) {
      $error[] = 'Enter your account';
      $validFlag = false;
    }

    return array(
      'validate' => $validFlag,
      'error' => $error
    );
  }

  function render_process_details_from_system($transactionId = NULL) {
     $data = array();

     if (!empty($transactionId)) {
       $accountFrom = $this->get_transaction_field($transactionId, 'xcoinmoney_invoice_account_owner_from');
     } else {
       $accountFrom = $_POST['xcoinmoney_invoice_account_owner_from'];
     }

     $data[0]['label'] = 'xCoinMoney account';
     $data[0]['value'] = $accountFrom;

     return $data;
  }

  function process_from($post) {


    $paymentSystemFrom = $post['system_from'];
    $paymentSystemTo = $post['system_to'];
    $accountRecipient = $post['xcoinmoney_invoice_account_owner_from'];

    $accountOwner = $this->get_account($paymentSystemFrom);

    $paymentSystemFromObj = $this->get_payment_system($paymentSystemFrom);
    $paymentSystemToObj = $this->get_payment_system($paymentSystemTo);

    $currencyFrom = $this->get_currency_abbr($paymentSystemFromObj->currency_name);
    $currencyTo = $this->get_currency_abbr($paymentSystemToObj->currency_name);

    $amountTo = $this->round_amount($post['amount_to'],  $paymentSystemToObj->currency_name);
    $amountFrom = $this->get_amount_from($paymentSystemFrom, $paymentSystemTo, $amountTo);

    $rate = $this->get_rate($paymentSystemFrom, $paymentSystemTo);
    $fee = $this->get_fee($paymentSystemFrom, $paymentSystemTo,  $amountTo/$rate);

    $amountFrom += $fee;

    $amountFrom = $this->round_amount($amountFrom, $paymentSystemFromObj->currency_name);
    $fee = $this->round_amount($fee, $paymentSystemFromObj->currency_name);

    $title = get_bloginfo( 'name' );
    $description = "You Pay: ". $amountFrom ." ". $currencyFrom .",
       You get: ". $amountTo ." ". $currencyTo .",
       Fee: ". $fee ." ". $currencyFrom .",
       Rate: ". $rate.",
       Order ID: ". $post['uuid'];


    $callbackUrl = get_permalink().'&callback='.$paymentSystemFromObj->name;
    if ($this->_api->createBuyNowInvoice($accountRecipient, $accountOwner['account_id'], $amountFrom, $title, $description, 24, $callbackUrl)) {
      $result = $this->_api->getResult();

      $data = array(
        'status' => 'success',
        'transaction_from' => $result['invoice_id']
      );
    }
    else {
      $errors = array();

      foreach ($this->_api->getErrorMessages() as $error) {
        $errors[] = $error[1];
      }
      $data = array(
        'status' => 'error',
        'result' => $errors
      );
    }

    return $data;
  }

  function callback() {
    if (empty($_POST) || !isset($_POST['data']) || !isset($_POST['hash'])) {
      header("HTTP/1.0 404 Not Found");
      die();
    }

      $hash = md5(stripcslashes($_POST['data']) . get_option('xcoinmoney_exchange_api_key'));

      if ($hash == $_POST['hash']) {
        $data = json_decode(stripcslashes($_POST['data']));

        if(isset($data->invoice_id)) {
          echo 'OK';
          $transaction = $this->get_transaction_by_from_id($data->invoice_id);

          if ($this->get_transaction_status($transaction->id) == 'pending') {

           // $this->update_transaction_status($transaction->id, 'received');
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

