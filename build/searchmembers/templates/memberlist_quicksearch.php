<?php
$words = new MOD_words();
?>
<div id="nextmap">
<p class="clearfix">
    <a <?php if ($mapstyle=='mapon') { ?> href="searchmembers/mapoff" <?php } ?> class="small <?php if ($mapstyle=='mapoff') echo 'active'; ?> float_right">
    <img src="images/misc/list6<?php if ($mapstyle=='mapoff') echo '_green'; ?>.gif" class="list-icon"> <?php echo $words->getFormatted('searchmembersViewText');  ?> &nbsp;
    </a>
</p>
<p class="clearfix">
    <a <?php if ($mapstyle=='mapoff') { ?> href="searchmembers/mapon" <?php } ?> class="small <?php if ($mapstyle=='mapon') echo 'active'; ?> float-right">
    <img src="images/misc/list-map<?php if ($mapstyle=='mapon') echo '_green'; ?>.gif" class="list-icon"> <?php echo $words->getFormatted('searchmembersViewMap');  ?> &nbsp;
    </a>

</p>
<!--
    <div id="searchinfo">
        <h3><?php echo $words->getFormatted('quicksearchIntro'); ?></h3>
        <?php echo $words->getFormatted('quicksearchIntroText'); ?>
        
        <h3><?php echo $words->getFormatted('quicksearchIntro2'); ?></h3>
        <?php echo $words->getFormatted('quicksearchIntroText2'); ?>
    </div>
-->
</div>
