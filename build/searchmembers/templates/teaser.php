<?php
$User = APP_User::login();

$words = new MOD_words();
$address = (isset($vars) && $vars && isset($vars['CityName'])) ? $vars['CityName'] : $words->getBuffered('searchmembersAllOver');
if (isset($_GET['vars'])) $address = $_GET['vars'];

?>

<div id="teaser" class="clearfix">
    <h1><?php echo $words->getFormatted('searchmembersTitle'); ?></h1>
    <div id="scriptinfo" class="clearfix NotDisplayed">
        <p class="note"><b><?php echo $words->getFormatted('searchmembersScriptInfo'); ?></b><br /><?php echo $words->getFormatted('searchmembersScriptInfoText','<a href="searchmembers/quicksearch">','</a>','<a href="country/">','</a>'); ?></p>
    </div>
    
    <div id="searchteaser" style="width: 100%;" class="floatbox">
        <div id="searchtop" class="float_left">
        <span class="small">
            <!--<input type="radio" name="SelectedSearchField" value="Address" checked="checked"><?php echo $words->getBuffered('Address'); ?>
            <input type="radio" name="SelectedSearchField" value="Username"><?php echo $words->getBuffered('Username'); ?>
            <input type="radio" name="SelectedSearchField" value="TextToFind"><?php echo $words->getBuffered('TextToFind'); ?> -->
            <label for="Address"><?php echo $words->getFormatted('FindPeopleEnterLocation'); ?></label>
        </span><br />
        <input type="text" size="30" name="Address" id="Address" class="float_left" value="<?=$address?>"
            onfocus="this.value='';" onkeypress="if(chkEnt(this, event)) {if(CheckEmpty(this)) {searchGlobal(0)} else {searchByText(this.value, 0)}};"/>
        <?php echo $words->flushBuffer(); ?>
        <input id="text_search" class="float_left button" type="button" value="<?php echo $words->getBuffered('FindPeopleSubmitSearch'); ?>"
            onclick="if(CheckEmpty(getElementById('Address'))) {searchGlobal(0)} else {searchByText(get_val('Address'), 0)};" /><?php echo $words->flushBuffer(); ?>
        <input type="reset" id="advancedbuttons" class="NotDisplayed float_left button" value="<?php echo $words->getBuffered('SearchClearValues'); ?>" />
        </div>
        <div id="searchteaser_sub" class="float_left">
            <div id="loading_container" style="float:left">
            <span id="loading"></span>
            </div>

        </div>
    </div>
    
<!-- NEXT ROW -->
<div class="floatbox" style="padding-top: 10px">
    <div class="float_left">
        <span class="small">
        <a style="cursor:pointer;" id="linkadvanced"><img id="linkadvancedimage" alt="Advanced Options" src="images/icons/add.png" style="vertical-align: top" /> <?php echo $words->getFormatted('searchmembersAdvanced'); ?></a>
        &nbsp; &nbsp; &nbsp;
        <a href="places"><img src="images/icons/world.png" alt="<?php echo $words->getBuffered('BrowseCountries'); ?>" style="vertical-align: top" /> <?php echo $words->getFormatted('BrowseCountries'); ?></a>
        </span>
    </div>  


    
    <div class="floatbox float_right" style="padding: 0 0 10px 0; width: 300px;">
    <span class="small" style="padding: 0; float: left;"><?php echo $words->getBuffered('View'); ?>: &nbsp; </span>
    <table border="0" cellpadding="0" cellspacing="0" width="63" style="padding: 0; float: left;">
      <tr>
       <td style="padding: 0;"><a <?php if ($mapstyle != 'mapon') echo 'href="searchmembers/mapon"'; ?> title="Map view" style="background-color: #fff;" onmouseover="BTchange('IdImg1', ViewImg1_f2)" onmouseout="BTchange('IdImg1', ViewImg1<?php if ($mapstyle=='mapon') echo '_f2'; ?>)"><img name="one" src="images/misc/one<?php if ($mapstyle=='mapon') echo '_f2'; ?>.gif" width="30" height="24" border="0" alt="" id="IdImg1" /></a></td>
    <?php /*   THIRD 'MIXED' VIEW - DEACTIVATED FOR NOW
        <td style="padding: 0;"><a style="background-color: #fff;" <?php if ($mapstyle != 'mix') echo 'href="searchmembers/mix"'; ?>  alt="Mixed view" onmouseover="BTchange('IdImg2', ViewImg2_f2)" onmouseout="BTchange('IdImg2', ViewImg2<?php if ($mapstyle=='mix') echo '_f2'; ?>)" onfocus="BTchange('IdImg2', ViewImg2_f2)" ><img name="two" src="images/misc/two<?php if ($mapstyle=='mix') echo '_f2'; ?>.gif" width="30" height="24" border="0" alt="" id="IdImg2" /></a></td>
    */ ?>
       <td style="padding: 0;"><a style="background-color: #fff;" title="Text view" <?php if ($mapstyle != 'mapoff') echo 'href="searchmembers/mapoff"'; ?> onmouseover="BTchange('IdImg3', ViewImg3_f2)" onmouseout="BTchange('IdImg3', ViewImg3<?php if ($mapstyle=='mapoff') echo '_f2'; ?>)"><img name="three" src="images/misc/three<?php if ($mapstyle=='mapoff') echo '_f2'; ?>.gif" width="33" height="24" border="0" alt="" id="IdImg3" /></a></td>
      </tr>
    </table>
    <span class="small" style="padding: 0; float: left;">&nbsp; &nbsp; <label for="thisorder"><?php echo $words->getBuffered('SortOrder'); ?>:</label> &nbsp; </span>
    <form id="changethisorder" style="overflow: visible;" action="">
    <select name="OrderBy" id="thisorder" onchange="changeSortOrder(this.value);">
        <?php foreach($TabSortOrder as $key=>$val) { ?>
        <option value="<?php echo $key; ?>"><?php echo $words->getBuffered($val); ?></option>
        <?php } ?>
    </select>
    </form>
    </div>
</div>

</div>
