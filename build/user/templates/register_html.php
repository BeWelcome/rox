<?php
/**
 * Template HTML registration confirm mail
 * 
 * This template needs the following variables to be set:
 * $registerMailText - Text array (./text/[lang]/apps/user/mail/register.php)
 * $logoCid          - cid: of the logo
 * $confirmUrl       - The URL, which opens the confirmation page
 * 
 * @package user_templates
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: user.view.php 66 2006-06-22 17:16:52Z kang $
 */
echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
    <head>
        <title><?php echo $registerMailText['subject']; ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body style="font-family:verdana,arial,sans-serif;font-size:80%">
        <div style="padding:.5em;border-bottom:1px solid #CCC">
            <a href="<?php echo PVars::getObj('env')->baseuri; ?>"><img src="cid:<?php echo $logoCid; ?>" alt="myTravlebook" style="border:0"/></a>
        </div>
        <h1 style="font-family:verdana,arial,sans-serif"><?php echo $registerMailText['subject']; ?></h1>
        <p><?php echo $registerMailText['message_body_html']; ?></p>
        <p style="border:1px solid #CCC;padding:1em"><a href="<?php echo $confirmUrl; ?>"><?php echo $confirmUrl; ?></a>
    </body>
</html>