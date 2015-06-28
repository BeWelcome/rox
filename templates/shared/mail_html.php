<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $subject; ?></title>
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
<div style="margin: 1em;padding: 0 0 2em 0; background: #fff; border-radius: 1em;">
    <div>
        <div style="padding: .5em 1em; margin: 10px; background-color: #f5f5f5; border-top: 1px solid #e5e5e5; border-bottom: 1px solid #e5e5e5; text-align: right;">
            <a href="<?php echo PVars::getObj('env')->baseuri; ?>"><img src="<?php echo PVars::getObj('env')->baseuri; ?>images/misc/email_logo.gif" alt="<?php echo PVars::getObj('env')->sitename; ?>" style="border:0"/></a>
        </div>
        <div style="margin: 40px; background-color: #ffffff;">
            <?php if($title) { ?>
                <h1 style="font-size:16px; font-weight: bold; padding-bottom: 10px; line-height: 1.2em">
                    <?=$title?></h1>
            <?php } ?>
            <div><?= $body ?></div>
        </div>
    </div>
</div>
<div>
    <?php echo $footer_message; ?>
</div>
</body>
</html>
