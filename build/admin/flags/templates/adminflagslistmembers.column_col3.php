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
    $this->membersWithFlags = $this->getRedirectedMem('membersWithFlags');
}

$callbackTags = $this->layoutkit->formkit->setPostCallback('AdminFlagsController', 'listMembersCallback');
$layoutbits = new MOD_layoutbits();

?>
<div class="row w-100 p-3">
    <form method="post">
        <?= $callbackTags ?>

            <label for="member"><?= $words->get("AdminFlagsMember") ?></label>
            <?= memberSelect($this->members, $this->vars['member']) ?>

            <input type="checkbox" id="history" name="history" value="1" <?= (isset($this->vars['history'])) ? 'checked="checked' : '' ?> />
            <label for="history"><?= $words->get("AdminFlagsHistory") ?></label>

            <input type="submit" id="submit" name="submit" class="btn btn-primary"
                   value="<?= $words->getSilent("AdminFlagsListMembersSubmit") ?>"/><?php echo $words->flushBuffer(); ?>
    </form>
</div>
<div class="w-100 p-0">
    <table id="flags" class="table table-striped table-hover">
        <tr>
            <th><?= $words->get('AdminFlagsUsername') ?></th>
            <th><?= $words->get('AdminFlagsFlag') ?></th>
            <th><?= $words->get('AdminFlagsLevel') ?></th>
            <th colspan="3"><?= $words->get('AdminFlagsComment') ?></th>
        </tr>
<?php

    foreach($this->membersWithFlags as $username => $details){
    ?>
    <tr><td rowspan="<?= count($details->Flags) ?>"><?php
		echo $layoutbits->PIC_50_50($username, 'class="framed"'); ?><br>
            <a href="members/<?= $username; ?>"
               target="_blank"><?= $username; ?></a><br>
            <span class="small"><?= $details->Status ?></span><br>
            <span class="smaller">Last login: <?= $details->LastLogin ?><br>
        <a href="admin/flags/assign/<?= $username ?>" class="btn btn-sm btn-outline-primary">
            <i class="fa fa-plus-square-o"></i>
            <?= $words->getSilent('AdminFlagsAssignFlag') ?></a><?= $words->flushBuffer() ?></td>
        <?php foreach($details->Flags as $id => $flag) {
        $ss = ($flag->level == 0) ? '<span class="adminhistory">' : '';
        $se = ($flag->level == 0) ? '</span>' : '';
        ?>
        <td><span title="tooltip<?= $id ?>"><?= $ss . $this->flags[$id]->Name . $se ?></span></td>
        <td><?= $ss . $flag->level . $se ?></td>
        <td><?= $flag->comment ?></td>
        <td><a href="admin/flags/edit/<?= $id ?>/<?= $username ?>">
                <i class="fa fa-edit" alt="edit"></i></a></td>
        <td><?php if ($flag->level <> 0) : ?>
                <a href="admin/flags/remove/<?= $id ?>/<?= $username ?>">
                    <i class="fa fa-times" alt="remove"></i></a>
            <?php endif; ?></td>
    </tr>
    <?php
        }
    } ?>
    </table>
</div>