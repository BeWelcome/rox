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
    $this->flagsWithMembers = $this->getRedirectedMem('flagsWithMembers');
}

$callbackTags = $this->layoutkit->formkit->setPostCallback('AdminFlagsController', 'listFlagsCallback');
$layoutbits = new MOD_layoutbits();
?>
<div class="w-100 row">
    <form method="post">
        <?= $callbackTags ?>

            <label for="flagid" class="mb-0"><?= $words->get("AdminFlagsFlag") ?></label>
            <?= $this->flagsSelect($this->flags, $this->vars['flagid']) ?>

            <input type="checkbox" id="history" name="history" value="1" <?= (isset($this->vars['history'])) ? 'checked="checked' : '' ?> />
            <label for="history"><?= $words->get("AdminFlagsHistory") ?></label>

            <input type="submit" id="submit" name="submit" class="btn btn-primary"
                   value="<?= $words->getSilent("AdminFlagsListFlagsSubmit") ?>"/><?php echo $words->flushBuffer(); ?>

    </form>

    <table id="flags" class="table table-striped table-hover">
        <tr>
            <th><?= $words->get('AdminFlagsFlag') ?></th>
            <th><?= $words->get('AdminFlagsUsername') ?></th>
            <th><?= $words->get('AdminFlagsLevel') ?></th>
            <th colspan="3" ><?= $words->get('AdminFlagsComment') ?></th>
        </tr>
<?php
    foreach($this->flagsWithMembers as $flagId => $details) {
        ?>
        <tr>
        <td rowspan="<?= count($details->Members) ?>"><?= $this->flags[$flagId]->Name ?></td>
        <?php foreach ($details->Members as $id => $memberDetails) {
            $ss = ($memberDetails->level == 0) ? '<span class="adminhistory">' : '';
            $se = ($memberDetails->level == 0) ? '</span>' : '';
            ?>
            <td>
                    <?= $layoutbits->PIC_30_30($memberDetails->Username) ?><br>
                    <a href="members/<?= $memberDetails->Username ?>"
                            target="_blank"><?= $memberDetails->Username ?></a><br/>
                        <span class="small"><?= $memberDetails->Status ?></span>
                        <span class="smaller">Last login: <?= $memberDetails->LastLogin ?></span>
            </td>
            <td><?= $ss . $memberDetails->level . $se ?></td>
            <td class="w-100"><?= $ss . $memberDetails->comment . $se ?></td>
            <td><a href="admin/flags/edit/<?= $flagId ?>/<?= $memberDetails->Username ?>">
                    <i class="fa fa-edit" alt="edit"></i></a></td>
            <td><?php if ($memberDetails->level <> 0) { ?>
                <a href="admin/flags/remove/<?= $flagId ?>/<?= $memberDetails->Username ?>">
                    <i class="fa fa-times" alt="remove"></i></a>
                <?php } ?>
                </td>
            </tr>
        <?php } ?>
    <?php } ?>

    </table>
</div>