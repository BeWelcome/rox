<?php
/*
Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $google_conf->maps_api_key; ?>" type="text/javascript"></script>
*/
    $words = new MOD_words();
?>

<?php if ($mapstyle == "mapon") { ?>
<div id="MapDisplay">
<div id="map" style="height:440px;width:100%; border: 1px solid #888; padding: 1px;"></div>

<div id="legend" class="floatbox" style="padding: 20px;">
<table><tr>
<?php
function mapLegend($icon, $words, $accom)
{
    echo '<td><img src="images/icons/gicon'.$icon.'_a.png" title="'. $words->getBuffered("Accomodation_".$accom) .'" class="forum_icon" /></td>';
    echo '<td>'.$words->getBuffered("Accomodation_".$accom).'</td>';
}
mapLegend(1, $words, $TabAccomodation[0]);
mapLegend(2, $words, $TabAccomodation[1]);
mapLegend(3, $words, $TabAccomodation[2]);
$words->flushBuffer();
?>
</tr></table>
</div>
</div>
<?php } ?>

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

/* BTchange is the small view-menu in searchmembers */

ViewImg1 = "images/misc/one.gif";
ViewImg1_f2 = "images/misc/one_f2.gif";

ViewImg2 = "images/misc/two.gif";
ViewImg2_f2 = "images/misc/two_f2.gif";

ViewImg3 = "images/misc/three.gif";
ViewImg3_f2 = "images/misc/three_f2.gif";

function BTchange (IdImg, ImgObj) {
  document.getElementById(IdImg).src = ImgObj;
}
function changeSortOrder (SortOrder) {
  varsOnLoad = '/varsonload';
  varSortOrder = '/'+SortOrder;
  document.getElementById('filterorder').value = SortOrder;
  searchGlobal(0);
  varsOnLoad = '';
}

// other stuff
var varSortOrder = '';
var searchInDivText = decodeURIComponent('<?php echo rawurlencode($words->getBuffered('FindPeopleSubmitMapSearch')); ?>');
var searchHelp = decodeURIComponent('<?php echo rawurlencode($words->getBuffered('searchHelp')); ?>');
var fieldHelpAddress = decodeURIComponent('<?php echo rawurlencode($words->getBuffered('FindPeopleHelpAddress')); ?>');
var fieldHelpUsername = decodeURIComponent('<?php echo rawurlencode($words->getBuffered('FindPeopleHelpUsername')); ?>');
var fieldHelpTextToFind = decodeURIComponent('<?php echo rawurlencode($words->getBuffered('FindPeopleHelpTextToFind')); ?>');
var fieldHelpMapBoundaries = decodeURIComponent('<?php echo rawurlencode($words->getBuffered('FindPeopleHelpMapBoundaries')); ?>');
var mapoff = <?php echo ($mapstyle == "mapoff") ? 'true' : 'false'; ?>;
var varsOnLoad = '<?php echo $varsOnLoad ? '/varsonload' : ''; ?>';
var varsGet = <?php echo isset($_GET['vars']) ? $_GET['vars'] : 0; ?>;
var queries = '<?php echo $queries ? '/queries' : ''; ?>';
var hideShowMap = decodeURIComponent('<?php echo rawurlencode($words->getBuffered('FindPeopleHideShowMap')); ?>');
var loading = '<img src="images/misc/loading_orange.gif"> ' + decodeURIComponent('<?php echo rawurlencode($words->getBuffered('FindPeopleIndicateLoading')); ?>');
var addressNotFound = decodeURIComponent('<?php echo rawurlencode($words->getBuffered('FindPeopleIndicateAddressNotFound')); ?>');
var membersDisplayed = decodeURIComponent('<?php echo rawurlencode($words->getBuffered('FindPeopleMembersDisplayed')); ?>');
var noMembersFound = decodeURIComponent('<?php echo rawurlencode($words->getBuffered('FindPeopleNoMembersFound')); ?>');
var wordOf = decodeURIComponent('<?php echo rawurlencode($words->getBuffered('wordOf')); ?>');
var wordFound = decodeURIComponent('<?php echo rawurlencode($words->getBuffered('wordFound')); ?>');

function addTips() {
// prototip tips
new Tip('Address', fieldHelpAddress,{className: 'clean', hook: {target: 'bottomLeft', tip: 'topLeft' }});
new Tip('map_search', fieldHelpMapBoundaries,{className: 'clean', hook: {target: 'bottomLeft', tip: 'topLeft' }});
new Tip('UsernameField', fieldHelpUsername,{className: 'clean', hook: {target: 'bottomLeft', tip: 'topLeft' }});
new Tip('TextToFindField', fieldHelpTextToFind,{className: 'clean', hook: {target: 'bottomLeft', tip: 'topLeft' }});
}
</script>
<script src="script/searchmembers.js" type="text/javascript"></script>
<script type="text/javascript">
Event.observe(window, "load", addTips); 
</script>
<?php echo $words->flushBuffer() ?>
