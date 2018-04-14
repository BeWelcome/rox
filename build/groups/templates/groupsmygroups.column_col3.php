
<div class="col-12 col-md-6">
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
<div class="col-12 col-md-6">
    <h3><?= $words->get('GroupsCreateHeading'); ?></h3>
    <a class="btn btn-primary" role="button" href="groups/new"><?= $words->get('GroupsCreateNew'); ?></a>
</div>

    <div class="col-12 mt-3">
        <div class="row">

        <?php
        $search_result = $this->search_result;
        if ($search_result)
        {
        $mygroups = $this->pager->getActiveSubset($search_result);
        $this->pager->render();
            foreach ($mygroups as $group_data) :
               ?>

        <div class="col-12 col-md-6 col-lg-4">

            <div class="w-100"><h4 class="mb-0"><a href="groups/<?= $group_data->getPKValue() ?>"><?= htmlspecialchars($group_data->Name, ENT_QUOTES); ?></a></h4></div>

            <div class="float-left h-100 mr-2" style="width: 80px;">
                <!-- group image -->
                <a href="groups/<?=$group_data->getPKValue() ?>">
                    <img class="framed" width="80px" height="80px" alt="Group" src="<?= ((strlen($group_data->Picture) > 0) ? "groups/thumbimg/{$group_data->getPKValue()}" : 'images/icons/group.png' ) ;?>"/>
                </a>
            </div>
            <div>
                <ul class="groupul">
                    <li><i class="fa fa-group pr-1" title="Number of group members"></i><?=$group_data->getMemberCount(); ?></li>
                    <li><i class="fa fa-user-plus pr-1" title="<?= $words->get('GroupsNewMembers');?>"></i><?=count($group_data->getNewMembers()) ; ?></li>
                    <?php if ($group_data !== 0) {?>
                        <li><i class="fa fa-comments-o pr-1" title="<?= $words->get('GroupsLastPost');?>"></i><span class="text-nowrap"><?=date($words->getBuffered('d F Y'), ServerToLocalDateTime($group_data->latestPost, $this->getSession())); ?></span></li>
                    <?php } ?>
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
        </div>
    </div>
