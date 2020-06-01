<?php
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
?>
<!-- #main: content begins here -->
    <?php $this->topnav() ?>
    <div id="toasts" class="position-absolute w-100 d-flex flex-column px-2" style="z-index:1000">
    </div>
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
    <?php $flashError = $this->getFlashError(true);
    if (substr($flashError,0, 2) == 't.') {
        $flashError = $this->words->getSilent($flashError);
    }
    if (strlen($flashError) != 0): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-danger" role="alert"><?= $flashError ?></div>
            </div>
        </div>
    <?php endif; ?>
    <?php $flashNotice = $this->getFlashNotice(true);
    if (substr($flashNotice,0, 2) == 't.') {
        $flashNotice = $this->words->getSilent($flashNotice);
    }
    if (strlen($flashNotice) != 0): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-warning" role="alert"><?= $flashNotice ?></div>
            </div>
        </div>
    <?php endif; ?>
    <?php $flashSuccess = $this->getFlashSuccess(true);
    if (substr($flashSuccess,0, 2) == 't.') {
        $flashSuccess = $this->words->getSilent($flashSuccess);
    }
    if (strlen($flashSuccess) != 0): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-success" role="alert"><?= $flashSuccess ?></div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="container">
    <?php
        $side_column_names = $this->getColumnNames();
        $mid_column_name = array_pop($side_column_names);

        if ($this->getSubmenuItems()) { ?>
        <div class="row row-offcanvas row-offcanvas-right">
            <div class="col-12 col-md-9">
    <?php } else { ?>
        <div class="row mt-2">
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
</div>
<div>
</div>
