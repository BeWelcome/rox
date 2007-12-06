<?php

/**
 * Copyright (c) 2003, The Burgiss Group, LLC
 * This source code is part of eWiki LiveUser Plugin.
 *
 * eWiki LiveUser Plugin is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at your
 * option) any later version.
 *
 * eWiki LiveUser Plugin is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License
 * for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Wiki LiveUser Plugin; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once(dirname(__FILE__).'/pref_liveuser.php');

/*
 * key points to value which will later be used for the public variable; if there
 * is a default value, point to an array
 */
$prefs = array( 
    'Email' 	=> true,
    'FirstName'	=> true,
    'MiddleName'=> true,
    'LastName' 	=> true,
    'Company' 	=> true,
    'Phone' 	=> true,
    'Address' 	=> true,
    'City' 	=> true,
    'State' 	=> true,
    'ZipCode'	=> true,
    'Country' 	=> true
);

$prefs_default_value = array( 
    'Country'	=> 'USA'
);

$prefs_possible_values = array(
    'State' 	=> array(
	'Alabama',
	'Alaska',
	'Arizona',
	'Arkansas',
	'California',
	'Colorado',
	'Connecticut',
	'Delaware',
	'Florida',
	'Georgia',
	'Hawaii',
	'Idaho',
	'Illinois',
	'Indiana',
	'Iowa',
	'Kansas',
	'Kentucky',
	'Louisiana',
	'Maine',
	'Maryland',
	'Massachusetts',
	'Michigan',
	'Minnesota',
	'Mississippi',
	'Missouri',
	'Montana',
	'Nebraska',
	'Nevada',
	'New Hampshire',
	'New Jersey',
	'New Mexico',
	'New York',
	'North Carolina',
	'North Dakota',
	'Ohio',
	'Oklahoma',
	'Oregon',
	'Pennsylvania',
	'Rhode Island',
	'South Carolina',
	'South Dakota',
	'Tennessee',
	'Texas',
	'Utah',
	'Vermont',
	'Virginia',
	'Washington',
	'West Virginia',
	'Wisconsin',
	'Wyoming'
    )
);

// iterate through list of prefs and add them as user preference fields
foreach ($prefs as $pref => $public) {
    liveuser_pref_setField($pref, $public, $prefs_default_value[$pref], $prefs_possible_values[$pref]);
    echo 'Added field '.$pref."\n";	    
}

?>
