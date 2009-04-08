<?php
$userbarText = array();
$words = new MOD_words();
?>

        <h3><?php echo $words->get('About_AtAGlance') ?></h3>
        <ul class="linklist">
            <li class="floatbox"><img src="styles/YAML/images/circle1.gif" class="float_left" alt="circle1" /> <a href="about"<?php echo ($currentSubPage === 'theidea') ? ' class="active"' : ''; ?>><?php echo $words->get('About_TheIdea') ?></a></li>
            <li class="floatbox"><img src="styles/YAML/images/circle2.gif" class="float_left" alt="circle2" /> <a href="about/thepeople"<?php echo ($currentSubPage === 'thepeople') ? ' class="active"' : ''; ?>><?php echo $words->get('About_ThePeople') ?></a></li>
            <li class="floatbox"><img src="styles/YAML/images/circle3.gif" class="float_left" alt="circle3" /> <a href="about/getactive"<?php echo ($currentSubPage === 'getactive') ? ' class="active"' : ''; ?>><?php echo $words->get('About_GetActive') ?></a></li>
        </ul>
        <h3><?php echo $words->get('MoreInfo') ?></h3>
        <ul class="linklist">
            <li><a href="press"><?php echo $words->get('PressInfoPage') ?></a></li>
            <li><a href="bod"><?php echo $words->get('BoardOfDirectorsPage') ?></a></li>
            <li><a href="http://blogs.bevolunteer.org"><?php echo $words->get('BeVolunteerBlogs') ?></a></li>
            <li><a href="terms"><?php echo $words->get('TermsPage') ?></a></li>
            <li><a href="privacy"><?php echo $words->get('PrivacyPage') ?></a></li>
            <li><a href="stats"><?php echo $words->get('StatsPage') ?></a></li>
        </ul>

<?
/*
 * TODO: fix this menu for shops
 * 
 * 
<!-- [START_FASTMENU] -->
<div id="schnellzugriff">
<li><a href="about" class="schnellzugriff" id="schnellzugriff_a" onmouseover="einblenden()" onmouseout="ausblenden()">Shop <img src="styles/YAML/images/sign_pagedown.gif" id="linkadvancedimage"/></a></li>
<ul id="schnellzugriff_ul" onmouseover="einblenden()" onmouseout="ausblenden()">
<!-- [START_INCLUDE] -->
<li class="first">
    <ul class="second">

    <li><a href="shop/world/">Australia</a></li>
    <li><a href="shop/europe/">Austria</a></li>
         <li><a href="shop/europe/">Belgium</a></li>
         <li><a href="shop/world/">Canada</a></li>
         <li><a href="shop/world/">China</a></li>
         <li><a href="shop/europe/">Cyprus</a></li>
         <li><a href="shop/europe/">Czech Republic</a></li>
         <li><a href="shop/europe/">Denmark</a></li>
         <li><a href="shop/europe/">Estonia</a></li>
         <li><a href="shop/europe/">Finnland</a></li>
         <li><a href="shop/europe/">France</a></li>
         <li><a href="shop/europe/">Germany</a></li>
         <li><a href="shop/europe/">Greece</a></li>
         <li><a href="shop/europe/">Hungary</a></li>
         <li><a href="shop/europe/">Iceland</a></li>
         <li><a href="shop/europe/">Ireland</a></li>
         <li><a href="shop/europe/">Italy</a></li>
         <li><a href="shop/world/">Japan</a></li>
         <li><a href="shop/world/">Korea (Republic)</a></li>
         <li><a href="shop/europe/">Latvia</a></li>
         <li><a href="shop/europe/">Liechtenstein</a></li>
         <li><a href="shop/europe/">Lithuania</a></li>
         <li><a href="shop/europe/">Luxemburg</a></li>
         <li><a href="shop/europe/">Malta</a></li>
         <li><a href="shop/world/">Mexico</a></li>
         <li><a href="shop/europe/">Monaco</a></li>
         <li><a href="shop/world/">New Zealand</a></li>
         <li><a href="shop/europe/">Norway</a></li>
         <li><a href="shop/europe/">Poland</a></li>
         <li><a href="shop/europe/">Portugal</a></li>
         <li><a href="shop/world/">Puerto Rico</a></li>
         <li><a href="shop/europe/">Singapore</a></li>
         <li><a href="shop/europe/">Slovakia</a></li>
         <li><a href="shop/europe/">Slovenia</a></li>
         <li><a href="shop/europe/">Spain</a></li>
         <li><a href="shop/europe/">Sweden</a></li>
         <li><a href="shop/europe/">Switzerland</a></li>
         <li><a href="shop/europe/">The Netherlands</a></li>
         <li><a href="shop/europe/">United Kingdom</a></li>
         <li><a href="shop/world/">United States</a></li>
    </ul>

</li>
<!-- [STOP_INCLUDE] -->
</ul>
</div><!-- close #fastmenu -->
<!-- [STOP_FASTMENU] -->
<li><?php echo $words->get('Choose your shipping country') ?></li>
        </ul>
*/
?>
