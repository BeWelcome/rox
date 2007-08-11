<?php
// environment
$Env = PVars::getObj('env');
// default page elements
$Page = PVars::getObj('page');
// HC widgets
$HC = new HcifController;
$MyTravelbook = new MytravelbookController;
$User = new UserController;
$Cal = new CalController;
echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=PVars::get()->lang?>" lang="<?=PVars::get()->lang?>" xmlns:v="urn:schemas-microsoft-com:vml">
    <head>
        <title><?php echo $Page->title; ?></title>
        <base id="baseuri" href="<?php echo $Env->baseuri; ?>"/>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="keywords" content="Travel planning trip information discussion community Reisen, Information, Kultur, St&auml;dte, Landschaften, Land, Reiseziel, Reiseland, Traumland, Travel, Urlaub"/> 
        <meta name="description" content="Travel Community diary"/>
        <link rel="stylesheet" href="styles/main.css" type="text/css"/>
        <link rel="stylesheet" href="styles/blog.css" type="text/css"/>
        <link rel="stylesheet" href="styles/forums.css" type="text/css"/>

        <script type="text/javascript" src="script/main.js"></script>
    </head>
    <body>
  <div id="h">
            <h1><span class="hidden"><?=$Page->title?></span><a href="<?php echo $Env->baseuri; ?>"><img src="images/logo.png" alt="BeWelcomeLogo"/></a></h1>
  <div id="hm">

                <?php
//$HC->topMenu();
$MyTravelbook->topMenu();
                ?>
            </div>
        </div>
        <div id="content">
            <div id="leftarea">
                <div class="box">
                    <?php $User->displayLoginForm(); ?>
                </div>
            </div>
            <div id="rightarea">
                <div id="langselect" class="box">
                    <p>
                        <a href="mytravelbook/in/en"><img src="images/icons/flags/en.png" alt="en"/></a> | <a href="mytravelbook/in/de"><img src="images/icons/flags/de.png" alt="de"/></a> | <a href="mytravelbook/in/fr"><img src="images/icons/flags/fr.png" alt="fr"/></a>
                    </p>
                </div>
<?php /*
                <div class="box">
                    <?php $Cal->displayCalMonth(); ?>
                </div>
*/ ?>
            </div>
            <div id="main">
                <?php echo $Page->content; ?>
            </div>
        </div>
<?php
if (PVars::get()->debug) {
?>
<!-- 
<?php echo 'Build: '.PVars::get()->build; ?> 
<?php echo 'Templates: '.basename(TEMPLATE_DIR); ?> 
-->
<?php
}
?>
    </body>
</html>
