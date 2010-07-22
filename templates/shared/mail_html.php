<?php

$words = new MOD_words();

?>

<html>
<head>
    <title><?php echo $subject; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" bgcolor="#ffffff" >

<table width="100%" cellpadding="0" cellspacing="0" bgcolor="#ffffff" >
    <tr>
        <td valign="top">
            <table width="580" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="background-color:#FFFFFF; border-bottom: 1px solid #e5e5e5; height: 60px;">
                            <a href="<?php echo PVars::getObj('env')->baseuri; ?>"><img src="<?php echo PVars::getObj('env')->baseuri; ?>images/misc/email_logo.gif" alt="<?php echo PVars::getObj('env')->sitename; ?>" border="0" align="right"></a>
                    </td>
                </tr>
            </table>
            <table width="580" cellpadding="20" cellspacing="0" bgcolor="#ffffff" >
                <tr>
                    <td bgcolor="#ffffff" valign="top" style="font-size:13px; color: #333;line-height: 1.5em;font-family: arial,verdana,sans-serif;">
                        <?if($title) { ?><h1 style="font-size: 20px; line-height: 1.2em"><?=$title?></h1><?}?>
                        <p><?=($body_html) ? $body_html : $body?></p>
                    </td>
                </tr>
                <tr>
                    <td style="background-color:#ffffff; border-top:1px solid #e5e5e5;" valign="top">
                        <center>
                        <span style="font-size:11px; color:#333; line-height:1.2em; font-family: arial,verdana,sans-serif;">
                            <?php echo $words->getBuffered('MailFooterMessage')?>
                        </span>
                        </center>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
