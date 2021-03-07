<?php
$User = $this->_model->getLoggedInMember();

$keyword = '';
if (isset($this->keyword)) {
    $keyword = $this->keyword;
}
?>
<div class="row no-gutters mb-3">
    <div class="col-sm-6 align-self-center">
        <h3>
            <a href="forums"><?php echo $this->_model->words->getFormatted('CommunityLanding'); ?></a>

            <?php if ($this->_model->getTopMode() == Forums::CV_TOPMODE_FORUM) {
                echo '&raquo <a href="forums/bwforum">' . $this->_model->words->getFormatted('AgoraForum') . '</a>';
            }
            ?>
        </h3>
    </div>
    <div class="col-sm-6">
        <form action="/forums/search" method="POST" id="search-box" class="mb-1">
            <div class="input-group">
                <input type="hidden" name="<?= $this->searchCallbackId ?>" value="1" />
                <input type="text" name="fs-keyword" class="form-control" placeholder="<?= $this->_model->words->getFormatted('ForumSearch') ?>" value="<?= $keyword ?>" />
                <span class="input-group-append">
                    <button type="submit" name="fss" class="btn btn-primary"><i class="fa fa-search"></i> <?php echo $this->_model->words->getSilent('Search') ?></button> <?php echo $this->_model->words->flushBuffer(); ?>
                </span>
            </div>
        </form>
    </div>
</div>
<!-- CategoryTitle in teaser -->
<div class="forumtitle">
    <?php $title = $boards->getBoardName();
    if (($title != 'Forums') and (!(empty($title)))) {
        //  echo '<a href="forums/', $title ,'">', $title,'</a>';
    }
    ?>
</div> <!-- forumtitle -->

