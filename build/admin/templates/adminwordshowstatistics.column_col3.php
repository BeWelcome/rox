<?php
/*
Copyright (c) 2007-2009 BeVolunteer

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
    /** 
     * @author Tsjoek
     */

    /** 
     * words management overview template
     * 
     * @package Apps
     * @subpackage Admin
     */
?>

  <table class="awstatstable"><tr><td valign=top><table>
<?php
    $split = round(count($this->data)/3+0.5);
    $cnt = 0;
    foreach ($this->data as $dat) {
        echo '<tr class="awstatsrow';
        // mark languages that are within scope
        if ($dat['scope']){echo ' awstatsinscope';}
        echo '"><td>'.htmlspecialchars($dat['name']).'</td><td>';
        printf("%01.1f", (float)$dat['perc']);
        echo  '% done</td></tr>';
        $cnt++;
        if ($cnt % $split == 0){echo '</table></td><td class="awstatscol"><table>';}
    }
?>
</table></td></tr></table>
