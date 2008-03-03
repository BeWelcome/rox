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
if (TextObject.value == '<?php echo $words->getFormatted('searchmembersAllOver');?>')
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
    <div id="searchteaser" style="width: 50%">
        <fieldset id="searchtop" name="searchtop">
        <strong class="small"><?php echo $words->getFormatted('FindPeopleEnterLocation'); ?></strong><br />
        <input type="text" size="30" name="address" id="address" class="float_left" value="<?php echo $words->getBuffered('searchmembersAllOver');?>"
            onfocus="this.value='';" onKeyPress="if(chkEnt(this, event)) {if(CheckEmpty(this)) {searchGlobal(0)} else {searchByText(this.value, 0)}};"/><?php echo $words->flushBuffer(); ?>
        <input id="text_search" class="button" type="button" value="<?php echo $words->getBuffered('FindPeopleSubmitSearch'); ?>"
            onclick="if(CheckEmpty(getElementById('address'))) {searchGlobal(0)} else {searchByText(get_val('address'), 0)};" /><?php echo $words->flushBuffer(); ?>
        &nbsp; &nbsp; &nbsp; 
        <span class="small">
        <a style="cursor:pointer;" id="linkadvanced" onclick="new Effect.toggle('SearchAdvanced', 'blind');"><?php echo $words->getFormatted('searchmembersAdvanced'); ?></a>
        </span>
        </fieldset>
    </div>
    <div id="searchteaser_sub" class="clearfix">
        <div id="loading_container" class="float_left">
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
