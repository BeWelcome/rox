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
$words = new MOD_words($this->getSession());
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
            <p><?php echo $words->getFormatted('BoD_FinancingText','<a href="donate">','</a>'); ?></p>
    </div>
  </div>
</div>

<h3><?php echo $words->get('BoD_TheBoardMembers'); ?></h3>
<ul class="clearfix">
    <li class="userpicbox_big float_left"><h4><a href="members/thorgal67"><img src="http://www.bewelcome.org/members/avatar/thorgal67" class="framed float_left" style="height:70px; width: 70px;" alt="Frank" />Frank</a><br /><span class="small"><?php echo $words->get('BoD_RoleOfThorgal67'); ?></span></h4>
    <p>
    <a href="javascript:;" id="infocol1" onclick="new Effect.BlindUp('info1', {duration: .3}); $('infocol1', 'infoexp1').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();" style="Display: none;">&lt;&lt; hide info</a> <a id="infoexp1" href="javascript:;" onclick="new Effect.BlindDown('info1', {duration: .3}); $('infocol1', 'infoexp1').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();"><?php echo $words->get('ShowInfoLink'); ?> &gt;&gt;</a>
    </p>
    <div id="info1" style="Display: none;">
        <p>
         <?php echo $words->get('BoD_TheBoardThorgal67_1'); ?>
        </p>
        <p>
         <?php echo $words->get('BoD_TheBoardThorgal67_2'); ?>
        </p>
    </div>
    </li>
    <li class="userpicbox_big float_left">
        <h4>
        <a href="user/pietshah"><img src="http://www.bewelcome.org/members/avatar/pietshah" class="framed float_left" style="height:70px; width: 70px;" alt="Pierre-Charles" />Pierre-Charles</a><br /><span class="small"><?php echo $words->get('BoD_RoleOfPietshah'); ?></span>
        </h4>
        <p>
        <a href="javascript:;" id="infocol3" onclick="new Effect.BlindUp('info3', {duration: .3}); $('infocol3', 'infoexp3').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();" style="Display: none;">&lt;&lt; <?php echo $words->get('HideInfoLink'); ?></a> <a id="infoexp3" href="javascript:;" onclick="new Effect.BlindDown('info3', {duration: .3}); $('infocol3', 'infoexp3').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();"><?php echo $words->get('ShowInfoLink'); ?> &gt;&gt;</a>
        </p>
        <div id="info3" style="Display: none;">
        <p>
         <?php echo $words->get('BoD_TheBoardPietshah_1'); ?>
        </p>
        <p>
         <?php echo $words->get('BoD_TheBoardPietshah_2'); ?>
        </p>
        </div>
    </li>
    <li class="userpicbox_big float_left">
        <h4>
        <a href="user/claudiaab"><img src="http://www.bewelcome.org/members/avatar/claudiaab" class="framed float_left" style="height:70px; width: 70px;" alt="Claudia" />Claudia</a><br /><span class="small"><?php echo $words->get('BoD_RoleOfClaudiaab'); ?></span>
        </h4>
        <p>
        <a href="javascript:;" id="infocol4" onclick="new Effect.BlindUp('info4', {duration: .3}); $('infocol4', 'infoexp4').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();" style="Display: none;">&lt;&lt;<?php echo $words->get('HideInfoLink'); ?></a> <a id="infoexp4" href="javascript:;" onclick="new Effect.BlindDown('info4', {duration: .3}); $('infocol4', 'infoexp4').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();"><?php echo $words->get('ShowInfoLink'); ?> &gt;&gt;</a>
        </p>
        <div id="info4" style="Display: none;">
        <p>
         <?php echo $words->get('BoD_TheBoardClaudiaab_1'); ?>
        </p>
        <p>
         <?php echo $words->get('BoD_TheBoardClaudiaab_2'); ?>
        </p>
        </div>
    </li>
    <li class="userpicbox_big float_left">
    <div>
        <h4>
            <a href="user/tgoorden"><img src="https://www.bewelcome.org/members/avatar/tgoorden" class="framed float_left" style="height:70px; width: 70px;" alt="Thomas" />Thomas</a><br /><span class="small"><?php echo $words->get('BoD_RoleOfTgoorden'); ?></span>
        </h4>
    <p>
    <a href="javascript:;" id="infocol5" onclick="new Effect.BlindUp('info5', {duration: .3}); $('infocol5', 'infoexp5').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();" style="Display: none;">&lt;&lt; <?php echo $words->get('HideInfoLink'); ?></a> <a id="infoexp5" href="javascript:;" onclick="new Effect.BlindDown('info5', {duration: .3}); $('infocol5', 'infoexp5').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();"><?php echo $words->get('ShowInfoLink'); ?> &gt;&gt;</a>
    </p>
    </div>
    <div id="info5" style="Display: none;">
        <p>
         <?php echo $words->get('BoD_TheBoardTgoorden_1'); ?>
        </p>
        <p>
         <?php echo $words->get('BoD_TheBoardTgoorden_2'); ?>
        </p>
    </div>
    </li>
    <li class="userpicbox_big float_left">
    <div>
        <h4>
            <a href="user/jeanyves"><img src="http://www.bewelcome.org/members/avatar/jeanyves" class="framed float_left" style="height:70px; width: 70px;" alt="Jean- Yves" />JeanYves</a><br /><span class="small"><?php echo $words->get('BoD_RoleOfJeanYves'); ?></span>
        </h4>
    <p>
    <a href="javascript:;" id="infocol6" onclick="new Effect.BlindUp('info5', {duration: .3}); $('infocol6', 'infoexp6').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();" style="Display: none;">&lt;&lt; <?php echo $words->get('HideInfoLink'); ?></a> <a id="infoexp6" href="javascript:;" onclick="new Effect.BlindDown('info6', {duration: .3}); $('infocol6', 'infoexp6').each(function(el){ Effect.toggle(el, 'appear', {duration: 0}) })" onfocus="blur();"><?php echo $words->get('ShowInfoLink'); ?> &gt;&gt;</a>
    </p>
    </div>
    <div id="info6" style="Display: none;">
        <p>
         <?php echo $words->get('BoD_TheBoardJeanyves_1'); ?>
        </p>
        <p>
         <?php echo $words->get('BoD_TheBoardJeanyves_2'); ?>
        </p>
    </div>
    </li>
</ul>

<h3><?php echo $words->get('BoD_IWantToKnow'); ?></h3>
<p><?php echo $words->get('BoD_IWantToKnowText','<a href="http://bevolunteer.org/wiki"','</a>');?></p>

