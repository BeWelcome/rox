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
     * This template is used to remove related groups from the group
     *
     * @package Apps
     * @subpackage RelatedGroups
     */
?>
<div class="subcolumns"> 
    <div class="subcr">
        <?php // display my groups, if there are any
        if (!empty($relatedgroups)) {
            echo "<h3>" . $words->get('TitleDeleteRelatedGroupFromGroup') . " " . htmlspecialchars($group->Name, ENT_QUOTES) . "</h3>" ;
            foreach($relatedgroups as $relatedgroup) :
                if (strlen($relatedgroup->Picture) > 0) {
                    $img_link = "groups/thumbimg/{$relatedgroup->getPKValue()}";
                } else {
                    $img_link = "images/icons/group.png";
                } ?>

                <div class="relatedgroupinfo">
                    <div class="groupbox floatbox">
                        <a href="groups/<?php echo $relatedgroup->getPKValue(); ?>">
                            <img class="framed float_left"  width="60px" height="60px" alt="Group" src="<?php echo $img_link; ?>"/>
                        </a>
                        <div class="groupinfo">
                            <h4><a href="groups/<?php echo $relatedgroup->getPKValue(); ?>"><?php echo htmlspecialchars($relatedgroup->Name, ENT_QUOTES); ?></a></h4>
                            <a class="button" href="groups/<?php echo $group->getPKValue(); ?>/deleterelatedgroup/<?php echo $relatedgroup->id; ?>"><span><?php echo $words->get('GroupsRemoveFromGroup'); ?></span></a>
                        </div> <!-- groupinfo -->
                    </div> <!-- groupbox floatbox -->
                </div>   
            <?php endforeach;
        } ?>
                

    </div> <!-- subcr -->
</div> <!-- subcolumns -->
</div> <!-- groups -->

