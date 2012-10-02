

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
        <?php if ($this->getFlashError()): ?>
        <div class="flash error"><?php echo $this->getFlashError(true); ?></div>
        <?php endif; ?>
        <?php if ($this->getFlashNotice()): ?>
        <div class="flash notice"><?php echo $this->getFlashNotice(true); ?></div>
        <?php endif; ?>

        <?php $this->statusMessage() ?>
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

<?php
$piwikBaseURL = PVars::getObj('piwik')->baseurl;
$piwikType = PVars::getObj('piwik')->type;
$proto = 'http';
if ($_SERVER['HTTPS']) {
    $proto .= 's';
}
if ($piwikBaseURL) {
    $piwikId = intval(PVars::getObj('piwik')->siteid);
    if ($piwikId == 0) {
        $piwikId = 1;
    }
    $piwikBaseName = preg_replace('/^([a-z]+:\/\/)*(.*?)\/*$/','$2',$piwikBaseURL);
    
    if ($piwikType == 'javascript') { ?>          
<!-- Piwik -->
<script type="text/javascript">
var pkBaseURL = (("https:" == document.location.protocol) ? "https://<?php echo $piwikBaseName ?>/" : "http://https://<?php echo $piwikBaseName ?>/");
document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
</script><script type="text/javascript">
try {
    var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", <?php echo $piwikId ?>);
    piwikTracker.trackPageView();
    piwikTracker.enableLinkTracking();
} catch( err ) {
}
</script><noscript><p><img src="<?php echo $proto ?>://<?php echo $piwikBaseName ?>/piwik.php?idsite=<?php echo $piwikId ?>&amp;rec=1" style="border:0" alt="" width="1" height="1" /></p></noscript>
<!-- End Piwik Tracking Code -->
<?php    } else { ?>
<!-- Piwik Image Tracker -->
<img src="<?php echo $proto ?>://<?php echo $piwikBaseName ?>/piwik.php?idsite=<?php echo $piwikId ?>&amp;rec=1" style="border:0" alt="" width="1" height="1" />
<!-- End Piwik -->
<?php    }
} ?>
