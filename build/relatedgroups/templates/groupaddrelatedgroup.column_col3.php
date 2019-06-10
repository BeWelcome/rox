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
     * This template is used to add related groups to the group
     *
     * @package Apps
     * @subpackage RelatedGroups
     */
?>

        <?php // display my groups, if there are any
        if (!empty($my_groups)) {
            echo "<div class='col-12'><h3>" . $words->get('TitleAddRelatedGroupToGroup') . " " . htmlspecialchars($group->Name, ENT_QUOTES) . "</h3></div>" ;

            foreach($my_groups as $my_group) :
                if ($my_group->id != $group->getPKValue()) {
                    if (strlen($my_group->Picture) > 0) {
                        $img_link = "group/thumbimg/{$my_group->getPKValue()}";
                    } else {
                        $img_link = "images/icons/group.png";
                    } ?>

                    <div class="col-12 col-md-6 p-2">
                        <div class="float-left h-100 mr-2" style="width: 80px;">
                            <!-- group image -->
                            <a href="group/<?php echo $my_group->getPKValue(); ?>">
                                <img class="groupimg framed" alt="Group" src="<?php echo $img_link; ?>"/>
                            </a>
                        </div>
                        <div>
                            <h4>
                                <a href="group/<?php echo $my_group->getPKValue(); ?>"><?php echo htmlspecialchars($my_group->Name, ENT_QUOTES); ?></a>
                            </h4>
                            <a class="btn btn-outline-primary" role="button" href="group/<?php echo $group->getPKValue(); ?>/addrelatedgroup/<?php echo $my_group->id; ?>"><?php echo $words->get('GroupsAddAsRelatedGroup'); ?></a>
                        </div>
                    </div>

                <?php
                }
            endforeach;
        } ?>

