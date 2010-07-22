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
/** 
 * @author Matthias Heß <globetrotter_tt>
 */
$words = new MOD_words();

?>

<h3>Links</h3>
<ul class="linklist">
    <li><a href="groups/60/wiki">How it works</a></li>
    <li><a href="groups/60">Translation Group</a></li>
    <li><a href="admin/words">Admin word</a></li>
    <li><a href="admin/words/important">FIXME Important words</a></li>
    <li><a href="admin/words/ShowLanguageStatus=". $rr->id)."\">FIXME All in ". $rr->EnglishName. "</a></li>
    <li><a href="admin/words/onlymissing&ShowLanguageStatus=". $rr->id)."\">FIXME Only missing in ". $rr->EnglishName. "</a></li>
    <li><a href="admin/words/onlyobsolete&ShowLanguageStatus=". $rr->id)."\">FIXME Only obsolete in ". $rr->EnglishName. "</a></li>
    <li><a href="admin/words/stats">FIXME Statistic</a></li>
    <li><a href="admin/words/memcache">FIXME Show memcache</a></li>
</ul>

<h3>Shouts from the team</h3>
<?php
// Displaying Shouts for Accepter Team
$shoutsCtrl = new ShoutsController;
$shoutsCtrl->format = 'compact';
$shoutsCtrl->shoutsList('admin_words', 1);
?>
