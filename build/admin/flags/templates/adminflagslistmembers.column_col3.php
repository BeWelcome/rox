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
<div>
    <form class="yform" method="post">
        <?= $callbackTags ?>
        <div class="type-select">
            <label for="member"><?= $words->get("AdminFlagsMember") ?></label>
            <?= memberSelect($this->members, $this->vars['member']) ?>
        </div>
        <div class="type-check">
            <input type="checkbox" id="history" name="history" value="1" <?= (isset($this->vars['history'])) ? 'checked="checked' : '' ?> />
            <label for="history"><?= $words->get("AdminFlagsHistory") ?></label>
        </div>
        <div class="type-button">
            <input type="submit" id="submit" name="submit"
                   value="<?= $words->getSilent("AdminFlagsListMembersSubmit") ?>"/><?php echo $words->flushBuffer(); ?>
        </div>
    </form>
    <div style="height:50px">&nbsp;</div>
    <table id="flags" style="width:130%">
        <tr>
            <th class="usercol"><?= $words->get('AdminFlagsUsername') ?></th>
            <th class="flag"><?= $words->get('AdminFlagsFlag') ?></th>
            <th class="level"><?= $words->get('AdminFlagsLevel') ?></th>
            <th colspan="3" class="comment"><?= $words->get('AdminFlagsComment') ?></th>
        </tr>
<?php
    $i = 0;
    foreach($this->membersWithFlags as $username => $details) :
    $firstRow = true;
    if ($i % 2 == 0) {
        $class = 'highlight';
    } else {
        $class = 'blank';
    }?>
    <tr class="<?= $class ?>"><td class="usercol" rowspan="<?= count($details->Flags) ?>"><?php
		echo $layoutbits->PIC_50_50($username, 'class="framed"') . '<br />';
		echo $username; ?><br/>(<?= $details->Status ?>, <?= $details->LastLogin ?>)<br />
        <a href="admin/flags/assign/<?= $username ?>">
            <img src="images/icons/add.png" alt="add flag"></a><br />
        <a href="admin/flags/assign/<?= $username ?>">
            <?= $words->getSilent('AdminFlagsAssignFlag') ?></a><?= $words->flushBuffer() ?></td>
        <?php foreach($details->Flags as $id => $flag) :
            if ($firstRow) :
                $firstRow = false;
            else :
                echo '<tr class="' . $class . '">';
            endif;
            $ss = ($flag->level == 0) ? '<span style="text-decoration: line-through; color: red;">' : '';
            $se = ($flag->level == 0) ? '</span>' : '';
        ?>
        <td class="flag"><span title="tooltip<?= $id ?>"><?= $ss .  $this->flags[$id]->Name . $se ?></span></td>
        <td class="level"><?= $ss . $flag->level . $se ?></td>
        <td class="comment"><?= $flag->comment ?></td>
        <td class="icon"><a href="admin/flags/edit/<?= $id ?>/<?= $username ?>">
                <img src="images/icons/comment_edit.png" alt="edit"/></a></td>
        <td class="icon"><?php if ($flag->level <> 0) : ?>
            <a href="admin/flags/remove/<?= $id ?>/<?= $username ?>">
                <img src="images/icons/delete.png" alt="remove"/></a>
            <?php endif; ?></td>
        </tr>
        <?php
            endforeach;
        $i++;
    endforeach; ?>
    </table>
</div>