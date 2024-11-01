<?php function render_admin_currencies($data) {?>
  <div class="wrap">
    <div id="icon-options-general" class="icon32">
      <br>
    </div>
    <h2>Currencies</h2>
    <form method="post">
      <p> </p>
      <table class="widefat fixed comments" cellspacing="0">
        <thead>
        <tr>
          <th id="fee_flat" class="manage-column" style="" scope="col">
            Name
          </th>

        </tr>
        </thead>
        <tbody>
        <?php foreach ($data['allCurrencies'] as $currency) {?>
          <tr>
            <td><?php echo $currency->name ?></td>
          </tr>
        <?php } ?>
        </tbody>
      </table>


    </form>
  </div>
<? } ?>