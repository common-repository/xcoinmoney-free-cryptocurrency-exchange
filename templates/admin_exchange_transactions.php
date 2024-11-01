<?php function render_admin_transactions($data) {?>
  <link rel='stylesheet' id='genericons-css'  href='<?php echo plugins_url( 'css/jquery-ui-1.10.4.custom.min.css' , __FILE__ ) ?>' type='text/css' media='all' />
  <script type='text/javascript' src='<?php echo plugins_url( 'js/jquery-1.11.0.min.js' , __FILE__ ) ?>'></script>
  <script type='text/javascript' src='<?php echo plugins_url( 'js/jquery-ui-1.10.4.custom.min.js' , __FILE__ ) ?>'></script>
  <script>
    $(function() {
      $('.date').datepicker({ dateFormat: "yy-mm-dd" });
    });
  </script>

  <div class="wrap">
  <div id="icon-options-general" class="icon32">
    <br>
  </div>

    <h2>Profit</h2>
    <form method="get" >
       <input type="hidden" name="page" value="xcoinmoney_exchange_transactions">
       Date From
       <input type="text" name="date_from" value="<?php echo $data['date_from']; ?>" class="date" id="date_from"/>
       Date To
       <input type="text" name="date_to" value="<?php echo $data['date_to']; ?>" class="date" id="date_to"/>
       <button type="submit" class="button button-primary">Search</button>
    </form>
    <?php if (!empty($data['balance'])) { ?>
    <table class="widefat fixed comments" cellspacing="0">
      <?php foreach ($data['balance'] as $balance) { ?>
        <tr>
          <td style="width:15%;">
             <?php echo $balance['currency'] ?>:
          </td>
          <td>
            <?php echo $balance['amount'] ?>
          </td>
        </tr>
      <?php } ?>
    </table>
    <?php }?>

    <h2>Transactions</h2>
    <?php if(!empty($data['allTransactions'])) { ?>
    <table class="widefat fixed comments" cellspacing="0">
      <thead>
        <tr>
          <th id="system_from" class="manage-column" style="" scope="col">
            From
          </th>
          <th id="amount_from" class="manage-column" style="" scope="col">
            Amount
          </th>
          <th id="system_to" class="manage-column" style="" scope="col">
            To
          </th>
          <th id="amount_to" class="manage-column" style="" scope="col">
            Amount
          </th>
          <th id="rate" class="manage-column" style="" scope="col">
            Exchange Rate
          </th>
          <th id="status" class="manage-column" style="" scope="col">
            Status
          </th>
          <th id="date" class="manage-column" style="" scope="col">
            Date
          </th>
          <th id="date" class="manage-column" style="" scope="col">

          </th>
        </tr>
      </thead>
      <tbody>
    <?  foreach ($data['allTransactions'] as $transaction) { ?>
      <tr>
        <td>
          <?php echo $transaction->system_from_name; ?>
        </td>
        <td>
          <?php echo round_amount($transaction->amount_from, $transaction->currency_name_from)." ".$transaction->currency_name_from ; ?>
        </td>
        <td>
          <?php echo $transaction->system_to_name; ?>
        </td>
        <td>
          <?php echo round_amount($transaction->amount_to, $transaction->currency_name_to)." ".$transaction->currency_name_to ; ?>
        </td>
        <td>
          <?php echo $transaction->exchange_rate ; ?>
        </td>
        <td>
          <?php echo $transaction->status ; ?>
        </td>
        <td>
          <?php echo $transaction->created ; ?>
        </td>
        <td>
          <a href="admin.php?page=xcoinmoney_exchange_transactions&transaction_id=<?php  echo $transaction->id ; ?>">Details</a>
        </td>
      </tr>
    <?php } ?>
      </tbody>
    </table>
    <?php

    $page_links = paginate_links( array(
      'base' => add_query_arg( 'pagenum', '%#%' ),
      'format' => '',
      'prev_text' => __( '&laquo;', 'text-domain' ),
      'next_text' => __( '&raquo;', 'text-domain' ),
      'total' => $data['numOfPages'],
      'current' => $data['pagenum']
    ) );

    if ( $page_links ) {
      echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
    }

  }else{ ?>
      Sorry, there are no transactions yet
    <?php  } ?>
<?php }

function round_amount($amount, $paymentSystem = 'BTC') {
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
} ?>
