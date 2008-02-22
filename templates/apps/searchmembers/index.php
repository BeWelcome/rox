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
<script src="script/prototype.js" type="text/javascript"></script>
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $google_conf->maps_api_key; ?>" type="text/javascript"></script>


<?php if ($MapOff == "mapoff") { ?>
    <a name="memberlist"></a>
    <div id="member_list"></div>
<?php } ?>

<?php if ($MapOff != "mapoff") { ?>
<div id="MapDisplay">
<div id="map" style="<?php if ($mapstyle=='small') {echo 'height:200px;';} else {echo 'height:480px;';}?> width:99%;"></div>
</div>

<div id="legend" class="floatbox" style="padding: 20px;">
    <div class="floatbox small float_left" style="width: 80%">
        <?php echo '<img width="20px" src="images/icons/gicon1.png" alt="'. $words->getBuffered('MapLegendGIcon1') .'" title="'. $words->getBuffered('MapLegendGIcon1') .'" class="forum_icon" />' . $words->flushBuffer();
        ?>
         = <?php echo $words->get('MapLegendGIcon1');?>
    </div>
    <div class="floatbox small float_left" style="width: 80%">
        <?php echo '<img width="20px" src="images/icons/gicon2.png" alt="'. $words->getBuffered('MapLegendGIcon2') .'" title="'. $words->getBuffered('MapLegendGIcon2') .'" class="forum_icon" />' . $words->flushBuffer();
        ?>
         = <?php echo $words->get('MapLegendGIcon2');?>
    </div>
    <div class="floatbox small float_left" style="width: 80%">
        <?php echo '<img width="20px" src="images/icons/gicon3.png" alt="'. $words->getBuffered('MapLegendGIcon3') .'" title="'. $words->getBuffered('MapLegendGIcon3') .'" class="forum_icon" />' . $words->flushBuffer();
        ?>
         = <?php echo $words->get('MapLegendGIcon3');?>
    </div>
</div>
<?php } ?>


<script type="text/javascript">
// other stuff
var mapoff = <?php echo ($MapOff == "mapoff") ? 'true' : 'false'; ?>;
var mapstyle = <?php echo ($mapstyle == "small") ? 'true' : 'false'; ?>;
var varsOnLoad = '<?php echo $varsOnLoad ? '/varsonload' : ''; ?>';
var queries = '<?php echo $queries ? '/queries' : ''; ?>';
var loading = '<img src="images/misc/loading_orange.gif"> <?php echo $words->getBuffered('FindPeopleIndicateLoading'); ?>';
var addressNotFound = '<?php echo $words->getBuffered('FindPeopleIndicateAddressNotFound'); ?>';
var membersDisplayed = '<?php echo $words->getBuffered('FindPeopleMembersDisplayed'); ?>';
var jumpToResults = '<?php echo $words->getBuffered('FindPeopleJumpToResults'); ?>';
var wordOf = '<?php echo $words->getBuffered('wordOf'); ?>';
var wordFound = '<?php echo $words->getBuffered('wordFound'); ?>';
</script>
<script src="script/searchmembers.js" type="text/javascript"></script>
<?php echo $words->flushBuffer() ?>