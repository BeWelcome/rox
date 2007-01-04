<?php
/**
 * internationalization settings for msg
 *
 * @package il8n_en
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */


$writeText = array(
    'title_write'       => 'Private Mitteilung schreiben',
    'legend_recipient'  => 'Empf&auml;nger',
    'label_recipient'   => 'Benutzername(n)',
    'desc_recipient'    => 'Sie k&ouml;nnen mehrere Empf&auml;nger angeben, mit Komma getrennt.<br />'.
                           'Die Suche nach Benutzernamen braucht mindestens 4 Buchstaben.<br />'.
                           '(Erlaubte Buchstaben: a-z, 0-9, "-", "_", ".")',

    'label_similar_recipients' => '&Auml;hnliche Benutzernamen gefunden',
    'hint_similar_recipients' => '(klicken zum hinzuf&uuml;gen)',
    'submit_validate'   => 'Weiter',

    'legend_message'    => 'Mitteilung',
    'label_subject'     => 'Betreff',
    'label_text'        => 'Text',
    'label_store_outbox'=> 'Speichern',
    'submit_send'       => 'Senden',

    'verified_recipients' => 'Verifizierte Empf&auml;ngner',
    'finish_write_title'      => 'Mitteilung erfolgreich gesendet',
    'finish_write_text'       => '',
    'finish_write_info'       => '',
    );

$errorText = array(
    'not_sent'      => 'Ihre Mitteilung wurde nicht gesendet, probieren Sie es sp&auml;ter nochmals.',
    'repicient_max' => 'Sie k&ouml;nnen maximal 10 Empf&auml;nger angeben.',
    'subject'       => 'Betreff darf nicht leer sein.',
    'text'          => 'Text darf nicht leer sein.',
    'recipient'     => 'Sie hatten ung&uuml;ltige Empf&auml;nger, welche entfernt wurden.',
    'not_logged_in' => 'Sie müssen eingeloggt sein um Beiträge zu verfassen/bearbeiten.',
    );

?>
