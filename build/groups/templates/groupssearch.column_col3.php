        <h3><?= $words->get('GroupsSearchHeading'); ?></h3>
        <form action="groups/search" method="GET">
        <input type="text" name="GroupsSearchInput" value="" id="GroupsSearchInput" /><input type="submit" value="<?= $words->get('GroupsSearchSubmit'); ?>" /><br />
        </form>

        <h3><?= $words->get('GroupsSearchResult'); ?></h3>
        <?php
        if ($this->search_result)
        {
            $name_order = (($this->result_order == "nameasc") ? 'namedesc' : 'nameasc');
            $member_order = (($this->result_order == "membersasc") ? 'membersdesc' : 'membersasc');
            $created_order = (($this->result_order == "createdasc") ? 'createddesc' : 'createdasc');
            $category_order = (($this->result_order == "categoryasc") ? 'categorydesc' : 'categoryasc');
            echo <<<HTML
            <h4>Order by:</h4>
            <a class="grey" href="groups/search?GroupsSearchInput={$this->search_terms}&amp;Order={$name_order}&Page={$this->result_page}">Group name</a>
            |
            <a class="grey" href="groups/search?GroupsSearchInput={$this->search_terms}&amp;Order={$member_order}&Page={$this->result_page}">Number of Members</a>
            |
            <a class="grey" href="groups/search?GroupsSearchInput={$this->search_terms}&amp;Order={$created_order}&Page={$this->result_page}">Date of creation</a>
            |
            <a class="grey" href="groups/search?GroupsSearchInput={$this->search_terms}&amp;Order={$category_order}&Page={$this->result_page}">Category</a>
            
HTML;
            foreach ($this->search_result as $group_data) : ?>
                <div class="floatbox">
                    <img class="framed float_left" height="50" width="50" alt="group" src="<?= ((strlen($group_data->picture) > 0) ? $group_data->picture : 'images/icons/group.png' ) ?>"/>
                    <h5>
                        <a href="groups/<?=$group_data->id ?>"><?=$group_data->Name ?></a>
                    </h5>
                    <h6 class="small"><?= $words->get('GroupsMemberCount', $group_data->getMemberCount); ?></h6>
                    <p><?= $group_data->getDescription(); ?></p>
                </div>
            <?php endforeach ; 
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
