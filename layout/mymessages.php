<?php

/**
 * Contains layout functions for the message system
 *
 * @package Messaging
 * @author JY and Fake51 (PHP), Wukk (design)
 */

require_once ("menus.php");

/**
 * Layout function for mail overview
 *
 * @author JY and Fake51 (PHP), Wukk (design)
 * @param array $TMess array of message info, grabbed from the DB
 * @param string $Title title of the page
 * @param string $menutab used to track which message-subpage to displaying
 * @param string $msgAction defines what action-links to display in the lefthand menu
 * @param string $MessageOrder defines the sort-order of messages
 * @param int $from defines what msg to start displaying from, in the paginated overview
 */
function DisplayMessages($TMess, $Title, $menutab, $msgAction, $MessageOrder, $from = 0) {
	global $title;
	$title = $Title;
	include "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("mymessages.php?action=" . $menutab . ww("MyMessage")); // Displays the second menu

	echo "\n<div id=\"maincontent\">\n";
	echo "  <div id=\"topcontent\">";
	echo "&nbsp<h2>&nbsp;&nbsp;&nbsp;", $Title, " </h2>\n";

	echo "	<div id=\"main\">";
	echo "      <div id=\"col1\">\n"; 
	echo "        <div id=\"col1_content\" class=\"clearfix\"> \n"; 
	echo "        </div>\n"; 
	echo "      </div>\n";
	echo "      <div id=\"col3\">\n"; 
	echo "        <div id=\"col3_content\" class=\"clearfix\"> \n"; 
	echo "		<p></p>\n";
	echo "        </div>\n"; 
	echo "      </div>\n";
	
	echo "\n  </div>\n";
	echo "</div>\n";
	

	echo "	<div id=\"columns\">";
	menumessages("mymessages.php?action=" . $menutab, $Title);
	echo "		<div id=\"columns-low\">";
	// MAIN begin 3-column-part
	echo "    <div id=\"main\">";

	messageActions($msgAction,true); // Show the Actions
//	ShowAds(); // Show the Ads

	// middle column
	echo "      <div id=\"col3\"> \n"; 
	echo "	    <div id=\"col3_content\" class=\"clearfix\"> \n"; 

	//start of mail content divided into rows - top row first
	echo "<div class=\"subframe\">\n";
	echo "<div class=\"subframe_inner\">\n";
	echo "	<div class=\"subcolumns\">\n";
	echo "		<div class=\"c75l\">\n";
    	echo "			<div class=\"subcl\">\n";
	echo "				<div class=\"c38l\">\n";
	if ($MessageOrder == "members.Username ASC"){
		echo "					 <a href=\"" . bwlink("mymessages.php?action=" . $menutab . "&amp;msgsortorder=UD") . "\"><img src=\"images/icons1616/icon_updown.png\"></img> " . ww("Username") . "</a>\n";
	} else {
		echo "					 <a href=\"" . bwlink("mymessages.php?action=" . $menutab . "&amp;msgsortorder=UA") . "\"><img src=\"images/icons1616/icon_updown.png\"></img> " . ww("Username") . "</a>\n";
	}
	echo "				</div>\n";
	echo "				<div class=\"c62r\">\n";
	if ($MessageOrder == "m2.IdParent DESC"){
		echo "					 <a href=\"" . bwlink("mymessages.php?action=" . $menutab . "&amp;msgsortorder=RA") . "\"><img src=\"images/icons1616/icon_updown.png\"></img> " . ww("MessagesReplyStatus") . "</a>\n";
	} else {
		echo "					 <a href=\"" . bwlink("mymessages.php?action=" . $menutab . "&amp;msgsortorder=RD") . "\"><img src=\"images/icons1616/icon_updown.png\"></img> " . ww("MessagesReplyStatus") . "</a>\n";
	}
	echo "				</div>\n";
	echo "			</div>\n";
	echo "		</div>\n";
	echo "		<div class=\"c25r\">\n";
	echo "				<div class=\"subcr\">\n";
	if ($MessageOrder == "m1.created DESC"){
		echo "					 <a href=\"" . bwlink("mymessages.php?action=" . $menutab . "&amp;msgsortorder=cA") . "\"><img src=\"images/icons1616/icon_updown.png\"></img> " . ww("MessagesDate") . "</a>\n";
	} else {
		echo "					 <a href=\"" . bwlink("mymessages.php?action=" . $menutab . "&amp;msgsortorder=cD") . "\"><img src=\"images/icons1616/icon_updown.png\"></img> " . ww("MessagesDate") . "</a>\n";
	}
	echo "				</div>\n";
	echo "		</div>\n";
	echo "	</div>\n";
	echo "</div>\n";
	echo "</div>\n";

	//end of top columns for messages


	echo "<form name=\"msgform\" id=\"msgform\" action=\"mymessages.php?action=MultiMsg&amp;menutab=$menutab\" method=\"post\">";

	$max = count($TMess);	//get number of messages to display
	$from = (floor($from / 20)) * 20;	//make sure that we're always starting from n * 20

	if ($from > $max){	//if $from would put us past the number of msgs, $from is faulty and should be treated
		$from = 0;	//as such == set to zero
	}


	if (($from + 20) < $max){		//determine the amount of messages to show
		$ShowNumber = $from + 20;	//show 20 msgs, in the right range based on $from
		$StartNumber = $ShowNumber - 20;
	} elseif ($from < $max){
		$ShowNumber = $max;		//show less than 20 msgs, i.e. the last page of messages
		$StartNumber = (floor($max / 20)) * 20;
	} else {
		$ShowNumber = 0;		//or show no messages at all
		$StartNumber = 0;
	}

	$HighlightArray = array(" highlight","");		//array to fix highlighting for message rows
	for ($i = $StartNumber; $i < $ShowNumber; $i++){	//loop through messages and display

		echo "<div class=\"subframe" . $HighlightArray[($i%2)]. "\">\n";
		echo "<div class=\"subframe_inner message_list\">\n";
		echo "	<div class=\"subcolumns\">\n";
		echo "		<div class=\"c75l\">\n";
		echo "			<div class=\"subcl\">\n";
		echo "				<div class=\"c38l\">\n";
		echo "					<input type=\"checkbox\" name=\"message-mark[]\" value=\"" . $TMess[$i]['IdMess'] ."\" />\n";
		echo "					" . LinkWithUsername($TMess[$i]['Username']) . "\n";
		echo "				</div>\n";
		echo "				<div class=\"c62r\">\n";
		echo "					<a href=\"" . bwlink("mymessages.php?action=ViewMsg&amp;menutab=$menutab&amp;msg=" . $TMess[$i]['IdMess']) . "\" class=\"msg\">";
		if (($TMess[$i]['IdParent']) && (($menutab=="Received") || ($menutab == "Spam"))){
			echo "<img src=\"images/icons1616/icon_reply.png\" alt=\"" . ww("replymessage") . "\" />";	//if we're on the Received or Spam page, we should show the Replied icon if relevant
		}
		if (($TMess[$i]['WhenFirstRead'] == "0000-00-00 00:00:00") && ($menutab=="Received")){
			echo "<b>";		//if the message hasn't been read yet, highlight it with <b></b>
		}
		if (strlen($TMess[$i]['Message'])>50){	//show the first 50 chars of the msg
			echo substr($TMess[$i]['Message'],0,50) . "...</a>\n";
		} else {
			echo $TMess[$i]['Message'] . "</a>\n";
		}
		if (($TMess[$i]['WhenFirstRead'] == "0000-00-00 00:00:00") && (($menutab=="Received") || ($menutab=="Spam"))){
			echo "</b>";		//if the message hasn't been read yet, highlight it with <b></b>
		}
		echo "				</div>\n";
    		echo "			</div>\n";
		echo "	 	</div>\n";
		echo "		<div class=\"c25r\">\n";
		echo "			<div class=\"subcr\">\n";
		echo "				<div class=\"c50l\">";
		echo date("d.m.y, H:i",strtotime($TMess[$i]['created']));
		echo "\n				</div>\n";
//		if (($menutab=="Received") || ($menutab=="Spam")){	//again, if on the Received or Spam page, allow member to reply to messages
//			echo "				<div class=\"c50r\"><a href=\"contactmember.php?action=reply&amp;cid=" . $TMess[$i]['Username'] . "&amp;IdMess=" . $TMess[$i]['IdMess'] . "\" class=\"msg\"><img src=\"images/icons1616/icon_reply.png\" alt=\"" . ww("replymessage") . "\" /> " . ww("replymessage") . "</a>\n";
//			echo "				</div>\n";
//		}
		echo "			</div>\n";
		echo "		</div>\n";
		echo "	</div>\n";
		echo "</div>\n";
		echo "</div>\n";


	}
	//end of message display loop - start of bottom pagination

	echo "<noscript>\n";
	echo "	<div class=\"subframe" . $HighlightArray[($i%2)]. "\" id=\"noscriptdiv\">\n";
	echo "	<div class=\"subframe_inner\">\n";
	echo "		<div class=\"subcolumns\">\n";
	echo "			<div class=\"c50l\">\n";

	echo "				<input type=\"radio\" name=\"noscriptaction\" value=\"delmsg\" /> " .ww("delmessage") . "&nbsp;&nbsp;";
	if ($menutab=="Spam"){
		echo "				<input type=\"radio\" name=\"noscriptaction\" value=\"notspam\" /> " .ww("marknospam");
	} elseif ($menutab=="Received") {
		echo "				<input type=\"radio\" name=\"noscriptaction\" value=\"isspam\" /> " .ww("markspam");
	}
  	echo "			</div>\n";
	echo "			<div class=\"c50r\" align=\"right\">\n";
	echo "				<input type=\"submit\" value=\"" . ww("ProcessMessages") . "\" />";
  	echo "			</div>\n";
  	echo "		</div>\n";
  	echo "	</div>\n";
  	echo "	</div>\n";

	echo "</noscript>\n";


	echo "<input type=\"hidden\" name=\"actiontodo\" value=\"none\" />\n";
	echo "</form>";

	echo "	<div class=\"subframe" . $HighlightArray[($i%2)]. "\">\n";
	echo "	<div class=\"subframe_inner\">\n";
	echo "		<div class=\"subcolumns\">\n";
	echo "			<div class=\"c33l\">\n";
	if ($from > 0){
		$newfrom = $from - 20;
		echo "				<a href=\"" . bwlink("mymessages.php?action=" . $menutab. "&amp;from=" . $newfrom) . "\"><img src=\"images/icons1616/icon_previous.png\"></img></a> " . ($newfrom+1) . "-" . ($newfrom+20) . "\n";
	} else {
		echo "&nbsp;";
	}
 	echo "		 	</div>\n";
  	echo "			<div class=\"c33l\" align=\"center\">\n";
	if ($max == 0){
		echo "				<b>0 </b> messages\n";
	} else {
		echo "				" . $StartNumber+1 . "-" . $ShowNumber . "of <b>" . $max . "</b> messages\n";
	}
 	echo "			</div>\n";
  	echo "			<div class=\"c33r\" align=\"right\">\n";
	if (($from+20) < $max){
		$newfrom = $from + 20;
		if (($newfrom+20) < $max){
			$newto = $newfrom + 20;
		} else {
			$newto = $max;
		}
		echo "				" . ($newfrom+1) . "-" . ($newto) . " <a href=\"" . bwlink("mymessages.php?action=" . $menutab. "&amp;from=" . $newfrom) . "\"><img src=\"images/icons1616/icon_next.png\"></img></a>\n";
	} else {
		echo "&nbsp;";
	}

  	echo "			</div>\n";
  	echo "		</div>\n";
  	echo "	</div>\n";
  	echo "	</div>\n";



	echo "<div class=\"clear\ />\n";
	echo "</div>\n";
	echo "	</div>";
	echo "				</div>";
	echo "				<div class=\"clear\" />";
	echo "			</div>	";
	echo "			<div class=\"clear\" />	";
	echo "		</div>	";
	echo "		</div>	";
	echo "	</div>	";

	include "footer.php";
}

/**
 * Layout function for displaying a single message
 *
 * @author JY and Fake51 (PHP), Wukk (design)
 * @param array $TMess array of message info, grabbed from the DB
 * @param string $Title title of the page
 * @param string $menutab used to track which message-subpage to displaying
 * @param string $msgAction defines what action-links to display in the lefthand menu
 * @param int $MsgToView is an index to $TMess
 * @param array $ExtraDetails an array holding extra information of the sender or receiver of the msg
 */
function DisplayAMessage($TMess, $Title, $menutab, $msgAction, $MsgToView, $ExtraDetails){
	global $title;
	$title = $Title;
	include "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("mymessages.php?action=" . $menutab . ww("MyMessage")); // Displays the second menu

	echo "\n<div id=\"maincontent\">\n";
	echo "  <div id=\"topcontent\">";
	echo "&nbsp<h2>&nbsp;&nbsp;&nbsp;", $Title, " </h2>\n";

	echo "	<div id=\"main\">";
	echo "      <div id=\"col1\">\n"; 
	echo "        <div id=\"col1_content\" class=\"clearfix\"> \n"; 
	echo "        </div>\n"; 
	echo "      </div>\n";
	echo "      <div id=\"col3\">\n"; 
	echo "        <div id=\"col3_content\" class=\"clearfix\"> \n"; 
	echo "		<p></p>\n";
	echo "        </div>\n"; 
	echo "      </div>\n";
	
	echo "\n  </div>\n";
	echo "</div>\n";
	

	echo "	<div id=\"columns\">";
	menumessages("mymessages.php?action=" . $menutab, $Title);
	echo "		<div id=\"columns-low\">";
	// MAIN begin 3-column-part
	echo "    <div id=\"main\">";

	messageActions($msgAction,false); // Show the Actions
//	ShowAds(); // Show the Ads

	// middle column
	echo "      <div id=\"col3\"> \n"; 
	echo "	    <div id=\"col3_content\" class=\"clearfix\"> \n"; 

	//here the fun content starts - i.e. the actual message

	echo "	<div class=\"subframe\">\n";
	echo "		<div class=\"subframe_inner\">\n";
	echo "			<div class=\"subcolumns\">\n";
	echo "				<div class=\"c75l\">\n";
    	echo "					<div class=\"subcl\">\n";
	echo "						<div class=\"c38l\">\n";
	echo "							 ". ww("MessageFrom") . " " . LinkWithUsername($TMess[$MsgToView]['Username']) . "\n";
	echo "						</div>\n";
	echo "					</div>\n";
	echo "				</div>\n";
	echo "			</div>\n";
	echo "		</div>\n";
	echo "		</div>\n";



	echo "		<div class=\"subframe\">\n";
	echo "		<div class=\"subframe_inner message_list\">\n";
	echo "			<div class=\"subcolumns\">\n";
	echo "				<div class=\"subcl\">\n";
	echo "					<div class=\"c25l\">\n";
	echo "						<div class=\"subframe\">\n";

	$thumb = getthumb($_SYSHCVOL['IMAGEDIR']."/".$ExtraDetails['FilePath'], 100, 100 );
	if ($thumb){
		echo "							<img src=\"" . bwlink($thumb) . "\" alt=\"" . $TMess[$MsgToView]['Username'] . "\" /><br />\n";
	} else {
		echo "							&nbsp;<br />\n";
	}
	echo "						</div>\n";
	echo "							<div class=\"subframe highlight\">" . $TMess[$MsgToView]['Username'] . "</div>\n";
	echo "							<div class=\"subframe\">" . fage($ExtraDetails['BirthDate'],$ExtraDetails['HideBirthDate']) . "</div>\n";
	echo "							<div class=\"subframe highlight\">" . ww("AccommodationTitle") . ": " . ww("Accomodation_" .$ExtraDetails['Accomodation']) . "</div>\n";
	echo "							<div class=\"subframe\"><a href=\"" . bwlink("member.php?cid=" . $TMess[$MsgToView]['Username']) . "\">>> View Profile </a></div>\n";
	echo "							<div class=\"subframe highlight\"><a href=\"" . bwlink("viewcomments.php?cid=" . $TMess[$MsgToView]['OtherUserID']) . "\">>> " . ww("NbComments", $ExtraDetails['NumComments']) . "</a></div>\n";
	echo "						</div>\n";
	echo "						<div class=\"c75r\">\n";
	echo "							<div class=\"subframe\">" . $TMess[$MsgToView]['Message'] . "\n";
	echo "							</div>\n";
	echo "						</div>\n";
	echo "					</div>\n";
	echo "				</div>\n";
	echo "			</div>\n";
	echo "		</div>\n";

	echo "		<div class=\"subframe highlight\">\n";
	echo "		<div class=\"subframe_inner\">\n";
	echo "			<div class=\"subcolumns\">\n";
	echo "				<div class=\"c33l\">\n";
 	echo "			 	</div>\n";
	echo "				<div class=\"c33l\" align=\"center\">\n";
	echo "				</div>\n";
	echo "				<div class=\"c33r\" align=\"right\">\n";
	echo "					<img src=\"images/icons1616/icon_reply.png\" alt=\"" . ww("replymessage") . "\" /> <a href=\"" . bwlink("contactmember.php?action=reply&amp;cid=" . $TMess[$MsgToView]['Username'] . "&amp;IdMess=" . $TMess[$MsgToView]['IdMess']). "\">reply</a>\n";
	echo "				</div>\n";
	echo "			</div>\n";
	echo "		</div>\n";
	echo "		</div>\n";

	echo "<form name=\"msgform\" id=\"msgform\" action=\"mymessages.php?action=MultiMsg&amp;menutab=$menutab\" method=\"post\">";
	echo "<input type=\"hidden\" name=\"message-mark[]\" value=\"" . $TMess[$MsgToView]['IdMess'] . "\" />";
	echo "<noscript>\n";
	echo "	<div class=\"subframe highlight\" id=\"noscriptdiv\">\n";
	echo "	<div class=\"subframe_inner\">\n";
	echo "		<div class=\"subcolumns\">\n";
	echo "			<div class=\"c50l\">\n";

	echo "				<input type=\"radio\" name=\"noscriptaction\" value=\"delmsg\" /> " .ww("delmessage") . "&nbsp;&nbsp;";
	if ($menutab=="Spam"){
		echo "				<input type=\"radio\" name=\"noscriptaction\" value=\"notspam\" /> " .ww("marknospam");
	} elseif ($menutab=="Received") {
		echo "				<input type=\"radio\" name=\"noscriptaction\" value=\"isspam\" /> " .ww("markspam");
	}
  	echo "			</div>\n";
	echo "			<div class=\"c50r\" align=\"right\">\n";
	echo "				<input type=\"submit\" value=\"" . ww("ProcessMessages") . "\" />";
  	echo "			</div>\n";
  	echo "		</div>\n";
  	echo "	</div>\n";
  	echo "	</div>\n";

	echo "</noscript>\n";


	echo "<input type=\"hidden\" name=\"actiontodo\" value=\"none\" />\n";
	echo "</form>";

//pagination part
	echo "		<div class=\"subframe\">\n";
	echo "		<div class=\"subframe_inner\">\n";
	echo "			<div class=\"subcolumns\">\n";
	echo "				<div class=\"c33l\">\n";
	if ($MsgToView > 0){
		echo "					<a href=\"" . bwlink("mymessages.php?action=ViewMsg&amp;menutab=$menutab&amp;msg=" . $TMess[$MsgToView-1]['IdMess']) . "\"><img src=\"images/icons1616/icon_previous.png\" /></a> previous\n";
	} else {
		echo "&nbsp;";
	}
	echo "			 	</div>\n";
	echo "				<div class=\"c33l\" align=\"center\">\n";
	echo "				</div>\n";
	echo "				<div class=\"c33r\" align=\"right\">\n";
	if (isset($TMess[$MsgToView+1])){
		echo "					next <a href=\"" . bwlink("mymessages.php?action=ViewMsg&amp;menutab=$menutab&amp;msg=" . $TMess[$MsgToView+1]['IdMess']) . "\"><img src=\"images/icons1616/icon_next.png\" /></a>\n";
	} else {
		echo "&nbsp;";
	}
	echo "				</div>\n";
	echo "			</div>\n";
	echo "		</div>\n";
	echo "		</div>\n";

//end of message display - start of closing html and footer


	echo "<div class=\"clear\ />\n";
	echo "</div>\n";
	echo "	</div>";
	echo "				</div>";
	echo "				<div class=\"clear\" />";
	echo "			</div>	";
	echo "			<div class=\"clear\" />	";
	echo "		</div>	";
	echo "		</div>	";
	echo "	</div>	";

	include "footer.php";

}

/**
 * Layout function for mail actions - the menu on the left
 *
 * @author Fake51 (PHP), Wukk (design)
 * @param string $CaseSpam defines whether the action should deal with spam, wrongly marked spam, or just normal mail
 */
function messageActions($CaseSpam,$ShowAll){

	echo "<script type=\"text/javascript\" src=\"" . bwlink("lib/messaging.js") . "\"></script>";

	echo "<script type=\"text/javascript\">\n//<![cdata[\n";
	echo "function submitform(actionToDo){";
	echo "	if (window.confirm('" . ww("ConfirmAction") . "')){";
	echo "		submitformsub(actionToDo);";
	echo "	} else {";
	echo "		return false;";
	echo "	}";
	echo "}";

	echo "messagelinks = '<div id=\"col1\">';\n";
	echo "messagelinks += ' <div id=\"col1_content\" class=\"clearfix\">';\n"; 
	echo "messagelinks += '  <div id=\"content\">';\n"; 
	echo "messagelinks += '   <div class=\"info\">';\n";
	echo "messagelinks += '    <h3>", ww("Actions"), "</h3>';\n";
	echo "messagelinks += '    <ul>';\n";
	switch ($CaseSpam){
		case "notspam":
			echo "messagelinks += '<li><a href=\"#\" onclick=\"return submitform' + \"('notspam')\" + ';\"><img src=\"images/icons1616/icon_marknospam.png\" alt=\"" . ww("marknospam") . "\" /> " . ww("marknospam") . "</a></li>';\n";
			break;
		case "isspam":
			echo "messagelinks += '<li><a href=\"#\" onclick=\"return submitform' + \"('isspam')\" + ';\"><img src=\"images/icons1616/icon_markspam.png\" alt=\"" . ww("markspam") . "\" /> " . ww("markspam") . "</a></li>';\n";
			break;
	}
	//	echo "<li><a href=\"" . bwlink("mymessages.php?action=createnew") . "\">" . ww("CreateNewMessage") . "</a></li>\n";
	echo "messagelinks += '<li><a href=\"#\" onclick=\"return submitform' + \"('delmsg')\" + ';\"><img src=\"images/icons1616/icon_delete.png\" alt=\"" . ww("delmessage") . "\" /> " . ww("delmessage") . "</a></li>';\n";
	if ($ShowAll == true){
		echo "messagelinks += '<li>" . ww("SelectMessages") . " <a href=\"#\" onclick=\"SelectMsg' + \"('ALL')\" + ';return false;\">" . ww("SelectAll") . "</a> / <a href=\"#\" onclick=\"SelectMsg' + \"('NONE')\" + ';return false;\">" . ww("SelectNone") . "</a></li>';\n";
	}
	echo "messagelinks += '    </ul>';\n";
	echo "messagelinks += '   </div>';\n"; // Class info 
	echo "messagelinks += '  </div>';\n"; // content
	echo "messagelinks += ' </div>';\n"; // col1_content
	echo "messagelinks += '</div>';\n"; // col1
	echo "document.write(messagelinks);";
	echo "\n//]]>\n";
	echo "</script>\n";

}

?>
