<?php
/**
 * internationalization user registration
 *
 * @package user
 * @subpackage il8n_de
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: register.php 9 2007-03-06 15:21:54Z won_gak $
 */
$regText = array(
    'title'             => 'Registrieren',
    'label_username'    => 'Ihr gewünschter Benutzername:',
    'subline_username'  => 'Mind. 4 Zeichen. Erlaubte Zeichen: a-z, 0-9, "-". Beginnend mit einem Buchstaben.',
    'label_email'       => 'E-Mail Adresse:',
    'subline_email'     => 'Sie müssen eine gültige E-Mail Adresse angeben, um Ihre Registrierung abzuschließen.',
    'label_password'    => 'Passwort:',
    'subline_password'  => 'Mind. 8 Zeichen.',
    'label_passwordc'   => 'Passwort wiederholen:',
    'subline_passwordc' => '',
    'submit'            => 'registrieren',
    'finish_title'      => 'Registrierung erfolgreich',
    'finish_text'       => 'Ihre Daten wurden erfolgreich gespeichert. In Kürze erhalten Sie eine Mail mit einem Freischaltlink. Nach dem Öffnen dieser Seite ist Ihr Account freigeschaltet.',
);
$errors = array(
    'username'   => 'Bitte überprüfen Sie den Benutzernamen.',
    'uinuse'     => 'Der Benutzername ist leider in Verwendung. Bitte versuchen Sie es mit einem anderen.',
    'email'      => 'Bitte überprüfen Sie die E-Mail Adresse.',
    'einuse'     => 'Die E-Mail Adresse ist leider in Verwendung. Bitte versuchen Sie es mit einer anderen.',
    'pw'         => 'Bitte überprüfen Sie die Passwörter.',
    'pwmismatch' => 'Die eingegebenen Passwörter stimmen leider nicht überein.',
    'inserror'   => 'Ihre Daten konnten leider nicht gespeichert werden. Bitte versuchen Sie es zu einem späteren Zeitpunkt noch einmal, oder wenden Sie sich an den Support.',
);
$registerMailText = array(
    'subject'    => 'Ihre myTravelbook Registrierung',
    'from_name'  => 'myTravelbook Registrierung',
    'message_body_html' => '
<p>Vielen Dank für Ihre Registrierung. Nun fehlt nur noch ein Schritt, damit Sie alle Funktionen von <a href="'.PVars::getObj('env')->baseuri.'">myTravelbook</a> nutzen können.</p>
<p>Bitte öffnen Sie die folgende Seite in Ihrem Browser. Nach dieser Verifikation haben Sie Zugriff auf alle Funktionen.</p>
    ',
    'message_body_plain' => 
'Vielen Dank für Ihre Registrierung. Nun fehlt nur noch ein Schritt, damit Sie alle Funktionen von myTravelbook ('.PVars::getObj('env')->baseuri.') nutzen können.

Bitte öffnen Sie die folgende Seite in Ihrem Browser. Nach dieser Verifikation haben Sie Zugriff auf alle Funktionen.',
);
$confirmText = array(
    'error_title'   => 'Fehler',
    'error_text'    => 'Die Aktivierung Ihres Accounts ist fehlgeschlagen. Bitte überprüfen Sie den Link und versuchen Sie es erneut.',
    'confirm_title' => 'Fertig!',
    'confirm_text'  => 'Die Aktivierung war erfolgreich. Damit ist die Registrierung abgeschlossen, und Sie haben ab sofort Zugang zu allen Funktionen.',
);
?>