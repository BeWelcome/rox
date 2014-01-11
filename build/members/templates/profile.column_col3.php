<div id="profile">
   <?php $style='';
   if ($this->passedAway) {
       $style = 'style="border: .25em solid black; padding: .5em;"';
   } ?>
  <div class="subcolumns" id="profile_subcolumns" <?=$style?>>

    <div class="c50l" >
      <div class="subcl" >
        <? require 'profile.subcolumn_left.php' ?>
      </div> <!-- subcl -->
    </div> <!-- c50l -->
    <div class="c50r" >
      <div class="subcr" >
        <? require 'profile.subcolumn_right.php' ?>
      </div> <!-- subcr -->
    </div> <!-- c50r -->

  </div> <!-- subcolumns -->
    
</div> <!-- profile -->
