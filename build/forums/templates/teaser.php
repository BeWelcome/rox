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
        <form action="https://www.google.com/cse" id="cse-search-box">
            <div>
                <input type="hidden" name="cx" value="003793464580395137050:n7s_x10-itw" />
                <input type="hidden" name="cof" value="FORID:9" />
                <input type="hidden" name="ie" value="UTF-8" />
                <input type="text" name="q" size="15" />
                <input type="submit" name="sa" value="<?php echo $this->_model->words->getSilent('Search')?>" /> <?php echo $this->_model->words->flushBuffer(); ?>
            </div>
        </form>
        <script type="text/javascript" src="https://www.google.com/coop/cse/brand?form=cse-search-box&amp;lang=en"></script>
    </div> <!-- float_right -->
</div> <!-- teaser -->
