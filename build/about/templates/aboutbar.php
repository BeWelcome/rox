<?php
$userbarText = array();
$words = new MOD_words();
?>

        <h3><?php echo $words->get('About_AtAGlance') ?></h3>
        <ul class="linklist">
            <li class="circle1"><a href="about"<?php echo ($currentSubPage === 'theidea') ? ' class="active"' : ''; ?>><?php echo $words->get('About_TheIdea') ?></a></li>
            <li class="circle2"><a href="about/thepeople"<?php echo ($currentSubPage === 'thepeople') ? ' class="active"' : ''; ?>><?php echo $words->get('About_ThePeople') ?></a></li>
            <li class="circle3"><a href="about/getactive"<?php echo ($currentSubPage === 'getactive') ? ' class="active"' : ''; ?>><?php echo $words->get('About_GetActive') ?></a></li>
        </ul>
        <h3><?php echo $words->get('MoreInfo') ?></h3>
        <ul class="linklist">
            <li><a href="wiki/press%20information"><?php echo $words->get('PressInfoPage') ?></a></li>
            <li><a href="http://www.bevolunteer.org/about-bevolunteer/board-of-directors/" target="_blank"><?php echo $words->get('BoardOfDirectorsPage') ?></a></li>
            <li><a href="http://www.bevolunteer.org/" target="_blank"><?php echo $words->get('BeVolunteerBlogs') ?></a></li>
            <li><a href="terms"><?php echo $words->get('TermsPage') ?></a></li>
            <li><a href="privacy"><?php echo $words->get('PrivacyPage') ?></a></li>
            <li><a href="stats"><?php echo $words->get('StatsPage') ?></a></li>
        </ul>
