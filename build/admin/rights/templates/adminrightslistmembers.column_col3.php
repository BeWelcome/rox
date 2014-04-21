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
<div>
    <form class="yform" method="post">
        <?= $callbackTags ?>
        <div class="type-select">
            <label for="member"><?= $words->get("AdminRightsMember") ?></label>
            <?= memberSelect($this->members, $this->vars['member']) ?>
        </div>
        <div class="type-check">
            <input type="checkbox" id="history" name="history" value="1" <?= ($this->vars['history']) ? 'checked="checked' : '' ?> />
            <label for="history"><?= $words->get("AdminRightsHistory") ?></label>
        </div>
        <div class="type-button">
            <input type="submit" id="submit" name="submit"
                   value="<?= $words->getSilent("AdminRightsListMembersSubmit") ?>"/><?php echo $words->flushBuffer(); ?>
        </div>
    </form>
    <div style="height:50px">&nbsp;</div>
    <table id="rights" style="width:130%">
        <tr>
            <th class="usercol"><?= $words->get('AdminRightsUsername') ?></th>
            <th class="right"><?= $words->get('AdminRightsRight') ?></th>
            <th class="level"><?= $words->get('AdminRightsLevel') ?></th>
            <th class="scope"><?= $words->get('AdminRightsScope') ?></th>
            <th colspan="3" class="comment"><?= $words->get('AdminRightsComment') ?></th>
        </tr>
<?php
    $i = 0;
    foreach($this->membersWithRights as $username => $details) :
    $firstRow = true;
    if ($i % 2 == 0) {
        $class = 'highlight';
    } else {
        $class = 'blank';
    }?>
    <tr class="<?= $class ?>"><td class="usercol" rowspan="<?= count($details->Rights) ?>"><?php
		echo $layoutbits->PIC_50_50($username, 'class="framed"') . '<br />';
		echo $username . '<br />'; ?>
        <a href="admin/rights/assign/<?= $username ?>">
            <img src="images/icons/add.png" alt="add right"></a><br />
        <a href="admin/rights/assign/<?= $username ?>">
            <?= $words->getSilent('AdminRightsAssignRight') ?></a><?= $words->flushBuffer() ?></td>
        <?php foreach($details->Rights as $id => $right) :
            if ($firstRow) :
                $firstRow = false;
            else :
                echo '<tr class="' . $class . '">';
            endif;
            $ss = ($right->level == 0) ? '<span style="text-decoration: line-through; color: red;">' : '';
            $se = ($right->level == 0) ? '</span>' : '';
        ?>
        <td class="right"><span title="tooltip<?= $id ?>"><?= $ss .  $this->rights[$id]->Name . $se ?></span></td>
        <td class="level"><?= $ss . $right->level . $se ?></td>
        <td class="scope"><?= $ss . $right->scope . $se ?></td>
        <td class="comment"><?= $right->comment ?></td>
        <td class="icon"><a href="admin/rights/edit/<?= $id ?>/<?= $username ?>">
                <img src="images/icons/comment_edit.png" alt="edit"/></a></td>
        <td class="icon"><?php if ($right->level <> 0) : ?>
            <a href="admin/rights/remove/<?= $id ?>/<?= $username ?>">
                <img src="images/icons/delete.png" alt="remove"/></a>
            <?php endif; ?></td>
        </tr>
        <?php
            endforeach;
        $i++;
    endforeach; ?>
    </table>
</div>