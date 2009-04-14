<div id="profile">
    <div class="profile_translations">
        <?php 
        $urlstring = 'members/'.$member->Username;
        require 'profileversion.php'; 
        ?>
    </div> <!-- profile_translations -->
    
  <div class="subcolumns" id="profile_subcolumns">

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
