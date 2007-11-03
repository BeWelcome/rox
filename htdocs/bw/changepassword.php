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
require_once "lib/FunctionsLogin.php";
require_once "layout/error.php";
require_once "layout/changepassword.php";
require_once "layout/main.php";

$action = GetParam("action");
$password = trim(GetParam("NewPassword"));
$OldPassword = trim(GetParam("OldPassword"));
$SecPassword = trim(GetParam("SecPassword"));

MustLogIn();

$CurrentError = "";
switch ($action) {
	case "logout" :
		Logout();
		exit (0);
	case "changepassword" :
		$rCheckId = LoadRow("select id from members where id=" . $_SESSION["IdMember"] . " and PassWord=PASSWORD('" . $OldPassword . "')");

		if (!isset ($rCheckId->id))
			$CurrentError .= ww('BadPassworErrorCheck') . "<br />";
		if ((($password != $SecPassword) or ($password == "")) or (strlen($password) < 8))
			$CurrentError .= ww('SignupErrorPasswordCheck') . "<br />";

		if ($CurrentError != "") {
			DisplayChangePasswordForm($CurrentError); // call the layout
			exit (0);
		}

		$str = "update members set password=PASSWORD('" . $password . "') where id=" . $_SESSION["IdMember"];
		sql_query($str);
		LogStr("changing password", "change password");

		$m = LoadRow("select * from members where id=" . $_SESSION["IdMember"]);
		DisplayMain($m, ww("PasswordSuccesfulyChanged", $m->Username));
		exit (0);
		break;
}

DisplayChangePasswordForm($CurrentError); // call the layout
?>
