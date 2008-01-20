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
	<p><?php echo $words->get('BoD_WhatIsText','<a href="/bw/feedback.php">','</a>')?></p>

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
    <li class="userpicbox_big float_left"><h4><a href="user/kiwiflave"><img src="http://www.bewelcome.org/memberphotos/thumbs/kiwiflave_1171823734.square.80x80.jpg" class="framed float_left" style="height:70px; width: 70px;">Florian</a><br /><span class="small">Vice Executive</span></h4>
    <p>
    <a href="javascript:;" id="infocol2" onclick="new Effect.BlindUp('info2', {duration: .3}); $('infocol2', 'infoexp2').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();" style="Display: none;"><< hide info </a> <a id="infoexp2" href="javascript:;" onclick="new Effect.BlindDown('info2', {duration: .3}); $('infocol2', 'infoexp2').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();"><?php echo $words->get('ShowInfoLink'); ?> >></a>
    </p>
    <div id="info2" style="Display: none;">
        <p>
        Florian is not only an avid traveller but has also  graduated in tourism management which makes him an expert in the travel community. At the age of 19 he went backpacking around New Zealand and loved it. Since then he has lived in Munich, Freiburg (both in Germany) and Eastbourne (UK). An entrepreneur at heart Florian currently works as freelance journalist and copy writer based in Barcelona, Spain. He loves languages, the beauty of nature and a glas of good wine.
        </p>
        <p>
        Now both "talent scout" and volunteer coordinator for BeWelcome he has previously been focussing on public relations and content writing. Florian is currently enrolling in a docorate programme in small business management and entrepreneurship at the UAB, Barcelona. 
        </p>
    </div>
    </li>
    <li class="userpicbox_big float_left">
        <h4>
        <a href="user/pietshah"><img src="http://www.bewelcome.org/memberphotos/thumbs/pietshah_1169955258.square.80x80.jpg" class="framed float_left" style="height:70px; width: 70px;">Pierre-Charles</a><br /><span class="small">Secretary</span>
        </h4>
        <p>
        <a href="javascript:;" id="infocol3" onclick="new Effect.BlindUp('info3', {duration: .3}); $('infocol3', 'infoexp3').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();" style="Display: none;"><< hide info </a> <a id="infoexp3" href="javascript:;" onclick="new Effect.BlindDown('info3', {duration: .3}); $('infocol3', 'infoexp3').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();"><?php echo $words->get('ShowInfoLink'); ?> >></a>
        </p>
        <div id="info3" style="Display: none;">
            <p>
            No info yet. Please look at <a href="user/pietshah">Pierre-Charles' profile</a>.
            </p>
        </div>
    </li>
    <li class="userpicbox_big float_left">
        <h4>
        <a href="user/claudiaab"><img src="http://www.bewelcome.org/memberphotos/thumbs/claudiaab_1169485927.square.80x80.jpg" class="framed float_left" style="height:70px; width: 70px;">Claudia</a><br /><span class="small">Vice Secretary</span>
        </h4>
        <p>
        <a href="javascript:;" id="infocol4" onclick="new Effect.BlindUp('info4', {duration: .3}); $('infocol4', 'infoexp4').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();" style="Display: none;"><< hide info </a> <a id="infoexp4" href="javascript:;" onclick="new Effect.BlindDown('info4', {duration: .3}); $('infocol4', 'infoexp4').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();"><?php echo $words->get('ShowInfoLink'); ?> >></a>
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
            <a href="user/loulou"><img src="http://www.bewelcome.org/memberphotos/thumbs/jeanyves_1167996233.square.100x100.jpg" class="framed float_left" style="height:70px; width: 70px;">JeanYves</a><br /><span class="small">Treasurer</span>
        </h4>
    <p>
    <a href="javascript:;" id="infocol5" onclick="new Effect.BlindUp('info5', {duration: .3}); $('infocol5', 'infoexp5').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();" style="Display: none;"><< hide info </a> <a id="infoexp5" href="javascript:;" onclick="new Effect.BlindDown('info5', {duration: .3}); $('infocol5', 'infoexp5').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();"><?php echo $words->get('ShowInfoLink'); ?> >></a>
    </p>
    </div>
    <div id="info5" style="Display: none;">
        <p>
            JeanYves, husband and father of two children, graduated in electricity and automation and is specialist in database design. He currently works in a sensor analysis laboratory, previous work areas are as diverse as nuclear and navigation systems, airports, pharmacy and military. The most "exotic" destinations JeanYves has visited so far include Japan, Azerbaijan and Southern Arabia.
        </p>
        <p>
            As co-founder and key programmer of the project BeWelcome JeanYves loves to contribute to hospitality and cultural sharing and he highly appreciates the unexpected ideas and new concepts you encounter when looking across personal borders. 
        </p>
    </div>    
    </li>
    <li class="userpicbox_big float_left">
        <h4>
        <a href="user/lupochen"><img src="http://www.bewelcome.org/memberphotos/thumbs/lupochen_1168442549.square.100x100.jpg" class="framed float_left" style="height:70px; width: 70px;">Micha</a><br /><span class="small">Vice Treasurer</span>
        </h4>
        <p>
        <a href="javascript:;" id="infocol6" onclick="new Effect.toggle('info6'); $('infocol6', 'infoexp6').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();" style="Display: none;"><< hide info </a> <a id="infoexp6" href="javascript:;" onclick="new Effect.toggle('info6'); $('infocol7', 'infoexp7').each(function(el){ Effect.toggle(el) })" onfocus="blur();"><?php echo $words->get('ShowInfoLink'); ?> >></a>
        </p>
        <div id="info6" style="Display: none;">
            <p>
            Micha is both a student of humanities and a passioned designer which puts him to the core of our creative team. After years of environmental activism he took off to study heartfully and find out more about this world. Hitchhiking and riding trains he made his way to Sibiria, Japan and South Corea. Still this leaves him stunned for all the hospitality that he experienced along the way. Back in Berlin, Europe, he likes to write, to draw and climb roofs from time to time.
            </p>
            <p>
            Still struggling with the limits of his own knowledge of coding he is often trying to bring new features for our website to life. While this is not an easy task, he also has to and likes to work as a freelance in graphics design. Apart from this, Micha is currently finishing his master at the Humboldt-University.
            </p>
        </div>
    </li>
    <li class="userpicbox_big float_left">
    <h4>
    <a href="user/irinka"><img src="http://www.bewelcome.org/memberphotos/thumbs/irinka_1170020723.square.80x80.jpg" class="framed float_left" style="height:70px; width: 70px;">Irina</a><br /><span class="small">Board Member</span>
    </h4>
    <p>
    <a href="javascript:;" id="infocol7" onclick="new Effect.BlindUp('info7', {duration: .3}); $('infocol7', 'infoexp7').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();" style="Display: none;"><< hide info </a> <a id="infoexp7" href="javascript:;" onclick="new Effect.BlindDown('info7', {duration: .3}); $('infocol7', 'infoexp7').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();"><?php echo $words->get('ShowInfoLink'); ?> >></a>
    </p>
    <div id="info7" style="Display: none;">
        <p>
        Irina is a project manager dealing with translation and editing and working for her nice small company from Klin, Moscow and sometimes from stranger places like Hamburg. She enjoys traveling (hitchhiking is the best key) and hosting her friends and guests from Hospex world. In BeWelcome project Irina deals with translation and safety issues. She started to be active in hospitality exchange being totally fascinated about the very idea of sharing homes with strangers. But it turned out that hospitality does not only open ways for better traveling, but also teaches, brings to new realities and great people. And Irina wants to share her discoveries it with other people.
        </p>
    </div>
    </li>
    <li class="userpicbox_big float_left">
    <h4>
    <a href="user/junglerover"><img src="http://www.bewelcome.org/memberphotos/thumbs/junglerover_1170585974.square.80x80.jpg" class="framed float_left" style="height:70px; width: 70px;">Marco</a><br /><span class="small">Board Member</span>
    </h4>
    <p>
    <a href="javascript:;" id="infocol8" onclick="new Effect.BlindUp('info8', {duration: .3}); $('infocol8', 'infoexp8').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();" style="Display: none;"><< hide info </a> <a id="infoexp8" href="javascript:;" onclick="new Effect.BlindDown('info8', {duration: .3}); $('infocol8', 'infoexp8').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();"><?php echo $words->get('ShowInfoLink'); ?> >></a>
    </p>
    <div id="info8" style="Display: none;">
        <p>
            No info yet. Please look at <a href="user/junglerover">Marco's profile</a>.
        </p>
    </div>
    </li>
    <li class="userpicbox_big float_left">
        <h4>
        <a href="user/fake51"><img src="http://www.bewelcome.org/memberphotos/thumbs/fake51_1171292120.square.80x80.jpg" class="framed float_left" style="height:70px; width: 70px;">Peter</a><br /><span class="small">Board Member</span>
        </h4>
        <p>
        <a href="javascript:;" id="infocol9" onClick="new Effect.BlindUp('info9', {duration: .3}); $('infocol9', 'infoexp9').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();" style="Display: none;"><< hide info </a> <a id="infoexp9" href="javascript:;" onclick="new Effect.BlindDown('info9', {duration: .3}); $('infocol9', 'infoexp9').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();"><?php echo $words->get('ShowInfoLink'); ?> >></a>
        </p>
        <div id="info9" style="Display: none;">
            <p>
            Peter, native Danish, currently lives in London, UK, where he works in a publishing company dealing with healthcare and academic content. He is also studying philosophy and has extensively travelled throughout Europe. His main reason for volunteering for BeWelcome are the project's ideals and he strongly believes that it deserves every bit of support it can get. Future travel plans include Australia, China, South America and Canada.
            </p>
        </div>
    </li>
</ul>

<h3><?php echo $words->get('BoD_IWantToKnow'); ?></h3>
<p><?php echo $words->get('BoD_IWantToKnowText','<a href="http://bevolunteer.org/wiki"','</a>');?></p>
	 