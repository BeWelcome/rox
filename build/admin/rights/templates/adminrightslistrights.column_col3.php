<?php
/**
 * Created by PhpStorm.
 * User: raymund
 * Date: 08.04.14
 * Time: 20:48
 */
$vars = $this->getRedirectedMem('vars');
if ($vars) {
    // overwrite the vars
    $this->vars = $vars;
    $this->rightsWithMembers = $this->getRedirectedMem('rightsWithMembers');
}

$callbackTags = $this->layoutkit->formkit->setPostCallback('AdminRightsController', 'listRightsCallback');
$layoutbits = new MOD_layoutbits();
?>
<div class="w-100 row">
    <form class="yform" method="post">
        <?= $callbackTags ?>
            <label for="rightid" class="mb-0"><?= $words->get("AdminRightsRight") ?></label>
            <?= $this->rightsSelect($this->rights, $this->vars['rightid']) ?>

            <input type="checkbox" id="history" name="history" value="1" <?= (isset($this->vars['history'])) ? 'checked="checked' : '' ?> />
            <label for="history"><?= $words->get("AdminRightsHistory") ?></label>

            <input type="submit" id="submit" name="submit"
                   value="<?= $words->getSilent("AdminRightsListRightsSubmit") ?>"/><?php echo $words->flushBuffer(); ?>

    </form>
</div>
<div class="w-100 p-0">
    <table id="rights" class="table table-striped table-hover">
        <tr>
            <th><?= $words->get('AdminRightsRight') ?></th>
            <th><?= $words->get('AdminRightsUsername') ?></th>
            <th><?= $words->get('AdminRightsLevel') ?></th>
            <th><?= $words->get('AdminRightsScope') ?></th>
            <th colspan="3"><?= $words->get('AdminRightsComment') ?></th>
        </tr>
<?php
    foreach($this->rightsWithMembers as $rightId => $details) :
    $firstRow = true;
    ?>
    <tr><td rowspan="<?= count($details->Members) ?>"><?= $this->rights[$rightId]->Name ?></td>
        <?php foreach($details->Members as $id => $memberDetails) :
            if ($firstRow) :
                $firstRow = false;
            else :
                echo '<tr>';
            endif;
            $ss = ($memberDetails->level == 0) ? '<span class="adminhistory">' : '';
            $se = ($memberDetails->level == 0) ? '</span>' : '';
        ?>
        <td>
			<?= $layoutbits->PIC_30_30($memberDetails->Username) ?><br>
            <a href="members/<?= $memberDetails->Username ?>" target="_blank"><?= $memberDetails->Username ?></a><br>
                <span class="small"><?= $memberDetails->Status ?></span><br>
                 <span class="smaller">Last login: <?= $memberDetails->LastLogin ?>
		</td>
        <td><?= $ss . $memberDetails->level . $se ?></td>
        <td><?= $ss . $memberDetails->scope . $se ?></td>
        <td class="w-100"><?= $ss . $memberDetails->comment . $se ?></td>
        <td><a href="admin/rights/edit/<?= $rightId ?>/<?= $memberDetails->Username ?>">
                <i class="fa fa-edit" alt="edit"></i></a></td>
        <td><?php if ($memberDetails->level <> 0) : ?>
            <a href="admin/rights/remove/<?= $rightId ?>/<?= $memberDetails->Username ?>">
                <i class="fa fa-times" alt="remove"></i></a>
            <?php endif; ?></td>
        </tr>
        <?php
            endforeach;
    endforeach; ?>
    </table>
</div>