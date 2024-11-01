<?php

class XCoinmoneyApi {

  const XCOINMONEY_ADDRESS = 'https://www.xcoinmoney.com/api/';

  const INTERNAL = 'internal';
  const EXTERNAL = 'external';

  private $_data = array();
  private $_request = array();
  private $_errors = array();

  private $_apiKey = '';
  private $_userId = '';

  private $_hashChecked = false;

  public function __construct($businessAccountId, $apiKey) {
    $this->_userId = $businessAccountId;
    $this->_apiKey = $apiKey;
  }

  /**
   * Set data and calc hash
   *
   * @param array $data
   */
  public function setData($data) {
    $data['user_id'] = $this->_userId;

    $str = '';
    $keys = array_keys($data);
    sort($keys);
    for ($i=0; $i < count($keys); $i++) {
      $str .= $data[$keys[$i]];
    }
    $str .= $this->_apiKey;
    $data['hash'] = md5($str);

    $this->_data = $data;
  }

  /**
   * @param $json
   * @return bool
   */
  private function _decodeRequest($json) {

    $result = false;

    $request = json_decode($json);

    if (isset($request->hash) && isset($request->data)) {
      $this->_hashChecked = (md5($request->data . $this->_apiKey) === $request->hash);
    }

    if (isset($request->data)) {
      $this->_request = json_decode($request->data, TRUE);

      if ($this->_request['result'] === 'error') {
        $this->_errors = $this->_request['errors'];
      }
      else {
        $result = true;
      }
    }

    return $result;
  }

  /**
   * @return bool
   */
  private function _sendApiCall() {
    $result = FALSE;

    $this->_request = array();
    $this->_hashChecked = false;
    $this->_errors = array();

    try {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, self::XCOINMONEY_ADDRESS);
      curl_setopt($ch, CURLOPT_HEADER, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_data);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
      $json = curl_exec($ch);
      curl_close($ch);

      $result = $this->_decodeRequest($json);
    }
    catch (Exception $e) {

    }

    return $result;
  }

  /**
   * @return array
   */
  public function getResult() {
    if ($this->_hashChecked && ($this->_request['result'] === 'success')) {
      $data = $this->_request;
      unset($data->result);
      return $data;
    }
    else {
      return array();
    }
  }

  /**
   * @return array
   */
  public function getErrorMessages() {
    return $this->_errors;
  }


  /**
   * @param string $accountName
   * @return bool
   */
  public function getBalance($accountName = '') {
    $data = array(
      'cmd' => 'balance_info'
    );
    if (!empty($accountName)) {
      $data['account_id'] = $accountName;
    }
    $this->setData($data);

    return $this->_sendApiCall();
  }

  /**
   * @param string $accountName
   * @param string $startDate
   * @param string $endData
   * @return bool
   */
  public function getTransactions($startDate, $endData, $accountName = '') {
    $data = array(
      'cmd' => 'transaction_list',
      'account_id' => $accountName,
      'date_from' => $startDate,
      'date_to' => $endData
    );
    $this->setData($data);

    return $this->_sendApiCall();
  }

  /**
   * @param string $recipientAccountName
   * @param string $subscriptionId
   * @param string $title
   * @param string $description
   * @param int $maxLifeTime
   * @return bool
   */
  public function createSubscriptionInvoice($recipientAccountName, $subscriptionId, $title = '', $description = '', $maxLifeTime = 168) {
    $data = array(
      'cmd' => 'create_invoice',
      'invoice_type' => 'subscription',
      'recipient_account' => $recipientAccountName,
      'title' => $title,
      'description' => $description,
      'subscription_id' => $subscriptionId,
      'max_life_time' => $maxLifeTime
    );
    $this->setData($data);

    return $this->_sendApiCall();
  }

  /**
   * @param string $recipientAccountName
   * @param string $ownerAccountName
   * @param double $amount
   * @param string $title
   * @param string $description
   * @param int $maxLifeTime
   * @param string|null $customCallbackUrl
   * @return bool
   */
  public function createBuyNowInvoice($recipientAccountName, $ownerAccountName, $amount, $title = '', $description = '', $maxLifeTime = 168, $customCallbackUrl = NULL) {
    $data = array(
      'cmd' => 'create_invoice',
      'invoice_type' => 'buy_now',
      'recipient_account' => $recipientAccountName,
      'owner_account' => $ownerAccountName,
      'amount' => $amount,
      'title' => $title,
      'description' => $description,
      'max_life_time' => $maxLifeTime
    );
    if (!empty($customCallbackUrl)) {
      $data['callback_url'] = $customCallbackUrl;
    }
    $this->setData($data);

    return $this->_sendApiCall();
  }

  /**
   * Transfer funds
   *
   * @param double $amount
   * @param string $fromAccount
   * @param string $toAccount
   * @param string $direction set 'internal' for transfers within the system; 'external' for withdraw funds to external addresses.
   * @param string $details
   * @return bool
   */
  public function transactionPayout($amount, $fromAccount, $toAccount, $direction, $details = '') {
    $data = array(
      'cmd' => 'transaction_payout',
      'amount' => $amount,
      'direction' => $direction,
      'from_account' => $fromAccount,
      'to_account' => $toAccount,
      'details' => $details
    );
    $this->setData($data);

    return $this->_sendApiCall();
  }

  /**
   * Make order
   *
   * @param array $params
   * @param string|null $customCallbackUrl
   * @return bool
   */
  public function makeOrder($params = array(), $customCallbackUrl = null) {
    $data = array(
      'cmd' => 'order'
    );
    if (!empty($customCallbackUrl)) {
      $data['callback_url'] = $customCallbackUrl;
    }
    $this->setData(array_merge($data, $params));

    return $this->_sendApiCall();
  }
}
