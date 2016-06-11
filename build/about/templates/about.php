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

<h2><?php echo $words->get("SoWhat") ?></h2>

<div class="subcolumns">
  <div class="c50l">
    <div class="subcl">
<?php
    echo "<h3>", $words->get("AboutUs_TheIdea"),"</h3>";
    echo "<p>",$words->get("AboutUs_TheIdeaText"),"
    </p>";
    echo "<h3>", $words->get("AboutUs_GetActive"),"</h3>";
    echo "<p>",$words->get("AboutUs_GetActiveText"),"</p>";
    echo "<p>",$words->get("AboutUs_Greetings"),"</p>";
    echo "<h3>", $words->get("AboutUs_GiveFeedback"),"</h3>";
    echo "<p>",$words->get("AboutUs_GiveFeedbackText"),"</p>";
?>
    </div>
   </div>


  <div class="c50r">
    <div class="subcr">
<?php
    echo "<h3>", $words->get("AboutUs_HowOrganized"),"</h3>";
    echo "<p>",$words->get("AboutUs_HowOrganizedText"),"</p>";
    
//Blog model to fetch the Community News
$Blog = new Blog();
$postIt      = $Blog->getTaggedPostsIt('Community News for the frontpage', true);
$format = array('short'=>$words->getSilent('DateFormatShort'));

    ?><h3 class="first" ><a href="blog/tags/Community News for the frontpage"><?php echo $words->getFormatted('CommunityNews') ?></a> <a href="rss/blog/tags/Community%20News%20for%20the%20frontpage"><img src="images/icons/feed.png" alt="<?=$words->getSilent('GetRSSFeed')?>"></a><?php echo $words->flushBuffer(); ?></h3>
                <div class="clearfix">
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
                            $snippet = ((strlen($txt[0]) > 600) ? substr($txt[0], 0, 600) . '...': $txt[0]);
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
    </div>
  </div>
</div>
</div>
