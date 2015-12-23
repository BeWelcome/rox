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
if (is_array($pages) && count($pages) > 0) {
    $req = htmlspecialchars(implode('/', $request), ENT_QUOTES);
    $req = preg_replace('/\/page[0-9]+/i', '', $req);
    $req = $req.'/page%d/';
} elseif (is_array($multipages)) {
    $req = htmlspecialchars(implode('/', $request), ENT_QUOTES);
    $req = preg_replace('/\/page[0-9]+/i', '', $req);
    foreach ($multipages as $mp) {
        if (is_array($mp) && count($mp) > 0) {
            $pages = $mp;
            $req = $req.'/page%d';
        } elseif (is_int($mp)) {
            $req = $req.'/page'.$mp;
        } else {
            return false;
        }
    }
    $req = $req.'/';
} else {
    return false;
}

?>

<div class="pages clearfix">
	<ul class="pagination m-t-0 m-b-0 pull-xs-right">
		

<?php

if ($currentPage != 1) {

?>
			
			<li class="page-item"><a class="page-link" href="<?=sprintf($req, ($currentPage - 1))?>">&laquo;</a></li>

<?php

} else {
	echo '<li class="pager-previous page-item disabled"><a class="page-link">&laquo;</a></li>';
}
?>
		
<?php
foreach ($pages as $page) {
	if (!is_array($page)) {
		echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
		continue;
	}
	if (!isset($page['current'])) {
		echo '<li class="page-item">';
		echo '<a class="page-link" href="'.sprintf($req, $page['pageno']).'">';
		echo $page['pageno'];
		echo '</a>';
		echo '</li>';
	} else {
		echo '<li class="page-item active"><a class="page-link">'.$page['pageno'].'</a></li>';
	}
}
?>
		
<?php
if ($currentPage != $maxPage) {
?>
			<li class="page-item"><a  class="page-link" href="<?=sprintf($req, ($currentPage + 1))?>">&raquo;</a></li>
<?php
} else {
	echo '<li class="page-item disabled"><a class="page-link">&raquo;</a></li>';
}
?>
	</ul>
</div> <!-- pages -->
