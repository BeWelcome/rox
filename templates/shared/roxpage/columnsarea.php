
<?php foreach ($side_column_names as $column_name) { ?>

  <div id="<?=$column_name ?>">
    <div id="<?=$column_name ?>_content" class="clearfix">
      <?php $this->_column($column_name) ?>
    </div> <!-- <?=$column_name ?>_content -->
  </div> <!-- <?=$column_name ?> -->

<?php } ?>

  <div id="<?=$mid_column_name ?>">
    <div id="<?=$mid_column_name ?>_content" class="clearfix">
      <?php $this->_column($mid_column_name) ?>
    </div> <!-- <?=$mid_column_name ?>_content -->
    <!-- IE Column Clearing -->
    <div id="ie_clearing">&nbsp;</div>
    <!-- Ende: IE Column Clearing -->
  </div> <!-- <?=$mid_column_name ?> -->
