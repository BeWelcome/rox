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
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/
$words = new MOD_words();
?>
<div class="subcolumns">
  <div class="c50l">
    <div class="subcl">
		<div><canvas id="members-alltime" height="200"></canvas></div>
		<div><canvas id="newmembers-alltime" height="200" ></canvas></div>
		<div><canvas id="percentnewmembers-alltime" height="200" ></canvas></div>
		<div><canvas id="messages-alltime" height="200" ></canvas></div>
		<div><canvas id="loginperday-alltime" height="200" ></canvas></div>
		<div><canvas id="percentloginperday-alltime" height="200" ></canvas></div>
		<div><canvas id="onetrust-alltime" height="200" ></canvas></div>
		<div><canvas id="percentonetrust-alltime" height="200" ></canvas></div>
		<div><canvas id="lastlogin" height="200" ></canvas></div>

    </div>
  </div>
  <div class="c50r">
    <div class="subcr">
		<div><canvas id="members-last" height="200"></canvas></div>
		<div><canvas id="newmembers-last" height="200" ></canvas></div>
		<div><canvas id="percentnewmembers-last" height="200" ></canvas></div>
		<div><canvas id="messages-last" height="200" ></canvas></div>
		<div><canvas id="loginperday-last" height="200" ></canvas></div>
		<div><canvas id="percentloginperday-last" height="200" ></canvas></div>
		<div><canvas id="onetrust-last" height="200" ></canvas></div>
		<div><canvas id="percentonetrust-last" height="200" ></canvas></div>	
		<div><canvas id="countryranking" height="200" ></canvas></div>	
	
	    </div>
  </div>
</div>	



<script type="text/javascript" src="script/mochikit/MochiKit.js"></script>
<script type="text/javascript" src="script/plotkit/Base.js"></script>
<script type="text/javascript" src="script/plotkit/Layout.js"></script>
<script type="text/javascript" src="script/plotkit/Canvas.js"></script>
<script type="text/javascript" src="script/plotkit/SweetCanvas.js"></script>
<?
 
//get number of members per country
$i=0;
foreach ($countryrank as $key=>$val) {
	$country[$i] = "\"".$key."\"";
	$countrycnt[$i] = "[".$i.",". $val ."]";
	$i++;
}

//get login rank
$i=0;
foreach ($loginrank as $key=>$val) {
	$lastlogin[$i] = "\"".$key."\"";
	$lastlogincnt[$i] = "[".$key.",". $val ."]";
	$i++;
}

//get all values from stats table
$i=0;

foreach ($statsall as $val) {
	$MembersTmp[$i] = round($val->NbActiveMembers);
	//prevent devision by zero 
		if ($MembersTmp[$i] == 0) {
			$MembersTmp[$i] = 1;
		}
    $createdfull = split( " ",$val->created);
	$created[$i] = "\"".$createdfull[0]."\"";
	$NbActiveMembers[$i] = "[".$i.",". round($val->NbActiveMembers) ."]";
	if ($i==0) {
	$NbNewMembersTmp[$i] = $MembersTmp[$i] - $MembersTmp[$i];
	} else {
	$NbNewMembersTmp[$i] = $MembersTmp[$i] - $MembersTmp[$i-1];
	}
	$NbNewMembers[$i] = "[".$i.",".$NbNewMembersTmp[$i] ."]";
	$PercentNewMembers[$i] = "[".$i.",".round($NbNewMembersTmp[$i] / $MembersTmp[$i] * 100) ."]";
	$NbMessageSent[$i] = "[".$i.",". round($val->NbMessageSent) ."]";
	$NbMessageRead[$i] = "[".$i.",". round($val->NbMessageRead) ."]";
	$NbMemberWithOneTrust[$i] = "[".$i.",". round($val->NbMemberWithOneTrust) ."]";
	$PercentNbMemberWithOneTrust[$i] = "[".$i.",".round($val->NbMemberWithOneTrust / $MembersTmp[$i] * 100) ."]";
	$NbMemberWhoLoggedToday[$i] = "[".$i.",". round($val->NbMemberWhoLoggedToday) ."]";
	$PercentNbMemberWhoLoggedToday[$i] = "[".$i.",".round($val->NbMemberWhoLoggedToday / $MembersTmp[$i] * 100) ."]";
    $i++;
 }
 $nbentries = $i;
 $xticknb = 5;
 $xticks = $nbentries / $xticknb;
 for ($a=0; $a<=$xticknb; $a++) {
  $xtick[$a] = round($a*$xticks);
  }
 $xtick[$xticknb] = $nbentries-1;

 //get all values from stats tabel (last 2 months)
 $i=0;
foreach ($statslast as $val) {
	$MembersLastTmp[$i] = round($val->NbActiveMembers);
	//prevent devision by zero 
		if ($MembersLastTmp[$i] == 0) {
			$MembersLastTmp[$i] = 1;
		}
    $createdfullLast = split( " ",$val->created);
	$createdLast[$i] = "\"".$createdfullLast[0]."\"";
	$NbActiveMembersLast[$i] = "[".$i.",". round($val->NbActiveMembers) ."]";
	if ($i==0){
	$NbNewMembersLastTmp[$i] = $MembersLastTmp[$i] - $MembersLastTmp[$i];
	} else {
	$NbNewMembersLastTmp[$i] = $MembersLastTmp[$i] - $MembersLastTmp[$i-1];
	}
	$NbNewMembersLast[$i] = "[".$i.",".$NbNewMembersLastTmp[$i] ."]";
	$PercentNewMembersLast[$i] = "[".$i.",".round($NbNewMembersLastTmp[$i] / $MembersLastTmp[$i] * 100) ."]";
	$NbMessageSentLast[$i] = "[".$i.",". round($val->NbMessageSent) ."]";
	$NbMessageReadLast[$i] = "[".$i.",". round($val->NbMessageRead) ."]";
	$NbMemberWithOneTrustLast[$i] = "[".$i.",". round($val->NbMemberWithOneTrust) ."]";
	$PercentNbMemberWithOneTrustLast[$i] = "[".$i.",".round($val->NbMemberWithOneTrust / $MembersLastTmp[$i] * 100) ."]";
	$NbMemberWhoLoggedTodayLast[$i] = "[".$i.",". round($val->NbMemberWhoLoggedToday) ."]";
	$PercentNbMemberWhoLoggedTodayLast[$i] = "[".$i.",".round($val->NbMemberWhoLoggedToday / $MembersLastTmp[$i] * 100) ."]";
    $i++;
 } 
 

 $lastnbentries = $i;
 $lastxticknb = 5;
 $lastxticks = $lastnbentries / $lastxticknb;
 for ($a=0; $a<=$lastxticknb; $a++) {
  $lastxtick[$a] = round($a*$lastxticks);
  }
 $lastxtick[$xticknb] = $lastnbentries-1; 
 
 ?>
 <script type="text/javascript">
 <?
 echo 'var NbActiveMembers = new Array ('.implode(',',$NbActiveMembers).'); ';
 echo 'var NbNewMembers = new Array ('.implode(',',$NbNewMembers).'); ';
 echo 'var PercentNewMembers = new Array ('.implode(',',$PercentNewMembers).'); ';
 echo 'var NbMessageSent = new Array ('.implode(',',$NbMessageSent).'); ';
 echo 'var NbMessageRead = new Array ('.implode(',',$NbMessageRead).'); ';
 echo 'var NbMemberWithOneTrust = new Array ('.implode(',',$NbMemberWithOneTrust).'); ';
 echo 'var PercentNbMemberWithOneTrust = new Array ('.implode(',',$PercentNbMemberWithOneTrust).'); ';
 echo 'var NbMemberWhoLoggedToday = new Array ('.implode(',',$NbMemberWhoLoggedToday).'); ';
 echo 'var PercentNbMemberWhoLoggedToday = new Array ('.implode(',',$PercentNbMemberWhoLoggedToday).'); ';
 echo 'var xtick = new Array ('.implode(',',$xtick).'); ';
 echo 'var created = new Array('.implode(', ',$created).'); ';
 echo 'var country = new Array('.implode(', ',$country).'); ';
 echo 'var countrycnt = new Array('.implode(', ',$countrycnt).'); ';
 echo 'var lastlogin = new Array('.implode(', ',$lastlogin).'); '; 
 echo 'var lastlogincnt = new Array('.implode(', ',$lastlogincnt).'); ';
 
 
 echo 'var NbActiveMembersLast = new Array ('.implode(',',$NbActiveMembersLast).'); ';
 echo 'var NbNewMembersLast = new Array ('.implode(',',$NbNewMembersLast).'); ';
 echo 'var PercentNewMembersLast = new Array ('.implode(',',$PercentNewMembersLast).'); ';
 echo 'var NbMessageSentLast = new Array ('.implode(',',$NbMessageSentLast).'); ';
 echo 'var NbMessageReadLast = new Array ('.implode(',',$NbMessageReadLast).'); ';
 echo 'var NbMemberWithOneTrustLast = new Array ('.implode(',',$NbMemberWithOneTrustLast).'); ';
 echo 'var PercentNbMemberWithOneTrustLast = new Array ('.implode(',',$PercentNbMemberWithOneTrustLast).'); ';
 echo 'var NbMemberWhoLoggedTodayLast = new Array ('.implode(',',$NbMemberWhoLoggedTodayLast).'); ';
 echo 'var PercentNbMemberWhoLoggedTodayLast = new Array ('.implode(',',$PercentNbMemberWhoLoggedTodayLast).'); ';
 echo 'var lastxtick = new Array ('.implode(',',$lastxtick).'); ';
 echo 'var createdLast = new Array('.implode(', ',$createdLast).'); ';

 ?> 
 </script>


<script type="text/javascript">

var opt1 = {
   "IECanvasHTC": "/plotkit/iecanvas.htc",
   "colorScheme": PlotKit.Base.palette(PlotKit.Base.baseColors()[0]),
   "padding":{left: 30, right:20, top: 20, bottom: 40},
   "xTicks": [
<?
	foreach ($xtick as $val) {
		echo '{v:'.$val.', label:created['.$val.']},';
		}
?>
		],
};

// number of members, weekly average all time

function drawGraph1() {
    var layout = new PlotKit.Layout("line", opt1);
	layout.addDataset("db",NbActiveMembers);
    layout.evaluate();
    var canvas = MochiKit.DOM.getElement("members-alltime");
    var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, opt1);
    plotter.render();
};
MochiKit.DOM.addLoadEvent(drawGraph1);


// number of new members per day, weekly average all time
function drawGraph5() {
    var layout = new PlotKit.Layout("line", opt1);
	layout.addDataset("db",NbNewMembers);
    layout.evaluate();
    var canvas = MochiKit.DOM.getElement("newmembers-alltime");
    var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, opt1);
    plotter.render();
};
MochiKit.DOM.addLoadEvent(drawGraph5);


//percentage of new members, weekly average all time
function drawGraph6() {
    var layout = new PlotKit.Layout("line", opt1);
	layout.addDataset("db",PercentNewMembers);
    layout.evaluate();
    var canvas = MochiKit.DOM.getElement("percentnewmembers-alltime");
    var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, opt1);
    plotter.render();
};
MochiKit.DOM.addLoadEvent(drawGraph6);

// number of members logged in per day, weekly average alltime.
function drawGraph7() {
    var layout = new PlotKit.Layout("line", opt1);
	layout.addDataset("db",NbMemberWhoLoggedToday);
    layout.evaluate();
    var canvas = MochiKit.DOM.getElement("loginperday-alltime");
    var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, opt1);
    plotter.render();
};
MochiKit.DOM.addLoadEvent(drawGraph7);

// percentage of members logged in per day, weekly average alltime.
function drawGraph8() {
    var layout = new PlotKit.Layout("line", opt1);
	layout.addDataset("db",PercentNbMemberWhoLoggedToday);
    layout.evaluate();
    var canvas = MochiKit.DOM.getElement("percentloginperday-alltime");
    var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, opt1);
    plotter.render();
};
MochiKit.DOM.addLoadEvent(drawGraph8);

// number of members who have at least one comment, weekly average alltime.
function drawGraph9() {
    var layout = new PlotKit.Layout("line", opt1);
	layout.addDataset("db",NbMemberWithOneTrust);
    layout.evaluate();
    var canvas = MochiKit.DOM.getElement("onetrust-alltime");
    var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, opt1);
    plotter.render();
};
MochiKit.DOM.addLoadEvent(drawGraph9);

//percent of members who have at least one comment, weekly average alltime.
function drawGraph10() {
    var layout = new PlotKit.Layout("line", opt1);
	layout.addDataset("db",PercentNbMemberWithOneTrust);
    layout.evaluate();
    var canvas = MochiKit.DOM.getElement("percentonetrust-alltime");
    var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, opt1);
    plotter.render();
};
MochiKit.DOM.addLoadEvent(drawGraph10);

// 3 - messages alltime

function drawGraph3() {
    var layout = new PlotKit.Layout("line", opt1);
	layout.addDataset("sent",NbMessageSent);
	layout.addDataset("read",NbMessageRead);
    layout.evaluate();
    var canvas = MochiKit.DOM.getElement("messages-alltime");
    var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, opt1);
    plotter.render();
};
MochiKit.DOM.addLoadEvent(drawGraph3);

///last two months, no averaging 

var opt3 = {
   "IECanvasHTC": "/plotkit/iecanvas.htc",
   "colorScheme": PlotKit.Base.palette(PlotKit.Base.baseColors()[0]),
   "padding":{left: 30, right:20, top: 20, bottom: 40},
   "xTicks": [
<?
	foreach ($lastxtick as $val) {
		echo '{v:'.$val.', label:createdLast['.$val.']},';
		}
?>
		],
};



// number of members,last two months
function drawGraph11() {
    var layout = new PlotKit.Layout("line", opt3);
	layout.addDataset("db",NbActiveMembersLast);
    layout.evaluate();
    var canvas = MochiKit.DOM.getElement("members-last");
    var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, opt3);
    plotter.render();
};
MochiKit.DOM.addLoadEvent(drawGraph11);


// number of new members per day, last two months
function drawGraph15() {
    var layout = new PlotKit.Layout("line", opt3);
	layout.addDataset("db",NbNewMembersLast);
    layout.evaluate();
    var canvas = MochiKit.DOM.getElement("newmembers-last");
    var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, opt3);
    plotter.render();
};
MochiKit.DOM.addLoadEvent(drawGraph15);


//percentage of new members, last two months
function drawGraph16() {
    var layout = new PlotKit.Layout("line", opt3);
	layout.addDataset("db",PercentNewMembersLast);
    layout.evaluate();
    var canvas = MochiKit.DOM.getElement("percentnewmembers-last");
    var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, opt3);
    plotter.render();
};
MochiKit.DOM.addLoadEvent(drawGraph16);

// number of members logged in per day, last two months
function drawGraph17() {
    var layout = new PlotKit.Layout("line", opt3);
	layout.addDataset("db",NbMemberWhoLoggedTodayLast);
    layout.evaluate();
    var canvas = MochiKit.DOM.getElement("loginperday-last");
    var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, opt3);
    plotter.render();
};
MochiKit.DOM.addLoadEvent(drawGraph17);

// percentage of members logged in per day, last two months.
function drawGraph18() {
    var layout = new PlotKit.Layout("line", opt3);
	layout.addDataset("db",PercentNbMemberWhoLoggedTodayLast);
    layout.evaluate();
    var canvas = MochiKit.DOM.getElement("percentloginperday-last");
    var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, opt3);
    plotter.render();
};
MochiKit.DOM.addLoadEvent(drawGraph18);

// number of members who have at least one comment,last two months.
function drawGraph19() {
    var layout = new PlotKit.Layout("line", opt3);
	layout.addDataset("db",NbMemberWithOneTrustLast);
    layout.evaluate();
    var canvas = MochiKit.DOM.getElement("onetrust-last");
    var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, opt3);
    plotter.render();
};
MochiKit.DOM.addLoadEvent(drawGraph19);

//percent of members who have at least one comment, last two months.
function drawGraph20() {
    var layout = new PlotKit.Layout("line", opt3);
	layout.addDataset("db",PercentNbMemberWithOneTrustLast);
    layout.evaluate();
    var canvas = MochiKit.DOM.getElement("percentonetrust-last");
    var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, opt3);
    plotter.render();
};
MochiKit.DOM.addLoadEvent(drawGraph20);

// 3 - messages last two months

function drawGraph13() {
    var layout = new PlotKit.Layout("line", opt3);
	layout.addDataset("sent",NbMessageSentLast);
	layout.addDataset("read",NbMessageReadLast);
    layout.evaluate();
    var canvas = MochiKit.DOM.getElement("messages-last");
    var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, opt3);
    plotter.render();
};
MochiKit.DOM.addLoadEvent(drawGraph13);



// 4 - last login
var opt4 = {
   "IECanvasHTC": "/plotkit/iecanvas.htc",
   "colorScheme": PlotKit.Base.palette(PlotKit.Base.baseColors()[0])
};

function drawGraph4() {
    var layout = new PlotKit.Layout("line", opt4);
	layout.addDataset("line1",lastlogincnt);
    layout.evaluate();
    var canvas = MochiKit.DOM.getElement("lastlogin");
    var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, opt4);
    plotter.render();
};
MochiKit.DOM.addLoadEvent(drawGraph4);



// country rank
 var opt2 = {
   "IECanvasHTC": "/plotkit/iecanvas.htc",
   "colorScheme": PlotKit.Base.palette(PlotKit.Base.baseColors()[0]),
   "padding": {left: 0, right: 0, top: 10, bottom: 30},
   "xTicks": [{v:0, label:country[0]}, 
          {v:1, label:country[1]}, 
          {v:2, label:country[2]},
          {v:3, label:country[3]},
          {v:4, label:country[4]}],
   "drawYAxis": false
};

function drawGraph2() {
    var layout = new PlotKit.Layout("pie", opt2);
	layout.addDataset("db",countrycnt);
    layout.evaluate();
    var canvas = MochiKit.DOM.getElement("countryranking");
    var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, opt2);
    plotter.render();
};
MochiKit.DOM.addLoadEvent(drawGraph2);


</script>


	
	