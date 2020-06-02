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
    $this->members = $this->getRedirectedMem('members');
    $this->membersWithRights = $this->getRedirectedMem('membersWithRights');
}

$callbackTags = $this->layoutkit->formkit->setPostCallback('AdminRightsController', 'listMembersCallback');
$layoutbits = new MOD_layoutbits();

?>
<div class="w-100 row p-3">
    <form method="post">
        <?= $callbackTags ?>

            <label for="member" class="mb-0"><?= $words->get("AdminRightsMember") ?></label>
            <?= memberSelect($this->members, $this->vars['member']) ?>

            <input type="checkbox" id="history" name="history" value="1" <?= (isset($this->vars['history'])) ? 'checked="checked' : '' ?> />
            <label for="history" class="mb-0"><?= $words->get("AdminRightsHistory") ?></label>

            <input type="submit" id="submit" name="submit" class="btn btn-primary"
                   value="<?= $words->getSilent("AdminRightsListMembersSubmit") ?>"/><?php echo $words->flushBuffer(); ?>
    </form>
</div>
<div class="table-responsive">
    <table id="rights" class="table table-striped table-hover" style="table-layout: fixed;">
        <tr>
            <th><?= $words->get('AdminRightsUsername') ?></th>
            <th><?= $words->get('AdminRightsRight') ?></th>
            <th><?= $words->get('AdminRightsLevel') ?></th>
            <th><?= $words->get('AdminRightsScope') ?></th>
            <th colspan="3"><?= $words->get('AdminRightsComment') ?></th>
        </tr>
<?php
    foreach($this->membersWithRights as $username => $details) :
    $firstRow = true;
    ?>
    <tr style="border-top: 2px solid #666;">
        <td rowspan="<?= count($details->Rights) ?>"><?php
		echo $layoutbits->PIC_50_50($username, 'class="framed"') . '<br>';
		echo $username; ?><br/>
            <span class="small"><?= $details->Status ?></span><br>
            <span class="smaller">Last login: <?= $details->LastLogin ?></span><br>
        <a href="admin/rights/assign/<?= $username ?>" class="btn btn-outline-primary btn-sm">
            <i class="fa fa-plus-square"></i>
            <?= $words->getSilent('AdminRightsAssignRight') ?></a><?= $words->flushBuffer() ?></td>
        <?php foreach($details->Rights as $id => $right) :
            if ($firstRow) :
                $firstRow = false;
            else :
                echo '<tr>';
            endif;
            $ss = ($right->level == 0) ? '<span class="adminhistory">' : '';
            $se = ($right->level == 0) ? '</span>' : '';
        ?>
        <td><span title="tooltip<?= $id ?>"><?= $ss .  $this->rights[$id]->Name . $se ?></span></td>
        <td><?= $ss . $right->level . $se ?></td>
        <td style="word-break: break-word"><?= $ss . $right->scope . $se ?></td>
        <td class="w-100"><?= $right->comment ?></td>
        <td><a href="admin/rights/edit/<?= $id ?>/<?= $username ?>">
                <i class="fa fa-edit" alt="edit"></i></a></td>
        <td><?php if ($right->level <> 0) : ?>
            <a href="admin/rights/remove/<?= $id ?>/<?= $username ?>">
                <i class="fa fa-times" alt="remove"></i></a>
            <?php endif; ?></td>
        </tr>
        <?php
            endforeach;
    endforeach; ?>
    </table>
</div>