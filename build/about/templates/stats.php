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
/* Include all the classes */
?>
<div class="alert alert-info" role="alert">
    <span class="h5"><?php echo $words->get("StatsHead") ?></span>
</div>
<?php
/*
 * Puts a set of divs onto the page that contain the generated images
 */
function drawCharts($filename, $headlineCode, $words)
{
?>
    <div class="col-xs-12 mb-1">
        <span class="h3"><?php echo $words->get($headlineCode) ?></span>
    </div>

    <div class="col-xs-12 col-lg-6">
        <h4><?php echo $words->get('StatsHeadCol1') ?></h4>
        <div><?php echo '<img class="statimage" src="/stats/' . $filename . '-alltime.png">';?></div>
    </div>

    <div class="col-xs-12 col-lg-6">
        <h4><?php echo $words->get("StatsHeadCol2") ?></h4>
        <div><?php echo '<img class="statimage" src="/stats/' . $filename . '-last2month.png">';?></div>
    </div>

<?php
 echo $words->flushBuffer();
}

drawCharts('allmembers', 'StatsMembersAlltime', $words);
drawCharts('newmembers', 'StatsNewMembersAlltime', $words);
drawCharts('percentmembers', 'StatsPercentNewMembersLast', $words);
drawCharts('login', 'StatsLoginAlltime', $words);
drawCharts('percentlogin', 'StatsPercentLoginAlltime', $words);
drawCharts('trust', 'StatsTrustAlltime', $words);
drawCharts('messages', 'StatsMessagesAlltime', $words);
?>

<div class="col-xs-12 col-lg-6">
    <h4><?php echo $words->get("StatsLastLogin") ?></h4>
    <div><img class="statimage" src="/stats/loginpie.png" /></div>
</div>

<div class="col-xs-12 col-lg-6">
    <h4><?php echo $words->get("StatsMemberCountry") ?></h4>
    <div><img class="statimage" src="/stats/countrypie.png" /></div>
</div>

<div class="col-xs-12 col-lg-6">
    <h4><?php echo $words->get("StatsLanguages") ?></h4>
    <div><img class="statimage" src="/stats/languagepie.png" /></div>
</div>

<div class="col-xs-12 col-lg-6">
    <h4><?php echo $words->get("StatsPreferredLanguages") ?></h4>
    <div><img class="statimage" src="/stats/preferredlanguagepie.png" /></div>
</div>