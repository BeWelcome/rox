<?php
    $vars = $this->getRedirectedMem('vars');
    if (empty($vars)) {
        $vars['search-location'] = '';
    }
    $members = $this->getRedirectedMem('members');
?>
<div>
<form method="post" name="searchmembers-form" style="padding-bottom: 0.5em; width: 100%;">
    <?php echo $this->layoutkit->formkit->setPostCallback('SearchController', 'searchMembersSimpleCallback');?>
    <div class="floatbox bottom">
        <div class="float_left"><label for='search-location'><span class="small"><?=$words->get('SearchEnterLocation');?></span></label><br />
        <input type="hidden" name="search-geoname-id" id="search-geoname-id" value="0" />
        <input name="search-location" id="search-location" value="<?php echo $vars['search-location']; ?>" /><noscript><br/>
        <span class="small"><?php echo $words->get('SearchLocationFormat');?></span></noscript>
        <?php echo $words->flushBuffer(); ?></div>
        <div class="float_left"><span class="small"><?=$words->get('CanHost');?></span><br/><select id="search-can-host" name="search-can-host" style="width: 5em;"><?php for($ii = 1; $ii < 30; $ii++)
        {
            echo "<option value='{$ii}'>{$ii}</option>";
        }
        ?></select></div>
        <div class="float_left"><span class="small">&nbsp;</span><br /><input id="search-submit-button" name="search-submit-button" class="button" type="submit" value="<?php echo $words->getBuffered('FindPeopleSubmitSearch'); ?>" /></div>
        <div class="float_left"><span class="small">&nbsp;</span><br /><input id="search-reset-button" name="search-reset-button" class="button" type="reset" value="<?php echo $words->getBuffered('SearchClearValues'); ?>" /></div>
        <div class="floatbox">
            <div id="loading" class="small"></div>
            <div id="paging-div" ></div>
        </div>
    </div>
</form>
</div>
<div><?php
foreach($members as $member) {
    echo $member->Username . " " . $member->ProfileSummary. " " . $member->Gender . "<br />";
} ?></div>