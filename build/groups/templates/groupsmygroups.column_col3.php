        <h3><?= $words->get('GroupsMyGroups'); ?></h3>
        <div class="floatbox">
        <?php
        $search_result = $this->search_result;
        if ($search_result)
        {
            $mygroups = $this->pager->getActiveSubset($search_result);
            $this->pager->render();
            foreach ($mygroups as $group_data) : ?>
                <div style='float: left; width: 48%'>
                <div class="groupbox floatbox">
                    <a href="groups/<?=$group_data->getPKValue() ?>">
                        <img class="framed float_left"  width="80px" height="80px" alt="Group" src="<?= ((strlen($group_data->Picture) > 0) ? "groups/thumbimg/{$group_data->getPKValue()}" : 'images/icons/group.png' ) ;?>"/>
                    </a>
                    <div class="groupinfo">
                    <h4><a href="groups/<?= $group_data->getPKValue() ?>"><?= htmlspecialchars($group_data->Name, ENT_QUOTES); ?></a></h4>
                        <ul>
                            <li><?= $words->get('GroupsMemberCount');?>: <?=$group_data->getMemberCount(); ?></li>
                            <li><?= $words->get('GroupsNewMembers');?>: <?=count($group_data->getNewMembers()) ; ?></li>
                            <li><?= $words->get('GroupsNewForumPosts');?>: <?=$group_data->getNewForumPosts; ?></li>
                        </ul>
                    </div> <!-- groupinfo -->
                </div> <!-- groupbox floatbox -->
                </div>
            <?php endforeach;
            $this->pager->render();
        }
        else
        {
            echo <<<HTML
            <p class="note">
            {$words->get('GroupSearchNoResults')}
            </p>
HTML;
        }
        ?>
        </div>
