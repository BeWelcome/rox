<?php
/**
 * internationalization user settings
 *
 * @package il8n_en
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
$settingsText = array(
    'title'                        => 'My settings',
    'legend_avatar'                => 'Avatar',
    
    'title_avatar'                 => 'Avatar',
    'label_avatar'                 => 'Choose your avatar',
    'desc_avatar'                  => 'GIF, JPEG or PNG',
    'avatar_submit'                => 'upload',
    
    'legend_password'              => 'Password',
    'title_password'               => 'Password',
    'label_password'               => 'Password:',
    'description_password'         => 'If you want to change your password, please enter a new password here.',
    'label_password_confirm'       => 'Confirm new password:',
    'description_password_confirm' => '',
    'submit_save'                  => 'save changes',

    'legend_profile'               => 'About me',
    'title_location'               => 'Location',
    'current_location'             => 'Current Location',
    'label_location'               => 'New Location',
    'description_location'         => 'If you moved to a new location, please enter it here',
    'submit_save_location'         => 'save new location',
    'label_search_location'		   => 'Search',
);
$errorText = array(
    'not_logged_in'        => 'You must be logged in to change your user settings.',
    'pwlength'             => 'Your password must consist of at least 8 characters.',
    'pwc'                  => 'Please confirm the password you entered.',
    'pwmismatch'           => 'The passwords you entered do not match.',
    'password_not_updated' => 'Your password could not be updated. Please try again later or contact our support.',
    'location_not_updated' => 'Your location could not be updated. Please try again later or contact our support.',
);
$messageText = array(
    'password_updated' => 'Your password was successfully updated and valid from now on.',
    'location_updated' => 'Your location was updated.',
);
?>