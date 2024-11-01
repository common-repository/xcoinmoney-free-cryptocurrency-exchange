<?php
function  render_ways_shortcode_form($data) { ?>
      <div>
        <table class="widefat fixed comments" cellspacing="0">
          <thead>
          <tr>
            <th id="system_from" class="manage-column" style="" scope="col">
              System From
            </th>
            <th id="system_to" class="manage-column" style="" scope="col">
              System To
            </th>
            <th id="fee_percentage" class="manage-column" style="" scope="col">
              Fee %
            </th>
            <th id="fee_flat" class="manage-column" style="" scope="col">
              Min Fee
            </th>
            <th id="exchange_rate" class="manage-column" style="" scope="col">
              Exchange Rate
            </th>
          </tr>
          </thead>
          <tbody>
          <?php foreach ($data['allWays'] as $way) {?>
            <tr>
              <td><?php echo $way->system_from_name ?></td>
              <td><?php echo $way->system_to_name ?></td>
              <td>
                <?php echo round_amount($way->fee_percentage,'%').' %'; ?>
              </td>
              <td>
                <?php echo round_amount($way->fee_flat, $way->currency_from_name)." ".$way->currency_from_name; ?>
              </td>
              <td>
                <?php if($way->is_manualy_exchange_rate && !empty($way->manualy_exchange_rate)) {?>
                  <?php echo $way->manualy_exchange_rate ?>
                <?php }else {?>
                  <?php echo $way->exchange_rate ?>
                <?php }?>
              </td>

            </tr>
          <?php } ?>
          </tbody>
        </table>
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