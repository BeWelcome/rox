
<!-- #teaser: the orange bar shows title and elements that summarize the content of the current page -->
<div id="teaser_bg">
  <?php $this->teaserContent() ?>
  <div id="teaser_shadow">
    <img src="styles/YAML/images/spacer.gif" width="95%" height="5px" alt="spacer" />

<?php if (!$this->getSubmenuItems()) {
    } else $this->submenu(); ?>

  </div> <!-- teaser_shadow -->
</div> <!-- teaser_bg -->
