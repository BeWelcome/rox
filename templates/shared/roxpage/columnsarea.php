<?php if ($side_column_names){ ?>
    <div class="row mt-3">
        <?php foreach ($side_column_names as $column_name) { ?>
          <div class="<?=$column_name ?>">
              <?php $this->_column($column_name) ?>
          </div>
        <?php } ?>
        <div class="<?=$mid_column_name ?> pl-3">
            <?php $this->_column($mid_column_name) ?>
        </div>
    </div>
<?php } else { ?>
    <div id="content">
        <?php $this->_column($mid_column_name) ?>
    </div> <!-- content -->
<?php } ?>