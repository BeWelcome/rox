<?php
$User = APP_User::login();

$words = new MOD_words();
?>

<div id="teaser" class="clearfix">
    <div id="title" class="float_left">
        <h1><?php echo $words->getFormatted('ForumTitle'); ?></h1>
        <!-- CategoryTitle in teaser -->
        <div class="forumtitle">
            <a href="forums"><?php echo $words->getFormatted('ForumOverviewLink') ?></a>
            <?php $title = $boards->getBoardName();
            if ($title != 'Forums') {echo ' > <a href="forums/', $title ,'">', $title,'</a>';}
            ?>
        </div> <!-- forumtitle -->
    </div> <!-- title -->
    <div class="float_right">
        <p></p>
        <p><label for="forumsboarddropdown"><?php echo $words->getFormatted('ForumBrowseCategories'); ?></label></p>
        <select name="board" id="forumsboarddropdown" onchange="window.location.href=this.value;">
        <option value=""><?php echo $words->getFormatted('ForumChooseCategory'); ?></option>
<?php
    foreach ($topboards as $topboard) {
        $url = 'forums/t'. $topboard->tagid.'-'.$topboard->tag;
        ?>
            <option value="<?php echo $url; ?>"><?php echo $topboard->tag; ?></option>
        <?php
        /*if ($board->hasSubBoards()) {
            foreach ($board as $b) {
                echo '<a href="'.$uri.$b->getBoardLink().'">'.$b->getBoardName().'</a>';
                echo '<br />';
            }
        }*/
    }
?>
            </select>

        <p><?php echo $words->getFormatted('ForumSearch'); ?></p>
        <form action="forums/search" id="cse-search-box">
  <div>
    <input type="hidden" name="cx" value="003793464580395137050:n7s_x10-itw" />
    <input type="hidden" name="cof" value="FORID:9" />
    <input type="hidden" name="ie" value="UTF-8" />
    <input type="text" name="q" size="31" />
    <input type="submit" name="sa" value="Search" />
  </div>
</form>
<script type="text/javascript" src="http://www.google.com/coop/cse/brand?form=cse-search-box&lang=en"></script>


    </div> <!-- float_right -->
</div> <!-- teaser -->
