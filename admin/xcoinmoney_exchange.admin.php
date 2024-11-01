<?php
$plugin_prefix_root = plugin_dir_path( __FILE__ );
$plugin_prefix_filename = "{$plugin_prefix_root}/../xcoinmoney_exchange.base.php";
include_once $plugin_prefix_filename;

class XCoinmoneyExchangeAdmin extends  XCoinmoneyExchangeBase {

  function xcoinmoney_exchange_admin_dashboard(){
    $data = array();

    if($_POST && isset($_POST['submit'])) {
      update_option('xcoinmoney_exchange_enable_user_register', $_POST['xcoinmoney_exchange_enable_user_register']);
      update_option('xcoinmoney_exchange_user_id', $_POST['xcoinmoney_exchange_user_id']);
      update_option('xcoinmoney_exchange_api_key', $_POST['xcoinmoney_exchange_api_key']);
      update_option('xcoinmoney_exchange_merchant_email', $_POST['xcoinmoney_exchange_merchant_email']);

      $data['setting_updated'] = TRUE;
    }

    $data['xcoinmoney_exchange_enable_user_register'] = get_option('xcoinmoney_exchange_enable_user_register');
    $data['xcoinmoney_exchange_user_id'] = get_option('xcoinmoney_exchange_user_id');
    $data['xcoinmoney_exchange_api_key'] = get_option('xcoinmoney_exchange_api_key');
    $data['xcoinmoney_exchange_merchant_email'] = get_option('xcoinmoney_exchange_merchant_email');

    include_once $this->get_template_dir()."admin_dashboard.php";
    render_admin_dashboard($data);
  }

  function xcoinmoney_exchange_admin_currencies(){
    global $wpdb;
    $data = array();
    if($_POST && isset($_POST['submit'])) {
      if(isset($_POST['currency'])) {
        foreach( $_POST['currency'] as $key => $value ) {
          $is_active = isset($value['is_active']) ? '1' : '0';

          $query = "UPDATE ". $wpdb->prefix ."xcm_currencies
           SET is_active='". $is_active ."'
           WHERE id=".$key;
          $wpdb->query($query);
        }

        $data['setting_updated'] = TRUE;
      }
    }

    include_once $this->get_template_dir()."admin_currencies.php";

    $query = "SELECT * FROM ". $wpdb->prefix ."xcm_currencies";

    $allCurrencies = $wpdb->get_results($query);
    $data['allCurrencies'] = $allCurrencies;
    render_admin_currencies($data);
  }

  function xcoinmoney_exchange_admin_systems(){
    global $wpdb;
    $data = array();
    if($_POST && isset($_POST['submit'])) {
      if(isset($_POST['exchange_system'])) {
        foreach( $_POST['exchange_system'] as $key => $value ) {
          $is_active = isset($value['is_active']) ? '1' : '0';
          $enable_in = isset($value['enable_in']) ? '1' : '0';
          $enable_out = isset($value['enable_out']) ? '1' : '0';

          $query = "UPDATE ". $wpdb->prefix ."xcm_exchange_systems
           SET label='". $value['label'] ."',
           enable_in='". $enable_in ."',
           enable_out='". $enable_out ."',
           is_active='". $is_active ."'
           WHERE id=".$key;

          $wpdb->query($query);
        }

        $data['setting_updated'] = TRUE;
      }
    }

    include_once $this->get_template_dir()."admin_exchange_systems.php";

    $query = "SELECT es.*, ec.name as currency_name
    FROM ". $wpdb->prefix ."xcm_exchange_systems es
    LEFT JOIN ". $wpdb->prefix ."xcm_currencies ec ON es.currency = ec.id
    ";

    $allSystems = $wpdb->get_results($query);

    $data['allSystems'] = $allSystems;
    render_admin_systems($data);
  }

  function xcoinmoney_exchange_admin_ways(){
    global $wpdb;
    $data = array();
    if ($_GET['update_rates'] == 1) {
      $plugin_prefix_root = plugin_dir_path( __FILE__ );
      $plugin_prefix_filename = "{$plugin_prefix_root}/../xcoinmoney_exchange.rates.php";
      include_once $plugin_prefix_filename;
      $xcoinmoney_exchange_rates_object = new XCoinmoneyExchangeRates();
      $xcoinmoney_exchange_rates_object->process();
    }

    if($_POST && isset($_POST['submit'])) {
      if(isset($_POST['exchange_way'])) {
        foreach( $_POST['exchange_way'] as $key => $value ) {
          $is_manualy_exchange_rate = isset($value['is_manualy_exchange_rate']) ? '1' : '0';
          $is_active = isset($value['is_active']) ? '1' : '0';

          $query = "UPDATE ". $wpdb->prefix ."xcm_exchange_ways
           SET fee_flat='". $value['fee_flat'] ."',
           fee_percentage='". $value['fee_percentage'] ."',
           exchange_rate='". $value['exchange_rate'] ."',
           is_manualy_exchange_rate='". $is_manualy_exchange_rate ."',
           manualy_exchange_rate='". $value['manualy_exchange_rate'] ."',
           is_active='". $is_active ."'
           WHERE id=".$key;

          $wpdb->query($query);
        }

        $data['setting_updated'] = TRUE;
      }
    }

    include_once $this->get_template_dir()."admin_exchange_ways.php";
    $query = "SELECT ew.*, es1.label as system_from_name, es2.label as system_to_name,
     c1.name as currency_from_name
    FROM ". $wpdb->prefix ."xcm_exchange_ways ew
    LEFT JOIN ". $wpdb->prefix ."xcm_exchange_systems es1 ON ew.system_from = es1.id
    LEFT JOIN ". $wpdb->prefix ."xcm_exchange_systems es2 ON ew.system_to = es2.id
    LEFT JOIN ". $wpdb->prefix ."xcm_currencies c1 ON es1.currency = c1.id
    ";

    $allWays = $wpdb->get_results($query);
    $data['allWays'] = $allWays;
    render_admin_ways($data);
  }

  function xcoinmoney_exchange_admin_transactions(){
    global $wpdb;

    $outputDate = array();


    //transaction details page
    if (!empty($_GET['transaction_id'])) {
      $transactionId = $_GET['transaction_id'];

      $transaction = $this->get_transaction_details($transactionId);
      include_once $this->get_template_dir()."admin_exchange_transaction_details.php";

      if(!empty($transaction)) {
        $transaction = (array)$transaction;

        if(!empty($transaction['uid'])) {
          $userInfo = get_userdata($transaction['uid']);
          $transaction['user'] = $userInfo->user_login;
        }

        $objectFrom  = $this->get_object_payment_system($transaction['system_from']);
        $objectTo  = $this->get_object_payment_system($transaction['system_to']);

        $dataViewFormObjectFrom = $objectFrom['object']->render_process_details_from_system($transactionId);
        $dataViewFormObjectTo = $objectTo['object']->render_process_details_to_system($transactionId);

        $paymentSystemFrom = $this->get_payment_system($transaction['system_from']);
        $paymentSystemTo = $this->get_payment_system($transaction['system_to']);

        $transaction['amount_from'] = $this->round_amount($transaction['amount_from'], $paymentSystemFrom->currency_name);
        $transaction['amount_to'] = $this->round_amount($transaction['amount_to'], $paymentSystemFrom->currency_name);
        $transaction['profit'] = $this->round_amount($transaction['profit'], $paymentSystemFrom->currency_name);
        $transaction['fee_system'] = $this->round_amount($transaction['fee_system'], $paymentSystemFrom->currency_name);

        $transaction['fields_from'] = $dataViewFormObjectFrom;
        $transaction['fields_to'] = $dataViewFormObjectTo;
      }

      $outputDate['transaction'] = $transaction;
      render_admin_transaction_details($outputDate);

    } else { //all transactions page
      $query = "SELECT * FROM ". $wpdb->prefix ."xcm_currencies";
      $allCurrencies = $wpdb->get_results($query);

      if(!empty($_GET['date_from'])) {
        $startDate = $_GET['date_from'];
        $outputDate['date_from'] = $_GET['date_from'];
      } else {
        $startDate = '1970-01-01';
        $outputDate['date_from'] = '';
      }

      if(!empty($_GET['date_to'])) {
        $endDate = $_GET['date_to'];
        $outputDate['date_to'] = $_GET['date_to'];
      } else {
        $endDate =  date("Y-m-d", time()+86400);
        $outputDate['date_to'] = '';
      }

      $dateFilter = " t.created BETWEEN '".$startDate."' AND '".$endDate."'";

      $balance = array();
      foreach ($allCurrencies as $currency) {
        $query = "SELECT SUM(fee_system) AS system_fee FROM ". $wpdb->prefix ."xcm_transactions t WHERE t.status='processed' AND currency_to=". $currency->id ." AND ". $dateFilter;
        $feeFrom = $wpdb->get_row($query);
        $query = "SELECT SUM(profit) AS profit_fee FROM ". $wpdb->prefix ."xcm_transactions t WHERE t.status='processed' AND currency_from=".$currency->id ." AND ". $dateFilter;
        $feeTo = $wpdb->get_row($query);
        $balance[$currency->id]['amount'] = $feeTo->profit_fee - $feeFrom->system_fee;
        $balance[$currency->id]['currency'] = $currency->name;
      }

      $outputDate['balance'] = $balance;


      $pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;

      $limit = 10;
      $offset = ( $pagenum - 1 ) * $limit;
      $total = $this->get_total_transactions($dateFilter);
      $numOfPages = ceil( $total / $limit );

      $allTransactions = $this->get_list_transactions($dateFilter, $offset, $limit);

      $outputDate['allTransactions'] = $allTransactions;
      $outputDate['numOfPages'] = $numOfPages;
      $outputDate['pagenum'] = $pagenum;

      include_once $this->get_template_dir()."admin_exchange_transactions.php";
      render_admin_transactions($outputDate);
    }
  }
}



