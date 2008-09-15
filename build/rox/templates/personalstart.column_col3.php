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

?>
<div class="subcolumns">
<div class="c50l">
<div class="subr" id="personallist">
            <!-- Another box -->
            <div class="box">
                <div class="corner"></div>

                <h3 class="first" id="two"><a><img class="float_right" onclick="this.parentNode.parentNode.parentNode.childNodes.item(5).toggle()" title="go to last post" alt="go to last post" src="images/icons/box-min1.png"/> <?php echo $words->getFormatted('NewMembers') ?></a></h3>			
                <div class="floatbox">  
					<div class="c50l"><div class="subr">
	                <h3><a><?php echo $words->getFormatted('RecentMember') ?></a></h3>
	                <?php
	                    // Display the last created members with a picture
	                    $m=MOD_visits::get()->RetrieveLastAcceptedProfileWithAPicture() ;
	                ?>
	                <p class="floatbox UserpicFloated">
	                    <?php echo MOD_layoutbits::PIC_30_30($m->Username,'',$style='float_left framed'); ?>
	                    <?php echo '<a href="bw/member.php?cid='.$m->Username.'">'.$m->Username.'</a>' ?>
	                    <br/>
	                    <?php echo $m->countryname ?> 
	                </p> 
				</div></div>
				<div id="sub" class="c50r"><div class="subr">
	                <h3><a><?php echo $words->getFormatted('RecentMemberCity')?></a></h3>
	                <?php
	                    // Display the last created members with a picture
	                    $m=MOD_visits::get()->RetrieveLastAcceptedProfileInCityWithAPicture($_SESSION['IdMember']) ;
	                ?>
	                <p class="floatbox UserpicFloated">
	                    <?php echo MOD_layoutbits::PIC_30_30($m->Username,'',$style='float_left framed'); ?>
	                    <?php echo '<a href="bw/member.php?cid='.$m->Username.'">'.$m->Username.'</a>' ?>
	                    <br/>
	                    <?php echo $m->countryname ?> 
	                </p> 
				</div></div>
				</div>
                <div class="boxbottom"><div class="author"></div><div class="links"></div></div>
            </div>

            <!-- Another box -->
            <div class="box">
                <div class="corner"></div>

                <h3 class="first" id="two"><a><img class="float_right" onclick="this.parentNode.parentNode.parentNode.childNodes.item(5).toggle()" title="go to last post" alt="go to last post" src="images/icons/box-min1.png"/> <?php echo $words->getFormatted('RecentVisitsOfyourProfile')  ?></a></h3>			
                <div class="floatbox">  				
                <?php
                    $DivForVisit[0]='c33l' ;
                    $DivForVisit[1]='c33l' ;
                    $DivForVisit[2]='c33r' ;
                    
                    // /*###   NEW   To be programmed: show the first visitor, then the second. !! Different div's (c50l, c50r)!  ###
                    $last_visits=MOD_visits::get()->BuildLastVisits() ;
                    for ($ii=0;$ii<count($last_visits);$ii++) {
                        $m=$last_visits[$ii] ;
                ?>
                <div class="<?php echo $DivForVisit[$ii] ?>"> 
                    <div class="subr">
                        <p class="floatbox UserpicFloated">
                            <?php echo MOD_layoutbits::PIC_30_30($m->Username,'',$style='float_left framed') ?>
                            <?php echo '<a href="bw/member.php?cid='.$m->Username.'">'.$m->Username.'</a>' ?>
                            <br />
                            <?php echo $m->countryname; ?>
                        </p> 
                    </div> 
                </div>
                <?php 
                    }
                ?>
                </div>
                <div class="boxbottom"><div class="author"></div><div class="links"></div></div>
           </div>

            <!-- Another box -->
            <div class="box">
                <div class="corner"></div>

                <h3 class="first" id="two"><a><img class="float_right" onclick="this.parentNode.parentNode.parentNode.childNodes.item(5).toggle()" title="go to last post" alt="go to last post" src="images/icons/box-min1.png"/> <?php echo $words->getFormatted('TripCity')  ?></a></h3>			
                <div class="floatbox">  				
                <?php
                    $DivForVisit[0]='c33l' ;
                    $DivForVisit[1]='c33l' ;
                    $DivForVisit[2]='c33r' ;
                    
                    // /*###   NEW   To be programmed: show the first visitor, then the second. !! Different div's (c50l, c50r)!  ###
                    $next_trips=MOD_trips::get()->RetrieveVisitorsInCityWithAPicture($_SESSION['IdMember']) ;
					for ($ii=0;$ii<count($next_trips);$ii++) {
                        $m=$next_trips[$ii] ;
						$tripDate = explode(" ",$m->tripDate);
                ?>
                <div class="<?php echo $DivForVisit[$ii] ?>"> 
                    <div class="subr">
                        <p class="floatbox UserpicFloated">
                            <?php echo MOD_layoutbits::PIC_30_30($m->Username,'',$style='float_left framed') ?>
                            <?php echo '<a href="bw/member.php?cid='.$m->Username.'">'.$m->Username.'</a>' ?>
                            <br />
                            <?php echo $m->city; ?> / <?php echo $m->country; ?>
							<br />
							<? echo '<a href="blog/'.$m->Username.'/'.$m->tripId.'">'.$words->get('ComingOn').' '.$tripDate[0].'</a>'; ?>
                        </p> 
                    </div> 
                </div>
                <?php 
                    }
                ?>
                </div>
                <div class="boxbottom"><div class="author"></div><div class="links"></div></div>
           </div>
		   
		   
        </div>
    </div> 
    <div class="c50r" > 
        <div class="subc" id="personallist2">
            <!-- Another box -->
            <div class="box">
                <div class="corner"></div>
                
                <h3 class="first" id="two"><a><img class="float_right" onclick="this.parentNode.parentNode.parentNode.childNodes.item(5).toggle()" title="go to last post" alt="go to last post" src="images/icons/box-min1.png"/> <?php echo $words->getFormatted('ForumRecentPostsLong') ?></a></h3>
                <div class="floatbox">
                    <?php echo $Forums->showExternalLatest(); ?>
                </div>
                <div class="boxbottom"><div class="author"></div><div class="links"></div></div>
           </div>
            <!-- Another box -->
            <div class="box">
                <div class="corner"></div>
                
                <h3 class="first" id="two"><a><img class="float_right" onclick="this.parentNode.parentNode.parentNode.childNodes.item(5).toggle()" title="go to last post" alt="go to last post" src="images/icons/box-min1.png"/> <?php echo $words->getFormatted('News') ?></a></h3>
                <div class="floatbox">
                    <?php
                        $newscount=MOD_news::get()->NewsCount() ; 
                        for ($ii=$newscount;$ii>$newscount-3;$ii--) {
                    ?>
                    <h4 class="news"><?php echo $words->get('NewsTitle_'.$ii); ?></h4>
                    <span class="small grey"><?php echo MOD_news::get()->NewsDate("NewsTitle_".$ii); ?></span>
                    <p><?php echo $words->get('NewsText_'.$ii); ?></p>
                    <?php 
                        }
                    ?>
                </div>
                <div class="boxbottom"><div class="author"></div><div class="links"></div></div>
           </div>		
        </div> 
    </div>
</div>

<script type="text/javascript">

Sortable.create('personallist', {
    tag:'div',
    containment: ['personallist','personallist2'],
    constraint: false,
    dropOnEmpty: true /*,
    onUpdate:function(){
        new Ajax.Updater('list-info', 'trip/reorder/', {
            onComplete:function(request){
                new Effect.Highlight('triplist',{});
                params = Sortable.serialize('triplist').toQueryParams();
                points = Object.values(params).toString().split(',');
                setPolyline();
                
            }, 
            parameters:Sortable.serialize('triplist'), 
            evalScripts:true, 
            asynchronous:true,
            method: 'get'
        })
    }*/
})
Sortable.create('personallist2', {
    tag:'div',
    containment: ['personallist','personallist2'],
    constraint: false,
    dropOnEmpty: true /*,
    onUpdate:function(){
        new Ajax.Updater('list-info', 'trip/reorder/', {
            onComplete:function(request){
                new Effect.Highlight('triplist',{});
                params = Sortable.serialize('triplist').toQueryParams();
                points = Object.values(params).toString().split(',');
                setPolyline();
                
            }, 
            parameters:Sortable.serialize('triplist'), 
            evalScripts:true, 
            asynchronous:true,
            method: 'get'
        })
    }*/
})
</script>
<script type="text/javascript">//<!--
BlogSuggest.initialize('searchwidget');
//-->
</script>

