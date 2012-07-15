<?php
$words = new MOD_words();
?>
<?php 
echo       "<h3>" . $words->get('VolunteerToolsBarTitle') . "</h3>\n";
echo       "<ul class=\"linklist\">\n";
echo		"<li><a href=\"volunteer/forum\" title=\"" . $words->get('VolunteerToolsTipForum') . "\" >" . $words->get('VolunteerToolsLinkForum') . "</a> <a href=\"http://www.bevolunteer.org/forum\" target =\"blank\" title=\"" . $words->get('VolunteerToolsTipForumExt') . "\" >[ext]</a></li>\n";
echo		"<li><a href=\"volunteer/trac\" title=\"" . $words->get('VolunteerToolsTipTrac') . "\" >" . $words->get('VolunteerToolsLinkTrac') . "</a> <a href=\"http://bevolunteer.org/trac/\" target =\"blank\" title=\"" . $words->get('VolunteerToolsTipForumExt') . "\" >[ext]</a></li>\n";
echo		"<li><a href=\"volunteer/otrs\" title=\"" . $words->get('VolunteerToolsTipOTRS') . "\" >" . $words->get('VolunteerToolsLinkOTRS') . "</a> <a href=\"http://otrs.bewelcome.org\" target =\"blank\" title=\"" . $words->get('VolunteerToolsTipForumExt') . "\" >[ext]</a></li>\n";
echo		"<li><a href=\"volunteer/blogs\" title=\"" . $words->get('VolunteerToolsTipBlogs') . "\" >" . $words->get('VolunteerToolsLinkBlogs') . "</a> <a href=\"http://blogs.bevolunteer.org/\" target =\"blank\" title=\"" . $words->get('VolunteerToolsTipForumExt') . "\" >[ext]</a></li>\n";
echo		"<li><a href=\"volunteer/mailman\" title=\"" . $words->get('VolunteerToolsTipMailman') . "\" >" . $words->get('VolunteerToolsLinkMailman') . "</a> <a href=\"http://bewelcome.org/mailman/listinfo/\" target =\"blank\" title=\"" . $words->get('VolunteerToolsTipForumExt') . "\" >[ext]</a></li>\n";
echo		"</ul>";

echo       "<h3>" . $words->get('VolunteerToolsBarTitle2') . "</h3>\n";
echo       "<ul class=\"linklist\">\n";
echo		"<li><a href=\"volunteer/newbug\" title=\"" . $words->get('VolunteerToolsTipNewbug') . "\" >" . $words->get('VolunteerToolsLinkNewbug') . "</a></li>";
echo		"<li><a href=\"volunteer/newtask\" title=\"" . $words->get('VolunteerToolsTipNewtask') . "\" >" . $words->get('VolunteerToolsLinkNewtask') . "</a></li>";
echo		"<li>new blog post</li>";
echo		"</ul>";
?>
