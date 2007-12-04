<?php
$userbarText = array();
$words = new MOD_words();
?>

           <h3>At a glance</h3>
           <ul class="linklist">
           
            <li class="floatbox"><img src="styles/YAML/images/circle1.gif" class="float_left"> <a href="about"<?php echo ($currentSubPage === 'theidea') ? ' class="active"' : ''; ?>><?php echo $words->get('TheIdea') ?>The idea</a></li>
            <li class="floatbox"><img src="styles/YAML/images/circle2.gif" class="float_left"> <a href="about/thepeople"<?php echo ($currentSubPage === 'thepeople') ? ' class="active"' : ''; ?>><?php echo $words->get('ThePeople') ?>The people behind</a></li>
            <li class="floatbox"><img src="styles/YAML/images/circle3.gif" class="float_left"> <a href="about/thestructures"<?php echo ($currentSubPage === 'thestructures') ? ' class="active"' : ''; ?>><?php echo $words->get('TheStructures') ?>The structures</a></li>
				
           </ul>

           <h3>More info</h3>
           <ul class="linklist">
            <li><a href="press"><?php echo $words->get('PressInfoPage') ?>Press info</a></li>	
            <li><a href="bod"><?php echo $words->get('BoardOfDirectorsPage') ?>Board of Directors</a></li>	
            <li><a href="http://blogs.bevolunteer.org"><?php echo $words->get('BeVolunteerBlogs') ?>BeVolunteer Blogs</a></li>
            <li><a href="terms"><?php echo $words->get('TermsPage') ?>Terms</a></li>
            <li><a href="privacy"><?php echo $words->get('PrivacyPage') ?>Privacy</a></li>

           </ul>