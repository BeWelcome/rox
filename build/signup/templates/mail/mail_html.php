<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
    <head>
        <title><?php echo $message_subject; ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body style="background-color: #cccccc;font-color: #333;font-size: 12px;line-height: 1.5em;font-family: arial,verdana,sans-serif;margin: 0;padding: 0;">
        <div style="width: 580px;margin: 0;padding: 2em 0 2em 0; background-color: #ffffff; background: #ffffff url(<?php echo PVars::getObj('env')->baseuri; ?>/images/misc/email_bg.gif) top left repeat-y;">
            <div>
                <div style="padding: 5px 20px 5px 20px; margin: 10px;background-color: #f5f5f5;border-top: 1px solid #e5e5e5;border-bottom: 1px solid #e5e5e5; text-align: right;">
                    <a href="<?php echo PVars::getObj('env')->baseuri; ?>"><img src="<?php echo PVars::getObj('env')->baseuri; ?>images/logo_index_top.svg" alt="<?php echo PVars::getObj('env')->sitename; ?>" style="border:0"/></a>
                </div>
                <div style="margin: 60px; background-color: #ffffff;">
                    <h1 style="font-size: 30px; padding-bottom: 20px"><?=$message_title?></h1>
                    <p><?=$message_text?></p>
                </div>
                <div style="padding: 5px 20px 5px 20px; margin: 10px;background-color: #f5f5f5;border-top: 1px solid #e5e5e5;border-bottom: 1px solid #e5e5e5; text-align: right;">
                </div>
            </div>
        </div>
        <div style="width: 580px; height: 24px; background: #cccccc url(<?php echo PVars::getObj('env')->baseuri; ?>/images/misc/email_bottom.gif) top left repeat-y;">
        </div>
    </body>
</html>