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
?>

<div id="teaser" class="page-teaser clearfix">

<div id="teaser_tour" style="width: 700px">
<?php
    echo "<h1 class=\"float_left\">", $words->get('thetourpage'),"</h1><img src=\"images/misc/steps.gif\" alt=\"steps\" />\n";
?>
<?php   //if ($step)
    //echo "<h2>", $words->get($title.'desc_'.$step),"</h2>\n"; // Needs to be something like "Go, travel the world!"
?>
</div>
<!--<div id="teaser_r">
</div>-->
</div>
