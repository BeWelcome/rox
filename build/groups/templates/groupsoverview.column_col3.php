<div id="groups">
    <div class="subcolumns">
        <div class="c62l">
            <div class="subcl">
                <div class="row">
                    <h3><?= $words->get('GroupsSearchHeadline'); ?></h3>
                    <p><?= $words->get('GroupsSearchDescription'); ?></p>
                    <form action="groups/search" method="get">
                    <input type="text" name="GroupsSearchInput" value="" id="GroupsSearchInput" /><input type="submit" value="<?= $words->get('Search'); ?>" />
                    </form>
                </div>             
            </div> <!-- subcl -->
        </div> <!-- c62l -->
        <div class="c38r">
            <div class="subcr">
                <div class="row">
                    <p><?= $words->get('GroupsCreateDescription'); ?></p>
                    <a class="button" href="groups/new"><span><?= $words->get('GroupsCreateNew'); ?></span></a>
                </div>
            </div> <!-- subcr -->
        </div> <!-- c38r -->
    </div> <!-- subcolums -->
            

    <div class="subcolumns">
        <div class="c62l">
            <div class="subcl">
            
                <h3><?= $words->get('GroupsList'); ?></h3>
                <?php
                foreach($this->featured_groups as $group_data) : ?>
                    <div class="groupinfo">
                        <img class="framed float_left"  width="60px" alt="group" src="<?= ((strlen($group_data->Picture) > 0) ? "groups/thumbimg/{$group_data->getPKValue()}" : 'images/icons/group.png' ) ?>"/>
                        <h4><a href="groups/<?=$group_data->id ?>"><?=$group_data->Name ?></a></h4>
                        <ul>
                            <li><?= $words->get('GroupsMemberCount');?>: <?=$group_data->getMemberCount(); ?></li>
                            <li><?= $words->get('GroupsNewMembers');?>: <?=count($group_data->getNewMembers()) ; ?></li>
                            <li><?= $words->get('GroupsNewForumPosts');?>: <?=$group_data->getNewForumPosts; ?></li>
                        </ul>
                        <p><?= $group_data->getDescription(); ?></p>
                    </div> <!-- floatbox -->
                <?php endforeach ; ?>

                <p><strong><a href="groups/featured"><?= $words->get('GroupsAllFeaturedLink'); ?></a></strong></p>
                
<!--  this should be displayed when groups categories are ready
                <h3><?= $words->get('GroupsBrowseByCategory'); ?></h3>
                <table>
                    <tr>
                        <th>Groupname</th>
                        <th>Members</th>
                        <th>Posts</th>
                    </tr>
                    <tr>
                        <td><a href="places">Places</a></td>
                        <td>1000</td>
                        <td>333</td>
                    </tr>
                    <tr>
                        <td><a href="#">Common Interests</a></td>
                        <td>1000</td>
                        <td>333</td>
                    </tr>
                    <tr>
                        <td><a href="#">Travelling</a></td>
                        <td>1000</td>
                        <td>333</td>
                    </tr>
                    <tr>
                        <td><a href="#">Organizations</a></td>
                        <td>1000</td>
                        <td>333</td>
                    </tr>
                    <tr>
                        <td><a href="#">Sports and Recreation</a></td>
                        <td>1000</td>
                        <td>333</td>
                    </tr>
                    <tr>
                        <td><a href="#">Bewelcome &amp; BeVolunteer</a></td>
                        <td>1000</td>
                        <td>333</td>
                    </tr>                    
                </table>
-->
            </div> <!-- subcl -->
        </div> <!-- c62l -->
        
        <div class="c38r">
            <div class="subcr">
                <?php // display my groups, if there are any
                $my_groups = $this->my_groups;
                if (!empty($my_groups)) :
                    echo "<h3>{$words->get('GroupsMyGroups')}</h3>";
                    for($i = 0; $i < count($my_groups) && $i < 2; $i++) : ?>
                        <div class="groupinfo">
                            <img class="framed float_left"  width="60px" alt="Group" src="<?= ((strlen($my_groups[$i]->Picture) > 0) ? "groups/thumbimg/{$my_groups[$i]->getPKValue()}" : 'images/icons/group.png' ) ;?>"/>
                            <h4><a href="groups/<?= $my_groups[$i]->id ?>"><?= $my_groups[$i]->Name ?></a></h4>
                            <ul>
                                <li><?= $words->get('GroupsMemberCount');?>: <?=$my_groups[$i]->getMemberCount(); ?> </li>
                                <li><?= $words->get('GroupsNewMembers');?>: <?=count($my_groups[$i]->getNewMembers()); ?> </li>
                                <li><?= $words->get('GroupsNewForumPosts');?>: <?=$my_groups[$i]->getNewForumPosts; ?></li>
                            </ul>
                            <p><?= $my_groups[$i]->getDescription(); ?></p>
                        </div>
                    <?php endfor; ?>
                    <p><strong><a href="groups/mygroups"><?= $words->get('GroupsAllMyLink'); ?></a></strong></p>
                <?php endif ; ?>
                
            </div> <!-- subcr -->
        </div> <!-- c38r -->
    </div> <!-- subcolumns -->
</div> <!-- groups -->
