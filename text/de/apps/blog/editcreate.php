<?php
/**
 * internationalization settings for blog
 *
 * @package il8n_de
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: editcreate.php 9 2007-03-06 15:21:54Z won_gak $
 */
$lang = array(
    // page
    'page_title_create' => 'Beitrag erstellen',
    'page_title_edit'   => 'Beitrag bearbeiten',

    // form
    'label_title'       => 'Titel',
    'label_text'        => 'Text',

    'legend_connections'=> 'Verbindungen',
    'label_trip'        => 'Reise',
    'label_categories'  => 'Kategorien',
    'label_country'     => 'Land',
    'label_tags'        => 'Tags',
    'subline_tags'      => 'Geben Sie hier Stichwörter ein, die Ihren Beitrag beschreiben. Kommagetrennt.',
    'subline_location'  =>  'Bitte geben Sie den Ort ein, von welchem dieser Blogeintrag handelt.',
    'hint_click_location' => 'Bitte klicken Sie den passenden Ort an.',
    'label_search_location' => 'Suchen',

    'legend_timeline'   => 'Zeit',
    'label_startdate'   => 'Start Datum',
    'subline_startdate' => 'Bitte ein gültiges Datum eintragen: JAHR-MONAT-TAG',
    'label_enddate'     => 'End Datum',
    'subline_enddate'   => 'Bitte ein gültiges Datum eintragen: JAHR-MONAT-TAG',
    'label_or_duration' => 'oder Dauer',
    
    'legend_settings'   => 'Einstellungen',
    'label_flag_sticky' => 'Erscheint auf der Startseite',
    
    // visibility settings
    'label_vis'                => 'Sichtbarkeit',
    'label_vispublic'          => 'öffentlich',
    'description_vispublic'    => 'jeder Besucher kann diesen Beitrag sehen',
    'label_visprotected'       => 'geschützt',
    'description_visprotected' => 'nur Freunde können diesen Beitrag sehen',
    'label_visprivate'         => 'privat',
    'description_visprivate'   => 'nur Sie selbst können diesen Beitrag sehen',

    'submit_create'     => 'erstellen',
    'submit_edit'       => 'speichern',

    // success
    'finish_create_title'      => 'Ihr Beitrag wurde erfolgreich angelegt',
    'finish_create_text'       => '',
    'finish_create_info'       => '',
    'finish_edit_title'      => 'Ihr Beitrag wurde erfolgreich bearbeitet',
    'finish_edit_text'       => '',
    'finish_edit_info'       => '',

    // other
    'no_trip'           => 'keine Reise',
    'no_category'       => 'keine Kategorie',
    'create_new_trip'    => 'Neue Reise anlegen',
    'create_new_category'=> 'Neue Kategorie anlegen',
    'days'               => 'Tage'
);



$errors = array(
    'inserror'      => 'Ihre Anfrage konnte leider nicht bearbeitet werden. Bitte versuchen Sie es zu einem späteren Zeitpunkt oder wenden Sie sich an unseren Support.',
    'upderror'      => 'Ihre Anfrage konnte leider nicht bearbeitet werden. Bitte versuchen Sie es zu einem späteren Zeitpunkt oder wenden Sie sich an unseren Support.',
    'title'         => 'Der Titel darf nicht leer sein.',
    'text'          => 'Der Text darf nicht leer sein.',
    'startdate'     => 'Das Startdatum ist leider fehlerhaft.',
    'enddate'       => 'Das Enddatum ist leider fehlerhaft.',
    'duration'      => 'Bitte tragen Sie ein gültiges Enddatum ein.',
    'category'      => 'Ungültige Kategorie.',
    'trip'          => 'Ungültige Reise.',
    'not_logged_in' => 'Sie müssen eingeloggt sein um Beiträge zu verfassen/bearbeiten.',
    'post_not_found'=> 'Der Beitrag existiert leider nicht.'
);
?>
