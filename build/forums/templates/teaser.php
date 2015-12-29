<?php
$User = APP_User::login();
$keyword = '';
if (isset($this->keyword)) {
    $keyword = $this->keyword;
}
?>

<div id="teaser" class="page-teaser clearfix">
    <div id="title" class="fullheight">
        <h1 class="pull-xs-left">
            <a href="forums"><?php echo $this->_model->words->getFormatted('CommunityLanding'); ?></a>

<?php if ($this->_model->getTopMode() == Forums::CV_TOPMODE_FORUM) {
          echo '&raquo <a href="forums/bwforum">'.$this->_model->words->getFormatted('AgoraForum').'</a>';
      }
?>
        </h1>
        <div class="pull-xs-right fullheight">
            <!-- Google froum search bar -->
            <form action="/forums/search" class="verticalalign" method="POST" id="search-box"><div>
                    <input type="hidden" name="<?= $this->searchCallbackId ?>" value="1" />
                    <input type="text" name="fs-keyword" size="15" placeholder="<?= $this->_model->words->getFormatted('ForumSearch') ?>" value="<?= $keyword ?>"/>
                    <input type="submit" name="fss" value="<?php echo $this->_model->words->getSilent('Search')?>" /> <?php echo $this->_model->words->flushBuffer(); ?></div>
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
