<?php
function render_main_form($data) {
  ?>
  <div>

    <link rel='stylesheet' id='genericons-css'  href='<?php echo plugins_url( 'css/style.css' , __FILE__ ) ?>' type='text/css' media='all' />
    <script type='text/javascript' src='<?php echo plugins_url( 'js/script.js' , __FILE__ ) ?>'></script>
    <script type="text/javascript">
      var AJAX_URL = '<?php echo plugins_url( '/../xcoinmoney_exchange.ajax.php' , __FILE__ ) ?>';
    </script>

    <?php if(!empty($data['error'])) { ?>
    <ul class="error">
      <?php foreach ($data['error'] as $error ) { ?>
          <li><? echo $error ?></li>
      <?php } ?>
    </ul>
    <?php } ?>

    <form method="post" action="<?php the_permalink(); ?>">
      <input type="hidden" name="step" value="1">
      <p>I want to get:
        <input class="small-text" type="text" value="" name="amount_to" placeholder="Amount" id="amount">
      </p>
      <p>
        From <select name="system_from" id="system_from_select">
          <option value="0">Select </option>
          <? foreach ($data['systemsIn'] as $in) { ?>
            <option value="<? echo $in->id ?>"><? echo $in->label ?> </option>
          <? } ?>
        </select>
        To <select name="system_to" id="system_to_select">
          <option value="0">Select </option>
        </select>
      </p>
      <p>
        <input id="submit" class="button button-primary" type="submit" value="Exchange" name="submit">
      </p>
    </form>

    <p style="display:none">You will get:
      <span id="amount_get"></span>
      <span id="currency_get"></span>
      </p>
  </div>
<? }
?>