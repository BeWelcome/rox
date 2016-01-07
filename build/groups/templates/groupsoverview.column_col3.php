<div id="groups">
    <div class="subcolumns m-b-1">
        <div class="c62l">
            <div class="subcl">
                    <h3><?= $words->get('GroupsSearchHeading'); ?></h3>
                    <p><?= $words->get('GroupsSearchDescription'); ?></p>
                    <form action="groups/search" method="get">
                    <input type="text" name="GroupsSearchInput" value="" id="GroupsSearchInput" /><input type="submit" class="button" value="<?= $words->get('Search'); ?>" />
                    </form>
            </div> <!-- subcl -->
        </div> <!-- c62l -->
        <div class="c38r">
            <div class="subcr">
                    <h3><?= $words->get('GroupsCreateHeading'); ?></h3>
                    <p><?= $words->get('GroupsCreateDescription'); ?></p>
                    <a class="button" role="button" href="groups/new"><span><?= $words->get('GroupsCreateNew'); ?></span></a>
            </div> <!-- subcr -->
        </div> <!-- c38r -->
    </div> <!-- subcolums -->


    <div class="subcolumns">
        <div class="c62l">
            <div class="subcl">

                <h3><?= $words->get('GroupsList'); ?></h3>
                <div class="subcolumns">
                <?php
                $count=0;
                $featured_groups = (($this->featured_groups) ? $this->featured_groups : array());
                foreach($featured_groups as $group_data) :
                    if ($group_data->Type == 'NeedInvitation' && !$this->model->getLoggedInMember()) continue;
                    if ($count % 2 == 0) { ?>
                    <div class="c50l groupbox clearfix">
                    <?php } else { ?>
                    <div class="c50r groupbox clearfix">
                    <?php } ?>
                        <a href="groups/<?=$group_data->id ?>">
                            <img class="framed float_left"  width="80px" height="80px" alt="group" src="<?= ((strlen($group_data->Picture) > 0) ? "groups/thumbimg/{$group_data->getPKValue()}" : 'images/icons/group.png' ) ?>"/>
                        </a>
                        <div class="groupinfo">
                            <h4><a href="groups/<?=$group_data->id ?>"><?= htmlspecialchars($group_data->Name, ENT_QUOTES) ?></a></h4>
                            <ul>
                                <li><?= $words->get('GroupsMemberCount');?>: <?=$group_data->getMemberCount(); ?></li>
                                <li><?= $words->get('GroupsNewMembers');?>: <?=count($group_data->getNewMembers()) ; ?></li>
                            <?php if ($group_data !== 0) {?>
                            <li><?php
                                if ($group_data->latestPost) {
                                    echo $words->get('GroupsLastPost') . ": " . date($words->getBuffered('DateHHMMShortFormat'), ServerToLocalDateTime($group_data->latestPost));
                                } else {
                                    echo $words->get('GroupsNoPostYet');
                                }
                            } ?>
                            </ul>
                        </div>  <!-- groupinfo -->
                    </div> <!-- groupbox clearfix -->
                <?php $count++;
                    endforeach ; ?>
                </div>
                <p><strong><a href="groups/featured"><?= $words->get('GroupsAllFeaturedLink'); ?></a></strong></p>
            </div> <!-- subcl -->
        </div> <!-- c62l -->

        <div class="c38r">
            <div class="subcr">
                <?php // display my groups, if there are any
                $my_groups = $this->my_groups;
                if (!empty($my_groups)) :
                    echo "<h3>{$words->get('GroupsMyGroups')}</h3>";
                    for($i = 0; $i < count($my_groups) && $i < 5; $i++) : ?>
                        <div class="groupbox clearfix">
                            <a href="groups/<?=$my_groups[$i]->id ?>">
                                <img class="framed float_left"  width="80px" height="80px" alt="Group" src="<?= ((strlen($my_groups[$i]->Picture) > 0) ? "groups/thumbimg/{$my_groups[$i]->getPKValue()}" : 'images/icons/group.png' ) ;?>"/>
                            </a>
                            <div class="groupinfo">
                            <h4><a href="groups/<?= $my_groups[$i]->id ?>"><?= htmlspecialchars($my_groups[$i]->Name, ENT_QUOTES) ?></a></h4>
                            <ul>
                                <li><?= $words->get('GroupsMemberCount');?>: <?=$my_groups[$i]->getMemberCount(); ?> </li>
                                <li><?= $words->get('GroupsNewMembers');?>: <?=count($my_groups[$i]->getNewMembers()); ?> </li>
                                <?php if ($group_data !== 0) {?>
                                <li><?= $words->get('GroupsLastPost');?>: <?=date($words->getBuffered('DateHHMMShortFormat'), ServerToLocalDateTime($my_groups[$i]->latestPost)); ?></li>
                                <?php } ?>
                            </ul>
                            </div>  <!-- groupinfo -->
                        </div> <!-- groupbox clearfix -->
                    <?php endfor; ?>
                    <p><strong><a href="groups/mygroups"><?= $words->get('GroupsAllMyLink'); ?></a></strong></p>
                <?php endif ; ?>

            </div> <!-- subcr -->
        </div> <!-- c38r -->
    </div> <!-- subcolumns -->
</div> <!-- groups -->
