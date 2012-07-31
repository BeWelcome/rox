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
if (!is_array($pages) || count($pages) == 0) {
	return false;
}

$request = implode('/', $request);
$request = preg_replace('/\/page[0-9]+\/?/i', '', $request);
$request = $request.'/page%d/';

?>

<div class="pages">
	<ul>
		<li>

<?php

if ($currentPage != 1) {

?>
			
			<a href="<?=sprintf($request, ($currentPage - 1))?>">&laquo;</a>

<?php

} else {
	echo '<a class="off">&laquo;</a>';
}
?>
		</li>
<?php
foreach ($pages as $page) {
	if (!is_array($page)) {
		echo '<li class="sep">...</li>';
		continue;
	}
	if (!isset($page['current'])) {
		echo '<li>';
		echo '<a href="'.sprintf($request, $page['pageno']).'">';
		echo $page['pageno'];
		echo '</a>';
		echo '</li>';
	} else {
		echo '<li class="current"><a class="off">'.$page['pageno'].'</a></li>';
	}
}
?>
		<li>
<?php
if ($currentPage != $maxPage) {
?>
			<a href="<?=sprintf($request, ($currentPage + 1))?>">&raquo;</a>
<?php
} else {
	echo '<a class="off">&raquo;</a>';
}
?>
		</li>
	</ul>
</div> <!-- pages -->
