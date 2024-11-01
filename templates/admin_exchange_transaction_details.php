<?php function render_admin_transaction_details($data) { ?>
  <div class="wrap">
    <div id="icon-options-general" class="icon32">
      <br>
    </div>
    <h2>Transaction Details</h2>
      <p> </p>
    <a class="button button-primary" href="admin.php?page=xcoinmoney_exchange_transactions">All transactions</a>
      <table class="form-table" cellspacing="0">
        <tbody>
          <?php if (!empty($data['transaction']['user'])) { ?>
            <tr>
              <th>User</th>
              <td>
                 <?php echo $data['transaction']['user']; ?>
              </td>
            </tr>
          <?php } ?>
          <tr>
            <th>System From</th>
            <td>

              <table class="widefat fixed " cellspacing="0">
                <tbody>

                <tr>
                  <td>Name:</td>
                  <td><?php echo $data['transaction']['system_from_name']; ?></td>
                </tr>
                <tr>
                  <td>Transaction ID:</td>
                  <td><?php echo $data['transaction']['transaction_from']; ?></td>
                </tr>
                <tr>
                  <td>Amount:</td>
                  <td><?php echo $data['transaction']['amount_from']." ".$data['transaction']['currency_name_from']; ?></td>
                </tr>
                <tr>
                  <td>Fee:</td>
                  <td>
                    <?php echo $data['transaction']['profit']." ".$data['transaction']['currency_name_from'];; ?>
                  </td>
                </tr>
                <?php if (!empty($data['transaction']['fields_from'])) { ?>
                  <?php foreach($data['transaction']['fields_from'] as $field) {
                    echo "<tr><td>".$field['label'].": </td><td> ".$field['value']."</td></tr>";
                   } ?>
                  <?php } ?>
                </tbody>
              </table>
            </td>
          </tr>


          <tr>
            <th>System To</th>
            <td>

              <table class="widefat fixed " cellspacing="0">
                <tbody>

                <tr>
                  <td>Name:</td>
                  <td><?php echo $data['transaction']['system_to_name']; ?></td>
                </tr>
                <tr>
                  <td>Transaction ID:</td>
                  <td><?php echo $data['transaction']['transaction_to']; ?></td>
                </tr>
                <tr>
                  <td>Amount:</td>
                  <td><?php echo $data['transaction']['amount_to']." ".$data['transaction']['currency_name_to']; ?></td>
                </tr>
                <tr>
                  <td>Fee:</td>
                  <td>
                    <?php echo $data['transaction']['fee_system']." ".$data['transaction']['currency_name_to'];; ?>
                  </td>
                  <?php if (!empty($data['transaction']['fields_to'])) { ?>
                    <?php foreach($data['transaction']['fields_to'] as $field) {
                      echo "<tr><td>".$field['label'].": </td><td> ".$field['value']."</td></tr>";
                    } ?>
                  <?php } ?>
                </tr>
                </tbody>
              </table>
            </td>
          </tr>

          <tr>
            <th>Exchange Rate</th>
            <td>
              <?php echo $data['transaction']['exchange_rate']; ?>
            </td>
          </tr>
          <tr>
            <th>Status</th>
            <td>
              <?php echo $data['transaction']['status']; ?>
            </td>
          </tr>
          <tr>
            <th>Date</th>
            <td>
              <?php echo $data['transaction']['created']; ?>
            </td>
          </tr>

        </tbody>

      </table>
  </div>


<?php } ?>