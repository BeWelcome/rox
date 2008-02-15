<?php
$userbarText = array();
$words = new MOD_words();

switch($currentSubPage) {

	case 'trac':
		echo	"<iframe src=\"http://". $_SESSION['Username'] ."@www.bevolunteer.org/trac/login\" width=\"100%\" height=\"600\" frameborder=\"0\" name=\"ToolsFrame\"></iframe>"; 
	break;
	case 'forum':
		echo	"<iframe src=\"http://www.bevolunteer.org/forum\" width=\"100%\" height=\"600\" frameborder=\"0\" name=\"ToolsFrame\"></iframe>"; 
	break;
	case 'otrs':
		echo	"<iframe src=\"http://www.bevolunteer.org/otrs\" width=\"100%\" height=\"600\" frameborder=\"0\" name=\"ToolsFrame\"></iframe>"; 
	break;
	case 'blogs':
		echo	"<iframe src=\"http://blogs.bevolunteer.org/\" width=\"100%\" height=\"600\" frameborder=\"0\" name=\"ToolsFrame\"></iframe>"; 
	break;	
	case 'mailman':
		echo	"<iframe src=\"http://bewelcome.org/mailman/listinfo//\" width=\"100%\" height=\"600\" frameborder=\"0\" name=\"ToolsFrame\"></iframe>"; 
	break;
	
	case 'tasks':
		echo	"<iframe src=\"http://www.bevolunteer.org/trac/query?status=new&status=assigned&status=reopened&show_on_bw=1&order=priority\" width=\"100%\" height=\"600\" frameborder=\"0\" name=\"ToolsFrame\"></iframe>"; 
	break;	
	
	case 'newtask':
		echo "<div class=\"info\">\n";
		echo "<h3>", $words->get("VolunteerTools_NewTask"),"</h3>";
		echo "<p>",$words->get("VolunteerTools_NewTaskText"),"</p>";
		echo "</div>\n";
		echo	"<iframe src=\"http://". $_SESSION['Username'] ."@www.bevolunteer.org/trac/newticket\" width=\"100%\" height=\"600\" frameborder=\"0\" name=\"ToolsFrame\"></iframe>";
	break;
	
	case 'newbug':
		echo "<div class=\"info\">\n";
		echo "<h3>", $words->get("VolunteerTools_NewBug"),"</h3>";
		echo "<p>",$words->get("VolunteerTools_NewBugText"),"</p>";
		echo "</div>\n";
		echo	"<iframe src=\"http://". $_SESSION['Username'] ."@www.bevolunteer.org/trac/newticket\" width=\"100%\" height=\"600\" frameborder=\"0\" name=\"ToolsFrame\"></iframe>";
	break;	
	
	default:
		echo "<div class=\"subcolumns\">\n";
		echo "  <div class=\"c50l\">\n";
		echo "  <div class=\"subcl\">\n";
		

		echo "<div class=\"info\">\n";
		echo "<h3>", $words->get("VolunteerTools_BvForum"),"</h3>";
		echo "<p>",$words->get("VolunteerTools_BvForumText"),"</p>";
		echo "<h3>", $words->get("VolunteerTools_Trac"),"</h3>";	
		echo "<p>",$words->get("VolunteerTools_TracText"),"</p>";
		echo "<h3>", $words->get("VolunteerTools_OTRS"),"</h3>";	
		echo "<p>",$words->get("VolunteerTools_OTRSText"),"</p>";
		echo "</div>\n";
		
		echo "</div>\n";
		echo "</div>\n";
		
		echo "<div class=\"c50r\">\n";
        echo "<div class=\"subcr\">\n";
		
		echo "<div class=\"info\">\n";
		echo "<h3>", $words->get("VolunteerTools_Blogs"),"</h3>";
		echo "<p>",$words->get("VolunteerTools_BlogsText"),"</p>";
		echo "<h3>", $words->get("VolunteerTools_Mailman"),"</h3>";	
		echo "<p>",$words->get("VolunteerTools_MailmanText"),"</p>";
		echo "<h3>", $words->get("VolunteerTools_Test"),"</h3>";	
		echo "<p>",$words->get("VolunteerTools_TestText"),"</p>";
		echo "<h3>", $words->get("VolunteerTools_Alpha"),"</h3>";	
		echo "<p>",$words->get("VolunteerTools_AlphaText"),"</p>";
		echo "</div>\n";		

		echo "</div>\n";
		echo "</div>\n";		
		echo "</div>\n";
		
	break;
		
}
?>