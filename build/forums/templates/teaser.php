<?php
$User = APP_User::login();
$keyword = '';
if (isset($this->keyword)) {
    $keyword = $this->keyword;
}
?>

<div class="p-a-1 clearfix">
    <div id="title" class="fullheight">
        <h1 class="pull-xs-left">
            <a href="forums"><?php echo $this->_model->words->getFormatted('CommunityLanding'); ?></a>

<?php if ($this->_model->getTopMode() == Forums::CV_TOPMODE_FORUM) {
          echo '&raquo <a href="forums/bwforum">'.$this->_model->words->getFormatted('AgoraForum').'</a>';
      }
?>
        </h1>
        <div class="pull-xs-right fullheight p-t-1">
            <!-- Google froum search bar -->
            <form action="/forums/search" class="verticalalign" method="POST" id="search-box">
                <div class="input-group verticalalign m-t-1">
                    <input type="hidden" name="<?= $this->searchCallbackId ?>" value="1" />
                    <input type="text" name="fs-keyword" class="form-control" placeholder="<?= $this->_model->words->getFormatted('ForumSearch') ?>" value="<?= $keyword ?>"/>
                    <span class="input-group-btn">
                    <button type="submit" name="fss" class="btn btn-primary"><i class="fa fa-search"></i> <?php echo $this->_model->words->getSilent('Search')?></button> <?php echo $this->_model->words->flushBuffer(); ?>
                    </span>
                </div>
            </form>
        </div> <!-- float_right -->
    </div>
    <!-- CategoryTitle in teaser -->
    <div class="forumtitle">
        <?php $title = $boards->getBoardName();
        if (($title != 'Forums') and (!(empty($title)))) {
            //  echo '<a href="forums/', $title ,'">', $title,'</a>';
        }
        ?>
    </div> <!-- forumtitle -->
</div> <!-- teaser -->
