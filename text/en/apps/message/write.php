<?php
/**
 * internationalization settings for msg
 *
 * @package il8n_en
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: write.php 9 2007-03-06 15:21:54Z won_gak $
 */


$writeText = array(
    'title_write'       => 'Write private message',
    'legend_recipient'  => 'Recipient',
    'label_recipient'   => 'Username(s)',
    'desc_recipient'    => 'You may specify several recipients separated by comma.<br />'.
                           'Note that for the usersearch feature to work you need<br />'.
                           'to specify at least 4 characters. (a-z, 0-9, "-", "_", ".")',
    'label_similar_recipients' => 'Similar recipients found',
    'hint_similar_recipients' => '(click to add)',
    'submit_validate'   => 'Next',

    'label_subject'     => 'Subject',
    'label_text'        => 'Text',
    'label_store_outbox'=> 'Store in sent messages',
    'legend_message'    => 'Message',
    'submit_send'       => 'Send',

    'verified_recipients' => 'Verified recipients',
    'finish_write_title'      => 'Message sent successfully',
    'finish_write_text'       => '',
    'finish_write_info'       => '',
    );

$errorText = array(
    'not_sent'      => 'Your message was not sent. Sorry. Please try again later.',
    'repicient_max' => 'Maximum allowed recipients is 10.',
    'subject'       => 'Subject cannot be empty.',
    'text'          => 'Text cannot be empty.',
    'recipient'     => 'You had invalid/incomplete recipients which have been removed.',
    'not_logged_in' => 'You must log in first to write a message.',
    );


?>
