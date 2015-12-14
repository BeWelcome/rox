<?php if ($side_column_names){ ?>
    <div class="bw_row">
        <?php foreach ($side_column_names as $column_name) { ?>
          <div id="<?=$column_name ?>">
              <?php $this->_column($column_name) ?>
          </div> <!-- <?=$column_name ?> -->
        <?php } ?>
        <div id="<?=$mid_column_name ?>">
            <?php $this->_column($mid_column_name) ?>
        </div> <!-- <?=$mid_column_name ?> -->
    </div>
<?php } else { ?>
    <div id="content">
        <?php $this->_column($mid_column_name) ?>
    </div> <!-- content -->
<?php } ?>