<?php
$plugin_prefix_root = plugin_dir_path( __FILE__ );
$plugin_prefix_filename = "{$plugin_prefix_root}/xcoinmoney_exchange.base.php";
include_once $plugin_prefix_filename;

class XCoinmoneyExchangeRates extends  XCoinmoneyExchangeBase {
  function process() {
    $pairs = $this->get_currency_pairs();
    if (!empty($pairs)) {
      foreach ($pairs as $res) {
        $currencies = explode('/', $res->pair);
        $rate = $this->get_value($currencies[0], $currencies[1]);
        if ($rate) {
          $this->update_rate($res->id, $rate['value']);

          $this->update_rate_in_ways($currencies[0], $currencies[1],  $rate['value']);
          $this->update_rate_in_ways($currencies[1], $currencies[0], 1 / $rate['value']);
        }
      }
    }
  }

  function update_rate_in_ways($currency1, $currency2, $rate) {
    $ways = $this->get_exchange_way_by_currencies($currency1, $currency2);

    foreach ($ways as $way) {
      $this->update_rate_in_way($way->id, $rate);
    }
  }

  function update_rate_in_way($wayId, $rate) {
    global $wpdb;

    $query = "UPDATE ". $wpdb->prefix ."xcm_exchange_ways SET exchange_rate='".$rate."'
     WHERE id=".$wayId;

    $wpdb->query($query);
  }

  function update_rate($pairId, $rate) {
    global $wpdb;

    $query = "UPDATE ". $wpdb->prefix ."xcm_exchange_rates SET rate='".$rate."'
     WHERE id=".$pairId;

    $wpdb->query($query);
  }

  function get_currency_pairs() {
    global $wpdb;
    $query = "SELECT * FROM ". $wpdb->prefix ."xcm_exchange_rates";

    $result = $wpdb->get_results($query);

    return $result;
  }

  public static function get_value($currency1, $currency2) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_URL, 'https://btc-e.com/api/2/'.strtolower($currency1).'_'.strtolower($currency2).'/ticker');
    curl_setopt($ch, CURLOPT_USERAGENT,
      'Mozilla/4.0 (compatible; MtGox PHP client; ' . php_uname('s') . '; PHP/' . phpversion() . ')');
    $curlRes = curl_exec($ch);
    curl_close($ch);

    $result = FALSE;

    if ($curlRes !== FALSE) {
      $json = json_decode($curlRes, TRUE);
      if (isset($json['ticker'])) {
        $result = array(
          'value'      => $json['ticker']['avg'],
          'value_min'  => $json['ticker']['low'],
          'value_max'  => $json['ticker']['high'],
          'value_last' => $json['ticker']['last'],
          'rate_time' => date('Y-m-d H:i:s', $json['ticker']['updated'])
        );
      }
    }

    return $result;
  }

  function get_exchange_way_by_currencies($currency1, $currency2) {
    global $wpdb;
    $query = "SELECT w.*
                FROM ". $wpdb->prefix ."xcm_exchange_ways w
                LEFT JOIN ". $wpdb->prefix ."xcm_exchange_systems es1 ON w.system_from = es1.id
                LEFT JOIN ". $wpdb->prefix ."xcm_exchange_systems es2 ON w.system_to = es2.id
                LEFT JOIN ". $wpdb->prefix ."xcm_currencies c1 ON es1.currency = c1.id
                LEFT JOIN ". $wpdb->prefix ."xcm_currencies c2 ON es2.currency = c2.id
                WHERE c1.`name`='".$currency1."' AND c2.`name`='".$currency2."' ";


    $result = $wpdb->get_results($query);

    return $result;
  }

}