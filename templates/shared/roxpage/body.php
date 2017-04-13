<!-- #main: content begins here -->
<div id="main" class="mt-4">
    <?php $this->topnav() ?>
    <?php $this->statusMessage() ?>
    <?php $loginMessages = $this->_getLoginMessages();
    if (!empty($loginMessages)) :
        foreach($loginMessages as $id => $loginMessage) : ?>
            <div class="loginmessage">
                <a href="/close/<?= $id ?>" class="boxclose"></a>
                <?= $loginMessage ?>
            </div>
            <?php
        endforeach;
    endif; ?>

    <?php if ($this->getFlashError()): ?>
        <div class="flash error"><?php echo $this->getFlashError(true); ?></div>
    <?php endif; ?>
    <?php if ($this->getFlashNotice()): ?>
        <div class="flash notice"><?php echo $this->getFlashNotice(true); ?></div>
    <?php endif; ?>

    <div class="row row-offcanvas row-offcanvas-right">

        <div class="col-12 col-md-9">
            <?php
            if (!isset($side_column_names)) {
                $side_column_names = false;
            }
            if ($this->getSubmenuItems() || ($side_column_names)) { ?>
                <p class="float-right d-md-none">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="offcanvas">Toggle nav</button>
                </p>
                <?php
            }
            ?>
            <div class="jumbotron">
                <?php $this->teaser() ?>
            </div>
            <?php
            $this->columnsArea(); ?>
        </div>
        <?php
        if ($this->getSubmenuItems() || ($side_column_names)) {
            $this->submenu();
        }
        ?>
    </div>
</div> <!-- main -->
<script src="bundles/app/js/offcanvas.js"></script>
<div>
    <?php $this->debugInfo() ?>
    <?php $this->leftoverTranslationLinks() ?>
</div>
