<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330,
Boston, MA  02111-1307, USA.

*/
// error_reporting(E_ALL & ~E_STRICT);
//Load the files we'll need
require_once __DIR__.'/../../../modules/mail/lib/mail.lib.php';

// -----------------------------------------------------------------------------
// bw_mail is a function to centralise all mail send thru BW
// it returns true if the mail was processed by swith in a correct way
// this doesn't mean that the receiver will have it, it just means that the parameters look OK
function bw_mail($to, $subject, $text, $extra_headers = "", $from = "", $IdLanguage = 0, $PreferenceHtmlEmail = "yes", $LogInfo = "", $replyto = "", $Greetings = "") {
    try {
        $iStatus = bw_sendmail($to, $subject, $text, "", $extra_headers, $from, $IdLanguage, $PreferenceHtmlEmail, $LogInfo, $replyto, $Greetings);
        if (!$iStatus) {
            throw new Exception("And Exception occured with bw_sendmail");
        }
        else {
            return($iStatus); // Successful exit
        }
    }
    catch (Exception $e) {
        LogStr("Problem with bw_mail".$e->getMessage()." to [".$to."]","bw_mail");
        return(false);
    }
} // end of bw_mail

// -----------------------------------------------------------------------------
// bw_sendmail is a function to centralise all mail send thru HC with more feature
// $to = email of receiver this can be ; separated
// $mail_subject=subject of mail
// $text = text of mail
// $textinhtml = text in html will be use if user preference are html
// $From= from mail (will also be the reply to)
// $deflanguage : default language of receiver
// $PreferenceHtmlEmail : if set to yes member will receive mail in html format, note that it will be force to html if text contain ";&#"
// $LogInfo = used for debugging

function bw_sendmail($to, $_mail_subject, $text, $textinhtml = "", $extra_headers = "", $_FromParam = "", $IdLanguage = 0, $PreferenceHtmlEmail = "yes", $LogInfo = "", $replyto = "", $ParamGreetings = "") {
    global $_SYSHCVOL;
    $mail_subject = $_mail_subject;

    // This is aimed to produce an additional information in the subject of the mail when it is not sent via www.bewelcome.org
    if ((isset($_SERVER['SERVER_NAME'])) and ($_SERVER['SERVER_NAME'] != "www.bewelcome.org")) {
        $mail_subject = "[via " . $_SERVER['SERVER_NAME'] . "]" . $_mail_subject;
    }

    if (isset($_SESSION['verbose'])) {
        $verbose = $_SESSION['verbose'];
    }
    else {
        $verbose = false;
    }

    $FromParam = $_FromParam;
    if ($_FromParam == "")
        $FromParam = $_SYSHCVOL['MessageSenderMail'];

    // Is sender in format "name" <email@address>?
    if (strpos($FromParam, '" <')) {
        $parts = explode('" <', $FromParam);
        $From = array(substr($parts[1],0,-1) => substr($parts[0],1));
    } else {
        $From = $FromParam;
    }
    $text = str_replace("<br />", "", $text);
    $text = str_replace("\r\n", "\n", $text); // solving the century-bug: NO MORE DAMN TOO MANY BLANK LINES!!!
    $use_html = $PreferenceHtmlEmail;

    if ($use_html == "html")
        $use_html = "yes";

    if ($verbose) {
        echo "<br />use_html=[" . $use_html . "] mail to " . $to . "<br />\n\$_SERVER['SERVER_NAME']=", $_SERVER['SERVER_NAME'], "<br />\n";
    }

    if (stristr($text, ";&#") != false) { // if there is any non ascii char, force html
        if ($verbose)
            echo "<br />1 <br />\n";

        if ($use_html != "yes") {
            if ($verbose)
                echo "<br /> no html 2<br />\n";

            $use_html = "yes";
            if ($LogInfo == "") {
                LogStr("Forcing HTML for message to " . $to, "hcvol_mail");
            } else {
                LogStr("Forcing HTML <b>$LogInfo</b>", "hcvol_mail");
            }
        }
    }

    $headers = $extra_headers;
    if (!(strstr($headers, "From:")) and ($From != "")) {
        $headers = $headers . "From:" . utf8_encode($FromParam) . "\n";
    }

    if (($use_html == "yes") or (strpos($text, "<html>") !== false)) { // if html is forced or text is in html then add the MIME header
        if ($verbose)
            echo "<br />3<br />";

        $use_html = "yes";
    }

    if ($replyto != "") {
        $headers = $headers . "Reply-To:" . utf8_encode($replyto);
        //replyto stays the same
    }
    if (!(strstr($headers, "Reply-To:")) and ($From != "")) {
        $headers = $headers . "Reply-To:" . utf8_encode($FromParam);
        $replyto = $From;
    } elseif (!strstr($headers, "Reply-To:")) {
        $headers = $headers . "Reply-To:" . utf8_encode($_SYSHCVOL['MessageSenderMail']);
        $replyto = $_SYSHCVOL['MessageSenderMail'];
    }
    $headers .= "\nX-Mailer:PHP"; // mail of client

    $Greetings = $ParamGreetings;

    if ($use_html == "yes") {
        if ($verbose)
            echo "<br/ >4<br />\n";

        if ($textinhtml != "") {
            if ($verbose)
                echo "<br>5 will use text in html paramameter<br>";

            $texttosend = $textinhtml;
        } else {
            if ($verbose)
                echo "<br>6<br>\n";

            $texttosend = $text;
        }
        if (strpos($texttosend, "<html>") === false) { // If not allready html
            if ($verbose) {
                echo "<br>7<br>";
            }
            $html_text = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n" . "<html>\n<head>\n<title>" . $mail_subject . "</title>\n</head>\n<body bgcolor='#ffffcc'>\n" . str_replace("\n", "<br>", $texttosend);
            $html_text .= "<br>" . $Greetings;
            $html_text .= "\n</body>\n</html>";
        } else {
            if ($verbose)
                echo "<br>8<br>\n";

            $html_text = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n" . str_replace("\n", "<br>\n", $texttosend); // In this case, its already in html, \n are to replace by <br>
        }
    }

    if ($verbose)
        echo "<br>9 <br>\n";

    $plain_text = $text ."\n" . $Greetings;
    $plain_text = str_replace("<br>", "\n", $plain_text);
    $plain_text = str_replace("</td>", "</td>\n", $plain_text);


    if ($verbose)
        echo "<br>10 " . nl2br($html_text) . "<br>\n";

    if ($verbose)
        echo "<br>12 " . $html_text . "<br>\n";

    // Debugging trick
    if ($verbose) {
        echo "<table bgcolor='#ffff99' cellspacing=3 cellpadding=3 border=2><tr><td>";
        echo "\$From:<font color=#6633ff>$From</font> \$To:<font color=#6633ff>$to</font><br />";
        echo "\$mail_subject:<font color=#6633ff><b>", $mail_subject, "</b></font></td>";
        $ss = $headers;
        echo "<tr><td>\$headers=<font color=#ff9933>";

        for ($ii = 0; $ii < strlen($ss); $ii++) {
            $jj = ord($ss { $ii });
            if ($jj == 10) {
                echo "\\n<br>";
            } elseif ($jj == 13) {
                echo "\\r";
            } else {
                echo chr($jj);
            }
        }
        echo "</font></td>";
        echo "<tr><td><font color=#6633ff>", htmlentities($html_text), "</font></td>";
        if ($use_html == "yes")
        echo "<tr><td>$html_text</td>";
        echo "</table><br />";
    } // end of for $ii
    // end of debugging trick

    // remove new line in $mail_subject because it is not accepted
    if ($verbose)
        echo "<br>13 removing extra \\n from \$mail_subject<br>\n";

    //CZ_070619: Removing the newlines
    $mail_subject = str_replace("\n", "", $mail_subject);
    $mail_subject = str_replace("\r", "", $mail_subject);

    //CZ_070702: Let's check if the string isnt already in utf8
    if (!mb_check_encoding($mail_subject, "UTF-8")) {
        //CZ_070619: now encoding the subject
        $mail_subject = utf8_encode($mail_subject);
    }
    if (!is_object($From) && !is_array($From) && !mb_check_encoding($From, "UTF-8")) {
        $From = utf8_encode($From);
    }

    //Create the message
    $message = Swift_Message::newInstance()
        //Give the message a subject
        ->setSubject($mail_subject)

        //Set the From address with an associative array
        ->setFrom($From)

        //Set the To addresses with an associative array
        ->setTo($to);

    $config = HTMLPurifier_Config::createDefault();
    $config->set('Cache.SerializerPath', SCRIPT_BASE . 'data');
    $config->set('HTML.Allowed', 'a[href]');
    $purifier = new HTMLPurifier($config);
    $plain_text = $purifier->purify($plain_text);
    $message->setBody($plain_text);
    $message->addPart($plain_text, 'text/plain', 'utf-8');

    //attach the html if used.
    if ($use_html){
        $message->addPart($html_text, 'text/html', 'utf-8');
    }

    // Create transport
    $transport = Swift_SmtpTransport::newInstance('localhost', 25, false);

    // Create mailer using transport
    $mailer = Swift_Mailer::newInstance($transport);

    //send the message to the list of member in the mail
    $tolist = explode(";", $to);
    $ret = "";
    foreach ($tolist as $email) {
        $ret = $ret . $mailer->send($message);
    }

    if ($verbose) {
        echo "<br />14 <br />\n";
        echo "headers:\n";
        print_r($headers);
        echo "\n<br />to=", $to, "<br />\n";
        echo "subj=", $mail_subject, "<br />";
        echo "text :<i>", htmlentities($html_text), "</i><br />\n";
        echo " \$ret=", $ret, "<br />\n";
    }

    return ($ret);

} // end of bw_sendmail

?>
