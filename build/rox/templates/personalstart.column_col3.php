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
<div class="c33l">
<div class="subcl" id="personallist">
            <h2>What's the news?</h2>
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
                            echo substr($txt[0], 0, 200);
                            if (strlen($txt[0]) > 200) echo '...';
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

        </div>
    </div> 
    <div class="c66r" > 
        <div class="c50l">
        <div class="subc" id="personallist2">
            <h2>Who is who?</h2>
            <!-- Another box -->
            <div class="box">
                <div class="corner"></div>
                
                <h3 class="first"><a><img class="float_right" onclick="this.parentNode.parentNode.parentNode.childNodes.item(5).toggle()" title="go to last post" alt="go to last post" style="cursor:pointer" src="images/icons/box-min1.png"/> <?php echo $words->getFormatted('RecentMember') ?></a></h3>
                <div class="floatbox">
                <?php
                    // Display the last created members with a picture
                    $latestmembers=MOD_visits::get()->RetrieveLastAcceptedProfilesWithAPicture(4);
                    for ($ii=0;$ii<count($latestmembers);$ii++) {
                        $m=$latestmembers[$ii] ;
                ?>
                <div class="float_left" style="width: 40%; overflow: hidden;">
                    <table>
                        <tr>
                        <td>
                        <?php echo MOD_layoutbits::PIC_30_30($m->Username,'',$style='framed') ?>
                        </td>
                        <td>
                        <?php echo '<a href="people/'.$m->Username.'">'.$m->Username.'</a>' ?>
                        <br />
                        <?php echo $m->countryname; ?>
                        </td>
                        </tr>
                    </table>
                </div>
                <? } ?>
                </div>
                <h3><a href="bw/myvisitors.php"><?php echo $words->get('RecentVisitsOfyourProfile') ?></a></h3> 
                <div class="floatbox">
                <?php
                    
                    // /*###   NEW   To be programmed: show the first visitor, then the second. !! Different div's (c50l, c50r)!  ###
                    $last_visits=MOD_visits::get()->BuildLastVisits(0, 4) ;
                    for ($ii=0;$ii<count($last_visits);$ii++) {
                        $m=$last_visits[$ii] ;
                ?>
                    <div class="float_left" style="width: 40%; overflow: hidden;">
                        <table>
                            <tr>
                            <td>
                            <?php echo MOD_layoutbits::PIC_30_30($m->Username,'',$style='framed') ?>
                            </td>
                            <td>
                            <?php echo '<a href="people/'.$m->Username.'">'.$m->Username.'</a>' ?>
                            <br />
                            <?php echo $m->countryname; ?>
                            </td>
                            </tr>
                        </table>
                    </div> 
                <?php 
                    }
                ?>
                </div>
                </div>
		
		            <!-- Another box -->
		            <div class="box">
		                <div class="corner"></div>

		                <h3 class="first"><a>
		                <!--<img class="float_right" onclick="this.parentNode.parentNode.parentNode.childNodes.item(5).toggle()" title="go to last post" alt="go to last post" src="images/icons/box-min1.png"/> -->
		                <?php echo $words->getFormatted('TripCity')  ?></a></h3>
		                <div class="floatbox">
		                <?php
		                    $next_trips=MOD_trips::get()->RetrieveVisitorsInCityWithAPicture($_SESSION['IdMember'], 4) ;
		                    for ($ii=0;$ii<count($next_trips);$ii++) {
		                        $m=$next_trips[$ii] ;
		                        $tripDate = explode(" ",$m->tripDate);
		                ?>
                        <div class="float_left" style="width: 40%; overflow: hidden;">
                            <table>
                                <tr>
                                <td>
                                <?php echo MOD_layoutbits::PIC_30_30($m->Username,'',$style='framed') ?>
                                </td>
                                <td>
		                        <?php echo '<a href="people/'.$m->Username.'">'.$m->Username.'</a>' ?>
                                <br />
	                            <?php echo $m->city; ?> / <?php echo $m->country; ?>
	                            <br />
	                            <? echo '<a href="blog/'.$m->Username.'/'.$m->tripId.'">'.$words->get('ComingOn').' '.$tripDate[0].'</a>'; ?>
                                </td>
                                </tr>
                            </table>
                        </div>
		                <?php
		                    }
		                ?>
		                </div>
		           </div>
               </div>

		    </div>
		    <div class="c50r">
                <div class="subcr">
        		    <h2>Who's talking?</h2>
                     <!-- Another box -->
                     <div class="box">
                         <div class="corner"></div>

                         <h3 class="first" id="two"><a><img class="float_right" onclick="this.parentNode.parentNode.parentNode.childNodes.item(5).toggle()" title="go to last post" alt="go to last post" style="cursor:pointer" src="images/icons/box-min1.png"/> <?php echo $words->getFormatted('ForumRecentPostsLong') ?></a></h3>
                         <div class="floatbox">
                             <?php echo $Forums->showExternalLatest(); ?>
                         </div>
                         <div class="boxbottom"><div class="author"></div><div class="links"></div></div>
                     </div>
                </div>
		    </div>
		
        </div> 
    </div>
</div>

