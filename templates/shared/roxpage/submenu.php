
<div id="middle_nav" class="clearfix">
  <div id="nav_sub">
    <ul>
      <?php

$active_menu_item = $this->getSubmenuActiveItem();
foreach ($this->getSubmenuItems() as $index => $item) {
    $name = $item[0];
    $url = $item[1];
    $label = $item[2];
    if ($name === $active_menu_item) {
        $attributes = ' class="active"';
    } else {
        $attributes = '';
    }
    
    ?><li id="sub<?=$index ?>" <?=$attributes ?>>
      <a style="cursor:pointer;" href="<?=$url ?>">
        <span><?=$label ?></span>
      </a>
      <?=$words->flushBuffer(); ?>
    </li>
    <?php
    
}

    ?></ul>
  </div>
</div>


