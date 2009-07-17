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
 * MOD_mails lets you create & send mails using our default template
 * + It gives you access to SWIFT too
 *
 * @author Micha (bw/cs:lupochen)
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
    private $message;
    private $from;

    public function __construct()
    {
        // Load the files we'll need
        require_once SCRIPT_BASE."lib/misc/swift-mailer/lib/swift_required.php";
        
        // Add some email addresses
        $this->from = PVars::getObj('mailAddresses')->default;
    }

    /**
     * singleton getter
     *
     * @param void
     * @return PApps
     */
    public static function get()
    {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }
    
    public function send()
    {
        if (!isset($this->message)) return false;

        //Create the Transport
        $transport = Swift_SmtpTransport::newInstance('localhost', 25);
        
        // FOR TESTING ONLY (using Gmail SMTP Connection for example):
        // $transport = Swift_SmtpTransport::newInstance("smtp.gmail.com", 465, 'tls');
        // $transport->setUsername("USERNAME");
        // $transport->setPassword("PASSWORD");
        $transport = Swift_SmtpTransport::newInstance("smtp.gmail.com", 465, 'tls');
        $transport->setUsername("micha.dettbarn");
        $transport->setPassword("muck2...");
        
        //Create the Mailer using your created Transport
        $mailer = Swift_Mailer::newInstance($transport);
        
        // FOR TESTING ONLY
        // $logger = new Swift_Plugins_Loggers_EchoLogger();
        // $mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));
        
        return $mailer->send($this->message);
    }
    
    public function getMessageHTML($subject, $from, $to, $title = false, $body, $body_html = false, $attach = array()) 
    {
        // Check that $to/$from are both arrays
        $from = (is_array($from)) ? $from : explode(',', $from);        
        $to = (is_array($to)) ? $to : explode(',', $to);
        
        //Create the message
        $message = Swift_Message::newInstance()

          //Give the message a subject
          ->setSubject($subject)

          //Set the From address with an associative array
          ->setFrom($from)

          //Set the To addresses with an associative array
          ->setTo($to)

          //Give it a body
          ->setBody($body)
        ;

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
        $this->message = $message;
    }

}
