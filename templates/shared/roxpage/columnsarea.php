<?php if ($side_column_names){ ?>
    <div class="row mt-3">
        <?php foreach ($side_column_names as $column_name) { ?>
          <div id="<?=$column_name ?>" class="col-sm-12 col-lg-9">
              <?php $this->_column($column_name) ?>
          </div> <!-- <?=$column_name ?> -->
        <?php } ?>
        <div id="<?=$mid_column_name ?>" class="col-sm-12 col-lg-3">
            <?php $this->_column($mid_column_name) ?>
        </div> <!-- <?=$mid_column_name ?> -->
    </div>
<?php } else { ?>
    <div id="content" class="col-md-12">
        <?php $this->_column($mid_column_name) ?>
    </div> <!-- content -->
<?php } ?>