<div class="subcolumns">
  <div class="c50l">
    <div class="subcl">
    <div id="groups">
        <h3><?= $words->get('GroupsMyGroups'); ?></h3>
        <?php
        $search_result = $this->search_result;
        if ($search_result)
        {
            $this->pager->render();
            foreach($this->pager->getActiveSubset($search_result) as $group_data)
            {
                ?>
                <div class="groupbox clearfix">
                    <a href="groups/<?=$group_data->id ?>">
                        <img class="framed float_left"  width="80px" height="80px" alt="Group" src="<?= ((strlen($group_data->Picture) > 0) ? "groups/thumbimg/{$group_data->getPKValue()}" : 'images/icons/group.png' ) ;?>"/>
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
            <p class="note">
            {$words->get('GroupSearchNoResults')}
            </p>
HTML;
        }
        ?>
        </div>
	</div>
   </div>


  <div class="c50r">
    <div class="subcr">
    <div id="groups">
    <h3>&nbsp;</h3>
        <?php
        $search_result = $this->search_result;
        if ($search_result)
        {
            foreach($search_result as $group_data)
            {
                ?>
                <div class="groupbox clearfix">
                    <a href="groups/<?=$group_data->id ?>">
                        <img class="framed float_left"  width="80px" alt="Group" src="<?= ((strlen($group_data->Picture) > 0) ? "groups/thumbimg/{$group_data->getPKValue()}" : 'images/icons/group.png' ) ;?>"/>
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
            <p class="note">
            {$words->get('GroupSearchNoResults')}
            </p>
HTML;
        }
        ?>
        </div>
    
    </div>
  </div>
</div>
