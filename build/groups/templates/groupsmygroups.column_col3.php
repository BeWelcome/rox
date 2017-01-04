<div id="groups">
        <h3><?= $words->get('GroupsMyGroups'); ?></h3>
        <div class="clearfix">
        <?php
        $search_result = $this->search_result;
        if ($search_result)
        {
            $mygroups = $this->pager->getActiveSubset($search_result);
            $this->pager->render();
            $ii = 0;
            foreach ($mygroups as $group_data) :
                if ($ii %3 == 0) :
                    echo '<div class="subcolumns">';
                endif;
                if ($ii % 3 != 2) :
                    echo '<div class="c33l">';
                else :
                    echo '<div class="c33r">';
                endif; ?>
                <div class="groupbox group_float_left">
                    <a href="groups/<?=$group_data->getPKValue() ?>">
                        <img class="framed float_left"  width="80px" height="80px" alt="Group" src="<?= ((strlen($group_data->Picture) > 0) ? "groups/thumbimg/{$group_data->getPKValue()}" : 'images/icons/group.png' ) ;?>"/>
                    </a>
                    <div class="groupinfo">
                    <h4><a href="groups/<?= $group_data->getPKValue() ?>"><?= htmlspecialchars($group_data->Name, ENT_QUOTES); ?></a></h4>
                        <ul>
                            <li><?= $words->get('GroupsMemberCount');?>: <?=$group_data->getMemberCount(); ?></li>
                            <li><?= $words->get('GroupsNewMembers');?>: <?=count($group_data->getNewMembers()) ; ?></li>
                            <?php if ($group_data !== 0) {?>
                            <li><?= $words->get('GroupsLastPost');?>: <?=date($words->getBuffered('DateHHMMShortFormat'), ServerToLocalDateTime($group_data->latestPost, $this->getSession())); ?></li>
                            <?php } ?>
                        </ul>
                    </div> <!-- groupinfo -->
                </div> <!-- groupbox -->
				</div> <!-- c33x -->
			<?php if ($ii % 3 == 2) :
				echo "</div> <!-- subcolumns -->"; // subcolumns
		    endif;
			$ii++;
            endforeach ; 
			// check if a subcolumns needs to be closed
			if ($ii % 3 != 0) :
				echo "</div> <!-- subcolumns -->"; // subcolumns
			endif;
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
