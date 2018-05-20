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
<div class="w-100 p-0">
    <table id="rights" class="table table-striped table-hover">
        <tr>
            <th class="usercol"><?= $words->get('AdminRightsUsername') ?></th>
            <th class="right"><?= $words->get('AdminRightsRight') ?></th>
            <th class="level"><?= $words->get('AdminRightsLevel') ?></th>
            <th class="scope"><?= $words->get('AdminRightsScope') ?></th>
            <th colspan="3" class="comment"><?= $words->get('AdminRightsComment') ?></th>
        </tr>
<?php
    foreach($this->membersWithRights as $username => $details) :
    $firstRow = true;
    ?>
    <tr style="border-top: 2px solid #666;">
        <td rowspan="<?= count($details->Rights) ?>"><?php
		echo $layoutbits->PIC_50_50($username, 'class="framed"') . '<br />';
		echo $username; ?><br/>(<?= $details->Status ?>, <?= $details->LastLogin ?>)<br />
        <a href="admin/rights/assign/<?= $username ?>">
            <i class="fa fa-plus-square-o"></i>
            <?= $words->getSilent('AdminRightsAssignRight') ?></a><?= $words->flushBuffer() ?></td>
        <?php foreach($details->Rights as $id => $right) :
            if ($firstRow) :
                $firstRow = false;
            else :
                echo '<tr>';
            endif;
            $ss = ($right->level == 0) ? '<span style="text-decoration: line-through; color: red;">' : '';
            $se = ($right->level == 0) ? '</span>' : '';
        ?>
        <td><span title="tooltip<?= $id ?>"><?= $ss .  $this->rights[$id]->Name . $se ?></span></td>
        <td><?= $ss . $right->level . $se ?></td>
        <td><?= $ss . $right->scope . $se ?></td>
        <td><?= $right->comment ?></td>
        <td><a href="admin/rights/edit/<?= $id ?>/<?= $username ?>">
                <i class="fa fa-edit" alt="edit"></i></a></td>
        <td><?php if ($right->level <> 0) : ?>
            <a href="admin/rights/remove/<?= $id ?>/<?= $username ?>">
                <i class="fa fa-times" alt="delete"></i></a>
            <?php endif; ?></td>
        </tr>
        <?php
            endforeach;
    endforeach; ?>
    </table>
</div>