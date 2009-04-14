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
            <h3>What's the news?</h3>
            <!-- Another box -->
            <div class="box">
                <div class="corner"></div>
                
                <h3 class="first" id="two"><a><img class="float_right" onclick="this.parentNode.parentNode.parentNode.childNodes.item(5).toggle()" title="go to last post" alt="go to last post" style="cursor:pointer" src="images/icons/box-min1.png"/> <?php echo $words->getFormatted('ForumRecentPostsLong') ?></a></h3>
                <div class="floatbox">
                    <?php echo $Forums->showExternalLatest(); ?>
                </div>
                <div class="boxbottom"><div class="author"></div><div class="links"></div></div>
           </div>
            <!-- Another box - Community news -->
            <div class="box">
                <div class="corner"></div>

                <h3 class="first" id="two"><a>
                <!--<img class="float_right" onclick="this.parentNode.parentNode.parentNode.childNodes.item(5).toggle()" title="reduce" alt="reduce" src="images/icons/box-min1.png"/> -->
                <?php echo $words->getFormatted('CommunityNews') ?></a></h3>
                <div class="floatbox">
                    <?php
                    $i=1;
                    foreach ($postIt as $blog) {
                    $i++;
                    if ($i <=3) {
                        $Blog = new Blog();
                        $View = new BlogView($Blog);
                        $txt = $View->blogText($blog->blog_text);
                    ?>
                        <h4 class="news"><a href="blog/<?=$blog->user_handle?>/<?=$blog->blog_id?>"><?=htmlentities($blog->blog_title, ENT_COMPAT, 'utf-8')?></a></h4>
                        <span class="small grey"><?=$words->get('written_by')?> <a href="user/<?=$blog->user_handle?>"><?=$blog->user_handle?></a> - <?=date($format['short'], $blog->unix_created)?></span>
                        <p>
                        <?php
                            echo $txt[0];
                            if ($txt[1]) {
                              echo '<p> <a href="blog/'.$blog->user_handle.'/'.$blog->blog_id.'">'.$words->get('BlogItemContinued').'</a></p>';
                            }
                        ?>
                        </p>
                    <?php
                    }
                    }
                    ?>

                <a href="blog/tags/Community News for the frontpage"><?echo $words->get('ReadMore');?></a>
                </div>

                <div class="boxbottom"><div class="author"></div><div class="links"></div></div>
           </div>

            <!-- Another box - BV News -->
            <div class="box">
                <div class="corner"></div>

                <h3 class="first" id="two"><a>
                <!--<img class="float_right" onclick="this.parentNode.parentNode.parentNode.childNodes.item(5).toggle()" title="reduce" alt="reduce" src="images/icons/box-min1.png"/> -->
                <?php echo $words->getFormatted('BVNews') ?></a></h3>
                <div class="floatbox">
                    <?
                    $url = 'http://www.bevolunteer.org/blog/?feed=rss2';
                        $num_items = 2;
                        $rss = fetch_rss($url);
                        $items = array_slice($rss->items, 0, $num_items);

                        //echo "<div class=\"info\">\n";
                        foreach ($items as $item ) {
                            $title = $item['title'];
                            $url   = $item['link'];
                            $description   = $item['description'];
                        /*  $subject = $item ['dc'] ['subject']; */
                            $date   = $item['pubdate'];
                            /*$type   = $item['type'];
                            $author   = $item['author'];     */
                            echo "<h4 class='news'><a href=\"",$url,"\">",$title,"</a></h4>
                            <span class='small grey'>",$date,"</span>
                            <p>",$description,"</p>

                        ";
                        }

                        //echo "</div>\n";

                        ?>
                <a href=\"http://blogs.bevolunteer.org\"><? echo $words->get("getMoreEntriesandComments");?></a>
                </div>

                <div class="boxbottom"><div class="author"></div><div class="links"></div></div>
           </div>
        </div>
    </div> 
    <div class="c50r" > 
        <div class="subc" id="personallist2">
            <h3>Who is who?</h3>
            <!-- Another box -->
            <div class="box">
                <div class="corner"></div>
                
                <h3 class="first"><a><img class="float_right" onclick="this.parentNode.parentNode.parentNode.childNodes.item(5).toggle()" title="go to last post" alt="go to last post" style="cursor:pointer" src="images/icons/box-min1.png"/> <?php echo $words->getFormatted('RecentMember') ?></a></h3>
                <div class="floatbox">
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
                <h3><a href="bw/myvisitors.php"><?php echo $words->get('RecentVisitsOfyourProfile') ?></a></h3> 
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

		                <h3 class="first"><a>
		                <!--<img class="float_right" onclick="this.parentNode.parentNode.parentNode.childNodes.item(5).toggle()" title="go to last post" alt="go to last post" src="images/icons/box-min1.png"/> -->
		                <?php echo $words->getFormatted('TripCity')  ?></a></h3>
		                <div class="floatbox">
		                <?php
		                    $DivForVisit[0]='c33l' ;
		                    $DivForVisit[1]='c33l' ;
		                    $DivForVisit[2]='c33r' ;
		                    $next_trips=MOD_trips::get()->RetrieveVisitorsInCityWithAPicture($_SESSION['IdMember']) ;
		                    for ($ii=0;$ii<count($next_trips);$ii++) {
		                        $m=$next_trips[$ii] ;
		                        $tripDate = explode(" ",$m->tripDate);
		                ?>
		                <div class="<?php echo $DivForVisit[$ii] ?>">
		                    <div class="subr">
		                        <p class="floatbox UserpicFloated">
		                            <?php echo MOD_layoutbits::PIC_30_30($m->Username,'',$style='float_left framed') ?>
		                            <?php echo '<a href="people/'.$m->Username.'">'.$m->Username.'</a>' ?>
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

