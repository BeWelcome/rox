<div id="groups">
        <h3><?= $words->get('GroupsMyGroups'); ?></h3>

    <div class="d-flex align-content-stretch flex-wrap">


        <?php
        $search_result = $this->search_result;
        if ($search_result)
        {
        $mygroups = $this->pager->getActiveSubset($search_result);
        $this->pager->render();
            foreach ($mygroups as $group_data) :
               ?>

        <div class="d-flex flex-row m-2">
            <div>
                <!-- group image -->
                <a href="groups/<?=$group_data->getPKValue() ?>">
                    <img class="framed"  width="80px" height="80px" alt="Group" src="<?= ((strlen($group_data->Picture) > 0) ? "groups/thumbimg/{$group_data->getPKValue()}" : 'images/icons/group.png' ) ;?>"/>
                </a>
            </div>
            <div>
                <!-- group name -->
                <h4><a href="groups/<?= $group_data->getPKValue() ?>"><?= htmlspecialchars($group_data->Name, ENT_QUOTES); ?></a></h4>
                <!-- group details -->
                <ul>
                    <li><?= $words->get('GroupsMemberCount');?>: <?=$group_data->getMemberCount(); ?></li>
                    <li><?= $words->get('GroupsNewMembers');?>: <?=count($group_data->getNewMembers()) ; ?></li>
                    <?php if ($group_data !== 0) {?>
                        <li><?= $words->get('GroupsLastPost');?>: <?=date($words->getBuffered('DateHHMMShortFormat'), ServerToLocalDateTime($group_data->latestPost, $this->getSession())); ?></li>
                    <?php } ?>
                </ul>
            </div>
        </div>

        <?php
            endforeach ;
            $this->pager->render();
        }
        else
        {
            echo <<<HTML
            <p class="note">
            {$words->get('GroupsMyGroupsNoResults', '<a href="groups/search">', '</a>', '<a href="groups/new">', '</a>')}
            </p>
HTML;
        }
        ?>

    </div>
</div>
