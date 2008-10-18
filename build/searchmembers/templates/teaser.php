<?php
$User = APP_User::login();

$words = new MOD_words();
?>
<script type="text/javascript">
// build regular expression object to find empty string or any number of spaces
var blankRE=/^\s*$/;
function CheckEmpty(TextObject)
{
if(blankRE.test(TextObject.value))
{
return true;}
if (TextObject.value == '<?php echo $words->getBuffered('searchmembersAllOver');?>')
{
return true}
else return false;
}
</script>
<div id="teaser" class="clearfix">
    <h1><?php echo $words->getFormatted('searchmembersTitle'); ?></h1>
    <div id="scriptinfo" class="clearfix NotDisplayed">
        <p class="note"><b><?php echo $words->getFormatted('searchmembersScriptInfo'); ?></b><br /><?php echo $words->getFormatted('searchmembersScriptInfoText','<a href="searchmembers/quicksearch">','</a>','<a href="country/">','</a>'); ?></p>
    </div>
    <div id="searchteaser" style="width: 90%" class="clearfix">
        <fieldset id="searchtop" name="searchtop" class="float_left">
        <span class="small">
            <!--<input type="radio" name="SelectedSearchField" value="Address" checked="checked"><?php echo $words->getBuffered('Address'); ?>
            <input type="radio" name="SelectedSearchField" value="Username"><?php echo $words->getBuffered('Username'); ?>
            <input type="radio" name="SelectedSearchField" value="TextToFind"><?php echo $words->getBuffered('TextToFind'); ?> -->
            <label for="Address"><?php echo $words->getFormatted('FindPeopleEnterLocation'); ?></label>
        </span><br />
        <input type="text" size="40" name="Address" id="Address" class="float_left" value="<?=isset($_GET['vars']) ? $_GET['vars'] : $words->getBuffered('searchmembersAllOver');?>"
            onfocus="this.value='';" onKeyPress="if(chkEnt(this, event)) {if(CheckEmpty(this)) {searchGlobal(0)} else {searchByText(this.value, 0)}};"/>
        <?php echo $words->flushBuffer(); ?>
        <input id="text_search" class="float_left button" type="button" value="<?php echo $words->getBuffered('FindPeopleSubmitSearch'); ?>"
            onclick="if(CheckEmpty(getElementById('Address'))) {searchGlobal(0)} else {searchByText(get_val('Address'), 0)};" /><?php echo $words->flushBuffer(); ?>
        <input type="reset" id="advancedbuttons" class="NotDisplayed float_left button" value="<?php echo $words->getBuffered('SearchClearValues'); ?>">
        </fieldset>
    </div>
        &nbsp; &nbsp; &nbsp;
        <span class="small">
        <a style="cursor:pointer;" id="linkadvanced"><img id="linkadvancedimage" src="images/icons/add.png" style="vertical-align: top"> <?php echo $words->getFormatted('searchmembersAdvanced'); ?></a>
        &nbsp; &nbsp; &nbsp;
        <a href="places"><img id="linkadvancedimage" src="images/icons/world.png" style="vertical-align: top"> <?php echo $words->getFormatted('BrowseCountries'); ?></a>
        </span>
    
    <div id="searchteaser_sub" class="clearfix">
        <div id="loading_container" style="float:left">
        <span id="loading"></span>
        </div>
<?php if ($mapstyle == "mapon") { ?>
        <div id="mapbuttons" class="float_right">
        <input id="map_search" class="button" type="button" value="<?php echo $words->getBuffered('FindPeopleSubmitMapSearch'); ?>"
            onclick="searchByMap(0);" /><?php echo $words->flushBuffer(); ?>&nbsp;
        </div>
<?php } ?>
    </div>    
</div>
