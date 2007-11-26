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


require_once ("menus.php");

function DisplayChangePasswordForm($CurrentError) {
	global $title;
	$title = ww('ChangePasswordPage');
	require_once "header.php";

	Menu1("", ww('ChangePasswordPage')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]);
?>
    <div id="main">
        <div id="teaser_bg">
            <div id="teaser" class="clearfix">
                <div id="title">    
                    <h1><?php echo ww("ChangePasswordPage") ?></h1>
                </div>
            </div>
        </div>
<?php
	ShowActions(); // Show the actions
	ShowAds(); // Show the Ads
?>
    <div id="col3">
        <div id="col3_content">
            <div class="info">
<?php	
	if ($CurrentError != "") { ?>
    <p class="error"><?php echo $CurrentError ?></p>
<?php
	}
?>
                <form method="post">                    
                    <input type="hidden" name="action" value="changepassword" />
                    <ul class="form">
                        <li>
                            <label for="OldPassword"><?php echo ww("OldPassword") ?></label><br />
                            <input type="password" id="OldPassword" name="OldPassword" />
                        </li>
                        <li>
                            <label for="NewPassword"><?php echo ww("NewPassword") ?></label><br />
                            <input type="password" id="NewPassword" name="NewPassword" />
                        </li>
                        <li>
                            <label for="SecPassword"><?php echo ww("SignupCheckPassword") ?></label><br />
                            <input type="password" id="SecPassword" name="SecPassword" />
                        </li>
                    </ul>
                    <input type="submit" id="submit" name="submit" value="submit" />
                </form>
            </div> <!-- info -->
<?php
	require_once "footer.php";
}
?>
