<?php
/**
 * message writeform template
 *
 * @package message
 * @subpackage template
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
if (!$User) {
    echo '<p class="error">'.$errors['not_logged_in'].'</p>';
    return false;
}

?>
<script type="text/javascript">//<!--
function addrecipient( handle ) {
    var el = document.getElementById('message-recipient');
    if (el.value != '')
        el.value += ', ';
    el.value += handle;
}
//-->
</script>
<?
?>
<script type="text/javascript">//<!--
tinyMCE.srcMode = '';
tinyMCE.baseURL = http_baseuri+'script/tiny_mce';
tinyMCE.init({
    mode: "exact",
    elements: "message-txt",
    theme: "advanced",
    relative_urls:false,
    convert_urls:false,
    theme_advanced_buttons1 : "bold,italic,underline,strikethrough,link, bullist,separator,justifyleft,justifycenter,justifyfull,bullist,numlist,forecolor,backcolor,charmap",
    theme_advanced_buttons2 : "",
    theme_advanced_buttons3 : "",    
    theme_advanced_toolbar_location: 'top',
    theme_advanced_statusbar_location: 'bottom',
    theme_advanced_resizing: true
    
});
//-->
</script>

<form method="post" action="message/write" class="def-form">
    <h2><?=$writeText['title_write']?></h2>
<?php
if (in_array('not_sent', $errors)) {
    echo '<p class="error">'.$errorText['not_sent'].'</p>';
}
?>    


    <fieldset id="message-main">
        <legend><?php echo $writeText['legend_recipient']; ?></legend>
        <div class="row">
            <label for="message-recipient"><?=$writeText['label_recipient']?></label><br/>
<?php
if (in_array('recipient', $errors)) {
    echo '<span class="error">'.$errorText['recipient'].'</span><br />';
}
?>
            <input type="text" id="message-recipient" name="r" class="long"<?php
if (isset($vars['r']) && $vars['r'])
    echo ' value="'.htmlentities($vars['r'], ENT_COMPAT, 'utf-8').'"';
            ?>/>
            <input type="submit" id="message-validate" name="submit-validate" value="<?php echo $writeText['submit_validate']; ?>" />
            <p class="desc"><?php echo $writeText['desc_recipient']; ?></p>
<?php
if (isset($errors['similar_recipients']) && count($errors['similar_recipients'])) {
    echo "\n            <p>\n";
    echo $writeText['label_similar_recipients'].': '.$writeText['hint_similar_recipients'].'<br />';
    foreach ($errors['similar_recipients'] as $r)
        echo '              <a href="#" onclick="javascript:addrecipient(\''.
            htmlentities($r, ENT_COMPAT, 'utf-8').'\'); return false;">'.htmlentities($r, ENT_COMPAT, 'utf-8').'</a>'."\n";
    echo "            </p>\n";
}
?>
        </div>
    </fieldset>


    <fieldset id="message-msg">
        <legend><?php echo $writeText['legend_message']; ?></legend>
<?php
// TODO if names are validated, print them here clickable.
if (isset($vars['r']) && strcmp($vars['r'],'')!=0 && !in_array('recipient', $errors)) {
?>
        <div class="row">
            <label><?php echo $writeText['verified_recipients']; ?></label><br />
<?php
    // make recipients clickable, viewing their userprofiles.
    $recp = explode(',', $vars['r']);
    foreach ($recp as $r) {
        $r = trim($r);
        echo '<a href="user/'.$r.'">'.$r.'</a> ';
    }
?>
        </div>
<?php
}
?>
        <div class="row">
            <label for="message-subject"><?=$writeText['label_subject']?></label><br/>
            <input type="text" id="message-subject" name="s" class="long"<?php
    if (isset($vars['s']) && $vars['s'])
        echo ' value="'.htmlentities($vars['s'], ENT_COMPAT, 'utf-8').'"';
            ?>/>
<?php
if (in_array('subject', $errors) && !isset($vars['submit-validate'])) {
    echo '<span class="error">'.$errorText['subject'].'</span>';
}
?>
            <p class="desc"></p>
        </div>
        <div class="row">
            <label for="message-txt"><?=$writeText['label_text']?></label><br/>
            <textarea id="message-txt" name="txt" cols="40" rows="7"><?php
    if (isset($vars['txt']) && $vars['txt'])
        echo htmlentities($vars['txt'], ENT_COMPAT, 'utf-8');
            ?></textarea>
<?php
if (in_array('text', $errors) && !isset($vars['submit-validate'])) {
    echo '<span class="error">'.$errorText['text'].'</span>';
}
?>
        </div>
    <p>
            <input type="hidden" name="<?=$callbackId?>" value="1"/>
            <input type="submit" value="<?=$writeText['submit_send']?>"/>
            <input type="checkbox" id="message-store" name="sto" checked="checked" />
            <label for="message-store"><?php echo $writeText['label_store_outbox']; ?></label>
    </p>
    </fieldset>
</form>

<script type="text/javascript">//<!--
createFieldsetMenu();
<?php
if (isset($vars['r']) && strcmp($vars['r'],'')!=0 && !in_array('recipient', $errors)) {
    echo "setFieldsetMenu('message-msg');\n";
}
else {
    echo "setFieldsetMenu('message-main');\n";
}
?>
//-->
</script>
<?php
PPostHandler::clearVars($callbackId);
?>

