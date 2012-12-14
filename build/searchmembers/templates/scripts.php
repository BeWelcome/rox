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
//  varsOnLoad = '/varsonload';
  varSortOrder = '/'+SortOrder;
  document.getElementById('filterorder').value = SortOrder;
  loadMap(0);
//  searchGlobal(0);
//  varsOnLoad = '';
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
var varsGet = '<?php echo isset($_GET['vars']) ? $_GET['vars'] : ''; ?>';
var queries = '<?php echo $queries ? '/queries' : ''; ?>';
var hideShowMap = decodeURIComponent('<?php echo rawurlencode($words->getBuffered('FindPeopleHideShowMap')); ?>');
var loading = decodeURIComponent('<?=rawurlencode('<img src="images/misc/loading.gif" alt="Loading" />')?> ') + decodeURIComponent('<?php echo rawurlencode($words->getBuffered('FindPeopleIndicateLoading')); ?>');
var addressNotFound = decodeURIComponent('<?php echo rawurlencode($words->getBuffered('FindPeopleIndicateAddressNotFound')); ?>');
var membersDisplayed = decodeURIComponent('<?php echo rawurlencode($words->getBuffered('FindPeopleMembersDisplayed')); ?>');
var noMembersFound = decodeURIComponent('<?php echo rawurlencode($words->getBuffered('FindPeopleNoMembersFound')); ?>');
var wordOf = decodeURIComponent('<?php echo rawurlencode($words->getBuffered('wordOf')); ?>');
var wordFound = decodeURIComponent('<?php echo rawurlencode($words->getBuffered('wordFound')); ?>');
var logIn = decodeURIComponent('<?php echo rawurlencode($words->getBuffered('logSpaceIn')); ?>');
var toSee = decodeURIComponent('<?php echo rawurlencode($words->getBuffered('toSee')); ?>');
var more = decodeURIComponent('<?php echo rawurlencode($words->getBuffered('moreResults')); ?>');

function addTips() {
// prototip tips
new Tip('Address', fieldHelpAddress,{className: 'clean', hook: {target: 'bottomLeft', tip: 'topLeft' }});
//new Tip('map_search', fieldHelpMapBoundaries,{className: 'clean', hook: {target: 'bottomLeft', tip: 'topLeft' }});
new Tip('UsernameField', fieldHelpUsername,{className: 'clean', hook: {target: 'bottomLeft', tip: 'topLeft' }});
new Tip('TextToFindField', fieldHelpTextToFind,{className: 'clean', hook: {target: 'bottomLeft', tip: 'topLeft' }});
}
</script>
<script type="text/javascript">
Event.observe(window, "load", addTips); 
$('flip-sort-direction-button').observe('click', flipSortDirection);
</script>
