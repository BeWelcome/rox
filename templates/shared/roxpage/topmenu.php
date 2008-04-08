


<!-- #nav: main navigation -->
<div id="nav">
  <div id="nav_main">
    <ul>
      <?php

foreach ($menu_items as $item) {
    $name = $item[0];
    $url = $item[1];
    $wordcode = $item[2];
    if ($name === $active_menu_item) {
        $attributes = ' class="active"';
    } else {
        $attributes = '';
    }
        
      ?>
      <li<?=$attributes ?>>
        <a href="<?=$url ?>">
          <span><?=$words->getBuffered($wordcode); ?></span>
        </a>
        <?=$words->flushBuffer(); ?>
      </li>
      <?php
      
}

      ?>
    </ul>
    
    <!-- #nav_flowright: This part of the main navigation floats to the right. The items have to be listed in reversed order to float properly-->         
    <div id="nav_flowright">
      <?php $this->quicksearch() ?>
    </div> <!-- nav_flowright -->
  </div>
</div>
<!-- #nav: - end -->



