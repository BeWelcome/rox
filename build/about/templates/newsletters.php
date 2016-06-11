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
write to the Free Software Foundation, Inc., 59 Temple PlaceSuite 330, 
Boston, MA  02111-1307, USA.

*/
$words = new MOD_words();

$A = new APP_User();
if ($a->isBWLoggedIn('NeedMore,Pending')) {
    $Username = $this->_session->get("Username");
}
else { 
    $Username = "guest";
}
$news_items = array(
					"June2009",
					"February2009",
					"September2008",
                    "April2008",
                    "February2008",
                    "October2007",
                    "July2007",
                    );
?>

<div class="info">
<?php

foreach ($news_items as $item) {
    ?>
    <?=$words->get("BroadCast_Title_News" . $item, $Username) ?><br /><br />
    <?=$words->get("BroadCast_Body_News" . $item, $Username) ?><hr />
    <?php
}
?>
</div>