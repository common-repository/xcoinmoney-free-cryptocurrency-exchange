<?php function render_admin_ways($data) {?>
<div class="wrap">
  <div id="icon-options-general" class="icon32">
    <br>
  </div>
  <h2>Exchange Ways</h2>
  <?php
    if ( $data['setting_updated'] ) echo '<div id="message" class="updated fade"><p>xCoinMoney Exchange Ways Saved.</p></div>';
  ?>
  <form method="post">
    <p> </p>
    <p class="submit">
      <input id="submit" class="button button-primary" type="submit" value="Save Changes" name="submit">
      <a href="admin.php?page=xcoinmoney_exchange_ways&update_rates=1" style="float:right;">Update Rates</a>
    </p>

    <table class="widefat fixed comments" cellspacing="0">
      <thead>
        <tr>
          <th id="system_from" class="manage-column" style="width:15%" scope="col">
            System From
          </th>
          <th id="system_to" class="manage-column" style="width:15%" scope="col">
            System To
          </th>
          <th id="fee_percentage" class="manage-column" style="width:7%" scope="col">
            Fee %
          </th>
          <th id="fee_flat" class="manage-column"style="width:10%" scope="col">
            Min Fee
          </th>
          <th id="exchange_rate" class="manage-column" style="width:7%" scope="col">
            Exchange Rate
          </th>
          <th id="is_manualy_exchange_rate" class="manage-column" style="width:5%" scope="col">
            Set Rate Manually
          </th>
          <th id="manualy_exchange_rate" class="manage-column" style="width:15%" scope="col">
            Manualy Exchange Rate
          </th>
          <th id="is_active" class="manage-column" style="width:5%" scope="col">
            Active
          </th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($data['allWays'] as $way) {?>
            <tr>
               <td><?php echo $way->system_from_name ?></td>
               <td><?php echo $way->system_to_name ?></td>
               <td>
                 <input type="text" class="small-text" name="exchange_way[<?php echo $way->id ?>][fee_percentage]" value="<?php echo round_amount($way->fee_percentage,'%'); ?>">%
               </td>
               <td>
                 <input type="text" class="small-text" name="exchange_way[<?php echo $way->id ?>][fee_flat]" value="<?php echo round_amount($way->fee_flat, $way->currency_from_name); ?>"><?php echo $way->currency_from_name; ?>
               </td>
               <td>
                 <?php echo $way->exchange_rate ?>
               </td>
               <td>
                 <input type="checkbox" name="exchange_way[<?php echo $way->id ?>][is_manualy_exchange_rate]" <?php echo $way->is_manualy_exchange_rate == 1 ? 'checked' : '';?>>
               </td>
               <td>
                 <input type="text" class="text" name="exchange_way[<?php echo $way->id ?>][manualy_exchange_rate]" value="<?php echo $way->manualy_exchange_rate ?>">
               </td>
               <td>
                 <input type="checkbox" name="exchange_way[<?php echo $way->id ?>][is_active]" <?php echo $way->is_active == 1 ? 'checked' : '';?>>
               </td>

            </tr>
        <?php } ?>
      </tbody>
    </table>

    <p class="submit">
      <input id="submit" class="button button-primary" type="submit" value="Save Changes" name="submit">
    </p>
  </form>
</div>
<? }
function round_amount($amount, $paymentSystem = 'BTC') {
  if (in_array($paymentSystem, array('USD', 'EUR', '%'))) {
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