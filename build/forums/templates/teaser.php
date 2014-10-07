<?php
$User = APP_User::login();
?>

<div id="teaser" class="clearfix">
    <div id="title" class="float_left">
        <h1>
            <a href="forums"><?php echo $this->_model->words->getFormatted('CommunityLanding'); ?></a>

<?php if ($this->_model->getTopMode() == Forums::CV_TOPMODE_FORUM) {
          echo '&raquo <a href="forums/bwforum">'.$this->_model->words->getFormatted('AgoraForum').'</a>';
      }
?>
        </h1>

        <!-- CategoryTitle in teaser -->
        <div class="forumtitle">
            <?php $title = $boards->getBoardName();
            if (($title != 'Forums')and(!(empty($title)))) {
            //  echo '<a href="forums/', $title ,'">', $title,'</a>';
            }
            ?>
        </div> <!-- forumtitle -->
    </div> <!-- title -->


    <div class="float_right">
        <!-- Google froum search bar -->
        <p><?php echo $this->_model->words->getFormatted('ForumSearch'); ?></p>
        <form action="/forums/search" method="POST" id="search-box"><div>
            <input type="hidden" name="<?= $this->searchCallbackId ?>" value="1" />
            <input type="text" name="fs-keyword" size="15" placeholder="<?php echo $this->_model->words->getSilent('ForumsSearchInfo')?>" />
            <input type="submit" name="fss" value="<?php echo $this->_model->words->getSilent('Search')?>" /> <?php echo $this->_model->words->flushBuffer(); ?></div>
        </form>
    </div> <!-- float_right -->
</div> <!-- teaser -->
