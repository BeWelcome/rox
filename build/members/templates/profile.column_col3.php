<?php $style='';
if ($this->passedAway) {
    $style = 'style="border: .25em solid black; padding: .5em;"';
} ?>
<div class="row mt-2">
    <div class="col-12 col-lg-6">
      <?php require 'profile.subcolumn_left.php' ?>
    </div>
    <div class="col-12 col-lg-6">
        <?php require 'profile.subcolumn_right.php' ?>
    </div>
</div>