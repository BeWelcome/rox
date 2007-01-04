<?php
/**
 * internationalization user registration
 *
 * @package il8n_en
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
$regText = array(
    'title'             => 'Registration',
    'label_username'    => 'Your desired username:',
    'subline_username'  => 'At least 4 characters. Allowed characters: a-z, 0-9, "-". Starting with a letter.',
    'label_email'       => 'E-Mail address:',
    'subline_email'     => 'You will need a valid e-mail address to complete the registration.',
    'label_password'    => 'Password:',
    'subline_password'  => 'At least 8 characters.',
    'label_passwordc'   => 'Repeat the password:',
    'subline_passwordc' => '',
    'submit'            => 'register',
    'finish_title'      => 'Registration successful',
    'finish_text'       => 'Your settings have been saved. You will receive a confirmation mail shortly. Please follow the link in the mail to finish you registration process.',
);
$errors = array(
    'username'   => 'Please check the username syntax.',
    'uinuse'     => 'Unfortunately this username is already in use. Please choose another one.',
    'email'      => 'Please check the e-mail address.',
    'einuse'     => 'Unfortunately this e-mail address is already in use. Please choose another one.',
    'pw'         => 'Please check the passwords.',
    'pwmismatch' => 'The submitted passwords do not match.',
    'inserror'   => 'Your request could not be performed. Please try again later or contact our support.',
);
$registerMailText = array(
    'subject'    => 'Your registration with myTravelbook',
    'from_name'  => 'myTravelbook registration service',
    'message_body_html' => '
<p>Thank you for registering with <a href="'.PVars::getObj('env')->baseuri.'">myTravelbook</a>. You will need to confirm your e-mail address.</p>
<p>Please click on the following link to confirm your e-mail address:</p>
    ',
    'message_body_plain' => 
'Thank you for registering with myTravelbook ('.PVars::getObj('env')->baseuri.'). You will need to confirm your e-mail address.

Please click on the following link to confirm your e-mail address:',
);
$confirmText = array(
    'error_title' => 'Error',
    'error_text'  => 'A problem with the activation process occured. Please check the link and try again.',
    'confirm_title' => 'Finished!',
    'confirm_text'  => 'The activation ended successfully. You are now fully registered and may proceed with all functions.',
);
?>