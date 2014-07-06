<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
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
    <body style="background-color: #cccccc;font-color: #333;font-size: 12px;line-height: 1.5em;font-family: arial,verdana,sans-serif;margin: 0;padding: 0;">
        <div style="width: 640px;margin: 0;padding: 0 0 2em 0; background-color: #ffffff; background: #ffffff url(<?php
        echo PVars::getObj('env')->baseuri; ?>/images/misc/email_bg.gif) top left repeat-y;">
            <div>
                <div style="padding: 10px 20px 10px 20px; margin: 10px;background-color: #f5f5f5;border-top: 1px solid #e5e5e5;border-bottom: 1px solid #e5e5e5; text-align: right;">
                    <a href="<?php echo PVars::getObj('env')->baseuri; ?>"><img src="<?php echo PVars::getObj('env')->baseuri; ?>images/misc/email_logo.gif" alt="<?php echo PVars::getObj('env')->sitename; ?>" style="border:0"/></a>
                </div>
                <div style="margin: 40px; background-color: #ffffff;">
                    <?if($title) { ?><h1 style="font-size: 30px; padding-bottom: 10px; line-height: 1.2em"><?=$title?></h1><?}?>
                    <div><?=($body_html) ? $body_html : $body?></div>
                </div>
            </div>
        </div>
        <div style="width: 640px; height: 80px; background: #cccccc url(<?php echo PVars::getObj('env')->baseuri;
        ?>/images/misc/email_bottom.gif) top left no-repeat; padding: 40px;">
            <?php echo $footer_message; ?>
        </div>
    </body>
</html>