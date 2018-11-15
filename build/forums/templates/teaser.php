<?php
$User = APP_User::login();
$keyword = '';
if (isset($this->keyword)) {
    $keyword = $this->keyword;
}
?>
<div class="row">
    <h1 class="col pl-0">
        <a href="forums"><?php echo $this->_model->words->getFormatted('CommunityLanding'); ?></a>

        <?php if ($this->_model->getTopMode() == Forums::CV_TOPMODE_FORUM) {
            echo '&raquo <a href="forums/bwforum">' . $this->_model->words->getFormatted('AgoraForum') . '</a>';
        }
        ?>
    </h1>
    <form action="/forums/search" method="POST" id="search-box" class="pull-right">
        <div class="input-group">
            <input type="hidden" name="<?= $this->searchCallbackId ?>" value="1"/>
            <input type="text" name="fs-keyword" class="form-control"
                   placeholder="<?= $this->_model->words->getFormatted('ForumSearch') ?>" value="<?= $keyword ?>"/>
            <span class="input-group-append">
                        <button type="submit" name="fss" class="btn btn-primary"><i
                                    class="fa fa-search"></i> <?php echo $this->_model->words->getSilent('Search') ?></button> <?php echo $this->_model->words->flushBuffer(); ?>
                        </span>
        </div>
    </form>
</div>
        <!-- Google froum search bar -->
<!-- CategoryTitle in teaser -->
<div class="forumtitle">
    <?php $title = $boards->getBoardName();
    if (($title != 'Forums') and (!(empty($title)))) {
        //  echo '<a href="forums/', $title ,'">', $title,'</a>';
    }
    ?>
</div> <!-- forumtitle -->

