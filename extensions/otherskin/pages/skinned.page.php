<?php


class PageWithActiveSkin extends PageWithRoxLayout
{
    protected function body()
    {
        ?>
        <!-- #page_margins: Obsolete for now. If we decide to use a fixed width or want a frame around the page, we will need them aswell -->
        <div id="page_margins">
          <!-- #page: Used to hold the floats -->
          <div id="page" class="hold_floats">
            
            <div id="header">
              <div id="topnav">
                <?php $this->topnav() ?>
              </div> <!-- topnav -->
              <?php $this->logo() ?>
            </div> <!-- header -->
            
            <?php $this->topmenu() ?>
            
            <!-- #main: content begins here -->
            <div id="main">
              This skin is modified in another place
              <?php $this->teaser() ?>
              <?php $this->columnsArea() ?>
            </div> <!-- main -->
            <?php $this->footer() ?>
            <?php $this->leftoverTranslationLinks() ?>
          </div> <!-- page -->
        </div> <!-- page_margins-->
        <?php $this->debugInfo() ?>
        </body>
        <?php
    }
}


?>