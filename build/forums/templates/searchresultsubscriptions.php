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
//	$i18n = new MOD_i18n('apps/forums/board.php');
//	$boardText = $i18n->getText('boardText');
	
	
	echo "<table><tr><td colspan=3>" ;
	
	if ($this->BW_Right->HasRight("ForumModerator","All")) {
	   echo " as a forum moderator with right \"ForumModerator\",\"All\" you are automatically subscribed to everything in the forum<hr />" ; 
	}
	if (!empty($TResults->Username)) {
	   echo "<h2>Subscriptions for <a href=\"bw/member.php?cid=".$TResults->Username."\">".$TResults->Username."</a></h2>" ;
	}
	else if (!empty($TResults->ThreadTitle)) {
	   echo "<h2>Subscriptions for thread <a href=\"forums/s".$TResults->IdThread."\">".$TResults->ThreadTitle."</a></h2>" ;
	}
	else {
	   if (count($TResults->TData)==0) {
	   		echo $words->getFormatted("forum_YourDontHaveSubscription") ;
	   }
	   else {
	   		echo "<h2>".$words->getFormatted("forum_YourSubscription")."</h2>" ;
	   }
	}
	echo "</td></tr>\n" ;
	echo "<tr><td colspan=2>",$words->getFormatted("forum_YourThreadSubscribted"),"</td>" ;
	foreach ($TResults->TData as $data) {
		echo "<tr><td> ",$data->subscribedtime,"</td><td>" ;
		if ($data->IdThread!=0) {
		   echo " <a href=\"forums/s".$data->IdThread."\">",$words->fTrad($data->IdTitle),"</a>" ;
		}
		else {
		   echo "<a href=\"bw/member.php?cid=".$data->Username."\">".$data->Username."</a>" ;
		}
		echo "</td><td><a href=\"forums/subscriptions/unsubscribe/thread/".$data->IdSubscribe."/".$data->UnSubscribeKey."\" class=\"button\">Unsubscribe</a></td></tr>\n" ;
	}
	echo "<tr><td colspan=2>",$words->getFormatted("forum_YourTagSubscribted"),"</td>" ;
	
	foreach ($TResults->TDataTag as $data) {
		echo "<tr><td> ",$data->subscribedtime,"</td><td>" ;
		if ($data->IdTag!=0) {
		   echo " <a href=\"forums/t".$data->IdTag."\">",$words->fTrad($data->IdName),"</a>" ;
		}
		else {
		   echo "<a href=\"bw/member.php?cid=".$data->Username."\">".$data->Username."</a>" ;
		}
		echo "</td><td><a href=\"forums/subscriptions/unsubscribe/tag/".$data->IdSubscribe."/".$data->UnSubscribeKey."\">Unsubscribe</a></td></tr>\n" ;
	}
	echo "</table>" ;
		
?>