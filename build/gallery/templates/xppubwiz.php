<?php
$pubText = array();
$i18n = new MOD_i18n('apps/gallery/xppubwiz.php');
$pubText = $i18n->getText('pubText');

$Env = PVars::getObj('env');
echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=PVars::get()->lang?>" lang="<?=PVars::get()->lang?>">
    <head>
        <title>-</title>
        <base id="baseuri" href="<?php echo $Env->baseuri; ?>"/>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" href="styles/main.css"/>
    </head>
    <body>
<?php
if (!$User = APP_User::login()) {
    $User = new User;
    $callbackId = $User->loginProcess();
?>
        <h1><?=$pubText['title_login']?></h1>
        <form method="post" id="login" action="gallery/xppubwiz" class="def-form">
            <div class="row">
                <label for="login-u"><?=$pubText['label_username']?></label><br/>
                <input type="text" id="login-u" name="u"/>
                <p class="desc"><?=$pubText['desc_username']?></p>
            </div>
            <div class="row">
                <label for="login-u"><?=$pubText['label_pw']?></label><br/>
                <input type="password" id="login-p" name="p"/>
                <p class="desc"><?=$pubText['desc_pw']?></p>
                <input type="hidden" name="<?=$callbackId?>" value="1"/>
            </div>
        </form>
        <script type="text/javascript">//<!--
function OnBack() {
    window.external.FinalBack();
}
function OnNext() {
	login.submit();
}
function window.onload() {
	window.external.SetWizardButtons(true,true,false);
}
        //-->
        </script>
<?php
} else {
    $Gallery = new GalleryModel;
    $callbackId = $Gallery->uploadProcess();
?>
        <script type="text/javascript">//<!--
function OnBack() {
}
function OnNext() {
}
function window.onload() {
    window.external.SetWizardButtons(true,true,true);
}

var xml = window.external.Property("TransferManifest");
var files = xml.selectNodes("transfermanifest/filelist/file");

for (i = 0; i < files.length; i++) {
    var postTag = xml.createNode(1, "post", "");
    postTag.setAttribute("href", "<?=$Env->baseuri?>gallery/xppubwiz");
    postTag.setAttribute("name", "gallery-file[]");
    
    var dataTag = xml.createNode(1, "formdata", "");
    dataTag.setAttribute("name", "<?=$callbackId?>");
    dataTag.text = "1";
    postTag.appendChild(dataTag);

    dataTag = xml.createNode(1, "formdata", "");
    dataTag.setAttribute("name", "galleryfile[]");
    dataTag.text = files[i].getAttribute("destination");
    postTag.appendChild(dataTag);

    dataTag.setAttribute("name", "action");
    dataTag.text = "SAVE";
    postTag.appendChild(dataTag);

    files.item(i).appendChild(postTag);
}

var uploadTag = xml.createNode(1, "uploadinfo", "");
var htmluiTag = xml.createNode(1, "htmlui", "");
htmluiTag.text = "<?=$Env->baseuri?>gallery/show/user/<?=$User->getHandle()?>";
uploadTag.appendChild(htmluiTag);

xml.documentElement.appendChild(uploadTag);

window.external.Property("TransferManifest")=xml;
window.external.SetWizardButtons(true,true,true);
window.external.FinalNext();
        //-->
        </script>
<?php
}
?>
    </body>
</html>
