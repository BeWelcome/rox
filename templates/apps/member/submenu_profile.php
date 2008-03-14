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

	$IdMember = $m->id;
	?>
	  <div id="middle_nav" class="clearfix">
	    <div id="nav_sub">
	      <ul>
<?php
// disable these links for non fully activated members
if (($m->Status!='Pending') and ($m->Status!='NeedMore')  and ($m->Status!='MailToConfirm')) {
		echo "            <li ", factive($link, "member.php?cid=" . $IdMember), "><a href=\"".bwlink("member.php?cid=" . $IdMember)."\"><span>", ww('MemberPage'), "</span></a></li>\n";
}
	if (isset($_SESSION["IdMember"]) && $_SESSION["IdMember"] == $IdMember) { // if member's own profile
// disable these links for non fully activated members
if (($m->Status!='Pending') and ($m->Status!='NeedMore')  and ($m->Status!='MailToConfirm')) {
		echo "            <li", factive($link, "myvisitors.php"), "><a href=\"".bwlink("myvisitors.php")."\"><span>", ww("MyVisitors"), "</span></a></li>\n";
		echo "            <li", factive($link, "mypreferences.php?cid=" . $IdMember), "><a href=\"".bwlink("mypreferences.php?cid=" . $IdMember . "")."\"><span>", ww("MyPreferences"), "</span></a></li>\n";
}
		echo "            <li", factive($link, "editmyprofile.php"), "><a href=\"".bwlink("editmyprofile.php")."\"><span>", ww('EditMyProfile')," ",FlagLanguage(), "</span></a></li>\n";
	}
// disable these links for non fully activated members
if (($m->Status!='Pending') and ($m->Status!='NeedMore')  and ($m->Status!='MailToConfirm')) {    
	echo "            <li", factive($link, "viewcomments.php?cid=" . $IdMember), "><a href=\"".bwlink("viewcomments.php?cid=" . $IdMember, "")."\"><span>", ww('ViewComments'), "(", $m->NbComment, ")</span></a></li>\n";
}

// link to cs profile
if (($m->Status!='Pending') and ($m->Status!='NeedMore')  and ($m->Status!='MailToConfirm')) {    
	echo "            <li", factive($link, "membercs.php?cid=" . $IdMember), "><a href=\"".bwlink("membercs.php?cid=" . $IdMember, "")."\"><span>", ww('MeCS'), "</span></a></li>\n";
}
    // Deactivated in 0.1 release	echo "            <li", factive($link, "../blog"), "><a href=\"../blog/".$_SESSION["Username"]."\"><span>", ww("Blog"), "</span></a></li>\n"; 
	?>
