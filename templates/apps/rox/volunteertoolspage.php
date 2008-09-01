<?php
$userbarText = array();
$words = new MOD_words();

switch($currentSubPage) {

	case 'trac':
		echo	"<iframe src=\"http://www.bevolunteer.org/trac/login\" width=\"100%\" height=\"600\" frameborder=\"0\" name=\"ToolsFrame\"></iframe>"; 
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
		echo	"<iframe src=\"http://www.bevolunteer.org/trac/query?status=new&status=assigned&status=reopened&group=status&type=volunteer+task&order=priority\" width=\"100%\" height=\"600\" frameborder=\"0\" name=\"ToolsFrame\"></iframe>"; 
	break;	

	case 'features':
		echo	"<iframe src=\"http://www.bevolunteer.org/trac/query?status=new&status=reopened&group=status&type=new+feature&type=improve+feature&order=priority\" width=\"100%\" height=\"600\" frameborder=\"0\" name=\"ToolsFrame\"></iframe>"; 
	break;
	
	case 'newtask':
		echo "<div class=\"info\">\n";
		echo "<h3>", $words->get("VolunteerTools_NewTask"),"</h3>";
		echo "<p>",$words->get("VolunteerTools_NewTaskText"),"</p>";
		echo "</div>\n";
		echo	"<iframe src=\"http://www.bevolunteer.org/trac/newticket\" width=\"100%\" height=\"600\" frameborder=\"0\" name=\"ToolsFrame\"></iframe>";
	break;
	
	case 'newbug':
		echo "<div class=\"info\">\n";
		echo "<h3>", $words->get("VolunteerTools_NewBug"),"</h3>";
		echo "<p>",$words->get("VolunteerTools_NewBugText"),"</p>";
		echo "</div>\n";
		echo	"<iframe src=\"http://www.bevolunteer.org/trac/newticket\" width=\"100%\" height=\"600\" frameborder=\"0\" name=\"ToolsFrame\"></iframe>";
	break;

	default:
	
	?>
	<h3><?php echo $words->get('VolunteerCoordinators'); ?></h3>
	<ul class="floatbox">
		<li class="userpicbox_big float_left"><h4><a href="user/kiwiflave"><img src="http://www.bewelcome.org/memberphotos/thumbs/kiwiflave_1171823734.square.80x80.jpg" class="framed float_left" style="height:70px; width: 70px;">Florian</a><br /></h4>
		</li>
		<li class="userpicbox_big float_left">
			<h4>
				<a href="user/claudiaab"><img src="http://www.bewelcome.org/memberphotos/thumbs/claudiaab_1169485927.square.80x80.jpg" class="framed float_left" style="height:70px; width: 70px;">Claudia</a><br />
			</h4>
		</li>
		<li class="userpicbox_big float_left">
			<p>
				<?php echo $words->get('VolunteerCoordinatorsText'); ?>
			</p>
       </li>
	</ul>
<?
	
		echo "<div class=\"subcolumns\">\n";
		echo "  <div class=\"c50l\">\n";
		echo "  <div class=\"subcl\">\n";
		

		echo "<div class=\"info\">\n";


		echo "<h3><a href=\"volunteer/forum\" title=\"" . $words->get('VolunteerToolsTipForum') . "\" >" . $words->get('VolunteerTools_BVForum') . "</a> <a href=\"http://www.bevolunteer.org/forum\" target =\"blank\" title=\"" . $words->get('VolunteerToolsTipForumExt') . "\" >[ext]</a></h3>";
		echo "<p>",$words->get("VolunteerTools_BvForumText"),"</p>";
		echo "<h3><a href=\"volunteer/trac\" title=\"" . $words->get('VolunteerToolsTipTrac') . "\" >" . $words->get('VolunteerTools_Trac') . "</a> <a href=\"http://bevolunteer.org/trac/\" target =\"blank\" title=\"" . $words->get('VolunteerToolsTipForumExt') . "\" >[ext]</a></h3>";	
		echo "<p>",$words->get("VolunteerTools_TracText"),"</p>";
		echo "<h3><a href=\"volunteer/otrs\" title=\"" . $words->get('VolunteerToolsTipOTRS') . "\" >" . $words->get('VolunteerTools_OTRS') . "</a> <a href=\"http://www.bevolunteer.org/otrs\" target =\"blank\" title=\"" . $words->get('VolunteerToolsTipForumExt') . "\" >[ext]</a></h3>";	
		echo "<p>",$words->get("VolunteerTools_OTRSText"),"</p>";
		echo "<h3><a href=\"volunteer/blogs\" title=\"" . $words->get('VolunteerToolsTipBlogs') . "\" >" . $words->get('VolunteerTools_Blogs') . "</a> <a href=\"http://blogs.bevolunteer.org/\" target =\"blank\" title=\"" . $words->get('VolunteerToolsTipForumExt') . "\" >[ext]</a></h3>";
		echo "<p>",$words->get("VolunteerTools_BlogsText"),"</p>";
		echo "<h3><a href=\"volunteer/mailman\" title=\"" . $words->get('VolunteerToolsTipMailman') . "\" >" . $words->get('VolunteerTools_Mailman') . "</a> <a href=\"http://bewelcome.org/mailman/listinfo/\" target =\"blank\" title=\"" . $words->get('VolunteerToolsTipForumExt') . "\" >[ext]</a></h3>";	
		echo "<p>",$words->get("VolunteerTools_MailmanText"),"</p>";		
		echo "</div>\n";
		
		echo "</div>\n";
		echo "</div>\n";
		
		echo "<div class=\"c50r\">\n";
        echo "<div class=\"subcr\">\n";
		
		echo "<div class=\"info\">\n";
		echo "<h3><a href=\"volunteer/filemanager\" title=\"" . $words->get('VolunteerToolsTipFilemanager') . "\" >" . $words->get('VolunteerTools_Filemanager') . "</a></h3>";	
		echo "<p>",$words->get("VolunteerTools_FilemanagerText"),"</p>";
		echo "<h3><a href=\"volunteer/download\" title=\"" . $words->get('VolunteerToolsTipDownload') . "\" >" . $words->get('VolunteerTools_Download') . "</a></h3>";	
		echo "<p>",$words->get("VolunteerTools_DownloadText"),"</p>";
		echo "<h3><a href=\"volunteer/playground\" title=\"" . $words->get('VolunteerToolsTipPlayground') . "\" >" . $words->get('VolunteerTools_Playground') . "</a></h3>";	
		echo "<p>",$words->get("VolunteerTools_PlaygroundText"),"</p>";		
		echo "<h3>", $words->get("VolunteerTools_Chat"),"</h3>";	
		echo "<p>",$words->get("VolunteerTools_ChatText"),"</p>";
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