<?php if ($side_column_names){ ?>
        <?php foreach ($side_column_names as $column_name) { ?>
        <div class="col-6 col-md-3 <?=$column_name ?>">
              <?php $this->_column($column_name) ?>
          </div>
        <?php } ?>
        <div class="sidebar-offcanvas <?=$mid_column_name ?> pl-3" id="sidebar">
            <?php $this->_column($mid_column_name) ?>
        </div>
<?php } else { ?>
    <div id="content">
        <?php $this->_column($mid_column_name) ?>
    </div> <!-- content -->
<?php } ?>