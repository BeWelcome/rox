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

function CreatePassword() {
    // *************************
    // Random Password Generator
    // *************************
    $totalChar = 8; // number of chars in the password
    $salt = "abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ123456789";  // salt to select chars from
    srand((double)microtime()*1000000); // start the random generator
    $password=""; // set the inital variable
    for ($i=0;$i<$totalChar;$i++)  // loop and create password
        $password = $password . substr ($salt, rand() % strlen($salt), 1);
    // *************************
    // Display Password
    // *************************
    return($password);
} // end of createpassword

require_once "layout/lostpassword.php";

$action = GetParam("action");

$CurrentError = "";
if (isset ($_COOKIE['MyBWusername'])) {
    $MyBWusername=$_COOKIE['MyBWusername'];
}
else { 
    $MyBWusername="";
} 
switch ($action) {
    case "sendpassword":
	    $UserNameOrEmail=GetStrParam("UserNameOrEmail");
			if (strstr($UserNameOrEmail,"@")!="") { 		 // If it is an emai
		   	 $email=$UserNameOrEmail;
		   	 $emailcrypt=GetCryptA($email);
				 $ss="select IdMember from ".$_SYSHCVOL['Crypted']."cryptedfields,members where AdminCryptedValue='" .$emailcrypt."' and members.id=IdMember and (members.Status='Active' or members.Status='ChoiceInactive'  or members.Status='Sleeper'  or members.Status='Renamed'   or members.Status='OutOfRemind') and TableColumn='members.Email'" ;
		   	 LogStr(" Debuging (to remove in losstpassword) <b>".$ss."</b>","lostpassword"); // Todo remove this line when everything will be ok
		   	 $rr=LoadRow($ss);
		   	 if (!isset($rr->IdMember)) {
		   	  	LogStr("No such user/email <b>".$UserNameOrEmail."</b> (CooKIE[MyBWusername]=".$MyBWusername.")","lostpassword");
		   	  	DisplayResult("No user for email ",$UserNameOrEmail);
		   	  	exit(0);
		   	 }
		   	 $IdMember=$rr->IdMember;
			}
			else {
		   	 $IdMember=IdMember($UserNameOrEmail);
		   	 if ($IdMember<=0) { // If no member with this username
		   	  	LogStr("No valid member for <b>".$UserNameOrEmail."</b> (CooKIE[MyBWusername]=".$MyBWusername.")","lostpassword");
		   	  	DisplayResult("Sorry no valid member ",$UserNameOrEmail);
		   	  	exit(0);
		   	 }
				 else { // else they are member with this username
				 		$rr=LoadRow("select Status,Username from members where id=".$IdMember) ;
						// Have they an allowed status ?
						if (!($rr->Status=='Active' ||  $rr->Status=='ChoiceInactive'  ||  $rr->Status=='Sleeper'  ||  $rr->Status=='Renamed'  ||  $rr->Status=='OutOfRemind')) {
							LogStr("<b>".$UserNameOrEmail."</b> has a not valid Status (".$rr->Status.") (CooKIE[MyBWusername]=".$MyBWusername.")","lostpassword");
		   	  		DisplayResult("Sorry Status is not valid (".$rr->Status.") ",$UserNameOrEmail);
		   	  		exit(0);
						}
				 }
		   	 $email=GetEmail($IdMember);
			}
		
			if (!CheckEmail($email)) {
		   	 LogStr("No valid email for <b>".$UserNameOrEmail."</b> (CooKIE[MyBWusername]=".$MyBWusername.")","lostpassword");
		   	 DisplayResult("Sorry no valid email for ",$UserNameOrEmail);
		   	 exit(0);
			}
		
			$Password=CreatePassword();
			$str="update members set password=PASSWORD('".$Password."') where id=".$IdMember;
			sql_query($str);
		
			$MemberIdLanguage = GetDefaultLanguage($IdMember);
			$subj = ww("lostpasswordsubj");
			$urltosignup = "http://".$_SYSHCVOL['SiteName'] .$_SYSHCVOL['MainDir']. "changepassword.php";
			$text=ww("lostpasswordtext",$Password);
			$_SERVER['SERVER_NAME'] = "www.bewelcome.org"; // to force because context is not defined

// echo $email,"<br />subj=",$subj,"<br />text=",$text,"<br />";
// if (IsAdmin()) $_SESSION['verbose']=true;
			if (!bw_mail($email, $subj, $text, "", $_SYSHCVOL['MessageSenderMail'], $MemberIdLanguage, "html", "", "")) {
		   	 bw_error("Cannot send message");
			};

	    LogStr("New password sent for <b>".$UserNameOrEmail."</b> (CooKIE[MyBWusername]=".$MyBWusername.")","lostpassword");
	    DisplayResult(ww("lostpasswordsent",$UserNameOrEmail));
		exit(0);
		break;
}

DisplayLostPasswordForm($CurrentError); // call the layout
?>
