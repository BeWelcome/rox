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
require_once "lib/init.php";
require_once "layout/error.php";
require_once "layout/viewcomments.php";
require_once "lib/prepare_profile_header.php";

$_defaultIDMember = "";
if (isset($_SESSION['IdMember'])) {
    $_defaultIDMember = $_SESSION['IdMember'];
}

$IdMember = GetParam("cid", $_defaultIDMember);
$photorank = 0; // Alway use picture 0 of view comment 

switch (GetParam("action")) {
}

if (!IsPublic($IdMember))
	MustLogIn();

$_defaultWhereStatus = "";
if (isset($wherestatus)) {
    $_defaultWhereStatus = $wherestatus;	
}

$m = prepareProfileHeader($IdMember, $_defaultWhereStatus);

// Try to load the Comments, prepare the layout data
$rWho = LoadRow("select * from members where id=" . $IdMember);
$str = "select comments.*,members.Username as Commenter from comments,members where IdToMember=" . $IdMember . " and members.id=comments.IdFromMember order by updated DESC;";
$qry = mysql_query($str);
$TCom = array ();
while ($rr = mysql_fetch_object($qry)) {
	$photo=LoadRow("select SQL_CACHE * from membersphotos where IdMember=" . $rr->IdFromMember . " and SortOrder=0");
	if (isset($photo->FilePath)) $rr->photo=$photo->FilePath; 
	array_push($TCom, $rr);
}

DisplayComments($m, $TCom); // call the layout
?>