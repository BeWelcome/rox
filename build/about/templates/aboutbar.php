<?php
$userbarText = array();
$words = new MOD_words();
?>

<h3 class="mt-3"><?php echo $words->get('About_AtAGlance') ?></h3>
<div class="list-group">
    <a href="about"
       class="list-group-item nav-link<?php echo ($currentSubPage === 'theidea') ? ' active' : ''; ?>"><?php echo $words->get('About_TheIdea') ?></a>
    <a href="about/thepeople"
       class="list-group-item nav-link<?php echo ($currentSubPage === 'thepeople') ? ' active' : ''; ?>"><?php echo $words->get('About_ThePeople') ?></a>
    <a href="about/getactive"
       class="list-group-item nav-link<?php echo ($currentSubPage === 'getactive') ? ' active' : ''; ?>"><?php echo $words->get('About_GetActive') ?></a>
</div>

<h3 class="mt-3"><?php echo $words->get('MoreInfo') ?></h3>
<div class="list-group">
    <a href="wiki/press%20information" class="list-group-item nav-link"><?php echo $words->get('PressInfoPage') ?></a>
    <a href="http://www.bevolunteer.org/about-bevolunteer/board-of-directors/" class="list-group-item nav-link"
           target="_blank"><?php echo $words->get('BoardOfDirectorsPage') ?></a>
    <a href="http://www.bevolunteer.org/" class="list-group-item nav-link" target="_blank"><?php echo $words->get('BeVolunteerBlogs') ?></a>
    <a href="terms" class="list-group-item nav-link"><?php echo $words->get('TermsPage') ?></a>
    <a href="privacy" class="list-group-item nav-link"><?php echo $words->get('PrivacyPage') ?></a>
    <a href="about/commentguidelines" class="list-group-item nav-link"><?php echo $words->get('CommentGuidelinesPage') ?></a>
    <a href="stats" class="list-group-item nav-link"><?php echo $words->get('StatsPage') ?></a>
    <a href="about/credits" class="list-group-item nav-link"><?php echo $words->get('credits.title') ?></a>
</div>
