
    <div class="row mt-2">

        <div class="col-12 col-md-6">
            <label class="h3" for="GroupsSearchInput"><?= $words->get('GroupsSearchHeading'); ?></label>
            <form action="groups/search" method="get">
                <div class="input-group">
                    <input type="text" class="form-control" name="GroupsSearchInput" value="" id="GroupsSearchInput"/>
                    <span class="input-group-append">
                            <button class="btn btn-primary"
                                    type="submit"><?= $words->getSilent('GroupsSearchSubmit'); ?></button>
                        <?= $words->flushBuffer() ?>
                        </span>
                </div>
                <div class="small mt-1"><?= $words->get('GroupsSearchInfo'); ?></div>
            </form>
        </div>
        <div class="col-12 col-md-6">
            <label class="h3" for="GroupsSearchInput"><?= $words->get('GroupsCreateHeading'); ?></label>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#CreateGroupModal">
                <?= $words->get('GroupsCreateNew'); ?>
            </button>

            <div class="modal fade" id="CreateGroupModal" tabindex="-1" role="dialog" aria-labelledby="CreateGroupModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="CreateGroupModalLabel">Create a new group</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p><?= $words->get('GroupsCreateDescription'); ?></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <a class="btn btn-primary" role="button" href="groups/new"><?= $words->get('GroupsCreateNew'); ?></a>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>

    <div class="row mt-2 mb-3 w-100">
        <div class="col col-auto col-lg-7 order-12 mt-3">
        <?php
        $search_result = $this->search_result;

        $this->pager->render();

        if ($search_result) :
        $act_order = (($this->result_order == "actdesc") ? 'actasc' : 'actdesc');
        $name_order = (($this->result_order == "nameasc") ? 'namedesc' : 'nameasc');
        $member_order = (($this->result_order == "membersdesc") ? 'membersasc' : 'membersdesc');
        $created_order = (($this->result_order == "createdasc") ? 'createddesc' : 'createdasc');
        $category_order = (($this->result_order == "categoryasc") ? 'categorydesc' : 'categoryasc');
        ?>
        </div>
        <div class="col col-lg-5">
            <h3><?= $words->get('GroupsSearchResult'); ?></h3>
            <label class="font-weight-bold"><?php echo $words->get('GroupsSearchOrdered'); ?>:</label>
            <span class="dropdown">
                <button class="btn btn-sm btn-info dropdown-toggle" type="button" id="dropdownVisibility" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo $words->get('GroupsSearchOrdered' . $this->result_order) ?></button>
                <div class="dropdown-menu ddextras px-0 py-1" aria-labelledby="dropdownVisibility">
                    <a class="btn btn-sm btn-light mx-1"
                       href="groups/search?GroupsSearchInput=<?= $this->search_terms; ?>&order=<?= $act_order; ?>&<?= $this->pager->getActivePageMarker(); ?>"><?= $words->get('GroupsOrderBy' . $act_order); ?></a>
                    <a class="btn btn-sm btn-light mx-1"
                       href="groups/search?GroupsSearchInput=<?= $this->search_terms; ?>&order=<?= $name_order; ?>&<?= $this->pager->getActivePageMarker(); ?>"><?= $words->get('GroupsOrderBy' . $name_order); ?></a>
                    <a class="btn btn-sm btn-light mx-1"
                       href="groups/search?GroupsSearchInput=<?= $this->search_terms; ?>&order=<?= $member_order; ?>&<?= $this->pager->getActivePageMarker(); ?>"><?= $words->get('GroupsOrderBy' . $member_order); ?></a>
                    <a class="btn btn-sm btn-light mx-1"
                       href="groups/search?GroupsSearchInput=<?= $this->search_terms; ?>&order=<?= $created_order; ?>&<?= $this->pager->getActivePageMarker(); ?>"><?= $words->get('GroupsOrderDate' . $created_order); ?></a>
                </div>
            </span>
        </div>
    </div>
    <div class="row px-2">

<?
        foreach ($search_result as $group_data) :

            ?>
            <div class="col-12 col-md-6 col-lg-4 p-2">
                <div class="float-left h-100 mr-2" style="width: 80px;">
                    <!-- group image -->
                    <a href="groups/<?= $group_data->getPKValue() ?>">
                        <img class="groupimg framed" alt="<?= htmlspecialchars($group_data->Name, ENT_QUOTES) ?>"
                             src="<?= ((strlen($group_data->Picture) > 0) ? "groups/thumbimg/{$group_data->getPKValue()}" : 'images/icons/group.png') ?>">
                    </a>
                </div>
                <div>
                    <!-- group name -->
                    <h4>
                        <a href="groups/<?= $group_data->getPKValue() ?>"><?= htmlspecialchars($group_data->Name, ENT_QUOTES) ?></a>
                    </h4>
                    <!-- group details -->
                    <ul class="groupul mt-1">
                        <li><i class="fa fa-group"
                               title="Number of group members"></i> <?= $group_data->getMemberCount(); ?></li>
                        <li><?= $words->get('GroupsDateCreation'); ?>
                            : <?= date('d F Y', ServerToLocalDateTime(strtotime($group_data->created), $this->getSession())); ?></li>
                        <?php if ($group_data !== 0) { ?>
                            <li><?php
                                if ($group_data->latestPost) {
                                    $interval = date_diff(date_create(date('d F Y')), date_create(date('d F Y', ServerToLocalDateTime($group_data->latestPost, $this->getSession()))));
                                    echo $words->get('GroupsLastPost') . ": " . $interval->format('%a') . " " . $words->get('days_ago');

                                } else {
                                    echo $words->get('GroupsNoPostYet');
                                }

                                ?></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>

            <?php
        endforeach;
        ?>
    </div><!-- end row -->
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
