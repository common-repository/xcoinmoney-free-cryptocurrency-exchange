<?php
function render_main_form_thank_you($data) {
  $transaction = $data['transaction'];
?>
  <div>
    <h3>Your request has been accepted! Thank You</h3>
    <table>
      <tr>
        <td>From</td>
        <td>To</td>
      </tr>
      <tr>
        <td><? echo $transaction['system_from_name'] ?></td>
        <td><? echo $transaction['system_to_name'] ?></td>
      </tr>
      <?php if (!empty($data['fields_from']) || !empty($data['fields_to'])) { ?>
        <tr>
          <td>
            <?php foreach($data['fields_from'] as $field) {
              echo "<p>".$field['label'].": <br />".$field['value']."<p>";
            } ?>
          </td>
          <td>
            <?php foreach($data['fields_to'] as $field) {
              echo "<p>".$field['label'].":<br />".$field['value']."<p>";
            } ?>
          </td>
        </tr>
      <?php } ?>
      <tr>
        <td colspan="2"> Exchange rate: <? echo $transaction['exchange_rate'] ?></td>
      </tr>
      <tr>
        <td colspan="2"> Fee: <? echo $transaction['profit']." ".$transaction['currency_name_from'] ?></td>
      </tr>
      <tr>
        <td><? echo $transaction['amount_from']." ".$transaction['currency_name_from'] ?></td>
        <td><? echo $transaction['amount_to']." ".$transaction['currency_name_to'] ?></td>
      </tr>
    </table>

  </div>
<? } ?>