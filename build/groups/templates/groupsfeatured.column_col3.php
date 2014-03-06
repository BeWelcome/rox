<div id="groups">
        <h3><? echo $this->words->get('GroupsFeaturedGroups'); ?></h3>
        <form action="groups/featured" method="get">
            <input type="hidden" name="GroupsFeaturedInput" value="" id="GroupsFeaturedInput" />
        </form>
        <?php
        $search_result = $this->search_result;
        if ($search_result) :
            $name_order = (($this->result_order == "nameasc") ? 'namedesc' : 'nameasc');
            $member_order = (($this->result_order == "membersdesc") ? 'membersasc' : 'membersdesc');
            $created_order = (($this->result_order == "createdasc") ? 'createddesc' : 'createdasc');
            $category_order = (($this->result_order == "categoryasc") ? 'categorydesc' : 'categoryasc');
            ?>
            <h4><?= $words->get('GroupsSearchOrder');?></h4>
            <a class="grey" href="groups/featured?GroupsFeaturedInput=<?=$this->search_terms;?>&amp;order=<?=$name_order;?>&amp;<?=$this->pager->getActivePageMarker();?>"><?= $words->get('GroupsOrderName'); ?></a>
            |
            <a class="grey" href="groups/featured?GroupsFeaturedInput=<?=$this->search_terms;?>&amp;order=<?=$member_order;?>&amp;<?=$this->pager->getActivePageMarker();?>"><?= $words->get('GroupsOrderMembers'); ?></a>
            |
            <a class="grey" href="groups/featured?GroupsFeaturedInput=<?=$this->search_terms;?>&amp;order=<?=$created_order;?>&amp;<?=$this->pager->getActivePageMarker();?>"><?= $words->get('GroupsOrderDate'); ?></a>
<?
// Categories link disabled until we have categories
//            |
//            <a class="grey" href="groups/search?GroupsSearchInput={$this->search_terms}&amp;Order={$category_order}&Page={$this->result_page}">Category</a>
            $this->pager->render();
            echo <<<HTML
<div class="clearfix">
HTML;
            foreach ($search_result as $group_data) : ?>
                <div class="groupbox float_left">
                    <a href="groups/<?=$group_data->getPKValue() ?>">
                        <img class="framed float_left"  width="80px" height="80px" alt="group" src="<?= ((strlen($group_data->Picture) > 0) ? "groups/thumbimg/{$group_data->getPKValue()}" : 'images/icons/group.png' ) ?>"/>
                    </a>
                    <div class="groupinfo">
                        <h4><a href="groups/<?=$group_data->getPKValue() ?>"><?=$group_data->Name ?></a></h4>
                        <ul>
                            <li><?= $words->get('GroupsMemberCount');?>: <?=$group_data->getMemberCount(); ?></li>
                            <li><?= $words->get('GroupsDateCreation');?>: <?=$group_data->created; ?></li>
                            <?php if ($group_data !== 0) {?>
                            <li><?php
                                if ($group_data->latestPost) {
                                    echo $words->get('GroupsLastPost') . ": " . date($words->getBuffered('DateHHMMShortFormat'), ServerToLocalDateTime($group_data->latestPost));
                                } else {
                                    echo $words->get('GroupsNoPostYet');
                                }
                            } ?>
                        </ul>
                    </div> <!-- groupinfo -->
                </div> <!-- groupbox -->
            <?php endforeach ;?>
</div> <!-- clearfix -->
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
</div> <!-- groups -->

