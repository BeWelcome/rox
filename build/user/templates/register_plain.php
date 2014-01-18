<?php
/**
 * Template plaintext registration confirm mail
 * 
 * This template needs the following variables to be set:
 * $registerMailText - Text array (./text/[lang]/apps/user/mail/register.php)
 * $confirmUrl       - The URL, which opens the confirmation page
 * 
 * @package user_templates
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: user.view.php 66 2006-06-22 17:16:52Z kang $
 */
$mailText = str_repeat('=', strlen($registerMailText['subject']))."\n";
$mailText .= $registerMailText['subject']."\n";
$mailText .= str_repeat('=', strlen($registerMailText['subject']))."\n\n";
$mailText .= $registerMailText['message_body_plain'];
$mailText .= '

**
  '.$confirmUrl.'
**
';