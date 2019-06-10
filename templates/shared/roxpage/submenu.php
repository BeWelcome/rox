<div class="col-md-3 offcanvas-collapse mb-2" id="sidebar">
    <div class="w-100 p-1 text-right d-md-none">
        <button type="button" class="btn btn-sm" aria-label="Close" data-toggle="offcanvas">
            <i class="fa fa-lg fa-times" aria-hidden="true"></i>
        </button>
    </div>
    <div class="list-group mb-2">

<?php
$active_menu_item = $this->getSubmenuActiveItem();
foreach ($this->getSubmenuItems() as $index => $item) {
    $name = $item[0];
    $url = $item[1];
    $label = $item[2];
    if ($name === $active_menu_item) {
        echo '<span class="list-group-item nav-link active">' . $label . '</span>';
    } else {
        echo '<a class="list-group-item nav-link" href="' . $url . '">' . $label . '</a>';
    }

    ?>
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
