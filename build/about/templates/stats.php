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
<h2><?php echo $words->get("StatsHead") ?></h2>
<?php
/*
 * Puts a set of divs onto the page that contain the generated images
 */
function drawCharts($filename, $headlineCode, $words)
{
?>
    <h3><?php echo $words->get($headlineCode) ?></h3>
    <div class="subcolumns bw_row">
        <div class="c50l">
            <div class="subcl">
                <h4><?php echo $words->get('StatsHeadCol1') ?></h4>
            </div>
        </div>
        <div class="c50r">
            <div class="subcr">
                <h4><?php echo $words->get("StatsHeadCol2") ?></h4>
            </div>
        </div>
            <div class="c50l">
            <div class="subcl">
                <div><?php echo '<img class="statimage" src="/stats/' . $filename . '-alltime.png" alt="' . $words->getSilent($headlineCode . "AllTimeInfo") . '" />';?></div>
            </div>
        </div>
        <div class="c50r">
            <div class="subcr">
                <div><?php echo '<img class="statimage" src="/stats/' . $filename . '-last2month.png" alt="' . $words->getSilent($headlineCode . "Last2MonthInfo") . '" />';?></div>
            </div>
        </div>
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

<div class="subcolumns bw_row">
    <div class="c50l">
        <div class="subcl">
            <h3><?php echo $words->get("StatsLastLogin") ?></h3>
        </div>
    </div>
    <div class="c50r">
        <div class="subcr">
            <h3><?php echo $words->get("StatsMemberCountry") ?></h3>
        </div>
    </div>
</div>
<div class="subcolumns bw_row">
    <div class="c50l">
        <div class="subcl">
            <div><img class="statimage" src="/stats/loginpie.png" /></div>
        </div>
    </div>
    <div class="c50r">
        <div class="subcr">
            <div><img class="statimage" src="/stats/countrypie.png" /></div>
        </div>
    </div>
</div>
<div class="subcolumns bw_row">
    <div class="c50l">
        <div class="subcl">
            <h3><?php echo $words->get("StatsLanguages") ?></h3>
        </div>
    </div>
    <div class="c50r">
        <div class="subcr">
            <h3><?php echo $words->get("StatsPreferredLanguages") ?></h3>
        </div>
    </div>
    <div class="c50l">
        <div class="subcl">
            <div><img class="statimage" src="/stats/languagepie.png" /></div>
        </div>
    </div>
    <div class="c50r">
        <div class="subcr">
            <div><img class="statimage" src="/stats/preferredlanguagepie.png" /></div>
        </div>
    </div>
</div>
