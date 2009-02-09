<div id="groups">
        <h3><?= $words->get('GroupsMyGroups'); ?></h3>
        <?php
        if ($this->search_result)
        {
            foreach($this->search_result as $group_data)
            {
                ?>
                <div class="groupbox clearfix">
                    <a href="groups/<?=$group_data->id ?>">
                        <img class="framed float_left"  width="80px" alt="Group" src="<?= ((strlen($group_data->picture) > 0) ? $group_data->picture : 'images/icons/group.png' ) ;?>"/>
                    </a>
                    <div class="groupinfo">
                    <h4><a href="groups/<?= $group_data->id ?>"><?= $group_data->Name ?></a></h4>
                        <ul>
                            <li><?= $words->get('GroupsMemberCount');?>: <?=$group_data->getMemberCount(); ?></li>
                            <li><?= $words->get('GroupsNewMembers');?>: <?=count($group_data->getNewMembers()) ; ?></li>
                            <li><?= $words->get('GroupsNewForumPosts');?>: <?=$group_data->getNewForumPosts; ?></li>
                        </ul>
                    </div> <!-- groupinfo -->
                </div> <!-- groupbox clearfix -->
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
