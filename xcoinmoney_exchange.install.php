<?php
function xcoinmoney_exchange_install() {
  global $wpdb;
  $collate = '';
  $prefix = 'wp_xcm';
  if (!empty($wpdb->charset))
    $collate = 'DEFAULT CHARACTER SET '. $wpdb->charset;
  if (!empty($wpdb->collate))
    $collate .= ' COLLATE ' . $wpdb->collate;
  if (!empty($wpdb->prefix))
    $prefix = $wpdb->prefix . 'xcm';

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

  $sqls = array();

  $table_name = $prefix . "_currencies";
  if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
    $sqls[] = "CREATE TABLE $table_name (
				id int(11) NOT NULL auto_increment,
				name varchar(3) NOT NULL DEFAULT '',
				is_active int(1) DEFAULT '1',
				PRIMARY KEY  (id)
			  ) $collate;";
  }

  $table_name = $prefix . "_exchange_rates";
  if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
    $sqls[] = "CREATE TABLE $table_name (
				id int(11) NOT NULL auto_increment,
				pair varchar(255) NOT NULL DEFAULT '',
			  rate DECIMAL(20,8) DEFAULT NULL,
				is_active int(1) DEFAULT '1',
				PRIMARY KEY  (id)
			  ) $collate;";
  }

  $table_name = $prefix . "_exchange_systems";
  if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
    $sqls[] = "CREATE TABLE $table_name (
        id int(11) NOT NULL auto_increment,
        name varchar(255) NOT NULL DEFAULT '',
        label varchar(255) NOT NULL DEFAULT '',
        currency int(11) NOT NULL,
        enable_in int(1) DEFAULT '1',
        enable_out int(1) DEFAULT '1',
        class_name varchar(255) DEFAULT NULL,
        file_name varchar(255) DEFAULT NULL,
        is_active int(1) DEFAULT '1',
        PRIMARY KEY  (id),
        KEY `currency` (`currency`),
        CONSTRAINT ".$prefix."_exchange_systems_ibfk_1 FOREIGN KEY (currency) REFERENCES ".$prefix."_currencies (id) ON DELETE CASCADE
        ) $collate;";
  }

  $table_name = $prefix . "_exchange_ways";
  if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
    $sqls[] = "CREATE TABLE $table_name (
        id int(11) NOT NULL auto_increment,
        system_from int(11) NOT NULL,
        system_to int(11) NOT NULL,
        fee_flat DECIMAL(20,8) DEFAULT NULL,
        fee_percentage DECIMAL(20,8) DEFAULT NULL,
        exchange_rate DECIMAL(20,8) DEFAULT NULL,
        is_manualy_exchange_rate int(1) DEFAULT '0',
        manualy_exchange_rate DECIMAL(20,8) DEFAULT NULL,
        execute_instant int(1) NOT NULL DEFAULT '1' COMMENT '#Should the orders be executed automatically or not',
        is_active int(1) DEFAULT '1',
        PRIMARY KEY  (id),
        KEY system_from (system_from),
        KEY system_to (system_to),
        CONSTRAINT ". $prefix ."_exchange_ways_ibfk_1 FOREIGN KEY (system_from) REFERENCES ". $prefix ."_exchange_systems (id) ON DELETE CASCADE,
        CONSTRAINT ". $prefix ."_exchange_ways_ibfk_2 FOREIGN KEY (system_to) REFERENCES ". $prefix ."_exchange_systems (id) ON DELETE CASCADE
        ) $collate;";
  }

  $table_name = $prefix . "_transactions";
  if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
    $sqls[] = "CREATE TABLE $table_name (
        id int(11) NOT NULL auto_increment,
        uid int(11) COMMENT 'User ID',
        uuid varchar(64)  NOT NULL,
        system_from int(11) NOT NULL,
        currency_from int(11) NOT NULL,
        amount_from DECIMAL(20,8) DEFAULT NULL,
        profit DECIMAL(20,8) DEFAULT NULL,
        transaction_from varchar(50) DEFAULT NULL COMMENT 'Transaction ID of the payment system, user paid from',
        system_to int(11) NOT NULL,
        currency_to int(11) NOT NULL,
        amount_to DECIMAL(20,8) DEFAULT NULL,
        fee_system DECIMAL(20,8) DEFAULT NULL,
        transaction_to varchar(50) DEFAULT NULL COMMENT 'Transaction ID of the payment system, user received payment',
        exchange_rate DECIMAL(20,8) DEFAULT NULL,
        status enum('pending','received','processed') DEFAULT 'pending',
        details text COMMENT 'Wallets, account ID, addresses to receive payments',
        created datetime NOT NULL,
        PRIMARY KEY (id),
        KEY system_from (system_from),
        KEY currency_from (currency_from),
        KEY system_to (system_to),
        KEY currency_to (currency_to),
        CONSTRAINT ". $prefix ."_transactions_ibfk_1 FOREIGN KEY (system_from) REFERENCES ". $prefix ."_exchange_systems (id) ON DELETE CASCADE,
        CONSTRAINT ". $prefix ."_transactions_ibfk_2 FOREIGN KEY (currency_from) REFERENCES ". $prefix ."_currencies (id) ON DELETE CASCADE,
        CONSTRAINT ". $prefix ."_transactions_ibfk_3 FOREIGN KEY (system_to) REFERENCES ". $prefix ."_exchange_systems (id) ON DELETE CASCADE,
        CONSTRAINT ". $prefix ."_transactions_ibfk_4 FOREIGN KEY (currency_to) REFERENCES ". $prefix ."_currencies (id) ON DELETE CASCADE
        ) $collate;";
  }

  $table_name = $prefix . "_transaction_details";
  if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
    $sqls[] = "CREATE TABLE $table_name (
        id int(11) auto_increment,
        transaction_id int(11) NOT NULL,
        name varchar(255) DEFAULT NULL,
        value varchar(255) DEFAULT NULL,
        PRIMARY KEY (id),
        KEY `transaction_id` (`transaction_id`),
        CONSTRAINT ". $prefix ."_transaction_details_ibfk_1 FOREIGN KEY (transaction_id) REFERENCES ". $prefix ."_transactions (id)
        ) $collate;";
  }

  foreach ($sqls as $sql){
    dbDelta($sql);
  }
}

function xcoinmoney_exchange_install_data() {
  global $wpdb;

  if (!empty($wpdb->prefix))
    $prefix = $wpdb->prefix . 'xcm';


  $table_name = $prefix. '_currencies';
  if($wpdb->get_var("show tables like '$table_name'") == $table_name) {
    $rows = array(
      array('name' => 'USD'),
      array('name' => 'BTC'),
      array('name' => 'LTC')
    );

    $wpdb->query('DELETE FROM ' . $table_name . ' WHERE 1;' );
    foreach ($rows as $row) {
      $wpdb->insert($table_name, $row);
    }
  }


  $table_name = $prefix. '_exchange_rates';
  if($wpdb->get_var("show tables like '$table_name'") == $table_name) {
    $rows = array(
      array('pair' => 'BTC/USD'),
      array('pair' => 'LTC/USD'),
      array('pair' => 'LTC/BTC')
    );

    $wpdb->query('DELETE FROM ' . $table_name . ' WHERE 1;' );
    foreach ($rows as $row) {
      $wpdb->insert($table_name, $row);
    }
  }

  $table_name = $prefix. '_exchange_systems';
  if($wpdb->get_var("show tables like '$table_name'") == $table_name) {
    $rows = array(
      array('name' => 'xcm_usd', 'label' => 'xCoinMoney USD', 'currency' => 1, 'class_name' => 'XCoinmoneyExchangeXCoinmoneyModule', 'file_name' => 'xcoinmoney.php'),
      array('name' => 'xcm_btc', 'label' => 'xCoinMoney BTC', 'currency' => 2, 'class_name' => 'XCoinmoneyExchangeXCoinmoneyModule', 'file_name' => 'xcoinmoney.php'),
      array('name' => 'xcm_ltc', 'label' => 'xCoinMoney LTC', 'currency' => 3, 'class_name' => 'XCoinmoneyExchangeXCoinmoneyModule', 'file_name' => 'xcoinmoney.php'),
      array('name' => 'xcm_invoice_usd', 'label' => 'xCoinMoney Invoice USD', 'currency' => 1, 'class_name' => 'XCoinmoneyExchangeXCoinmoneyInvoiceModule', 'file_name' => 'xcoinmoney_invoice.php'),
      array('name' => 'xcm_invoice_btc', 'label' => 'xCoinMoney Invoice BTC', 'currency' => 2, 'class_name' => 'XCoinmoneyExchangeXCoinmoneyInvoiceModule', 'file_name' => 'xcoinmoney_invoice.php'),
      array('name' => 'xcm_invoice_ltc', 'label' => 'xCoinMoney Invoice LTC', 'currency' => 3, 'class_name' => 'XCoinmoneyExchangeXCoinmoneyInvoiceModule', 'file_name' => 'xcoinmoney_invoice.php'),
      array('name' => 'btc', 'label' => 'Bitcoin', 'currency' => 2, 'class_name' => 'XCoinmoneyExchangeBitcoinModule', 'file_name' => 'bitcoin.php'),
      array('name' => 'ltc', 'label' => 'Litecoin', 'currency' => 3, 'class_name' => 'XCoinmoneyExchangeLitecoinModule', 'file_name' => 'litecoin.php'),

      array('name' => 'xcm_merchant_usd', 'label' => 'xCoinMoney Merchant USD', 'currency' => 1, 'class_name' => 'XCoinmoneyExchangeXCoinmoneyMerchantModule', 'file_name' => 'xcoinmoney_merchant.php'),
      array('name' => 'xcm_merchant_btc', 'label' => 'xCoinMoney Merchant BTC', 'currency' => 2, 'class_name' => 'XCoinmoneyExchangeXCoinmoneyMerchantModule', 'file_name' => 'xcoinmoney_merchant.php'),
      array('name' => 'xcm_merchant_ltc', 'label' => 'xCoinMoney Merchant LTC', 'currency' => 3, 'class_name' => 'XCoinmoneyExchangeXCoinmoneyMerchantModule', 'file_name' => 'xcoinmoney_merchant.php')
    );

    $wpdb->query('DELETE FROM ' . $table_name . ' WHERE 1;' );
    foreach ($rows as $row) {
      $wpdb->insert($table_name, $row);
    }
  }

  $table_name = $prefix. '_exchange_ways';
  if($wpdb->get_var("show tables like '$table_name'") == $table_name) {
     $rows = array(
       array('system_from' => 4, 'system_to' => 2, 'fee_flat' => 0.5, 'fee_percentage' => 2.5),
       array('system_from' => 4, 'system_to' => 3, 'fee_flat' => '0.5', 'fee_percentage' => '2.5'),
       array('system_from' => 4, 'system_to' => 7, 'fee_flat' => '0.5', 'fee_percentage' => '3'),
       array('system_from' => 4, 'system_to' => 8, 'fee_flat' => '0.5', 'fee_percentage' => '3'),
       array('system_from' => 5, 'system_to' => 1, 'fee_flat' => '0.001', 'fee_percentage' => '2.5'),
       array('system_from' => 5, 'system_to' => 3, 'fee_flat' => '0.001', 'fee_percentage' => '2.5'),
       array('system_from' => 5, 'system_to' => 8, 'fee_flat' => '0.001', 'fee_percentage' => '3'),
       array('system_from' => 6, 'system_to' => 1, 'fee_flat' => '0.1', 'fee_percentage' => '2.5'),
       array('system_from' => 6, 'system_to' => 2, 'fee_flat' => '0.1', 'fee_percentage' => '2.5'),
       array('system_from' => 6, 'system_to' => 7, 'fee_flat' => '0.1', 'fee_percentage' => '3'),

       array('system_from' => 9, 'system_to' => 2, 'fee_flat' => 0.5, 'fee_percentage' => 2.5),
       array('system_from' => 9, 'system_to' => 3, 'fee_flat' => '0.5', 'fee_percentage' => '2.5'),
       array('system_from' => 9, 'system_to' => 7, 'fee_flat' => '0.5', 'fee_percentage' => '3'),
       array('system_from' => 9, 'system_to' => 8, 'fee_flat' => '0.5', 'fee_percentage' => '3'),
       array('system_from' => 10, 'system_to' => 1, 'fee_flat' => '0.001', 'fee_percentage' => '2.5'),
       array('system_from' => 10, 'system_to' => 3, 'fee_flat' => '0.001', 'fee_percentage' => '2.5'),
       array('system_from' => 10, 'system_to' => 8, 'fee_flat' => '0.001', 'fee_percentage' => '3'),
       array('system_from' => 11, 'system_to' => 1, 'fee_flat' => '0.1', 'fee_percentage' => '2.5'),
       array('system_from' => 11, 'system_to' => 2, 'fee_flat' => '0.1', 'fee_percentage' => '2.5'),
       array('system_from' => 11, 'system_to' => 7, 'fee_flat' => '0.1', 'fee_percentage' => '3')
     );

     $wpdb->query('DELETE FROM ' . $table_name . ' WHERE 1;' );
     foreach ($rows as $row) {
       $wpdb->insert($table_name, $row);
     }
  }
}

function xcoinmoney_exchange_uninstall() {
  global $wpdb;
  if (!empty($wpdb->prefix))
    $prefix = $wpdb->prefix . 'xcm';

  $sqls[] = "DROP TABLE IF EXISTS ". $prefix ."_transaction_details";
  $sqls[] = "DROP TABLE IF EXISTS ". $prefix ."_transactions";
  $sqls[] = "DROP TABLE IF EXISTS ". $prefix ."_exchange_ways";
  $sqls[] = "DROP TABLE IF EXISTS ". $prefix ."_exchange_systems";
  $sqls[] = "DROP TABLE IF EXISTS ". $prefix ."_exchange_rates";
  $sqls[] = "DROP TABLE IF EXISTS ". $prefix ."_currencies";

  foreach ($sqls as $sql){
    $wpdb->query($sql);
  }
}


