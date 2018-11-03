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
}

$callbackTags = $this->layoutkit->formkit->setPostCallback('AdminNewMembersController', 'listMembersCallback');
$layoutbits = new MOD_layoutbits();

?>
<div id="newmembers" class="table-responsive">
    <?= $this->pager->render() ?>
    <table class="table">
        <tr>
            <th class="usercol" colspan="3"><?= $words->get('AdminNewMembersMemberDetails') ?></th>
            <th class="send"></th>
            <?php if ($this->SafetyTeamOrAdmin) : ?>
                <th></th>
            <?php endif; ?>
        </tr>
<?php
    $i = 0;
    foreach($this->members as $details) :
    $firstRow = true;
    if ($i % 2 == 0) {
        $class = 'highlight';
    } else {
        $class = 'blank';
    }?>
    <tr class="<?= $class ?>">
        <td class="usercol">
            <?= $layoutbits->PIC_50_50($details->Username, 'class="framed"') ?><br />
            <a href="/members/<?= $details->Username ?>"><?= $details->Username ?></a><br/>
            <?= $details->CityName ?>, <?= $details->CountryName ?><br />(<?= $details->created ?>)
        </td>
        <td><?php if ($this->SafetyTeamOrAdmin) :
                echo $details->EmailAddress . "<br /><br />";
            endif; ?>
            <?= $details->ProfileSummary ?></td>
        <td><?php if ($details->languages) :
            $str = "";
            foreach($details->languages as $language) :
                $str .= $words->getBuffered($language->WordCode) . " (" . $words->getBuffered("LanguageLevel_" . $language->Level) . ")<br />";
            endforeach;
            if (!empty($str)) {
                $str = substr($str, 0, -6);
            }
            echo $str;
            endif;?>
        </td>
        <td class="send"><?= $this->localGlobal($words, $details); ?></td>
        <?php if ($this->SafetyTeamOrAdmin) : ?>
        <td><?= $this->statusForm($details->id, $details->Status) ?></td>
        <?php endif; ?>
        </tr>
    <?php
    $i++;
    endforeach; ?>
    </table>
    <?= $this->pager->render() ?>
</div>