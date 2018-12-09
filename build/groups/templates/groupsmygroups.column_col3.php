
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

                include('groupsdisplay.php');

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
