


<!-- #nav: main navigation -->
<div id="nav">
  <div id="nav_main">
<div id="nicemenu">
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
            <span class="head_menu">
            <a href="<?=$url ?>">
              <? if ($not_translatable) { echo $wordcode; } else { echo $words->getBuffered($wordcode); } ?>
            </a>
            </span>
            <div class="sub_menu">
            <?php 
            $max = count($item);
            for ($ii = 0; $ii < $max; $ii++) {
                if ($ii > 2) {
                    $name = $item[$ii][0];
                    $url = $item[$ii][1];
                    $wordcode = $item[$ii][2];
                    ?>
                <a href="<?=$url ?>">
                  <? if ($not_translatable) { echo $wordcode; } else { echo $words->getBuffered($wordcode); } ?>
                </a>        
            <?php
                }
            }
            ?>
            </div>
        <?=$words->flushBuffer(); ?>
      </li>
      <?php
      
}

      ?>
    </ul>
    
  </div>
  </div>
</div>
<!-- #nav: - end -->




