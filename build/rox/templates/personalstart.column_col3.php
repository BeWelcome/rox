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
        <div class="subcl">
            <!-- Community news -->
            <h3 class="first" ><a href="blog/tags/Community News for the frontpage"><?php echo $words->getFormatted('CommunityNews') ?></a> <a href="rss/blog/tags/Community%20News%20for%20the%20frontpage"><img src="images/icons/feed.png" alt="<?=$words->getSilent('GetRSSFeed')?>"></a><?php $words->flushBuffer()?></h3>
            <div class="clearfix">
                <?php
                $blogModel = new Blog();
                $view = new BlogView($blogModel);
                $i = 1;
                foreach ($postIt as $blog) {
                    // TODO: use for 1..3 loop instead
                    $i++;
                    if ($i <=3) {
                        $txt = $view->blogText($blog->blog_text);
                        $commentsCount = $blogModel->countComments($blog->blog_id);
                    ?>
                    <div class="newsitem">
                        <h4 class="news"><a href="blog/<?=$blog->user_handle?>/<?=$blog->blog_id?>"><?=htmlentities($blog->blog_title, ENT_COMPAT, 'utf-8')?></a></h4>
                        <span class="small grey"><?=$words->get('written_by')?> <a href="user/<?=$blog->user_handle?>"><?=$blog->user_handle?></a> - <?=date($format['short'], $blog->unix_created)?></span>
                        <div class="newsbody">
                        <?php
                            $snippet = ((strlen($txt[0]) > 200) ? substr($txt[0], 0, 200) . '...': $txt[0]);
                            $purifier = MOD_htmlpure::get()->getPurifier();
                            echo $purifier->purify($snippet);
                            if ($txt[1]) {
                              echo '<a href="blog/'.$blog->user_handle.'/'.$blog->blog_id.'">'.$words->get('BlogItemContinued').'</a>';
                            }
                        ?>
                        </div> <!-- newsbody -->
                        <div class="newscomments small">
                            <?php
                                echo '<a href="blog/'.$blog->user_handle.'/'.$blog->blog_id.'#comments">';
                                if ($commentsCount > 0) {
                                  if ($commentsCount == 1) {
                                    echo '1 '.$words->get('CommentsSingular');
                                  } else {
                                    echo $commentsCount . ' ' . $words->get('CommentsPlural');
                                  }
                                } else {
                                  echo $words->get('CommentsAdd');
                                }
                                echo '</a>';
                            ?>
                        </div> <!-- newscomments -->
                    </div> <!-- newsitem -->
                    <?php
                    }
                    ?>
                <?php
                }
                ?>
                <p><a href="blog/tags/Community News for the frontpage" ><?echo $words->get('ReadMore');?></a></p>
            </div> <!-- clearfix -->
        </div> <!-- subcl -->
    </div> <!-- c33l -->

    <div class="c33l" >
            <div class="subcl midcl">
            <?php
                // Show the recent member list only for members that joined at least a week ago
                // to avoid direct spam hits
                if (time() - strtotime($member->created) >  7 * 24 * 60 * 60) { ?>
                <h3 class="first"><?php echo $words->getFormatted('RecentMember') ?></h3>
                <div id="newmembers" class="clearfix">
                    <?php
                        // Display the last created members with a picture
                        if ($showVisitors) {
                            $latestmembersMax = 4;
                        } else {
                            $latestmembersMax = 6;
                        }
                        $latestmembers = MOD_visits::get()->RetrieveLastAcceptedProfilesWithAPicture($latestmembersMax);
                        for ($ii=0;$ii<count($latestmembers);$ii++)
                        {
                            $m=$latestmembers[$ii];
                            $img = MOD_layoutbits::PIC_50_50($m->Username,'',$style='float_left framed');
                            echo <<<HTML
                        <div class="newmember" >
                            {$img}
                            <div class="memberinfo" style="margin-left: 40px;">
                             <a href="members/{$m->Username}">{$m->Username}</a><br />
                            {$m->countryname}
                            </div>
                        </div> <!-- newmember -->
HTML;
                        }
                        echo <<<HTML
                </div> <!-- clearfix -->
HTML;
                }
                if ($showVisitors) {
                        echo <<<HTML
                <div id="visitors" class="clearfix">
                <h3><a href="myvisitors">{$words->get('RecentVisitsOfyourProfile')}</a></h3>
HTML;

                        $last_visits=MOD_visits::get()->BuildLastVisits(0, 4) ;
                        for ($ii=0;$ii<count($last_visits);$ii++) {
                            $m=$last_visits[$ii] ;
                            $img = MOD_layoutbits::PIC_50_50($m->Username,'',$style='float_left framed');
                            echo <<<HTML
                        <div class="visitors" >
                            {$img}
                            <div class="memberinfo" style="margin-left: 40px;">
                             <a href="members/{$m->Username}">{$m->Username}</a><br />
                            {$m->countryname}
                            </div> <!-- memberinfo -->
                        </div> <!-- visitors -->
HTML;
                        }
                        ?>
                </div> <!-- clearfix -->
<?php
                }

// TODO: move to controller
$next_trips = MOD_trips::get()->RetrieveVisitorsInCityWithAPicture($this->_session->get('IdMember'), 4);
$next_trips_count = count($next_trips);
?>
                <?php if ($next_trips_count > 0): ?>
                <h3 class="first"><?php echo $words->getFormatted('TripCity'); ?></h3>
                <div id="nextvisitors" class="clarfix">
                    <?php for ($ii = 0; $ii < $next_trips_count; $ii++): ?>
                    <?php
                        $m = $next_trips[$ii];
                        $tripDate = date('Y-m-d', strtotime($m->tripDate));
                    ?>
                    <div class="visitors">
                        <?php echo MOD_layoutbits::PIC_50_50($m->Username, '', $style = 'float_left framed'); ?>
                        <?php echo '<a href="members/' . $m->Username . '">' . $m->Username . '</a>'; ?><br />
                        <?php echo $m->country; ?><br />
                        <?php echo '<a href="blog/' . $m->Username . '/' . $m->tripId . '">' . $tripDate . '</a>'; ?>
                    </div> <!-- visitors -->
                    <?php endfor; ?>
                </div> <!-- clearfix -->
                <?php endif; ?>
            </div> <!-- subcl -->
        </div> <!-- c33l -->

        <div class="c33r">
            <div class="subcr">
                <h3 class="first" ><a href="forums"><?php echo $words->getFormatted('ForumRecentPostsLong') ?></a></h3>
                <div class="clearfix">
                    <?php $Forums->showExternalLatest(true); ?>
                </div> <!-- clearfix -->
            </div> <!-- subcr -->
        </div> <!-- c33r -->


</div> <!--subcolumns -->

