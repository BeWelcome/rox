// <?php
// /*

// Copyright (c) 2007 BeVolunteer

// This file is part of BW Rox.

// BW Rox is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

// BW Rox is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// You should have received a copy of the GNU General Public License
// along with this program; if not, see <http://www.gnu.org/licenses/> or 
// write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
// Boston, MA  02111-1307, USA.

// */
// $words = new MOD_words();
// ?>
// <div class="subcolumns">
  // <div class="c50l">
    // <div class="subcl">
		// <div><canvas id="members-alltime" height="200" ></canvas></div>
		// <div><canvas id="newmembers-alltime" height="200" ></canvas></div>
		// <div><canvas id="percentnewmembers-alltime" height="200" ></canvas></div>
		// <div><canvas id="messages-alltime" height="200" ></canvas></div>
		// <div><canvas id="loginperday-alltime" height="200" ></canvas></div>
		// <div><canvas id="percentloginperday-alltime" height="200" ></canvas></div>
		// <div><canvas id="lastlogin" height="200" ></canvas></div>

		// <div><canvas id="countryranking" height="200" ></canvas></div>

    // </div>
  // </div>
  // <div class="c50r">
    // <div class="subcr">
	
	
	
	
	    // </div>
  // </div>
// </div>	



// <script type="text/javascript" src="script/mochikit/MochiKit.js"></script>
// <script type="text/javascript" src="script/plotkit/Base.js"></script>
// <script type="text/javascript" src="script/plotkit/Layout.js"></script>
// <script type="text/javascript" src="script/plotkit/Canvas.js"></script>
// <script type="text/javascript" src="script/plotkit/SweetCanvas.js"></script>
// <?
 
// //get number of members per country
// $i=0;
// foreach ($countryrank as $key=>$val) {
	// $country[$i] = "\"".$key."\"";
	// $countrycnt[$i] = "[".$i.",". $val ."]";
	// $i++;
// }

// //get login rank
// $i=0;
// foreach ($loginrank as $key=>$val) {
	// $lastlogin[$i] = "\"".$key."\"";
	// $lastlogincnt[$i] = "[".$key.",". $val ."]";
	// $i++;
// }

// //get all values from stats table
// $i=0;

// foreach ($statsall as $val) {
	// $MembersTmp[$i] = round($val->NbActiveMembers);
    // $createdfull = split( " ",$val->created);
	// $created[$i] = "\"".$createdfull[0]."\"";
	// $NbActiveMembers[$i] = "[".$i.",". round($val->NbActiveMembers) ."]";
	// $NbNewMembersTmp[$i] = $MembersTmp[$i] - $MembersTmp[$i-1];
	// $NbNewMembers[$i] = "[".$i.",".$NbNewMembersTmp[$i] ."]";
	// $PercentNewMembers[$i] = "[".$i.",".round($NbNewMembersTmp[$i] / $MembersTmp[$i] * 100) ."]";
	// $NbMessageSent[$i] = "[".$i.",". round($val->NbMessageSent) ."]";
	// $NbMessageRead[$i] = "[".$i.",". round($val->NbMessageRead) ."]";
	// $NbMemberWithOneTrust[$i] = "[".$i.",". round($val->NbMemberWithOneTrust) ."]";
	// $PercentNbMemberWithOneTrust[$i] = "[".$i.",".round($NbMemberWithOneTrust[$i] / $MembersTmp[$i] * 100) ."]";
	// $NbMemberWhoLoggedToday[$i] = "[".$i.",". round($val->NbMemberWhoLoggedToday) ."]";
	// $PercentNbMemberWhoLoggedToday[$i] = "[".$i.",".round($NbMemberWhoLoggedToday[$i] / $MembersTmp[$i] * 100) ."]";
    // $i++;
 // }
 // $nbentries = $i;
 // $xticknb = 10;
 // $xticks = $nbentries / $xticknb;
 // for ($a=0; $a<=$xticknb; $a++) {
  // $xtick[$a] = round($a*$xticks);
  // }
 // $xtick[$xticknb] = $nbentries-1;

 
 
 
 // ?>
 // <script type="text/javascript">
 // <?
 // echo 'var NbActiveMembers = new Array ('.implode(',',$NbActiveMembers).'); ';
 // echo 'var NbNewMembers = new Array ('.implode(',',$NbNewMembers).'); ';
 // echo 'var PercentNewMembers = new Array ('.implode(',',$PercentNewMembers).'); ';
 // echo 'var NbMessageSent = new Array ('.implode(',',$NbMessageSent).'); ';
 // echo 'var NbMessageRead = new Array ('.implode(',',$NbMessageRead).'); ';
 // echo 'var NbMemberWithOneTrust = new Array ('.implode(',',$NbMemberWithOneTrust).'); ';
 // echo 'var PercentNbMemberWithOneTrust = new Array ('.implode(',',$PercentNbMemberWithOneTrust).'); ';
 // echo 'var NbMemberWhoLoggedToday = new Array ('.implode(',',$NbMemberWhoLoggedToday).'); ';
 // echo 'var PercentNbMemberWhoLoggedToday = new Array ('.implode(',',$PercentNbMemberWhoLoggedToday).'); ';
 // echo 'var xtick = new Array ('.implode(',',$xtick).'); ';
 // echo 'var created = new Array('.implode(', ',$created).'); ';
 // echo 'var country = new Array('.implode(', ',$country).'); ';
 // echo 'var countrycnt = new Array('.implode(', ',$countrycnt).'); ';
 // echo 'var lastlogin = new Array('.implode(', ',$lastlogin).'); '; 
 // echo 'var lastlogincnt = new Array('.implode(', ',$lastlogincnt).'); ';
 
 // ?> 
 // </script>


// <script type="text/javascript">
// var option = {
   // "IECanvasHTC": "/plotkit/iecanvas.htc",
   // "colorScheme": PlotKit.Base.palette(PlotKit.Base.baseColors()[0]),
   // "padding":{left: 60, right:20, top: 20, bottom: 40},
   // "xTicks": [
// <?
	// foreach ($xtick as $val) {
		// echo '{v:'.$val.', label:created['.$val.']},';
		// }
// ?>
		// ],
// };


// // members per countries 1

// var opt1 = {
   // "IECanvasHTC": "/plotkit/iecanvas.htc",
   // "colorScheme": PlotKit.Base.palette(PlotKit.Base.baseColors()[0]),
   // "padding":{left: 30, right:20, top: 20, bottom: 40},
   // "xTicks": [
// <?
	// foreach ($xtick as $val) {
		// echo '{v:'.$val.', label:created['.$val.']},';
		// }
// ?>
		// ],
// };

// function drawGraph1() {
    // var layout = new PlotKit.Layout("line", opt1);
	// layout.addDataset("db",NbActiveMembers);
    // layout.evaluate();
    // var canvas = MochiKit.DOM.getElement("members-alltime");
    // var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, opt1);
    // plotter.render();
// };
// MochiKit.DOM.addLoadEvent(drawGraph1);

// function drawGraph5() {
    // var layout = new PlotKit.Layout("line", opt1);
	// layout.addDataset("db",NbNewMembers);
    // layout.evaluate();
    // var canvas = MochiKit.DOM.getElement("newmembers-alltime");
    // var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, opt1);
    // plotter.render();
// };
// MochiKit.DOM.addLoadEvent(drawGraph5);

// function drawGraph6() {
    // var layout = new PlotKit.Layout("line", opt1);
	// layout.addDataset("db",PercentNewMembers);
    // layout.evaluate();
    // var canvas = MochiKit.DOM.getElement("percentnewmembers-alltime");
    // var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, opt1);
    // plotter.render();
// };
// MochiKit.DOM.addLoadEvent(drawGraph6);

// // number of members logged in per day, weekly average alltime.

// function drawGraph7() {
    // var layout = new PlotKit.Layout("line", opt1);
	// layout.addDataset("db",NbMemberWhoLoggedToday);
    // layout.evaluate();
    // var canvas = MochiKit.DOM.getElement("loginperday-alltime");
    // var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, opt1);
    // plotter.render();
// };
// MochiKit.DOM.addLoadEvent(drawGraph7);

// // number of members logged in per day, weekly average alltime.

// function drawGraph8() {
    // var layout = new PlotKit.Layout("line", opt1);
	// layout.addDataset("db",PercentNbMemberWhoLoggedToday);
    // layout.evaluate();
    // var canvas = MochiKit.DOM.getElement("percentloginperday-alltime");
    // var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, opt1);
    // plotter.render();
// };
// MochiKit.DOM.addLoadEvent(drawGraph8);

// // 3 - messages alltime

// var opt3 = {
   // "IECanvasHTC": "/plotkit/iecanvas.htc",
   // "colorScheme": PlotKit.Base.palette(PlotKit.Base.baseColors()[0]),

   // "xTicks": [
// <?
	// foreach ($xtick as $val) {
		// echo '{v:'.$val.', label:created['.$val.']},';
		// }
// ?>
		// ],
// };

// function drawGraph3() {
    // var layout = new PlotKit.Layout("line", opt3);
	// layout.addDataset("sent",NbMessageSent);
	// layout.addDataset("read",NbMessageRead);
    // layout.evaluate();
    // var canvas = MochiKit.DOM.getElement("messages-alltime");
    // var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, opt3);
    // plotter.render();
// };
// MochiKit.DOM.addLoadEvent(drawGraph3);

// // 4 -countryrank

// var opt4 = {
   // "IECanvasHTC": "/plotkit/iecanvas.htc",
   // "colorScheme": PlotKit.Base.palette(PlotKit.Base.baseColors()[0])
// };

// function drawGraph4() {
    // var layout = new PlotKit.Layout("line", opt4);
	// layout.addDataset("line1",lastlogincnt);
    // layout.evaluate();
    // var canvas = MochiKit.DOM.getElement("lastlogin");
    // var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, opt4);
    // plotter.render();
// };
// MochiKit.DOM.addLoadEvent(drawGraph4);



// // country rank
 // var opt2 = {
   // "IECanvasHTC": "/plotkit/iecanvas.htc",
   // "colorScheme": PlotKit.Base.palette(PlotKit.Base.baseColors()[0]),
   // "padding": {left: 0, right: 0, top: 10, bottom: 30},
   // "xTicks": [{v:0, label:country[0]}, 
          // {v:1, label:country[1]}, 
          // {v:2, label:country[2]},
          // {v:3, label:country[3]},
          // {v:4, label:country[4]}],
   // "drawYAxis": false
// };

// function drawGraph2() {
    // var layout = new PlotKit.Layout("pie", opt2);
	// layout.addDataset("db",countrycnt);
    // layout.evaluate();
    // var canvas = MochiKit.DOM.getElement("countryranking");
    // var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, opt2);
    // plotter.render();
// };
// MochiKit.DOM.addLoadEvent(drawGraph2);


// </script>


	
	