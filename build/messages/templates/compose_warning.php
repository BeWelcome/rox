<div class="error">
<h3><?= $words->get('MessagesError'); ?></h3>

  <?php foreach ($memory->problems as $key => $value) { ?>
  <strong><?=$key ?></strong>
  <p><?=$value ?></p>
  <?php } ?>

</div>
