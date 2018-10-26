
<div class="col-12 col-md-6 mb-3">
    <h3><?= $words->get('GroupsSearchHeading'); ?></h3>
    <form action="groups/search" method="get">
        <div class="input-group">
            <input type="text" class="form-control" name="GroupsSearchInput" value="" id="GroupsSearchInput" />
            <span class="input-group-append">
                            <button class="btn btn-primary" type="submit"><?= $words->getSilent('GroupsSearchSubmit'); ?></button>
                <?=$words->flushBuffer()?>
                        </span>
        </div>
    </form>
</div>
<div class="col-12 col-md-6 mb-3">
    <h3><?= $words->get('GroupsCreateHeading'); ?></h3>
    <a class="btn btn-primary" role="button" href="groups/new"><?= $words->get('GroupsCreateNew'); ?></a>
</div>

        <?php
        $search_result = $this->search_result;
        if ($search_result)
        {
        $mygroups = $this->pager->getActiveSubset($search_result);
        $this->pager->render();

            foreach ($mygroups as $group_data) :
               ?>

                <div class="col-12 col-md-6 col-lg-4">
                    <div class="float-left h-100 mr-2" style="width: 80px;">
                        <!-- group image -->
                        <a href="groups/<?= $group_data->getPKValue() ?>">
                            <img class="groupimg framed" alt="Group" src="<?= ((strlen($group_data->Picture) > 0) ? "groups/thumbimg/{$group_data->getPKValue()}" : 'images/icons/group.png' ) ;?>">
                        </a>
                    </div>
                    <div>
                        <!-- group name -->
                        <h5>
                            <a href="groups/<?= $group_data->getPKValue() ?>"><?= htmlspecialchars($group_data->Name, ENT_QUOTES); ?></a>
                        </h5>
                        <!-- group details -->
                        <ul class="groupul mt-1">
                            <li><i class="fa fa-users mr-1"
                                   title="<? echo $words->get('GroupsMemberCount'); ?>"></i><?= $group_data->getMemberCount(); ?></li>
                            <li><i class="fa fa-user-plus mr-1" title="<? echo $words->get('GroupsNewMembers'); ?>"></i><?php echo count($group_data->getNewMembers()); ?></li>
                            <li><?php
                                if ($group_data->latestPost) {
                                    $interval = date_diff(date_create(date('d F Y')), date_create(date('d F Y', ServerToLocalDateTime($group_data->latestPost, $this->getSession()))));
                                    ?>
                                <i class="fa fa-history mr-1" title="<? echo $words->get('GroupsLastPost'); ?>"></i><span class="text-nowrap"><?=date($words->getBuffered('d F Y'), ServerToLocalDateTime($group_data->latestPost, $this->getSession())); ?></span>
                                    <?
                                } else {
                                    echo $words->get('GroupsNoPostYet');
                                }
                                ?></li>
                        </ul>
                    </div>
                </div>
        <?php
            endforeach ;
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
