<!-- #main: content begins here -->
<div id="main">
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

    <div class="container">
    <?php $flashMessages = $this->getFlashError(true);
    if (strlen($flashMessages) != 0): ?>
        <div class="row">
            <div class="col-12 alert alert-danger" role="alert"><?= $flashMessages ?></div>
        </div>
    <?php endif; ?>
    <?php $flashMessages = $this->getFlashNotice(true);
    if (strlen($flashMessages) != 0): ?>
        <div class="row">
            <div class="col-12 alert alert-warning" role="alert"><?= $flashMessages ?></div>
        </div>
    <?php endif; ?>
    <?php $flashSuccess = $this->getFlashSuccess(true);
    if (strlen($flashSuccess) != 0): ?>
        <div class="row">
            <div class="col-12 alert alert-success" role="alert"><?= $flashSuccess; ?></div>
        </div>
    <?php endif; ?>
    </div>

    <?
        $side_column_names = $this->getColumnNames();
        $mid_column_name = array_pop($side_column_names);

        if ($this->getSubmenuItems()) { ?>
        <div class="row row-offcanvas row-offcanvas-right">
            <div class="col-12 col-md-9">
    <?php } else { ?>
        <div class="row">
            <div class="col-12">
    <?php } ?>

            <?php
            if ($this->getSubmenuItems()) { ?>
                <p class="float-right d-md-none">
                    <button type="button" class="btn btn-primary btn-sm ml-3" data-toggle="offcanvas"><i class="navbar-toggler-icon"></i></button>
                </p>
                <?php
            }
            ?>
                <div>
                <?php $this->teaser() ?>
                </div>

            <?php
            $this->columnsArea($mid_column_name); ?>
            <!-- col-12 -->
            </div>
        <?php
        if ($this->getSubmenuItems()) {
            $this->submenu();
        }
        ?>
    </div> <!-- row -->
</div> <!-- main -->
<script src="build/offcanvas.js"></script>
<div>
    <?php $this->debugInfo() ?>
    <?php $this->leftoverTranslationLinks() ?>
</div>
