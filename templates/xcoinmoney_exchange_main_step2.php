<?php
function render_main_form_step2($data) { ?>
  <script type='text/javascript' src='<?php echo plugins_url( 'js/script.js' , __FILE__ ) ?>'></script>
  <link rel='stylesheet' id='genericons-css'  href='<?php echo plugins_url( 'css/style.css' , __FILE__ ) ?>' type='text/css' media='all' />
  <div>
    <form method="post">
      <?php if(!empty($data['error_form'])) { ?>
        <ul class="error">
          <?php foreach($data['error_form'] as $error) { ?>
            <li> <?php echo $error ?></li>
          <?php } ?>
        </ul>
      <?php } ?>

      <input type="hidden" name="step" value="2">
      <input type="hidden" name="amount_to" value="<?php echo $data['amount_to']; ?>">
      <div class="payment-system-box">
        <h4>From </h4>
        <?php echo $data['form_from']; ?>
        <?php if(isset($data['error_form_from'])) { ?>
          <ul class="error">
            <?php foreach($data['error_form_from'] as $error) { ?>
              <li> <?php echo $error ?></li>
            <?php } ?>
          </ul>
        <?php } ?>
      </div>
      <div class="payment-system-box">
        <h4>To</h4>
        <?php echo $data['form_to']; ?>
        <?php if(isset($data['error_form_to'])) { ?>
          <ul class="error">
            <?php foreach($data['error_form_to'] as $error) { ?>
              <li> <?php echo $error ?></li>
            <?php } ?>
          </ul>
        <?php } ?>
      </div>
      <input id="submit" class="button button-primary" type="submit" value="Preview" name="submit">
    </form>
  </div>

<? } ?>