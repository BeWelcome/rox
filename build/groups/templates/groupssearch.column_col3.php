<div class="row">
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
            <label class="h3 w-100"><?= $words->get('GroupsCreateHeading'); ?></label>
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
                            <p><<?php
                            $str = $words->get('GroupsCreateDescription');
                            $str = str_replace('%linkstart%', '<a href="/forums/rules#groups" target="_blank">', $str);
                            $str = str_replace('%linkend%', '</a>', $str);
                            echo $str;
                            ?></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <a class="btn btn-primary" role="button" href="new/group"><?= $words->get('GroupsCreateNew'); ?></a>
                        </div>
                    </div>
                </div>
            </div>

        </div>


        <?php
        $search_result = $this->search_result;

        if ($search_result) :
        $act_order = (($this->result_order == "actdesc") ? 'actasc' : 'actdesc');
        $name_order = (($this->result_order == "nameasc") ? 'namedesc' : 'nameasc');
        $member_order = (($this->result_order == "membersdesc") ? 'membersasc' : 'membersdesc');
        $created_order = (($this->result_order == "createdasc") ? 'createddesc' : 'createdasc');
        $category_order = (($this->result_order == "categoryasc") ? 'categorydesc' : 'categoryasc');
        ?>
        <div class="col-12 col-md-6 mt-3">
            <h3><?= $words->get('GroupsSearchResult'); ?></h3>
            <label class="font-weight-bold"><?php echo $words->get('GroupsSearchOrdered'); ?>:</label>
            <span class="dropdown">
                <button class="btn btn-sm btn-secondary " type="button" id="dropdownVisibility" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo $words->get('GroupsSearchOrdered' . $this->result_order) ?></button>
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
        <?php
         $this->pager->render();
         ?>


<div class="col-12">
    <div class="row">
<?
        foreach ($search_result as $group_data) :

            include('groupsdisplay.php');

        endforeach;
        ?>
    </div>
</div>
      <div class="mt-3">
<?php
$this->pager->render();
?>
      </div>
<?php else :
    echo <<<HTML
            <p class="note">
            {$words->get('GroupSearchNoResults')}
            </p>
HTML;
endif;
?>
</div>
