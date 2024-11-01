<?php function render_admin_systems($data) {?>
  <div class="wrap">
    <div id="icon-options-general" class="icon32">
      <br>
    </div>
    <h2>Exchange Systems</h2>
    <?php
      if ( $data['setting_updated'] ) echo '<div id="message" class="updated fade"><p>xCoinMoney Exchange Systems Saved.</p></div>';
    ?>
    <form method="post">
      <p> </p>
      <table class="widefat fixed comments" cellspacing="0">
        <thead>
        <tr>
          <th id="system_from" class="manage-column" style="" scope="col">
           Name
          </th>
          <th id="system_to" class="manage-column" style="" scope="col">
           Label
          </th>
          <th id="manualy_exchange_rate" class="manage-column" style="" scope="col">
            Currency
          </th>
          <th id="is_active" class="manage-column" style="" scope="col">
            Enable In
          </th>
          <th id="is_active" class="manage-column" style="" scope="col">
            Enable Out
          </th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($data['allSystems'] as $system) {?>
          <tr>
            <td><?php echo $system->name ?></td>
            <td>
              <input type="text" class="regular-text"  name="exchange_system[<?php echo $system->id ?>][label]" value="<?php echo $system->label ?>">
            </td>
            <td><?php echo $system->currency_name ?></td>

            <td>
              <input type="checkbox" name="exchange_system[<?php echo $system->id ?>][enable_in]" <?php echo $system->enable_in == 1 ? 'checked' : '';?>>
            </td>
            <td>
              <input type="checkbox" name="exchange_system[<?php echo $system->id ?>][enable_out]" <?php echo $system->enable_out == 1 ? 'checked' : '';?>>
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
<? } ?>