<div>
        <h3><?= $words->get('GroupsMyGroups'); ?></h3>
        <?php
        if ($this->search_result)
        {
            foreach($this->search_result as $group_data)
            {

                ?>
                <div class="floatbox">
                    <img class="framed float_left" height="50" width="50" alt="Group" src="<?= ((strlen($group_data->picture) > 0) ? $group_data->picture : 'images/icons/group.png' ) ;?>"/>
                    <h5><a href="groups/<?= $group_data->id ?>"><?= $group_data->Name ?></a></h5>
                    <h6 class="small"><?= $words->get('GroupsMemberCount', $group_data->getMemberCount); ?> | <?= count($group_data->getNewGroupMembers) ; ?> | <?= $words->get('GroupsNewForumPosts', $group_data->getNewForumPosts); ?></h6>
                    <p><?= $group_data->getDescription(); ?></p>
                </div>
<?php
            }
        }
        else
        {
            echo <<<HTML
            <div>
            {$words->get('GroupSearchNoResults')}
            </div>
HTML;
        }
        ?>
        </div>
