<div class="col-6 col-md-3 offcanvas-collapse" id="sidebar">
    <div class="list-group mb-2">

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
?>
    </div>

<?php if (method_exists($this, 'leftSidebar')) { ?>
        <div class="list-group">
            <?php
$this->leftSidebar();
?>
        </div>
    <?php } ?>
</div>