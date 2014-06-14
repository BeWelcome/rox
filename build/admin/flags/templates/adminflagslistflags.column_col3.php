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
<div>
    <form class="yform" method="post">
        <?= $callbackTags ?>
        <div class="type-select">
            <label for="flagid"><?= $words->get("AdminFlagsFlag") ?></label>
            <?= $this->flagsSelect($this->flags, $this->vars['flagid']) ?>
        </div>
        <div class="type-check">
            <input type="checkbox" id="history" name="history" value="1" <?= (isset($this->vars['history'])) ? 'checked="checked' : '' ?> />
            <label for="history"><?= $words->get("AdminFlagsHistory") ?></label>
        </div>
        <div class="type-button">
            <input type="submit" id="submit" name="submit"
                   value="<?= $words->getSilent("AdminFlagsListFlagsSubmit") ?>"/><?php echo $words->flushBuffer(); ?>
        </div>
    </form>
    <div style="height:50px">&nbsp;</div>
    <table id="flags" style="width:130%">
        <tr>
            <th class="flag"><?= $words->get('AdminFlagsFlag') ?></th>
            <th class="usercol"><?= $words->get('AdminFlagsUsername') ?></th>
            <th class="level"><?= $words->get('AdminFlagsLevel') ?></th>
            <th colspan="3" ><?= $words->get('AdminFlagsComment') ?></th>
        </tr>
<?php
    $i = 0;
    foreach($this->flagsWithMembers as $flagId => $details) :
    $firstRow = true;
    if ($i % 2 == 0) {
        $class = 'highlight';
    } else {
        $class = 'blank';
    }?>
    <tr class="<?= $class ?>"><td class="flag" rowspan="<?= count($details->Members) ?>"><?= $this->flags[$flagId]->Name ?></td>
        <?php foreach($details->Members as $id => $memberDetails) :
            if ($firstRow) :
                $firstRow = false;
            else :
                echo '<tr class="' . $class . '">';
            endif;
            $ss = ($memberDetails->level == 0) ? '<span style="text-decoration: line-through; color: red;">' : '';
            $se = ($memberDetails->level == 0) ? '</span>' : '';
        ?>
        <td class="usercol"> 
			<div class="picture"><div><?= $layoutbits->PIC_30_30($memberDetails->Username) ?></div>
            <div><a href="members/<?= $memberDetails->Username ?>" target="_blank"><?= $memberDetails->Username ?></a><br/>
                (<?= $memberDetails->Status ?> , <?= $memberDetails->LastLogin ?>)<br/></div></div>
		</td>
        <td class="level"><?= $ss . $memberDetails->level . $se ?></td>
        <td class="comment"><?= $ss . $memberDetails->comment . $se ?></td>
        <td class="icon"><a href="admin/flags/edit/<?= $flagId ?>/<?= $memberDetails->Username ?>">
                <img src="images/icons/comment_edit.png" alt="edit"/></a></td>
        <td class="icon"><?php if ($memberDetails->level <> 0) : ?>
            <a href="admin/flags/remove/<?= $flagId ?>/<?= $memberDetails->Username ?>">
                <img src="images/icons/delete.png" alt="remove"/></a>
            <?php endif; ?></td>
        </tr>
        <?php
            endforeach;
        $i++;
    endforeach; ?>
    </table>
</div>