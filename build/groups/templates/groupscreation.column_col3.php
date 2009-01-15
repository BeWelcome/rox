<?php
    // get translation module
    $layoutkit = $this->layoutkit;
    $words = $layoutkit->getWords();
    $model = $this->getModel();

    $page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);

    $formkit = $layoutkit->formkit;
    $callback_tag = $formkit->setPostCallback('GroupsController', 'createGroupCallback');
    
    $R = MOD_right::get();
    $GroupRight = $R->hasRight('Group');

    if ($redirected = $formkit->mem_from_redirect)
    {
        $Group_ = ((!empty($redirected->post['Group_'])) ? $redirected->post['Group_'] : '');
        $GroupDesc_ = ((!empty($redirected->post['GroupDesc_'])) ? $redirected->post['GroupDesc_'] : '');
        $Type = ((!empty($redirected->post['Type'])) ? $redirected->post['Type']: false);
        $problems = ((is_array($redirected->problems)) ? $redirected->problems : array());
    }
    else
    {
        $Group_ = '';
        $GroupDesc_ = '';
        $Type = false;
        $problems = array();
    }

?>
    <div id="groups">
        <h3>Create a new Group</h3>
        <form method="post" action="<?=$page_url ?>">
        <?=$callback_tag ?>
            <?= ((!empty($problems['General'])) ? "<p class='error'>" . $words->get('GroupsCreationFailed') . "</p>" : '' ); ?>
            <label for="name">Name:</label><?= ((!empty($problems['Group_'])) ? "<span class='error'>" . $words->get('GroupsCreationNameMissing') . "</span>" : '' ); ?><br />
            <input type="text" id="name" name="Group_" class="long" value='<?=$Group_?>' />
            <br /><br />
            <label for="description">Description:</label><?= ((!empty($problems['GroupDesc_'])) ? "<span class='error'>" . $words->get('GroupsCreationDescriptionMissing') ."</span>" : '' ); ?><br />
            <textarea  id="description" name="GroupDesc_" cols="60" rows="5" class="long" ><?=$GroupDesc_?></textarea><br /><br />
            <h3>Who can join</h3><?= ((!empty($problems['Type'])) ? "<span class='error'>" . $words->get('GroupsCreationTypeMissing') . "</span>" : '' ); ?>
            <ul>
                <li><input type="radio" id="public" name="Type" value="Public"<?= (($Type=='Public') ? ' checked': ''); ?> /><label for="public" ><?=$words->get('GroupsJoinPublic'); ?></label></li>
                <li><input type="radio" id="approved" name="Type" value="Approved"<?= (($Type=='Approved') ? ' checked': ''); ?> /><label for="approed" ><?=$words->get('GroupsJoinApproved'); ?></label></li>
                <li><input type="radio" id="invited" name="Type" value="Invited"<?= (($Type=='Invited') ? ' checked': ''); ?> /><label for="invited" ><?=$words->get('GroupsJoinInvited'); ?></label></li>
            </ul>
            <p class="center"><input type="submit" value="Create Group" /></p>
        </form>
    </div>
