<div class="warning">
<h3>There were problems in your sent data.</h3>
<table>
  <tr><th>Field name</th><th>Problem</th></tr>
  <?php foreach ($memory->problems as $key => $value) { ?>
  <tr><td><?=$key ?></td><td><?=$value ?></td></tr>
  <?php } ?>
</table>
</div>