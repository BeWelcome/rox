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

*/
    $words = new MOD_words();
?>

<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $google_conf->maps_api_key; ?>" type="text/javascript"></script>

<?php if ($mapstyle == "mapon") { ?>
<div id="MapDisplay">
<div id="map" style="height:440px;width:100%;border-left: 2px solid #999"></div>

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
// other stuff
var varSortOrder = '';
var searchHelp = '<?php echo str_replace("/","\/", $words->getBuffered('searchHelp')); ?>';
var fieldHelpAddress = '<?php echo str_replace("/","\/", $words->getBuffered('FindPeopleHelpAddress')); ?>';
var fieldHelpUsername = '<?php echo str_replace("/","\/", $words->getBuffered('FindPeopleHelpUsername')); ?>';
var fieldHelpTextToFind = '<?php echo str_replace("/","\/", $words->getBuffered('FindPeopleHelpTextToFind')); ?>';
var mapoff = <?php echo ($mapstyle == "mapoff") ? 'true' : 'false'; ?>;
var varsOnLoad = '<?php echo $varsOnLoad ? '/varsonload' : ''; ?>';
var queries = '<?php echo $queries ? '/queries' : ''; ?>';
var hideShowMap = '<?php echo $words->getBuffered('FindPeopleHideShowMap'); ?>';
var loading = '<img src="images/misc/loading_orange.gif"> <?php echo $words->getBuffered('FindPeopleIndicateLoading'); ?>';
var addressNotFound = '<?php echo $words->getBuffered('FindPeopleIndicateAddressNotFound'); ?>';
var membersDisplayed = '<?php echo $words->getBuffered('FindPeopleMembersDisplayed'); ?>';
var wordOf = '<?php echo $words->getBuffered('wordOf'); ?>';
var wordFound = '<?php echo $words->getBuffered('wordFound'); ?>';
</script>
<script type="text/javascript" src="script/labeled_marker.js"></script>
<script src="script/searchmembers.js" type="text/javascript"></script>
<?php echo $words->flushBuffer() ?>
