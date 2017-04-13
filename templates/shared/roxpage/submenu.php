<div class="col-6 col-md-3 sidebar-offcanvas" id="sidebar">
    <div class="list-group">

<?php
$active_menu_item = $this->getSubmenuActiveItem();
foreach ($this->getSubmenuItems() as $index => $item) {
    $name = $item[0];
    $url = $item[1];
    $label = $item[2];
    if ($name === $active_menu_item) {
        $classes = 'nav-link active';
    } else {
        $classes = 'nav-link';
    }
    
    ?>
      <a class="list-group-item <?=$classes ?>" href="<?=$url ?>"><?=$label ?></a>
      <?=$words->flushBuffer(); ?>
    <?php
}


if ($side_column_names) {
    foreach ($side_column_names as $column_name) { ?>
            <?php $this->_column($column_name) ?>
    <?php }
}

?>
    </div>
</div>