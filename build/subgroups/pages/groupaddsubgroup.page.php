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
     * @author mahouni
     */

    /**
     * This page shows groups which the user could add as subgroup to the group
     *
     * @package Apps
     * @subpackage Subgroups
     */
class GroupAddSubgroupPage extends GroupsBasePage
{
    protected function column_col3()
    {
        $words = $this->getWords();
?>
        <div class="subcolumns"> 
            <div class="subcr">
                <?php // display my groups, if there are any
                $my_groups = $this->my_groups;
                $group = $this->group;
                if (!empty($my_groups)) {
                    echo "<h3>" . $words->get('TitleAddSubgroupToGroup') . " " . htmlspecialchars($group->Name, ENT_QUOTES) . "</h3>" ;
                    foreach($my_groups as $my_group) {
                        if ($my_group->id != $group->getPKValue()) {
                            if (strlen($my_group->Picture) > 0) {
                                $img_link = "groups/thumbimg/{$my_group->getPKValue()}";
                            } else {
                                $img_link = "images/icons/group.png";
                            } ?>

                            <div style='float: left; width: 48%'>
                            <div class="groupbox floatbox">
                                <a href="groups/<?php echo $my_group->getPKValue(); ?>">
                                    <img class="framed float_left"  width="60px" height="60px" alt="Group" src="<?php echo $img_link; ?>"/>
                                </a>
                                <div class="groupinfo">
                                <h4><a href="groups/<?php echo $my_group->getPKValue(); ?>"><?php echo htmlspecialchars($my_group->Name, ENT_QUOTES); ?></a></h4>
                                <a class="button" href="groups/<?php echo $group->getPKValue(); ?>/addsubgroup/<?php echo $my_group->id; ?>"><span><?php echo $words->get('GroupsAddAsSubgroup'); ?></span></a>
                                </div> <!-- groupinfo -->
                            </div> <!-- groupbox floatbox -->
                            </div>    
                            
                  <?php }
                    } 
                } ?>
                
        </div> <!-- subcr -->
    </div> <!-- subcolumns -->
</div> <!-- groups -->


    <?php
    }

    protected function getSubmenuActiveItem() {
        return 'subgroupsettings';
    }

}


?>
