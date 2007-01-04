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
    'title'             => 'Enregistrement',
    'label_username'    => "Nom d'utilisateur desiré:",
    'subline_username'  => 'Contenant au moins 4 charactères. Charactères permis: a-z, 0-9, "-". Commençant avec une lettre.',
    'label_email'       => 'Adresse internet:',
    'subline_email'     => "Il vous faudra une adresse internet valable pour effectuer l'enregistrement.",
    'label_password'    => 'Mot de passe:',
    'subline_password'  => 'Au moins 8 charactères.',
    'label_passwordc'   => 'Répetez le mot de passe:',
    'subline_passwordc' => '',
    'submit'            => "s'enregistrer",
    'finish_title'      => "L'enregistrement à été effectué avec succès",
    'finish_text'       => "Votre compte a été sauvegardé. Vous recevrez un courriel de comfirmation prochainement. Aillez l'obligeance de suivre le lien de ce courriel pour terminer votre enregistration.",
);
$errors = array(
    'username'   => "Vérifiez le syntaxe du nom d'utilisateur.",
    'uinuse'     => "Désolé, mais ce nom est déjà utilisé. Essayez un autre nom s'il vous plait.",
    'email'      => "Vérifiez l'adresse internet.",
    'einuse'     => "Désolé, mais cette adresse internet est déjà utilisée. Donnez une autre adresse s'il vous plait.",
    'pw'         => 'Vérifiez les mots de passe.',
    'pwmismatch' => 'Les mots de passe donnés ne sont pas identiques.',
    'inserror'   => "Votre demande n'a pas pu s'effectuer. Ayez l'obligeance de réessayer plus tard ou de contacter notre assistance technique.",
);
$registerMailText = array(
    'subject'    => "Votre enregistrement avec myTravelbook",
    'from_name'  => "service d'enregistrement myTravelbook",
    'message_body_html' => "
<p>Merci d'avoir enregistré un compte avec <a href='".PVars::getObj('env')->baseuri."'>myTravelbook</a>. A present il suffit de comfirmer votre adresse internet.</p>
<p>Cliquez le lien suivant pour comfirmer votre adresse:</p>
    ",
    'message_body_plain' => 
"Merci d'avoir enregistré un compte avec (".PVars::getObj('env')->baseuri."). A present il suffit de comfirmer votre adresse internet.

Cliquez le lien suivant pour comfirmer votre adresse:",
);
$confirmText = array(
    'error_title' => 'Erreur',
    'error_text'  => "Il y a eu une erreur avec l'activation. Vérifiez le lien et réessayez.",
    'confirm_title' => "C'est fini!",
    'confirm_text'  => "L'activation à été effectuée avec succès. Vous etes maintenant complêtement enregistré et avez accès à toutes fonctionalitées.",
);
?>