<?php
if (!$this->canMemberAccess())
{
    echo '<div class="row"><div class="col-12">' . $words->get("GroupsNotPublic") . '</div></div>';
}
else
{
?>
<div class="row d-flex justify-content-between">
    <div class="col">
        <h3><?= $words->get('GroupsMembers'); ?></h3>
    </div>

    <div class="col">
        <?php $this->pager_widget->render(); ?>
    </div>
</div>
<div class="row no-gutters">
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="blank">
            <tr>
                <th colspan="2" class="col-3">
                    <?= $words->get('Username'); ?>
                </th>
                <th class="col-9">
                    <?= $words->get('GroupsMemberComment'); ?>
                </th>
            </tr>
            </thead>
            <tbody>

            <?php
            $purifier = (new MOD_htmlpure())->getBasicHtmlPurifier();
            $count = 0;
            foreach ($this->pager_widget->getActiveSubset($this->group->getMembers('In', $this->pager_widget->getActiveStart(), $this->pager_widget->getActiveLength())) as $member){
            $membershipinfo = $member->getGroupMembership($this->group);
            ?>
            <tr>
                <td>
                    <?= MOD_layoutbits::PIC_50_50($member->Username) ?>
                </td>
                <td>
                    <a href="members/<?= $member->Username ?>" class="username"><?= $member->Username ?></a>
                    <p class="small m-0"><?= $words->get('Age') ?>: <?= $member->age; ?> </p>
                    <p class="small m-0"><?= $member->get_city(); ?>, <?= $member->get_country(); ?></p>
                </td>
                <td>
                    <em><?php echo $purifier->purify($words->mTrad($membershipinfo->Comment, true)) ?></em>
                </td>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>
    <div class="col-12">
        <?php
        $this->pager_widget->render();
        ?>
    </div>
</div>
<?php
}
