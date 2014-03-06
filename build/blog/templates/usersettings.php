<?php
$ctrl = new BlogController;
$callbackId = $ctrl->settingsProcess();
$vars =& PPostHandler::getVars($callbackId);

$settingsText = array();
$i18n = new MOD_i18n('apps/blog/usersettings.php');
$settingsText = $i18n->getText('settingsText');
?>
<fieldset id="blog-settings">
    <legend><?=$settingsText['title_legend']?></legend>
    <form method="post" action="user/settings/blog" class="def-form">
        <p><?=$settingsText['label_defaultvis']?>:</p>
        <div class="bw-row">
            <input type="radio" name="vis" value="p" id="blog-settings-visp"<?php
if (isset($vars['vis']) && $vars['vis'] == 'p')
    echo ' checked="checked"';
            ?>/> <label for="blog-settings-visp"><?=$settingsText['label_vispublic']?></label><br/>
            <p class="desc"><?=$settingsText['description_vispublic']?></p>
        </div>
        <div class="bw-row">
            <input type="radio" name="vis" value="r" id="blog-settings-visr"<?php
if (isset($vars['vis']) && $vars['vis'] == 'r')
    echo ' checked="checked"';
            ?>/> <label for="blog-settings-visr"><?=$settingsText['label_visprotected']?></label><br/>
            <p class="desc"><?=$settingsText['description_visprotected']?></p>
        </div>
        <div class="bw-row">
            <input type="radio" name="vis" value="v" id="blog-settings-visv"<?php
if (!isset($vars['vis']) || (isset($vars['vis']) && $vars['vis'] != 'p' && $vars['vis'] != 'r'))
    echo ' checked="checked"';
            ?>/> <label for="blog-settings-visv"><?=$settingsText['label_visprivate']?></label><br/>
            <p class="desc"><?=$settingsText['description_visprivate']?></p>
        </div>
        <p>
            <input type="hidden" name="<?=$callbackId?>" value="1"/>
            <input type="submit" class="button" value="<?=$settingsText['value_submit']?>"/>
        </p>
    </form>
</fieldset>
