<?php
$words = new MOD_words();
?>
<div id="nextmap">
<p class="floatbox">
    <a style="cursor:pointer;" class="small float_left" onclick="<?php if ($mapstyle != "mapoff") echo "map.clearOverlays();"; ?>put_html('help_and_markers', searchHelp);put_html('member_list', '');"><?php echo $words->getFormatted('searchmembersHelp');  ?>  &nbsp;</a>

    <a <?php if ($mapstyle=='mapon') { ?> href="searchmembers/mapoff" <?php } ?> class="small <?php if ($mapstyle=='mapoff') echo 'active'; ?> float_right">
    <img src="images/misc/list6<?php if ($mapstyle=='mapoff') echo '_green'; ?>.gif" class="list-icon"> <?php echo $words->getFormatted('searchmembersViewText');  ?> &nbsp;
    </a>
</p>
<p class="floatbox">
    <a <?php if ($mapstyle=='mapoff') { ?> href="searchmembers/mapon" <?php } ?> class="small <?php if ($mapstyle=='mapon') echo 'active'; ?> float_right">
    <img src="images/misc/list-map<?php if ($mapstyle=='mapon') echo '_green'; ?>.gif" class="list-icon"> <?php echo $words->getFormatted('searchmembersViewMap');  ?> &nbsp;
    </a>
    
</p>
<div id="help_and_markers"></div>
</div>
