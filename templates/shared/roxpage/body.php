<div id="page_margins">
  <!-- #page: Used to hold the floats -->
  <div id="page" class="hold_floats">

    <div id="topnav">
        <?php $this->topnav() ?>
    </div> <!-- topnav -->

    <!-- #main: content begins here -->
    <div id="main">
        <?php $this->statusMessage() ?>
        <div id="teaser_bg">
            <?php $this->teaser() ?>
        </div>

        <?php if ($this->getFlashError()): ?>
        <div class="flash error"><?php echo $this->getFlashError(true); ?></div>
        <?php endif; ?>
        <?php if ($this->getFlashNotice()): ?>
        <div class="flash notice"><?php echo $this->getFlashNotice(true); ?></div>
        <?php endif; ?>

        <?php $this->columnsArea() ?>
    </div> <!-- main -->

      <div>
          <?php $this->translator_block() ?>
          <?php $this->debugInfo() ?>
          <?php $this->leftoverTranslationLinks() ?>
      </div>
  </div> <!-- page -->

</div>
<?php
$piwikBaseURL = PVars::getObj('piwik')->baseurl;
$piwikType = PVars::getObj('piwik')->type;
$proto = 'http';
if (!empty($_SERVER['HTTPS'])) {
    $proto .= 's';
}
if ($piwikBaseURL) {
    $piwikId = intval(PVars::getObj('piwik')->siteid);
    if ($piwikId == 0) {
        $piwikId = 1;
    }
    $piwikBaseName = preg_replace('/^([a-z]+:\/\/)*(.*?)\/*$/','$2',$piwikBaseURL);
    if (isset($_SERVER['HTTP_REFERER'])) {
        $urlref = urlencode($_SERVER['HTTP_REFERER']);
    } else {
        $urlref = '';
    }

    if ($piwikType == 'javascript') { ?>
<!-- Piwik -->
<script type="text/javascript">
   var _paq = _paq || [];
   (function(){var u=(("https:" == document.location.protocol) ? "https" : "http") + "://<?php echo $piwikBaseName ?>/"; _paq.push(['setSiteId', <?php echo $piwikId ?>]); _paq.push(['setTrackerUrl', u+'piwik.php']); _paq.push(['trackPageView']); _paq.push(['enableLinkTracking']); var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0]; g.type='text/javascript'; g.defer=true; g.async=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s); })();
</script>
<noscript><p><img src="<?php echo $proto ?>://<?php echo $piwikBaseName ?>/piwik.php?idsite=<?php echo $piwikId ?>&amp;rec=1" style="border:0" alt="" width="1" height="1" /></p></noscript>
<!-- End Piwik Tracking Code -->
<?php    } else { ?>
<!-- Piwik Image Tracker -->
<img src="<?php echo $proto ?>://<?php echo $piwikBaseName ?>/piwik.php?idsite=<?php echo $piwikId ?>&amp;rec=1&amp;urlref=<?php echo $urlref; ?>" style="border:0" alt="" width="1" height="1" />
<!-- End Piwik -->
<?php    }
} ?>
