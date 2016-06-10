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
 * @author Manuel <crumbking>
 */
$words = new MOD_words($this->getSession());
?>
<h3><a href="admin" title="$words->get('BackToVolunteerToolsBarTitle')">&laquo; <?php echo $words->get('VolunteerToolsBarTitle') ?></a></h3>
<ul class="linklist">
<?php
    $right_names = array_keys($this->rights);
    $expand_class = count($right_names) == 1 ? 'expanded' : 'expandable';
    if (in_array('Debug', $right_names))
    {
        echo <<<HTML
        <li><a href='{$this->router->url('admin_debug_logs', array('log_type' => 'php'))}' title='Check the PHP error logs'>PHP error logs</a></li>
        <li><a href='{$this->router->url('admin_debug_logs', array('log_type' => 'exception'))}' title='Check the Exception logs'>Exception logs</a></li>
        <li><a href='{$this->router->url('admin_debug_logs', array('log_type' => 'mysql'))}' title='Check the MySQL logs'>MySQL logs</a></li>
        <li><a href='{$this->router->url('admin_debug_logs', array('log_type' => 'apache'))}' title='Check the MySQL logs'>Apache error logs</a></li>
    </ul>
</li>
HTML;
    }

?>
</ul>

<script type='text/javascript'>
late_loader.queueObjectMethod('common', 'makeExpandableLinks');
</script>
