<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/
$words = new MOD_words();
$countrycode = htmlspecialchars($countrycode);
$region = htmlspecialchars($region);
if (isset($country->name)){$country->name = htmlspecialchars($country->name);}
$city = htmlspecialchars($city);
?>

<div id="teaser" class="page-teaser clearfix">
<h1>
    <?php
    if (!$countrycode OR !isset($country->name)) { 
        echo $words->getFormatted('CountryTitle');
    } else { 
        echo '<a href="places">'.$words->getFormatted('CountryTitle').'</a>';
        echo '<span class="small">';
        if (!$region) { 
            echo ' &raquo; '.$country->name;
        } else {
            echo ' &raquo; <a href="places/'.$countrycode.'">'.$country->name.'</a>'; 
            if (!$city) { 
                echo ' &raquo; '.$region;
            } else {
                echo ' &raquo; <a href="places/'.$countrycode.'/'.$region.'">'.$region.'</a>'; 
                echo ' &raquo; '.$city;
            }
        }
        echo '</span>';
    }
    ?>
</h1> 
<?php
if (isset($title)) {
    if (MOD_right::get()->HasRight('Debug')) {  ?>
        <h2>
        <?php
        echo $title; 
        // This is only visible to people with debug rights
        echo " <a href=\"geo/displaylocation/".$countryinfo->IdCountry."\" title=\" specific debug right view database records\">view geo record #".$countryinfo->IdCountry."</a>" ;
        ?></h2>
        <?php
    }
}
?>
</div>