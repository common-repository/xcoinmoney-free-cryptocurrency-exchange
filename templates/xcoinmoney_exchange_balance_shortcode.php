<?php
function render_balance_shortcode_form($data) { ?>
  <link rel='stylesheet' id='genericons-css'  href='<?php echo plugins_url( 'css/style.css' , __FILE__ ) ?>' type='text/css' media='all' />
  <div>
     <h3>Exchange Available Balance</h3>
      <?
      if (empty($data['error'])) {
        foreach ($data['currencies'] as $currency) { ?>
        <p><?php echo $currency['name']; ?> : <?php echo $currency['amount']; ?></p>
        <?php }
      }else{ ?>
        <ul  class="error">
          <?php foreach($data['error'] as $error) { ?>
            <li> <?php echo $error ?></li>
          <?php } ?>
        </ul>
     <?  } ?>
  </div>
<? } ?>