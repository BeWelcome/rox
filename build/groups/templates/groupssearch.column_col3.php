        <h3><?= $words->get('GroupsSearchHeading'); ?></h3>
        <form action="groups/search" method="get">
            <input type="text" name="GroupsSearchInput" value="" id="GroupsSearchInput" /><input type="submit" value="<?= $words->get('GroupsSearchSubmit'); ?>" /><br />
        </form>
    
        <h3><?= $words->get('GroupsSearchResult'); ?></h3>
        <?php
        $search_result = $this->search_result;
        if ($search_result) :
            $name_order = (($this->result_order == "nameasc") ? 'namedesc' : 'nameasc');
            $member_order = (($this->result_order == "membersdesc") ? 'membersasc' : 'membersdesc');
            $created_order = (($this->result_order == "createdasc") ? 'createddesc' : 'createdasc');
            $category_order = (($this->result_order == "categoryasc") ? 'categorydesc' : 'categoryasc');
            ?>
            <h4><?= $words->get('GroupsSearchOrder');?></h4>
            <p class="grey">
            <a class="grey" href="groups/search?GroupsSearchInput=<?=$this->search_terms;?>&amp;order=<?=$name_order;?>&<?=$this->pager->getActivePageMarker();?>"><?= $words->get('GroupsOrderName'); ?></a>
            |
            <a class="grey" href="groups/search?GroupsSearchInput=<?=$this->search_terms;?>&amp;order=<?=$member_order;?>&<?=$this->pager->getActivePageMarker();?>"><?= $words->get('GroupsOrderMembers'); ?></a>
            |
            <a class="grey" href="groups/search?GroupsSearchInput=<?=$this->search_terms;?>&amp;order=<?=$created_order;?>&<?=$this->pager->getActivePageMarker();?>"><?= $words->get('GroupsOrderDate'); ?></a>
<?
// Categories link disabled until we have categories
//            |
//            <a class="grey" href="groups/search?GroupsSearchInput={$this->search_terms}&amp;Order={$category_order}&Page={$this->result_page}">Category</a>
            $this->pager->render();
            echo <<<HTML
<div>
HTML;
            foreach ($search_result as $group_data) :?>
                <div style='float: left; width: 48%'>
                <div class="groupbox floatbox">
                    <a href="groups/<?=$group_data->getPKValue() ?>">
                        <img class="framed float_left"  width="80px" height='80px' alt="group" src="<?= ((strlen($group_data->Picture) > 0) ? "groups/thumbimg/{$group_data->getPKValue()}" : 'images/icons/group.png' ) ?>"/>
                    </a>
                    <div class="groupinfo">
                        <h4><a href="groups/<?=$group_data->getPKValue() ?>"><?=$group_data->Name ?></a></h4>
                        <ul>
                            <li><?= $words->get('GroupsMemberCount');?>: <?=$group_data->getMemberCount(); ?></li>
                            <li><?= $words->get('GroupsDateCreation');?>: <?=$group_data->created; ?></li>
                            <li><?= $words->get('GroupsNewForumPosts');?>: <?=$group_data->getNewForumPosts; ?></li>
                        </ul>
                    </div> <!-- groupinfo -->
                </div> <!-- groupbox clearfix -->
                </div>
            <?php endforeach ; ?>
            </div>
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
