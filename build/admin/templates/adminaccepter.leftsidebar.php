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
/** 
 * @author Matthias Heß <globetrotter_tt>
 */
$words = new MOD_words();
?>
<h3><a href="admin" title="$words->get('BackToVolunteerToolsBarTitle')">&laquo; <?php echo $words->get('VolunteerToolsBarTitle') ?></a></h3>
<?php
    echo <<<HTML
<table>
    <tr>
        <th>Status</th>
        <th>#</th>
    </tr>
HTML;
    foreach ($this->model->getStatusOverview() as $status => $count) 
    {
        echo <<<HTML
    <tr>
        <td><a href="{$this->router->url('admin_accepter')}?status={$status}">{$status}</a></td>
        <td>{$count}</td>
    </tr>
HTML;
    }
    echo <<<HTML
</table>
HTML;
