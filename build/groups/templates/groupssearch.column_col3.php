<div class="subcolumns">
        <h3><?= $words->get('GroupsSearchHeading'); ?></h3>
        <form action="groups/search" method="get">
            <input type="text" name="GroupsSearchInput" value="" id="GroupsSearchInput" /><input type="submit" value="<?= $words->get('GroupsSearchSubmit'); ?>" /><br />
        </form>
    
        <h3><?= $words->get('GroupsSearchResult'); ?></h3>
        <?php
        $search_result = $this->search_result;
        if ($search_result)
        {
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
  ?>
  <div class="c50l">
    <div class="subcl">

<div>
<?php
            for ($i = 0; $i < 10 && $i < count($search_result); $i++) :
                $group_data = $search_result[$i];
                ?>
                <div class="groupbox clearfix">
                    <a href="groups/<?=$group_data->id ?>">
                        <img class="framed float_left"  width="80px" height='80px' alt="group" src="<?= ((strlen($group_data->Picture) > 0) ? "groups/thumbimg/{$group_data->getPKValue()}" : 'images/icons/group.png' ) ?>"/>
                    </a>
                    <div class="groupinfo">
                        <h4><a href="groups/<?=$group_data->id ?>"><?=$group_data->Name ?></a></h4>
                        <ul>
                            <li><?= $words->get('GroupsMemberCount');?>: <?=$group_data->getMemberCount(); ?></li>
                            <li><?= $words->get('GroupsDateCreation');?>: <?=$group_data->created; ?></li>
                            <li><?= $words->get('GroupsNewForumPosts');?>: <?=$group_data->getNewForumPosts; ?></li>
                        </ul>
                    </div> <!-- groupinfo -->
                </div> <!-- groupbox clearfix -->
            <?php endfor ; 
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
    <div>
        <?php
            if ($search_result) :
            for ($i = 10; $i < 20 && $i < count($search_result); $i++) :
                $group_data = $search_result[$i];
                ?>
                <div class="groupbox clearfix">
                    <a href="groups/<?=$group_data->id ?>">
                        <img class="framed float_left"  width="80px" height='80px' alt="group" src="<?= ((strlen($group_data->Picture) > 0) ? "groups/thumbimg/{$group_data->getPKValue()}" : 'images/icons/group.png' ) ?>"/>
                    </a>
                    <div class="groupinfo">
                        <h4><a href="groups/<?=$group_data->id ?>"><?=$group_data->Name ?></a></h4>
                        <ul>
                            <li><?= $words->get('GroupsMemberCount');?>: <?=$group_data->getMemberCount(); ?></li>
                            <li><?= $words->get('GroupsDateCreation');?>: <?=$group_data->created; ?></li>
                            <li><?= $words->get('GroupsNewForumPosts');?>: <?=$group_data->getNewForumPosts; ?></li>
                        </ul>
                    </div> <!-- groupinfo -->
                </div> <!-- groupbox clearfix -->
            <?php endfor ;
            endif;
            ?>
        </div>
    </div>
</div>
</div>
