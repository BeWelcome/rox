<!DOCTYPE html>
<html lang="en">
<head>
    <title>Title</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style type="text/css"><!--
        blockquote {
            color: #444;
            background: #f8f8f8;
            border: 1px #ddd solid;
            border-left: 8px #ddd solid;
            padding: 1em 1em 0 0.25em;
            margin: 0 0 1em 0.25em;
        }
        --></style>
</head>
<body style="background-color: #cccccc; color: #333;font-size: 1em;line-height: 1.5em;font-family: arial,verdana,sans-serif;margin: 0;padding: 0;">
<div style="margin: 1em 1em 0 1em;padding: 1em; background: #f5f5f5; -webkit-border-top-left-radius: 10px;
-webkit-border-top-right-radius: 10px;
-moz-border-radius-topleft: 10px;
-moz-border-radius-topright: 10px;
border-top-left-radius: 10px;
border-top-right-radius: 10px; text-align: right;">
    <a href="<?php echo PVars::getObj('env')->baseuri; ?>"><img src="<?php echo PVars::getObj('env')->baseuri; ?>images/misc/email_logo.gif" alt="<?php echo PVars::getObj('env')->sitename; ?>" style="border:0"/></a>
</div>
<div  style="-webkit-border-bottom-right-radius: 10px;
-webkit-border-bottom-left-radius: 10px;
-moz-border-radius-bottomright: 10px;
-moz-border-radius-bottomleft: 10px;
border-bottom-right-radius: 10px;
border-bottom-left-radius: 10px; margin: 0 1em .5em 1em;padding: 1em; background: #fff; ">
    <?php if($title) { ?>
        <h1 style="font-size:16px; font-weight: bold; padding-bottom: 10px; line-height: 1.2em">Title</h1><?= $title ?></h1>
    <?php } ?>

    <?= $body ?>
</div>
<div style="font-size: .7em; margin: 1em; padding: 1em;">
    <?php echo $footer_message; ?>
</div>
</body>
</html>