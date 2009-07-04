

<!-- #page_margins: Obsolete for now. If we decide to use a fixed width or want a frame around the page, we will need them aswell -->
<div id="page_margins">
  <!-- #page: Used to hold the floats -->
  <div id="page" class="hold_floats">
  
    <div id="header">      
    </div> <!-- header -->
    <?php $this->topmenu() ?>
    <div id="topnav">
        <?php $this->topnav() ?>
      </div> <!-- topnav -->

    <!-- #main: content begins here -->
    <div id="main">
        <div id="teaser_bg">
      <?php $this->teaser() ?>
      </div>
      <?php $this->columnsArea() ?>
    </div> <!-- main -->

    <?php $this->footer() ?>
    <?php $this->leftoverTranslationLinks() ?>

  </div> <!-- page -->
</div> <!-- page_margins-->
<?php $this->debugInfo() ?>

<!-- Piwik web analytics http://piwik.org/ -->
<script type="text/javascript">
var pkBaseURL = (("https:" == document.location.protocol) ? "https://www.bevolunteer.org/piwik/" : "http://www.bevolunteer.org/piwik/");
document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
</script><script type="text/javascript">
try {
var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", 1);
piwikTracker.trackPageView();
piwikTracker.enableLinkTracking();
} catch( err ) {}
</script>
<!-- End Piwik Tag -->

