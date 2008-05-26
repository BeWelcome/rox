

<!-- #page_margins: Obsolete for now. If we decide to use a fixed width or want a frame around the page, we will need them aswell -->
<div id="page_margins">
  <!-- #page: Used to hold the floats -->
  <div id="page" class="hold_floats">
    
    <div id="header">
    <?php $this->topmenu() ?>
      <div id="topnav">
        <?php $this->topnav() ?>
      <a href="start">
        <img id="logo" class="float_right overflow" src="styles/YAML/images/logo.gif"
          width="250" height="48" alt="Be Welcome" />
      </a>
      </div> <!-- topnav -->

    </div> <!-- header -->
    
    <!-- #main: content begins here -->
    <div id="main">
      <?php $this->teaser() ?>
      <?php $this->columnsArea() ?>
    </div> <!-- main -->
    <?php $this->footer() ?>
    <?php $this->leftoverTranslationLinks() ?>
  </div> <!-- page -->
</div> <!-- page_margins-->
<?php $this->debugInfo() ?>


