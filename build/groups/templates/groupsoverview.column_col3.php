<div id='groups'>
    <div class="subcolumns">
        <div class="c62l">
            <div class="subcl">
                <h3>Search for Groups</h3>
                <p>Here you may search for existing groups and choose some to your liking...</p>
                <form action="groups/search" method="GET">
                <input type="text" name="GroupsSearchInput" value="" id="GroupsSearchInput" /><input type="submit" value="<?= $words->get('Search'); ?>" />
                </form>                
            </div>
        </div>
        <div class="c38r"></div>
            <div class="subcr">
                <h3>Create a new Group</h3>
                <p>Can't find the group you need? Why don't you start one?</p>
                <a class="bigbutton" href="groups/new"><span><?= $words->get('GroupsCreateNew'); ?></span></a>
              
            </div>
        </div>
    </div>
            

    <div class="subcolumns">
        <div class="c62l">
            <div class="subcl">
                <?php // display my groups, if there are any
                $my_groups = $this->my_groups;
                if (!empty($my_groups)) :
                    echo "<h3>{$words->get('GroupsMyGroups')}</h3>";
                    for($i = 0; $i < count($my_groups) && $i < 2; $i++) : ?>
                        <div class="floatbox">
                            <img class="framed float_left" height="50" width="50" alt="Group" src="<?= ((strlen($my_groups[$i]->picture) > 0) ? $my_groups[$i]->picture : 'images/icons/group.png' ) ;?>"/>
                            <h5><a href="groups/<?= $my_groups[$i]->id ?>"><?= $my_groups[$i]->Name ?></a></h5>
                            <h6 class="small"><?= $words->get('GroupsMemberCount', $my_groups[$i]->getMemberCount); ?> | <?= count($my_groups[$i]->getNewGroupMembers) ; ?> | <?= $words->get('GroupsNewForumPosts', $my_groups[$i]->getNewForumPosts); ?></h6>
                            <p><?= $my_groups[$i]->getDescription(); ?></p>
                        </div>
                    <?php endfor; ?>
                    <p>
                        <strong><a href="groups/mygroups">See all of my groups</a></strong>
                    </p>
                <?php endif ; ?>

                <h3><?= $words->get('GroupsBrowseByCategory'); ?></h3>
                <ul></ul>
            </div>
        </div>

        <div class="c38r">
            <div class="subcr">
                <h3><?= $words->get('GroupsList'); ?></h3>
                <?php
                foreach($this->featured_groups as $group_data) : ?>
                    <div class="floatbox">
                        <img class="framed float_left" height="50" width="50" alt="group" src="<?= ((strlen($group_data->picture) > 0) ? $group_data->picture : 'images/icons/group.png' ) ?>"/>
                        <h5>
                            <a href="groups/<?=$group_data->id ?>"><?=$group_data->Name ?></a>
                        </h5>
                        <h6 class="small"><?= $words->get('GroupsMemberCount', $group_data->getMemberCount); ?></h6>
                        <p><?= $group_data->getDescription(); ?></p>
                    </div>
                <?php endforeach ; ?>

                <p>
                    <strong><a href="groups/featured">See all featured groups</a></strong>
                </p>

            </div>
        </div>
        <div id="ie_clearing">&nbsp;</div>
    </div>

