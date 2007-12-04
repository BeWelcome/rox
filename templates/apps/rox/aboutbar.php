<?php
$userbarText = array();
$words = new MOD_words();
?>

           <h3>BeWelcome in 3 steps</h3>
           <ul class="linklist">
           
            <li>1 | <a href="about"><?php echo $words->get('TheIdea') ?>The idea</a></li>
            <li>2 | <a href="bod"><?php echo $words->get('ThePeople') ?>The people</a></li>
            <li>3 | <a href="privacy"><?php echo $words->get('TheStructures') ?>The structures</a></li>
				
           </ul>

           <h3>More info</h3>
           <ul class="linklist">
            <li><a href="bod"><?php echo $words->get('BoardOfDirectorsPage') ?>Board of Directors</a></li>	
            <li><a href="http://blogs.bevolunteer.org"><?php echo $words->get('BeVolunteerBlogs') ?>BeVolunteer Blogs</a></li>
            <li><a href="terms"><?php echo $words->get('TermsPage') ?>Terms</a></li>
            <li><a href="privacy"><?php echo $words->get('PrivacyPage') ?>Privacy</a></li>

           </ul>