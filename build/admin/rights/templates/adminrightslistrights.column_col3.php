<?php
/**
 * Created by PhpStorm.
 * User: raymund
 * Date: 08.04.14
 * Time: 20:48
 */
$callbackTags = $this->layoutkit->formkit->setPostCallback('AdminRightsController', 'listRightsCallBack');
$layoutbits = new MOD_layoutbits();
?>
<div>
    <form class="yform" method="post">
        <?= $callbackTags ?>
        <div class="type-select">
            <label for="MemberSelect"><?= $words->get("AdminRightsMember") ?></label>
            <?= rightSelect($this->rights, $this->rightId) ?>
        </div>
        <div class="type-button">
            <input type="submit" id="submit" name="submit"
                   value="<?= $words->getSilent("AdminRightsListRightsSubmit") ?>"/><?php echo $words->flushBuffer(); ?>
        </div>
    </form>
    <div style="height:50px">&nbsp;</div>
    <table id="rights" style="width:130%">
        <tr>
            <th class="right"><?= $words->get('AdminRightsRight') ?></th>
            <th class="usercol"><?= $words->get('AdminRightsUsername') ?></th>
            <th class="level"><?= $words->get('AdminRightsLevel') ?></th>
            <th class="scope"><?= $words->get('AdminRightsScope') ?></th>
            <th colspan="3" class="comment"><?= $words->get('AdminRightsComment') ?></th>
        </tr>
<?php
    $i = 0;
    foreach($this->rightsWithMembers as $rightId => $details) :
    $firstRow = true;
    if ($i % 2 == 0) {
        $class = 'highlight';
    } else {
        $class = 'blank';
    }?>
    <tr class="<?= $class ?>"><td class="right" rowspan="<?= count($details->Members) ?>"><?= $this->rights[$rightId]->Name ?></td>
        <?php foreach($details->Members as $id => $memberDetails) :
            if ($firstRow) :
                $firstRow = false;
            else :
                echo '<tr class="' . $class . '">';
            endif; ?>
        <td class="usercol"> 
			<div class="picture"><div><?= $layoutbits->PIC_30_30($memberDetails->Username) ?></div>
            <div><a href="members/<?= $memberDetails->Username ?>" target="_blank"><?= $memberDetails->Username ?></a></div></div>           
		</td>
        <td class="level"><?= $memberDetails->level ?></td>
        <td class="scope"><?= $memberDetails->scope ?></td>
        <td class="comment"><?= $memberDetails->comment ?></td>
        <td class="icon"><a href="admin/rights/edit/<?= $rightId ?>/<?= $memberDetails->Username ?>">
                <img src="images/icons/comment_edit.png" alt="edit"/></a></td>
        <td class="icon"><a href="admin/rights/remove/<?= $rightId ?>/<?= $memberDetails->Username ?>">
                <img src="images/icons/delete.png" alt="remove"/></a></td>
        </tr>
        <?php
            endforeach;
        $i++;
    endforeach; ?>
    </table>
</div>