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
    <h1 style="width: 200px; float:left;"><?php echo $words->getFormatted('tripsTitle'); ?></h1>
    <div id="searchteaser" style="width: 40%; float: left;">
        <fieldset id="searchtop" name="searchtop">
        <div style="width: 495px; height: 15px; text-align: right;"><a href="#" onclick="javascript: Element.hide('map_alltrips'); return false;"></a></div><br />
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
        </fieldset>
    </div>
</div>
