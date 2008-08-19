

<!-- #page_margins: Obsolete for now. If we decide to use a fixed width or want a frame around the page, we will need them aswell -->
<div id="page_margins">
  <!-- #page: Used to hold the floats -->
  <div id="page" class="hold_floats">
    
    <div id="header">
      <div id="topnav">
      <div id="logobox" alt="Be Welcome">
          <a href="start">
          </a>
          <form action="searchmembers/quicksearch" method="post" id="form-quicksearch">
            <input type="text" name="searchtext" size="15" maxlength="30" id="text-field" value="Search...." onfocus="this.value='';"/>

                    <input type="hidden" name="quicksearch_callbackId" value="1"/>
            <input type="image" src="styles/YAML/images/icon_go.gif" id="submit-button" />
          </form>
      </div>
              <?php $this->topnav() ?>
      </div> <!-- topnav -->

    
    <?php $this->topmenu() ?>
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
</body>


