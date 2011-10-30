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
        <div class="subcl personallist">
            <h2><?php echo $words->getFormatted('StartpageNews') ?></h2>
            <!-- Community news -->
                <h3 class="first" ><a href="blog/tags/Community News for the frontpage"><?php echo $words->getFormatted('CommunityNews') ?></a> <a href="rss/blog/tags/Community%20News%20for%20the%20frontpage"><img src="images/icons/feed.png" alt="<?=$words->get('GetRSSFeed')?>" /></a></h3>
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
                            $snippet = ((strlen($txt[0]) > 200) ? substr($txt[0], 0, 200) . '...': $txt[0]);
                            $purifier = MOD_htmlpure::get()->getPurifier();
                            echo $purifier->purify($snippet);
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
                </div> <!-- floatbox -->
        </div> <!-- subcl -->
    </div> <!-- c33l -->
    
    <div class="c66r" > 
        <div class="c50l">
            <div class="subcl personallist">
                <h2><?php echo $words->getFormatted('StartpageWhoiswho') ?></h2>
                <h3 class="first"><?php echo $words->getFormatted('RecentMember') ?></h3>
                <div id="newmembers" class="floatbox">
                    <?php
                        // Display the last created members with a picture
                        $latestmembers = MOD_visits::get()->RetrieveLastAcceptedProfilesWithAPicture(4);
                        for ($ii=0;$ii<count($latestmembers);$ii++)
                        {
                            $m=$latestmembers[$ii];
                            $img = MOD_layoutbits::PIC_30_30($m->Username,'',$style='float_left framed');
                            echo <<<HTML
                        <div class="newmember" >
                            {$img}
                             <a href="members/{$m->Username}">{$m->Username}</a><br />
                            {$m->countryname}
                        </div>
HTML;
                        }
                        echo <<<HTML
                </div> <!-- floatbox -->
                
                <h3><a href="myvisitors">{$words->get('RecentVisitsOfyourProfile')}</a></h3> 
                <div id="visitors" class="floatbox">
HTML;
                        
                        $last_visits=MOD_visits::get()->BuildLastVisits(0, 4) ;
                        for ($ii=0;$ii<count($last_visits);$ii++) {
                            $m=$last_visits[$ii] ;
                            $img = MOD_layoutbits::PIC_30_30($m->Username,'',$style='float_left framed');
                            echo <<<HTML
                        <div class="visitors" >
                            {$img}
                             <a href="members/{$m->Username}">{$m->Username}</a><br />
                            {$m->countryname}
                        </div>
HTML;
                        }
                        ?>
                </div> <!-- floatbox -->
       
                <h3 class="first"><?php echo $words->getFormatted('TripCity')  ?></h3>
                <div id="nextvisitors" class="floatbox">
                    <?php
                        $next_trips=MOD_trips::get()->RetrieveVisitorsInCityWithAPicture($_SESSION['IdMember'], 4) ;
                        for ($ii=0;$ii<count($next_trips);$ii++) {
                            $m=$next_trips[$ii] ;
                            $tripDate = explode(" ",$m->tripDate);
                    ?>
                    <div class="visitors">
                        <?php echo MOD_layoutbits::PIC_30_30($m->Username,'',$style='float_left framed') ?>
                        <?php echo '<a href="members/'.$m->Username.'">'.$m->Username.'</a>' ?><br />
                        <?php echo $m->city; ?> / <?php echo $m->country; ?><br />
                        <?php echo '<a href="blog/'.$m->Username.'/'.$m->tripId.'">'.$words->get('ComingOn').' '.$tripDate[0].'</a>'; ?>
                    </div> <!-- visitors -->
                    <?php
                        }
                    ?>
                </div> <!-- floatbox -->
            </div> <!-- subc -->
        </div> <!-- c50l -->

        <div class="c50r">
            <div class="subcr personallist">
                <h2><?php echo $words->getFormatted('StartpageWhoistalking') ?></h2>
                <h3 class="first" ><a href="forums"><?php echo $words->getFormatted('ForumRecentPostsLong') ?></a></h3>
                <div class="floatbox">
                    <?php echo $Forums->showExternalLatest(); ?>
                </div> <!-- floatbox -->
            </div> <!-- subcr -->
        </div> <!-- c50r -->
        
    </div> <!-- c66r -->
</div> <!--subcolumns -->

