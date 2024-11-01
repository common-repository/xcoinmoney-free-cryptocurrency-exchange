<?php function render_admin_dashboard($data) {?>
  <link rel='stylesheet' id='genericons-css'  href='<?php echo plugins_url( 'css/jquery-ui-1.10.4.custom.min.css' , __FILE__ ) ?>' type='text/css' media='all' />
  <script type='text/javascript' src='<?php echo plugins_url( 'js/jquery-1.11.0.min.js' , __FILE__ ) ?>'></script>
  <script type='text/javascript' src='<?php echo plugins_url( 'js/jquery-ui-1.10.4.custom.min.js' , __FILE__ ) ?>'></script>
  <script>
    $(function() {
      $('.dashboard-tooltip').tooltip();
    });
  </script>
  <div class="wrap">
    <div id="icon-options-general" class="icon32">
      <br>
    </div>
    <h2>General Options</h2>
    <?php
      if ( $data['setting_updated'] ) echo '<div id="message" class="updated fade"><p>xCoinMoney Options Saved.</p></div>';
    ?>
    <form method="post">
      <p> </p>
      <table class="form-table" cellspacing="0">
        <tbody>
          <tr>
            <th>Users must be registered</th>
            <td>
                <select name="xcoinmoney_exchange_enable_user_register">
                  <option value="0" <?php echo (!isset($data['xcoinmoney_exchange_enable_user_register']) || $data['xcoinmoney_exchange_enable_user_register'] == 0) ? "selected" : ""?>>No</option>
                  <option value="1" <?php echo $data['xcoinmoney_exchange_enable_user_register'] == 1 ? "selected" : ""?>>Yes</option>
                </select>
            </td>
          </tr>
          <tr>
            <th>xCoinMoney User ID</th>
            <td><input type="text" class="regular-text"  name="xcoinmoney_exchange_user_id" value="<?php echo $data['xcoinmoney_exchange_user_id'] ?>"></td>
          </tr>
          <tr>
            <th>xCoinMoney API Key</th>
            <td><input type="text" class="regular-text"  name="xcoinmoney_exchange_api_key" value="<?php echo $data['xcoinmoney_exchange_api_key'] ?>"></td>
          </tr>
          <tr>
            <th>xCoinMoney Merchant Email</th>
            <td><input type="text" class="regular-text"  name="xcoinmoney_exchange_merchant_email" value="<?php echo $data['xcoinmoney_exchange_merchant_email'] ?>"></td>
          </tr>
        </tbody>
      </table>

      <p class="submit">
        <input id="submit" class="button button-primary" type="submit" value="Save Changes" name="submit">
      </p>
      <a href="http://www.xcoinmoney.com/user/settings" > Where can I get this info?</a>
    </form>
  </div>
<? } ?>