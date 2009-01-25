<?php

/**
 * This page allows to create a new group
 *
 */
class GroupAdminPage extends GroupsBasePage
{
    protected function teaserContent()
    {
        // &gt; or &raquo; ?
        ?>
        <div id="teaser" class="clearfix">
        <div id="teaser_l1"> 
        <h1><a href="groups">Groups</a> &raquo; <a href="">Admininstrate group</a></h1>
        </div>
        </div>
        <?php
    }

    protected function getSubmenuActiveItem()
    {
        return 'admin';
    }

    protected function column_col3()
    {
        // get translation module
        $layoutkit = $this->layoutkit;
        $words = $layoutkit->getWords();
        $model = $this->getModel();


?>
    <div id="groups">
        <h3><?= $words->get('GroupsAdminGroup'); ?></h3>
        <a href="groups/<?= $this->group->id; ?>/groupsettings"><?= $words->get('GroupsChangeSettings'); ?></a>
        <a href="groups/<?= $this->group->id; ?>/memberadministration"><?= $words->get('GroupsAdministrateMembers'); ?></a>
        <a href="groups/<?= $this->group->id; ?>/delete"><?= $words->get('GroupsDeleteGroup'); ?></a>
    </div>
    <?php
    }


}

?>
