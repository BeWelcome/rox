<div>
<form method="post" action="events/joinleave" class="def-form" id="event-create-form">
<fieldset>
    <legend>Details</legend>
    <div class="subcolumns">
    <input type='hidden' name='member_id' value='158' />
    <input type='hidden' name='event_id' value='70' />
        <div class="c33l">
        <label for="event-name">Name:</label><br/>
        <input type="text" id="event-name" name="event-name" class="long" value="Name of the event..." readonly="readonly"/>
        </div>
        <div class="c33l">
        <label for="event-start-date">Begin date:</label><br />
        <input type="text" id="event-start-date" name="startdate" class="date" maxlength="10" style="width:9em" value="Jan 10, 2013"  readonly="readonly"/>
        </div>
        <div class="c33r">
        <label for="event-start-time">Begin time:</label><br />
        <input type="text" id="event-start-time" name="event-start-time" class="time" maxlength="10" style="width:9em" value="10:00am"  readonly="readonly"/>
        </div>
    </div>
    <div class="subcolumns">
        <div class="c33l">
            <label for="event-address">Address:</label><br/>
            <input type="text" id="event-address" name="event-address" class="long" value="Address of the event..."  readonly="readonly"/>
        </div>
        <div class="c33l">
            <label for="event-end-date">End date:</label><br />
            <input type="text" id="event-end-date" name="enddate" class="date" maxlength="10" style="width:9em" value="Jan 10, 2013"  readonly="readonly"/>
        </div>
        <div class="c33r">
            <label for="event-end-time">End time:</label><br />
            <input type="text" id="event-end-time" name="event-end-time" class="time" maxlength="10" style="width:9em" value="1:00pm"  readonly="readonly"/>
        </div>
    </div>
    <div class="subcolumns">
    <label>Categories</label><br />
    <table style="width:100%">
    <?php 
    $cats = array ("Culture", "Food", "Music", "Travel", "Sport", "Urban exploration", "Teach/learn", "Outdoor", "Flashmob");
    $str = "";
    $ii = 0;
    $lineout = true;
    foreach($cats as $cat) {
        if ($lineout) {
            $str .= '<tr>';
            $lineout = false;
        }
        $str .= '<td><input type="checkbox" name="event-categories[]" id="event-category-' . $ii . '" readonly="readonly"/> <label for="event-category-' . $ii . '">' . $cat . '</label></td>';
        $ii++;
        if ($ii % 3 == 0) {
            $str .= '</tr>';
            $lineout = true;
        }
    }
    echo $str;
    ?>
    </table>
    </div>
    <div class="subcolumns">
            <label for="event-description">Description:</label><br/>
            <textarea id="event-description" name="event-description" rows="10" cols="80" style="width:90%" readonly="readonly"></textarea>
    </div>
    <hr />
    <div class="subcolumns">
        <label><strong>Are you attending? </strong></label>
        <input id='event-attendance-yes' type="radio" value="yes" name="event-attendance" />
        <label for="event-attendance-yes">Yes</label>&nbsp;
        <input id='event-attendance-maybe' type="radio" value="maybe" checked='checked' name="event-attendance" />
        <label for="event-attendance-maybe">Maybe</label>
        <input id='event-attendance-no' type="radio" value="no" checked='checked' name="event-attendance" />
        <label for="event-attendance-no">No</label><br /><br />
    <label for="comment">Comment (optional)</label><br />
    <textarea id="comment" name="membershipinfo_comment" cols="60" rows="5" class="long" ></textarea>
    <div class="row">
        <input type='submit' value='Attend' name='attend'/>&nbsp;
        <input type='submit' value='Leave' name='leave'/>
    </div>
    <hr />
<div class="floatbox">
Attendees: around 10 (7 Yes, 3 Maybe, 4 No)
<table style="width:100%"><tr>
<td style="vertical-align: top;">
<ul>
<li><div><a class="float_left" href="members/Soldan310" class="float_left" title="See profile Soldan310" ><img height="50" width="50" class="framed" src="members/avatar/Soldan310?50_50" alt="Profile" /></a><div class="eventuserinfo float_left">  <a class="username" href="members/Soldan310">Soldan310</a><br />  <span class="small">50 years old<br />Yes</span></div></div></li>
<li><a class="float_left" href="members/Draudt3667" title="See profile Draudt3667" ><img height="50" width="50" class="framed" src="members/avatar/Draudt3667?50_50" alt="Profile" /></a><div class="eventuserinfo float_left">  <a class="username" href="members/Draudt3667">Draudt3667</a><br />  <span class="small">88 years old<br />Yes</span></div></li>
<li><a class="float_left" href="members/Kuhn4378" title="See profile Kuhn4378" ><img height="50" width="50" class="framed" src="members/avatar/Kuhn4378?50_50" alt="Profile" /></a><div class="eventuserinfo float_left">  <a class="username" href="members/Kuhn4378">Kuhn4378</a><br />  <span class="small">41 years old<br />Yes</span></div></li>
<li><a class="float_left" href="members/Meerbott2252" title="See profile Meerbott2252" ><img height="50" width="50" class="framed" src="members/avatar/Meerbott2252?50_50" alt="Profile" /></a><div class="eventuserinfo float_left">  <a class="username" href="members/Meerbott2252">Meerbott2252</a><br />  <span class="small">53 years old<br />Maybe</span></div></li>
<li><a class="float_left" href="members/Meerbott2252" title="See profile Meerbott2252" ><img height="50" width="50" class="framed" src="members/avatar/Meerbott2252?50_50" alt="Profile" /></a><div class="eventuserinfo float_left">  <a class="username" href="members/Meerbott2252">Meerbott2252</a><br />  <span class="small">53 years old<br />No</span></div></li>
</ul></td>
<td style="vertical-align: top;"><ul>
<li><a class="float_left" href="members/Ziel4224" title="See profile Ziel4224" ><img height="50" width="50" class="framed" src="members/avatar/Ziel4224?50_50" alt="Profile" /></a><div class="eventuserinfo float_left">  <a class="username" href="members/Ziel4224">Ziel4224</a><br />  <span class="small">57 years old<br />Yes</span></div></li>
<li><a class="float_left" href="members/Lange3399" title="See profile Lange3399" ><img height="50" width="50" class="framed" src="members/avatar/Lange3399?50_50" alt="Profile" /></a><div class="eventuserinfo float_left">  <a class="username" href="members/Lange3399">Lange3399</a><br />  <span class="small">19 years old<br />Yes</span></div></li>
<li><a class="float_left" href="members/Piotrichin2432" title="See profile Piotrichin2432" ><img height="50" width="50" class="framed" src="members/avatar/Piotrichin2432?50_50" alt="Profile" /></a><div class="eventuserinfo float_left">  <a class="username" href="members/Piotrichin2432">Piotrichin2432</a><br />  <span class="small">90 years old<br />Maybe</span></div></li>
<li><a class="float_left" href="members/Fauerbach1390" title="See profile Fauerbach1390" ><img height="50" width="50" class="framed" src="members/avatar/Fauerbach1390?50_50" alt="Profile" /></a><div class="eventuserinfo float_left">  <a class="username" href="members/Fauerbach1390">Fauerbach1390</a><br />  <span class="small">97 years old<br />No</span></div></li>
<li><a class="float_left" href="members/Susenbeth4390" title="See profile Susenbeth4390" ><img height="50" width="50" class="framed" src="members/avatar/Susenbeth4390?50_50" alt="Profile" /></a><div class="eventuserinfo float_left">  <a class="username" href="members/Susenbeth4390">Susenbeth4390</a><br />  <span class="small">99 years old<br />No</span></div></li></ul></td>
<td style="vertical-align: top;"><ul>
<li><a class="float_left" href="members/Faber3188" title="See profile Faber3188" ><img height="50" width="50" class="framed" src="members/avatar/Faber3188?50_50" alt="Profile" /></a><div class="eventuserinfo float_left">  <a class="username" href="members/Faber3188">Faber3188</a><br />  <span class="small">46 years old<br />Yes</span></div></li>
<li><a class="float_left" href="members/Lindheimer1218" title="See profile Lindheimer1218" ><img height="50" width="50" class="framed" src="members/avatar/Lindheimer1218?50_50" alt="Profile" /></a><div class="eventuserinfo float_left">  <a class="username" href="members/Lindheimer1218">Lindheimer1218</a><br />  <span class="small">46 years old<br />Yes</span></div></li>
<li><a class="float_left" href="members/Kauschaim1457" title="See profile Kauschaim1457" ><img height="50" width="50" class="framed" src="members/avatar/Kauschaim1457?50_50" alt="Profile" /></a><div class="eventuserinfo float_left">  <a class="username" href="members/Kauschaim1457">Kauschaim1457</a><br />  <span class="small">58 years old<br />Maybe</span></div></li>
<li><a class="float_left" href="members/Margraf1473" title="See profile Margraf1473" ><img height="50" width="50" class="framed" src="members/avatar/Margraf1473?50_50" alt="Profile" /></a><div class="eventuserinfo float_left">  <a class="username" href="members/Margraf1473">Margraf1473</a><br />  <span class="small">85 years old<br />No</span></div></li>
</ul></td></tr></table>
</div>
<div class="pages">
	<ul>
		<li>

<a class="off">&laquo;</a>		</li>
<li class="current"><a class="off">1</a></li><li><a href="forums/page2/">2</a></li><li><a href="forums/page3/">3</a></li><li class="sep">...</li><li><a href="forums/page10/">10</a></li><li><a href="forums/page11/">11</a></li>		<li>
<a class="off">&raquo;</a>		</li>
	</ul>
</div> <!-- pages --></div>
    </fieldset>           
</form>
</div>