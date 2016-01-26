<div class="error">
<h3><?= $words->get('MessagesError'); ?></h3>

  <?php foreach ($memory->problems as $key => $value) { ?>
  <p><?=$value ?></p>
  <?php } ?>

</div>
