<?php
function render_main_form_step3($data) {
?>
  <link rel='stylesheet' id='genericons-css'  href='<?php echo plugins_url( 'css/style.css' , __FILE__ ) ?>' type='text/css' media='all' />
  <div>
    <form method="post">
      <input type="hidden" name="step" value="3">
      <?php if(!empty($data['error_form'])) { ?>
        <ul class="error">
          <?php foreach($data['error_form'] as $error) { ?>
            <li> <?php echo $error ?></li>
          <?php } ?>
        </ul>
      <?php } ?>

      <?php if(isset($data['form_data'])) {
        foreach ($data['form_data'] as $k=>$v) { ?>
          <input type="hidden" name="<?php echo $k; ?>" value="<?php echo $v; ?>">
       <?php }
      }?>
      <h3>Your request!</h3>
      <table>
        <tr>
          <td>From</td>
          <td>To</td>
        </tr>
        <tr>
          <td><? echo $data['system_from_name'] ?></td>
          <td><? echo $data['system_to_name'] ?></td>
        </tr>
        <?php if (!empty($data['fields_from']) || !empty($data['fields_to'])) { ?>
          <tr>
            <td>
              <?php foreach($data['fields_from'] as $field) {
                echo "<p>".$field['label']." : <br />".$field['value']."<p>";
              } ?>
            </td>
            <td>
              <?php foreach($data['fields_to'] as $field) {
                echo "<p>".$field['label']." : <br />".$field['value']."<p>";
              } ?>
            </td>
          </tr>
        <?php } ?>
        <tr>
          <td colspan="2"> Exchange rate: <? echo $data['exchange_rate'] ?></td>
        </tr>
        <tr>
          <td colspan="2"> Fee: <? echo $data['fee']." ".$data['currency_name_from'] ?></td>
        </tr>
        <tr>
          <td><? echo $data['amount_from']." ".$data['currency_name_from'] ?></td>
          <td><? echo $data['amount_to']." ".$data['currency_name_to'] ?></td>
        </tr>
      </table>

      <input id="submit" class="button button-primary" type="submit" value="Process" name="submit">

    </form>
  </div>
<? } ?>