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
     * This page shows subgroup logging
     *
     * @package Apps
     * @subpackage Subgroups
     */
class GroupSubgroupLogPage extends GroupsBasePage
{
    protected function column_col3() {
        $layoutbits = new MOD_layoutbits();
        $words = $this->getWords();
        $group_id = $this->group->id;
        //parent::column_col3();
          ?>
        <fieldset>
            <legend><?= $words->get('AdministrateSubgroupsTitle'); ?></legend>
            <div class="subcolumns">
            <h3><?php echo $words->getFormatted('CurrentSubgroupsTitle');?></h3>
               <ul class="floatbox">
                <?php 
                $subgroups = $this->group->findSubgroups($group_id);
                foreach ($subgroups as $group_data) { 
                    if (strlen($group_data->Picture) > 0) {
                        $img_link = "groups/thumbimg/{$group_data->getPKValue()}";
                    } else {
                        $img_link = "images/icons/group.png";
                    } ?>
                <li class="picbox_subgroup float_left">
                    <a href="groups/<?php echo $group_data->getPKValue() ?>">
                        <img class="framed_subgroup float_left"  width="60px" height="60px" alt="Group" src="<?php echo $img_link; ?>"/>
                    </a>
                    <div class="userinfo"><span class="small">
                    <h4><a href="groups/<?php echo $group_data->getPKValue() ?>"><?php echo htmlspecialchars($group_data->Name, ENT_QUOTES) ?></a></h4>
                        <?php echo $words->get('GroupsMemberCount');?>: <?php echo $group_data->getMemberCount(); ?><br />
                        <?php echo $words->get('GroupsNewMembers');?>: <?php echo count($group_data->getNewMembers()) ; ?><br />
                    </span></div> <!-- userinfo -->
                </li> <!-- userpicbox_subgroup -->

            <?php } ?>
            </ul>   
            </div><!-- subcolumns -->
            <div class="subcolumns">   
            <?php $logvar = $this->logs;?>
            </div><!-- subcolumns -->
            <div class="subcolumns"> <?php
                if ($this->isGroupMember())  {
                    $add_button_link = "groups/{$this->group->id}/selectsubgroup";
                    $add_button_word = $words->get('AddSubgroupButton');
                    $delete_button_link = "groups/{$this->group->id}/selectdeletesubgroup";
                    $delete_button_word = $words->get('RemoveSubgroupButton');
                    ?>
                    <div class="c50l">
                        <div class="subcl">
                            <hr>
                                <a class="button" href="<?php echo $add_button_link; ?>">
                                    <span><?php echo $add_button_word; ?></span>
                                </a>
                            <hr>
                        </div><!-- subcl -->
                    </div><!-- c50l -->
                    <div class="c50r">
                        <div class="subcr"> <?php 
                            if (!empty($subgroups)) { ?>
                                <hr>
                                    <a class="button" href="<?php echo $delete_button_link; ?>">
                                        <span><?php echo $delete_button_word; ?></span>
                                    </a>
                                <hr> <?php
                            } ?>
                        </div><!-- subcr -->
                    </div><!-- c50r --> <?php
                } ?>

            </div><!-- subcolumns -->
            <?php
            if ($this->isGroupAdmin) {
            ?>        
            <div class="subcolumns">
                <br />
                <h4> <?php echo $words->get('NbOfLogEntries') . ": " . count($logvar); ?> </h4>
                <ul class="floatbox"> <?php
                    foreach ($logvar as &$value) { ?>
                        <li class="picbox_subgroup float_left">
                            <img class="framed_subgroup float_left" src="members/avatar/<?php echo $value->member->Username; ?>?xs"/>
                            <div class="userinfo">
                                <span class="small">
                                    <?php
                                    $layoutbits->ago(strtotime($value->ts));
                                    ?>
                                    <br />
                                    <a href="members/<?php echo $value->member->Username; ?>"><?php echo $value->member->Username; ?></a>
                                    <?php echo $words->get($value->SubgroupAction); ?>
                                    <a href="groups/<?php echo $value->subgroup->getPKValue(); ?>"><?php echo htmlspecialchars($value->subgroup->Name, ENT_QUOTES); ?></a><br />
                                </span>
                            </div>
                        </li> <?php
                    } ?>
                </ul>  <!-- floatbox --> 
            </div><!-- subcolumns -->  
            <?php
            }
            ?>    
        </fieldset>
    <?php
    }
    protected function getStylesheets() {
       $stylesheets = parent::getStylesheets();
       $stylesheets[] = 'styles/css/minimal/screen/custom/groups.css?1';
       return $stylesheets;
    }
    protected function getSubmenuActiveItem() {
        return 'subgroupsettings';
    }
    
}
?>
