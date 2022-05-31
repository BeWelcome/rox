<?php
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
?>
<!-- #main: content begins here -->
    <?php $this->topnav() ?>
    <div id="toasts" class="position-absolute w-100 d-flex flex-column px-2" style="z-index:1000">
    </div>
    <?php /* $loginMessages = $this->_getLoginMessages();
    if (!empty($loginMessages)) :
        foreach($loginMessages as $id => $loginMessage) : ?>
            <div class="loginmessage">
                <a href="/close/<?= $id ?>" class="boxclose"></a>
                <?= $loginMessage ?>
            </div>
            <?php
        endforeach;
    endif; */?>

<div class="container-lg">
    <?php $flashError = $this->getFlashError(true);
    if (substr($flashError,0, 2) == 't.') {
        $flashError = $this->getWords()->getSilent($flashError);
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
        $flashNotice = $this->getWords()->getSilent($flashNotice);
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
        $flashSuccess = $this->getWords()->getSilent($flashSuccess);
    }
    if (strlen($flashSuccess) != 0): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-success" role="alert"><?= $flashSuccess ?></div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="container-lg">
    <?php
        $side_column_names = $this->getColumnNames();
        $mid_column_name = array_pop($side_column_names);

        $submenuItems = $this->getSubmenuItems();
        if ($submenuItems) { ?>
        <div class="row row-offcanvas row-offcanvas-right">
            <div class="col-12 col-md-9">
    <?php } else { ?>
        <div class="row mt-2">
            <div class="col-12">
    <?php } ?>

            <?php
            if ($submenuItems) { ?>
                <div class="float-right d-md-none">
                    <button type="button" class="btn btn-primary btn-sm ml-3" data-toggle="offcanvas"><i class="navbar-toggler-icon"></i></button>
                </div>
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
        if ($submenuItems) {
            $this->submenu();
        }
        ?>
    </div> <!-- row -->
</div>
<div>
</div>
