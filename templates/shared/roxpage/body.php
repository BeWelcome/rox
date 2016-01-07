<div class="navandcontent">
    <!-- #main: content begins here -->
    <div id="main">
        <?php $this->topnav() ?>
        <?php $this->statusMessage() ?>
        <?php $this->teaser() ?>

        <?php if ($this->getFlashError()): ?>
        <div class="flash error"><?php echo $this->getFlashError(true); ?></div>
        <?php endif; ?>
        <?php if ($this->getFlashNotice()): ?>
        <div class="flash notice"><?php echo $this->getFlashNotice(true); ?></div>
        <?php endif; ?>

        <?php $this->columnsArea() ?>
    </div> <!-- main -->
</div>
<div>
    <?php $this->debugInfo() ?>
    <?php $this->leftoverTranslationLinks() ?>
</div>