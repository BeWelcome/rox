<div class="p-l-0 m-b-1"><ul class="nav nav-pills">
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
    
    ?><li class="nav-item">
      <a class="<?=$classes ?>" href="<?=$url ?>"><?=$label ?></a>
      <?=$words->flushBuffer(); ?>
    </li>
    <?php
    
}?>
</ul>
</div>