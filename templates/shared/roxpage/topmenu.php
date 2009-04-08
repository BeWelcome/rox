


<!-- #nav: main navigation -->
<div id="nav">
  <div id="nav_main">
    <ul>
    
<!-- Disabled until we have a new topnaviagtion design
        <li>
          <a href="<?=$active_menu_item == ('main' || '') ? 'start' : ''; ?>">
            <img id="logo" class="float_right overflow" src="images/logo_index_top.png" alt="Be Welcome" />
          </a>
        </li>
-->
      <?php

foreach ($menu_items as $item) {
    $name = $item[0];
    $url = $item[1];
    $wordcode = $item[2];
    $not_translatable = isset($item[3]) && $item[3];
    if ($name === $active_menu_item) {
        $attributes = ' class="active"';
    } else {
        $attributes = '';
    }
        
      ?>
      <li<?=$attributes ?>>
        <a href="<?=$url ?>">
          <span><? if ($not_translatable) { echo $wordcode; } else { echo $words->getBuffered($wordcode); } ?></span>
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



