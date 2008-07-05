<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple PlaceSuite 330, 
Boston, MA  02111-1307, USA.

*/
$words = new MOD_words();
?>

<h2><?php echo $words->get('BoardOfDirectorsPage');?></h2>
<div class="subcolumns">
  <div class="c50l">
    <div class="subcl">

	<h3><?php echo $words->get('BoD_WhatIs');?></h3>
	<p><?php echo $words->getFormatted('BoD_WhatIsText','<a href="/bw/feedback.php">','</a>')?></p>

<h3><?php echo $words->get('BoD_WorkingTogetherTitle'); ?></h3>
<p><?php echo $words->get('BoD_WorkingTogetherText'); ?></p>
    </div>
   </div>

  <div class="c50r">
    <div class="subcr">
	
            <h3><?php echo $words->get('BoD_MainTasksTitle'); ?></h3>
            <p><?php echo $words->get('BoD_MainTasksText'); ?></p>
            <h3><?php echo $words->get('BoD_FinancingTitle'); ?></h3>
            <p><?php echo $words->getFormatted('BoD_FinancingText','<a href="http://www.bevolunteer.org/joomla/index.php/Donate!?Itemid=54&option=com_civicrm">','</a>'); ?></p>		  
    </div>
  </div>
</div>
	
<h3><?php echo $words->get('BoD_TheBoardMembers'); ?></h3>
<ul class="floatbox">
    <li class="userpicbox_big float_left"><h4><a href="user/thorgal67"><img src="http://www.bewelcome.org/memberphotos/thumbs/thorgal67_1187438461.square.80x80.jpg" class="framed float_left" style="height:70px; width: 70px;">Frank</a><br /><span class="small">Executive</span></h4>
    <p>
    <a href="javascript:;" id="infocol1" onclick="new Effect.BlindUp('info1', {duration: .3}); $('infocol1', 'infoexp1').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();" style="Display: none;"><< hide info</a> <a id="infoexp1" href="javascript:;" onclick="new Effect.BlindDown('info1', {duration: .3}); $('infocol1', 'infoexp1').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();"><?php echo $words->get('ShowInfoLink'); ?> >></a>
    </p>
    <div id="info1" style="Display: none;">
        <p>
        Frank is a translator (English/Italian), graduate in journalism and attending photography workshops. He started travelling from a very young age, first in Europe, later in North and Central America and South-East Asia. Frank firmly believes that meeting locals is what makes a trip so worthwhile and he likes to give back as a volunteer.
        </p>
        <p>
        He is also one of the founders of BeWelcome and BeVolunteer and feels that committing his work and enthusiasm to the project is much more rewarding than just donating money. Frank has followed the development of hospitality exchange networks from the very beginning and has extensively contributed to them as a host, guest and local volunteer.
        </p>
    </div>
    </li>
    <li class="userpicbox_big float_left">
        <h4>
        <a href="user/pietshah"><img src="http://www.bewelcome.org/memberphotos/thumbs/pietshah_1169955258.square.80x80.jpg" class="framed float_left" style="height:70px; width: 70px;">Pierre-Charles</a><br /><span class="small">Secretary</span>
        </h4>
        <p>
        <a href="javascript:;" id="infocol3" onclick="new Effect.BlindUp('info3', {duration: .3}); $('infocol3', 'infoexp3').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();" style="Display: none;"><< <?php echo $words->get('HideInfoLink'); ?></a> <a id="infoexp3" href="javascript:;" onclick="new Effect.BlindDown('info3', {duration: .3}); $('infocol3', 'infoexp3').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();"><?php echo $words->get('ShowInfoLink'); ?> >></a>
        </p>
        <div id="info3" style="Display: none;">
            <p>
            Pierre-Charles is studying architecture and currently lives in Caracas, Venezuela. He sees life like a journey: dreaming, reading a book, inventing new ways of working, meeting new people, receiving travelers at home, running the roads hitch-hiking or quietly living in a far isolated village.</p>

<p>He traveled mostly in Europe (motherland) and South America (heart land), and will probably continue to jump from one to the other for a while, between investigations about an integral architecture and social revolutions.</p>

<p>Pierre-Charles participated in the Hospitality movements from their beginning, and soon enjoyed traveling during his free time and receiving foreigners at home, making it a way of life. Co-founder of BeWelcome, he helps in various areas and mostly tries to maintain and develop the structure of BeVolunteer.
            </p>
        </div>
    </li>
    <li class="userpicbox_big float_left">
        <h4>
        <a href="user/claudiaab"><img src="http://www.bewelcome.org/memberphotos/thumbs/claudiaab_1169485927.square.80x80.jpg" class="framed float_left" style="height:70px; width: 70px;">Claudia</a><br /><span class="small">Vice Secretary</span>
        </h4>
        <p>
        <a href="javascript:;" id="infocol4" onclick="new Effect.BlindUp('info4', {duration: .3}); $('infocol4', 'infoexp4').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();" style="Display: none;"><< <?php echo $words->get('HideInfoLink'); ?></a> <a id="infoexp4" href="javascript:;" onclick="new Effect.BlindDown('info4', {duration: .3}); $('infocol4', 'infoexp4').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();"><?php echo $words->get('ShowInfoLink'); ?> >></a>
        </p>
        <div id="info4" style="Display: none;">
            <p>
Claudia is teaching English and Latin at secondary school in Aachen, Germany. She has always loved travelling and immersing herself into foreign cultures. Even with her parents they preferred to get in contact with locals so the idea of hospitality exchange came very natural to her. Claudia travelled a lot in Europe, but also to the U.S., Vietnam and India. Together with her boyfriend Frank she is now planning a trip to South Africa.   
            </p>
            <p>
            As volunteer for BeWelcome Claudia enjoys to contribute to the project as it enables others to benefit from hospitality exchange, too. Moreover, she got to know wonderful people through volunteering - some of whom have become valuable friends.
            </p>
        </div>    
    </li>
    <li class="userpicbox_big float_left">
    <div>
        <h4>
            <a href="user/tgoorden"><img src="https://www.bewelcome.org/bw/memberphotos/thumbs/tgoorden_1179743953.square.80x80.jpg" class="framed float_left" style="height:70px; width: 70px;">Thomas</a><br /><span class="small">Board Member</span>
        </h4>
    <p>
    <a href="javascript:;" id="infocol5" onclick="new Effect.BlindUp('info5', {duration: .3}); $('infocol5', 'infoexp5').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();" style="Display: none;"><< <?php echo $words->get('HideInfoLink'); ?></a> <a id="infoexp5" href="javascript:;" onclick="new Effect.BlindDown('info5', {duration: .3}); $('infocol5', 'infoexp5').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();"><?php echo $words->get('ShowInfoLink'); ?> >></a>
    </p>
    </div>
    <div id="info5" style="Display: none;">
        <p>
        </p>
    </div>    
    </li>
    <li class="userpicbox_big float_left">
    <div>
        <h4>
            <a href="user/jeanyves"><img src="http://www.bewelcome.org/memberphotos/thumbs/jeanyves_1167996233.square.100x100.jpg" class="framed float_left" style="height:70px; width: 70px;">JeanYves</a><br /><span class="small">Treasurer</span>
        </h4>
    <p>
    <a href="javascript:;" id="infocol5" onclick="new Effect.BlindUp('info5', {duration: .3}); $('infocol5', 'infoexp5').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();" style="Display: none;"><< <?php echo $words->get('HideInfoLink'); ?></a> <a id="infoexp5" href="javascript:;" onclick="new Effect.BlindDown('info5', {duration: .3}); $('infocol5', 'infoexp5').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();"><?php echo $words->get('ShowInfoLink'); ?> >></a>
    </p>
    </div>
    <div id="info5" style="Display: none;">
        <p>
            JeanYves, husband and father of three children, graduated in electricity and automation and is specialist in database design. He currently works in a sensor analysis laboratory, previous work areas are as diverse as nuclear and navigation systems, airports, pharmacy and military. The most "exotic" destinations JeanYves has visited so far include Japan, Azerbaijan and Southern Arabia.
        </p>
        <p>
            As co-founder and key programmer of the project BeWelcome JeanYves loves to contribute to hospitality and cultural sharing and he highly appreciates the unexpected ideas and new concepts you encounter when looking across personal borders. 
        </p>
    </div>    
    </li>
</ul>

<h3><?php echo $words->get('BoD_IWantToKnow'); ?></h3>
<p><?php echo $words->get('BoD_IWantToKnowText','<a href="http://bevolunteer.org/wiki"','</a>');?></p>
	 