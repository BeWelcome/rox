

<!-- #page_margins: Obsolete for now. If we decide to use a fixed width or want a frame around the page, we will need them aswell -->
<div id="page_margins">
  <!-- #page: Used to hold the floats -->
  <div id="page" class="hold_floats">
    
    <div id="header_wrapper" class="wrapper">
    <div id="header" class="inner_wrapper">
      <div id="topnav">
      <div id="logobox" alt="Be Welcome">
          <a href="start">
          </a>

      </div>
              <?php $this->topnav() ?>
      </div> <!-- topnav -->

    
    <?php $this->topmenu() ?>
    </div> <!-- header -->
    </div>

    <div id="teaser_wrapper" class="wrapper">
    <div id="teaser_inner_wrapper" class="inner_wrapper">
        <?php $this->teaser() ?>
    </div>
    </div>

    <!-- #main: content begins here -->
    <div id="main_wrapper" class="wrapper">
    <div id="main" class="inner_wrapper">
      <?php $this->columnsArea() ?>
    </div> <!-- main -->
    </div>
    
    <div id="footer_wrapper" class="wrapper">
    <div id="footer_inner_wrapper" class="inner_wrapper">
    <?php $this->footer() ?>
    <?php $this->leftoverTranslationLinks() ?>
    </div>
    </div>
  </div> <!-- page -->
</div> <!-- page_margins-->
<?php $this->debugInfo() ?>
</body>


