<?php
/**
 * internationalization settings for blog
 *
 * @package il8n_en
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: editcreate.php 9 2007-03-06 15:21:54Z won_gak $
 */
$lang = array(
    // page
    'page_title_create' => 'Créer une entrée blog',
    'page_title_edit'   => 'Editer une entrée blog',

    // form
    'label_title'       => 'Titre',
    'label_text'        => 'Texte',

    'legend_connections'=> 'Connéctions',
    'label_trip'        => 'Voyage',
    'label_categories'  => 'Catégories',
    'label_country'     => 'Pays',
    'label_tags'        => 'Etiquettes',
    'subline_tags'      => 'Spécifier des mots associés à cette entrée blog, séparés par virgules.',

    'legend_timeline'   => 'Chronologie',
    'label_startdate'   => 'Date de départ',
    'subline_startdate' => 'Entrez une date dans ce format: ANNÉE-MOI-JOUR',
    'label_enddate'     => 'Date finale', // date de retour ??
    'subline_enddate'   => 'Entrez une date dans ce format: ANNÉE-MOI-JOUR',
    'label_or_duration' => 'ou durée',
    
    'legend_settings'   => 'Paramètres',
    'label_flag_sticky' => "Apparaît sur la page d'accueil",
    
    // visibility settings
    'label_vis'                => 'Confidentialitée',
    'label_vispublic'          => 'publique',
    'description_vispublic'    => 'tout le monde peut voir cette entrée',
    'label_visprotected'       => 'protégée',
    'description_visprotected' => 'seulement vos amis peuvent voir cette entrée',
    'label_visprivate'         => 'privée',
    'description_visprivate'   => 'seulement vous pouvez voir cette entrée',
    
    'submit_create'     => 'créer',
    'submit_edit'       => 'editer',

    // success
    'finish_create_title'      => "L'entrée fus créé",
    'finish_create_text'       => '',
    'finish_create_info'       => '',
    'finish_edit_title'      => 'Le blog à été mis à jour',
    'finish_edit_text'       => '',
    'finish_edit_info'       => '',

    // other
    'no_trip'           => 'aucun voyage',
    'no_category'       => 'aucune catégorie',
    'create_new_trip'    => 'créer un nouveau voyage',
    'create_new_category'=> 'créer une nouvelle catégorie',
    'days'               => 'jours'
);



$errors = array(
    'inserror'      => "Votre demande ne peut pas être effectuée. Il est peu probable que la même opération réuississe. Ayez l'obligeance de contacter notre assistance technique.",
    'upderror'      => "Votre demande ne peut pas être effectuée. Il est peu probable que la même opération réuississe. Ayez l'obligeance de contacter notre assistance technique.",
    'title'         => "Le titre n'est pas rempli.",
    'text'          => 'Aucun texte donné.',
    'startdate'     => 'Je ne comprends pas la date de départ.',
    'enddate'       => 'Je ne comprends pas la date finale.',
    'duration'      => 'Il me faut une date ou une durée valable.',
    'category'      => 'La catégorie est invalide.',
    'trip'          => 'Le voyage est invalide.',
    'not_logged_in' => "Il faut d'abord vous connecter avant de créer un nouveau post.",
    'post_not_found'=> 'Cette entrée est introuvable.'
);

?>
