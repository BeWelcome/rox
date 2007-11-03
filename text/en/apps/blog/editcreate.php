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
    'page_title_create' => 'Create blogentry',
    'page_title_edit'   => 'Edit blogentry',

    // form
    'label_title'       => 'Title',
    'label_text'        => 'Text',

    'legend_connections'=> 'Connections',
    'label_trip'        => 'Trip',
    'label_categories'  => 'Categories',
    'label_country'     => 'Country',
    'label_location'    => 'Location',
    'label_tags'        => 'Tags',
    'subline_tags'      => 'Specify words that associate to this blogentry, comma separated.',
    'subline_location'  =>  'Please enter the location this blogentry is about.',
    'hint_click_location' => 'Please click the matching location.',
    'label_search_location' => 'Search',

    'legend_timeline'   => 'Timeline',
    'label_startdate'   => 'Start date',
    'subline_startdate' => 'Enter a valid date: YEAR-MONTH-DAY',
    'label_enddate'     => 'End date',
    'subline_enddate'   => 'Enter a valid date: YEAR-MONTH-DAY',
    'label_or_duration' => 'or duration',
    
    'legend_settings'   => 'Settings',
    'label_flag_sticky' => 'Appears on the startpage',
    
    // visibility settings
    'label_vis'                => 'Privacy',
    'label_vispublic'          => 'public',
    'description_vispublic'    => 'any visitor may see this entry',
    'label_visprotected'       => 'protected',
    'description_visprotected' => 'only friends may see this entry',
    'label_visprivate'         => 'private',
    'description_visprivate'   => 'only you may see this entry',
    
    'submit_create'     => 'create',
    'submit_edit'       => 'save',

    // success
    'finish_create_title'      => 'Entry creation successful',
    'finish_create_text'       => '',
    'finish_create_info'       => '',
    'finish_edit_title'      => 'Blog was updated',
    'finish_edit_text'       => '',
    'finish_edit_info'       => '',

    // other
    'no_trip'           => 'no trip',
    'no_category'       => 'no category',
    'create_new_trip'    => 'create new trip',
    'create_new_category'=> 'create new category',
    'days'               => 'days'
);



$errors = array(
    'inserror'      => 'Your request could not be performed. It is most likely that doing the same thing again will produce the same error. Please contact our support.',
    'upderror'      => 'Your request could not be performed. It is most likely that doing the same thing again will produce the same error. Please contact our support.',
    'title'         => 'Title cannot be empty.',
    'text'          => 'Text cannot be empty.',
    'startdate'     => 'I cannot recognize your start date.',
    'enddate'       => 'I cannot recognize your end date.',
    'duration'      => 'Submit a valid duration or end date.',
    'category'      => 'Invalid category.',
    'trip'          => 'Invalid trip.',
    'not_logged_in' => 'You must log in first to create a new post.',
    'post_not_found'=> 'I cannot find this blogentry.'
);

?>
