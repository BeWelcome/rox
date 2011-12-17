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

/**
 * @author Micha (bw/cs:lupochen)
 * @author Meinhard (bw:planetcruiser)
 */

/**
 * MOD_mails lets you create & send mails using our default template
 *
 * @package Modules
 * @subpackage Mail
 */
class MOD_mail
{

    /**
     * Singleton instance
     *
     * @var MOD_layoutbits
     * @access private
     */
    private static $_instance;


    /**
     * singleton getter
     *
     * @param void
     * @return PApps
     */
    private static function init()
    {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }

    private function __construct()
    {
        // Load the files we'll need
        require_once SCRIPT_BASE . "lib/misc/swift-mailer/lib/swift_required.php";
    }

    private function __clone() {}

    private static function getSwift()
    {
        return Swift_Message::newInstance();
    }

    private static function sendSwift($message)
    {
        // Read config section [smtp]
        $config = PVars::getObj('config_smtp');

        // Currently only SMTP backend is supported
        if ($config->backend == 'smtp') {
            if ($config->host) {
                $host = $config->host;
            } else {
                $host = 'localhost';
            }

            if ($config->port) {
                $port = $config->port;
            } else {
                $port = 25;
            }

            if ($config->tls) {
                $tls = 'tls';
            } else {
                $tls = null;
            }

            // Create transport
            $transport = Swift_SmtpTransport::newInstance($host, $port, $tls);

            if ($config->auth && $config->username && $config->password) {
                $transport->setUsername($config->username);
                $transport->setPassword($config->password);
            }

            // Create mailer using transport
            $mailer = Swift_Mailer::newInstance($transport);

            // Log if debug is enabled
            if ($config->debug) {
                $logger = new Swift_Plugins_Loggers_EchoLogger();
                $mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));
            }
            return $mailer->batchSend($message);
        } else {
            return false;
        }
    }

    public static function sendEmail($subject, $from, $to, $title = false, $body, $body_html = false, $attach = array(), $language = 'en')
    {
        self::init();

        // Check that $to/$from are both arrays
        $from = (is_array($from)) ? $from : explode(',', $from);        
        $to = (is_array($to)) ? $to : explode(',', $to);

        //Create the message
        $message = self::getSwift()

          //Give the message a subject
          ->setSubject($subject)

          //Set the From address with an associative array
          ->setFrom($from)

          //Set the To addresses with an associative array
          ->setTo($to)

          //Give it a body
          ->setBody($body)
        ;

        // Translate footer text (used in HTML template)
        $words = new MOD_words();
        $footer_message = $words->getPurified('MailFooterMessage', array(date('Y')), $language);

        // Using a html-template
        ob_start();
        require SCRIPT_BASE.'templates/shared/mail_html.php';
        $mail_html = ob_get_contents();
        ob_end_clean();
        // Add the html-body
        $message->addPart($mail_html, 'text/html');

        //Optionally add any attachments
        if (!empty($attach)) {
            foreach ($attach as $path) {
                $message->attach(Swift_Attachment::fromPath($path));
            }
        }

        return self::sendSwift($message);
    }

}
