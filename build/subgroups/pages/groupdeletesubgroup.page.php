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
     * This page asks if the user wants to remove a group as subgroups the group
     *
     * @package Apps
     * @subpackage Subgroups
     */
class GroupDeleteSubgroupPage extends GroupsBasePage
{
    protected function column_col3()
    {
        $words = $this->getWords();
?>
        <div class="subcolumns"> 
            <div class="subcr">
                <?php // display my groups, if there are any
                $subgroups = $this->subgroups;
                $group = $this->group;
                if (!empty($subgroups)) {
                    echo "<h3>" . $words->get('TitleDeleteSubgroupFromGroup') . " " . htmlspecialchars($group->Name, ENT_QUOTES) . "</h3>" ;
                    foreach($subgroups as $subgroup) {
                        if (strlen($subgroup->Picture) > 0) {
                            $img_link = "groups/thumbimg/{$subgroup->getPKValue()}";
                        } else {
                            $img_link = "images/icons/group.png";
                        } ?>

                        <div style='float: left; width: 48%'>
                        <div class="groupbox floatbox">
                            <a href="groups/<?php echo $subgroup->getPKValue(); ?>">
                                <img class="framed float_left"  width="60px" height="60px" alt="Group" src="<?php echo $img_link; ?>"/>
                            </a>
                            <div class="groupinfo">
                            <h4><a href="groups/<?php echo $subgroup->getPKValue(); ?>"><?php echo htmlspecialchars($subgroup->Name, ENT_QUOTES); ?></a></h4>
                            <a class="button" href="groups/<?php echo $group->getPKValue(); ?>/deletesubgroup/<?php echo $subgroup->id; ?>"><span><?php echo $words->get('GroupsRemoveFromGroup'); ?></span></a>
                            </div> <!-- groupinfo -->
                        </div> <!-- groupbox floatbox -->
                        </div>   
                    <?php }
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
