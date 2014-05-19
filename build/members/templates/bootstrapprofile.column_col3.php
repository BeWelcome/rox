<div id="profile">
   <?php $style='';
   if ($this->passedAway) {
       $style = 'style="border: .25em solid black; padding: .5em;"';
   } ?>
  <div class="subcolumns" id="profile_subcolumns" <?=$style?>>

    <div class="c50l" >
        <? require 'bootstrapprofile.subcolumn_left.php' ?>
    </div> <!-- c50l -->
    <div class="c50r" >
        <? require 'bootstrapprofile.subcolumn_right.php' ?>
    </div> <!-- c50r -->
  </div> <!-- subcolumns -->
</div> <!-- profile -->
