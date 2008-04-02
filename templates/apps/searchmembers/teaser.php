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
        <span class="small">
            <!--<input type="radio" name="SelectedSearchField" value="Address" checked="checked"><?php echo $words->getBuffered('Address'); ?>
            <input type="radio" name="SelectedSearchField" value="Username"><?php echo $words->getBuffered('Username'); ?>
            <input type="radio" name="SelectedSearchField" value="TextToFind"><?php echo $words->getBuffered('TextToFind'); ?> -->
            <?php echo $words->getFormatted('FindPeopleEnterLocation'); ?></span><br />
        <input type="text" size="40" name="Address" id="Address" class="float_left" value="<?php echo $words->getBuffered('searchmembersAllOver');?>"
            onfocus="this.value='';" onKeyPress="if(chkEnt(this, event)) {if(CheckEmpty(this)) {searchGlobal(0)} else {searchByText(this.value, 0)}};"/><?php echo $words->flushBuffer(); ?>
        <?php echo $words->flushBuffer(); ?>
        <input id="text_search" class="button" type="button" value="<?php echo $words->getBuffered('FindPeopleSubmitSearch'); ?>"
            onclick="if(CheckEmpty(getElementById('Address'))) {searchGlobal(0)} else {searchByText(get_val('Address'), 0)};" /><?php echo $words->flushBuffer(); ?>
        &nbsp; &nbsp; &nbsp;
        <span class="small">
        <a style="cursor:pointer;" id="linkadvanced" onclick="new Effect.toggle('SearchAdvanced', 'blind');"><?php echo $words->getFormatted('searchmembersAdvanced'); ?></a><br />
        </span>
        </fieldset>
        <script language="javascript" type="text/javascript">
            new Tip('Address', '<?php echo $words->getBuffered('FindPeopleHelpAddress'); ?>',{className: 'clean', hook: {target: 'bottomLeft', tip: 'topLeft' }});
        </script>
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
        <script language="javascript" type="text/javascript">
            new Tip('map_search', '<?php echo $words->getBuffered('FindPeopleHelpMapBoundaries'); ?>',{className: 'clean', hook: {target: 'bottomLeft', tip: 'topLeft' }});
        </script>
<?php } ?>
    </div>    
</div>
