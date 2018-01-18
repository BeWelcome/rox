<div id="groups">
        <div class="floatbox">
        <div class="subcolumns">
            <div class="c50l">
                <div class="groupbox float_left" style="width:90%">
                    <h3><?= $words->get('GroupsSearchHeading'); ?></h3>
                    <form action="groups/search" method="get">
                        <p><?= $words->get('GroupsSearchInfo'); ?></p>
                        <input type="text" name="GroupsSearchInput" value="" id="GroupsSearchInput" /><input type="submit" value="<?= $words->getSilent('GroupsSearchSubmit'); ?>" /><?=$words->flushBuffer()?><br />
                    </form>
                </div>
            </div>
            <div class="c50r">
                <div class="groupbox float_left">
                    <h3><?= $words->get('GroupsCreateHeading'); ?></h3>
                    <p><?= $words->get('GroupsCreateDescription'); ?></p>
                    <a class="button" href="groups/new"><span><?= $words->get('GroupsCreateNew'); ?></span></a>
                </div>
            </div>
        </div>

        <h3><?= $words->get('GroupsSearchResult'); ?></h3>
        <?php
        $search_result = $this->search_result;
        if ($search_result) :
            $act_order = (($this->result_order == "actdesc") ? 'actasc' : 'actdesc');
            $name_order = (($this->result_order == "nameasc") ? 'namedesc' : 'nameasc');
            $member_order = (($this->result_order == "membersdesc") ? 'membersasc' : 'membersdesc');
            $created_order = (($this->result_order == "createdasc") ? 'createddesc' : 'createdasc');
            $category_order = (($this->result_order == "categoryasc") ? 'categorydesc' : 'categoryasc');
            ?>
            <p><strong><?php echo $words->get('GroupsSearchOrdered');?>:</strong> <?php echo $words->get('GroupsSearchOrdered' . $this->result_order)?>&nbsp;&nbsp;&nbsp;
            <strong><?= $words->get('GroupsSearchOrder');?></strong>
            <a class="grey" href="groups/search?GroupsSearchInput=<?=$this->search_terms;?>&order=<?=$act_order;?>&<?=$this->pager->getActivePageMarker();?>"><?= $words->get('GroupsOrderBy' . $act_order); ?></a>
            |
            <a class="grey" href="groups/search?GroupsSearchInput=<?=$this->search_terms;?>&order=<?=$name_order;?>&<?=$this->pager->getActivePageMarker();?>"><?= $words->get('GroupsOrderBy' . $name_order); ?></a>
            |
            <a class="grey" href="groups/search?GroupsSearchInput=<?=$this->search_terms;?>&order=<?=$member_order;?>&<?=$this->pager->getActivePageMarker();?>"><?= $words->get('GroupsOrderBy' . $member_order); ?></a>
            |
            <a class="grey" href="groups/search?GroupsSearchInput=<?=$this->search_terms;?>&order=<?=$created_order;?>&<?=$this->pager->getActivePageMarker();?>"><?= $words->get('GroupsOrderDate' . $created_order); ?></a></p>
<?
// Categories link disabled until we have categories
//            |
//            <a class="grey" href="groups/search?GroupsSearchInput={$this->search_terms}&amp;Order={$category_order}&Page={$this->result_page}">Category</a>
            $this->pager->render();
            echo <<<HTML
<div class="floatbox">
HTML;
            $ii = 0;
            foreach ($search_result as $group_data) :
                if ($ii %3 == 0) :
                    echo '<div class="subcolumns">';
                endif;
                if ($ii % 3 != 2) :
                    echo '<div class="c33l">';
                else :
                    echo '<div class="c33r">';
                endif;
                ?>
                <div class="groupbox group_float_left">
                    <a href="groups/<?=$group_data->getPKValue() ?>">
                        <img class="framed float_left"  width="80px" height='80px' alt="group" src="<?= ((strlen($group_data->Picture) > 0) ? "groups/thumbimg/{$group_data->getPKValue()}" : 'images/icons/group.png' ) ?>"/>
                    </a>
                    <div class="groupinfo">
                        <h4><a href="groups/<?=$group_data->getPKValue() ?>"><?=htmlspecialchars($group_data->Name, ENT_QUOTES) ?></a></h4>
                        <ul>
                            <li><?= $words->get('GroupsMemberCount');?>: <?=$group_data->getMemberCount(); ?></li>
                            <li><?= $words->get('GroupsDateCreation');?>: <?=date($words->getBuffered('DateHHMMShortFormat'), ServerToLocalDateTime(strtotime($group_data->created))); ?></li>
                            <?php if ($group_data !== 0) {?>
                            <li><?php
                                if ($group_data->latestPost) {
                                    echo $words->get('GroupsLastPost') . ": " . date($words->getBuffered('DateHHMMShortFormat'), ServerToLocalDateTime($group_data->latestPost));
                                } else {
                                    echo $words->get('GroupsNoPostYet');
                                }
                            ?></li>
                            <?php } ?>
                        </ul>
                    </div> <!-- groupinfo -->
                </div> <!-- groupbox  -->
            </div>
			<?php if ($ii % 3 == 2) :
				echo "</div>"; // subcolumns
		    endif;
			$ii++;
            endforeach ; 
			// check if a subcolumns need to be closed
			if ($ii % 3 != 0) :
				echo "</div>"; // subcolumns
			endif; ?>
</div> <!-- floatbox -->
            <?php
            $this->pager->render();
            ?>
        <?php else :
            echo <<<HTML
            <p class="note">
            {$words->get('GroupSearchNoResults')}
            </p>
HTML;
        endif;
        ?>
</div> <!-- floatbox -->
</div> <!-- groups -->
