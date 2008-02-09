<?php
$userbarText = array();
$words = new MOD_words();

switch($currentSubPage) {
	case 'trac':
		echo	"<iframe src=\"http://". $_SESSION['Username'] ."@www.bevolunteer.org/trac/login\" width=\"100%\" height=\"400\" frameborder=\"0\" name=\"ToolsFrame\"></iframe>"; 
	break;
	case 'forum':
		echo	"<iframe src=\"http://www.bevolunteer.org/forum\" width=\"100%\" height=\"400\" frameborder=\"0\" name=\"ToolsFrame\"></iframe>"; 
	break;
	case 'otrs':
		echo	"<iframe src=\"http://www.bevolunteer.org/otrs\" width=\"100%\" height=\"400\" frameborder=\"0\" name=\"ToolsFrame\"></iframe>"; 
	break;
	case 'blogs':
		echo	"<iframe src=\"http://blogs.bevolunteer.org/\" width=\"100%\" height=\"400\" frameborder=\"0\" name=\"ToolsFrame\"></iframe>"; 
	break;	
	case 'mailman':
		echo	"<iframe src=\"http://bewelcome.org/mailman/listinfo//\" width=\"100%\" height=\"400\" frameborder=\"0\" name=\"ToolsFrame\"></iframe>"; 
	break;
}
?>