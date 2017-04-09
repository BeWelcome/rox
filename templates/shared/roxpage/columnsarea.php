<?php if ($side_column_names){ ?>
        <?php foreach ($side_column_names as $column_name) { ?>
        <div class="col-12 col-md-9 columnsarea<?=$column_name ?>">
              <?php $this->_column($column_name) ?>
          </div>
        <?php } ?>
        <div class="col-md-3 sidebar-offcanvas columnsarea<?=$mid_column_name ?>" id="sidebar">
            <?php $this->_column($mid_column_name) ?>
        </div>
<?php } else { ?>
    <div id="content">
        <?php $this->_column($mid_column_name) ?>
    </div> <!-- content -->
<?php } ?>