<div class="row">

    <?php
    // display my groups, if there are any
    if (!empty($my_groups)) :
        $purifier = MOD_htmlpure::getAdvancedHtmlPurifier();
        echo "<div class='col-12'><h3>{$words->getInLang('ProfileGroups', $profile_language_code)}</h3></div>";
            ?>

        <div class="col-12"><?php
            $this->pager->render();
            ?>
        </div><?php
            foreach ($this->pager->getActiveSubset($my_groups) as $group)  : ?>
                <div class="col-12 col-md-6 col-lg-4 pt-2">
                    <div class="media">
                        <a href="group/<?= $group->id ?>">
                            <img class="groupimg framed mr-1" alt="<?= htmlspecialchars($group->Name, ENT_QUOTES) ?>"
                                 src="<?= ((strlen($group->Picture) > 0) ? "group/thumbimg/{$group->getPKValue()}" : 'images/icons/group.png' ) ;?>">
                        </a>
                        <div class="media-body">
                            <h5><a href="group/<?= $group->id ?>"><?= htmlspecialchars($group->Name, ENT_QUOTES) ?></a></h5>
                            <?= $purifier->purify($words->mTrad($member->getGroupMembership($group)->Comment));?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <div class="col-12"><?php
            $this->pager->render(); ?>
        </div>
    <?php endif; ?>
</div> <!-- profile -->
