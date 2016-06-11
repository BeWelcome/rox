<?php
$words = new MOD_words();
?>

<div id="nextmap"> 
<strong class="small"><?php echo $words->getFormatted('FindPeopleSortOrderDirection'); ?></strong><br />
<select name="OrderByDirection">
    <option value="desc"><?php echo $words->getBuffered('Forward'); ?></option>
    <option value="asc"><?php echo $words->getBuffered('Reverse'); ?></option>
</select>
    <div id="member_list"></div>
    <div id="help_and_markers"></div>
</div>
