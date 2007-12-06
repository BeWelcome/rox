<?php
$words = new MOD_words();
?>
<?php 
echo       "<h3>" . $words->get('VolunteerToolsBarTitle') . "</h3>\n";
echo       "<ul class=\"linklist\">\n";
	

echo		"<li><a href=\"http://www.bevolunteer.org/forum\" target=_blank title=\"" . $words->get('VolunteerToolsTipForum') . "\" >" . $words->get('VolunteerToolsLinkForum') . "</a></li>\n";
echo		"<li><a href=\"http://www.bevolunteer.org/wiki\" target=_blank title=\"" . $words->get('VolunteerToolsTipWiki') . "\" >" . $words->get('VolunteerToolsLinkWiki') . "</a></li>\n";
echo		"<li><a href=\"https://www.bevolunteer.org/trac\" target=_blank title=\"" . $words->get('VolunteerToolsTipTrac') . "\" >" . $words->get('VolunteerToolsLinkTrac') . "</a></li>\n";
echo		"<li><a href=\"http://www.bevolunteer.org/otrs\" target=_blank title=\"" . $words->get('VolunteerToolsTipOtrs') . "\" >" . $words->get('VolunteerToolsLinkOtrs') . "</a></li>\n";
echo		"<li><a href=\"http://www.bevolunteer.org/joomla\" target=_blank title=\"" . $words->get('VolunteerToolsTipJoomla') . "\" >" . $words->get('VolunteerToolsLinkJoomla') . "</a></li>\n";
echo		"<li><a href=\"http://blogs.bevolunteer.org/\" target=_blank title=\"" . $words->get('VolunteerToolsTipBlogs') . "\" >" . $words->get('VolunteerToolsLinkBlogs') . "</a></li>\n";
echo		"<li><a href=\"http://bewelcome.org/mailman/listinfo/\" target=_blank title=\"" . $words->get('VolunteerToolsTipMailman') . "\" >" . $words->get('VolunteerToolsLinkMailman') . "</a></li>\n";
?>					
           </ul>