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
$layoutbits = new MOD_layoutbits();
?>

<!-- table structure -->
<table class="table table-responsive table-striped table-hover">
    <!-- beginning of table head -->
    <thead class="blank">
    <tr>
        <th><?php echo $words->getFormatted('Author'); ?></th>
        <th>
            <?php
            if (empty($TIGHT_THREADLIST)) {
            echo $words->getFormatted('Thread');
            }
            else {
            echo $words->getFormatted('ForumRecentPosts');
            } ?>
        </th>
        <th><?php echo $words->getFormatted('Replies'); ?></th>
        <th><?php echo $words->getFormatted('Views'); ?></th>
        <th class="text-nowrap"><?php echo $words->getFormatted('LastPost'); ?></th>
        <th></th>
    </tr>
    </thead>
    <!-- end of table head -->
    <tbody>
    <!-- beginning of row loop -->
    <?php
        foreach ($threads as $cnt =>  $thread) {

        if ($thread->IdGroup){
            $url = ForumsView::threadURL($thread, 'groups/'.$thread->IdGroup.'/forum/');
        }
        else {
            $url = ForumsView::threadURL($thread);
        }
        $max = $thread->replies + 1;
        $maxPage = ceil($max / $this->_model->POSTS_PER_PAGE);

        $last_url = $url.($maxPage != 1 ? '/page'.$maxPage : '').'/#post'.$thread->last_postid;
    ?>
    <tr>
        <th class="middle p-2">
            <a href="members/<?php echo $thread->first_author; ?>"><img src="members/avatar/<?php echo $thread->first_author; ?>?size=50" alt="<?php echo $thread->first_author; ?>" title="<?php echo $thread->first_author; ?>" /><br>
                <small><?php echo $thread->first_author; ?></small>
            </a>
        </th>
        <td class="middle text-left p-2">
            <?php
            if ($thread->stickyvalue < 0) {
                echo '<i class="fa fa-exclamation-circle" alt="'. $words->getSilent('PinnedPost') .'" title="'. $words->getSilent('PinnedPost') .'" /></i> ' . $words->flushBuffer();
            }
            if ($thread->ThreadDeleted=="Deleted") {
                echo "[Deleted] " ;
            }
            if ($thread->ThreadVisibility=="ModeratorOnly") {
                echo "[ModOnly] " ;
            }
            echo "<a href=\"",$url,"\">" ;
            echo $words->fTrad($thread->IdTitle);
            ?></a>
            <br />
            <span class="small gray"><?php
                // show tags if post is part of a group
                if ($thread->IdGroup>0) {
                    echo "<a href=\"groups/".$thread->IdGroup."\"><strong>" . $words->getFormatted('Group'). ": </strong>",$this->_model->getGroupName($thread->GroupName),"</a>" ;
                }

                $breadcrumb = '';

                if (isset($thread->continent) && $thread->continent) {
                    $continentset = 1;
                    $url_bit = 'k'.$thread->continentid.'-'.$thread->continent;
                    if (!in_array($url_bit, $request)) {
                        $url = $uri.$url_bit.'/';
                        $breadcrumb .= '<a href="'.$url.'">'.$thread->continent.'</a> ';
                    } else {
//                              $url = 'forums/'.$url_bit.'/';
                        $url = $uri;
                        $breadcrumb .= ''.$thread->continent.' ';
                    }

                    if (isset($thread->countryname) && $thread->countryname) {
                        $url_bit = 'c'.$thread->countrycode.'-'.$thread->countryname;
                        if (!in_array($url_bit, $request)) {
                            $url = $url.$url_bit.'/';
                            $breadcrumb .= '&raquo; <a href="'.$url.'">'.$thread->countryname.'</a> ';
                        } else {
//                                  $url = $url.$url_bit.'/';
                            $breadcrumb .= '&raquo; '.$thread->countryname.' ';
                        }


                        if (isset($thread->adminname) && $thread->adminname) {
                            $url_bit = 'a'.$thread->admincode.'-'.$thread->adminname;
                            if (!in_array($url_bit, $request)) {
                                $url = $url.$url_bit.'/';
                                $breadcrumb .= '&raquo; <a href="'.$url.'">'.$thread->adminname.'</a> ';
                            } else {
                                //                                  $url = $url.$url_bit.'/';
                                $breadcrumb .= '&raquo; '.$thread->adminname.' ';
                            }
//                                  echo '<a href="'.$uri.'k'.$thread->continentid.'-'.$thread->continent.'/c'.$thread->countrycode.'-'.$thread->countryname.'/a'.$thread->admincode.'-'.$thread->adminname.'">'.$thread->adminname.'</a> ';

                            if (isset($thread->geonames_name) && $thread->geonames_name) {
                                $url_bit = 'g'.$thread->geonameid.'-'.$thread->geonames_name;
                                if (!in_array($url_bit, $request)) {
                                    $url = $url.$url_bit.'/';
                                    $breadcrumb .= ':: <a href="'.$url.'">'.$thread->geonames_name.'</a> ';
                                } else {
                                    //                                  $url = $url.$url_bit.'/';
                                    $breadcrumb .= ':: '.$thread->geonames_name.' ';
                                }


//                                      echo '<a href="'.$uri.'k'.$thread->continentid.'-'.$thread->continent.'/c'.$thread->countrycode.'-'.$thread->countryname.'/a'.$thread->admincode.'-'.$thread->adminname.'/g'.$thread->geonameid.'-'.$thread->geonames_name.'">'.$thread->geonames_name.'</a> ';
                            }

                        }

                    }
                }

                $ShowHelp=false ; // todo process in a better way this hritage of travel book (create a type help for tags)
                for ($ii=0;$ii<$thread->NbTags;$ii++) {
                    if ($breadcrumb) {
                        $breadcrumb .= '<span class="small"> | </span>';
                    }
                    $wordtag=$words->fTrad($thread->IdName[$ii]) ;
                    $url_bit = 't'.$thread->IdTag[$ii].'-'.$wordtag;
                    if (!in_array($url_bit, $request)) {
                        $url = $uri.$url_bit.'/';
                        $breadcrumb .= '<a href="'.$url.'">'.$wordtag.'</a> ';
                    } else {
                        $breadcrumb .= ''.$wordtag.' ';
                    }

                    // Heritage of TravelBook
                    if ($wordtag=='help' ||$wordtag == 'Help and Support') {
                        $ShowHelp=true ; // todo deal with this in a better way
                    }
                }

                if ($breadcrumb) {
                    // we will later use the 'tags' word, but don't want an edit link inside the html tag!
                    if ($ShowHelp) {
                        echo '<i class="fa fa-question-circle-o pr-1" alt="'. $words->getBuffered('tags') .'" title="'. $words->getBuffered('tags') .'></i>' . $words->flushBuffer();
                    }
                    elseif (isset($thread->continent) && $thread->continent) {
                        echo '<i class="fa fa-globe pr-1" alt="'. $words->getBuffered('tags') .'" title="'. $words->getBuffered('tags') .'></i>' . $words->flushBuffer();
                    }
                    else {
                        echo '<i class="fa fa-tag" alt="'. $words->getBuffered('tags') .'" title="'. $words->getBuffered('tags') .'"/></i>' . $words->flushBuffer();
                    }
                    echo $breadcrumb;
                }
                ?></span>
        </td>
        <td class="middle p-2"><?php echo $thread->replies; ?></td>
        <td class="middle p-2"><?php echo number_format($thread->views); ?></td>
        <td class="middle text-nowrap p-2">
            <div class="d-flex flex-row mr-2">
                <div class="align-self-center"><a href="members/<?php echo $thread->last_author; ?>"><img src="members/avatar/<?php echo $thread->last_author; ?>?size=30" alt="<?php echo $thread->last_author; ?>" title="<?php echo $thread->last_author; ?>" /></a></div>
                <div class="pl-2 align-self-center text-left">
                    <small><a href="members/<?php echo $thread->last_author; ?>"><?php echo $thread->last_author; ?></a></small><br>
                    <span class="small gray" title="<?php echo date($words->getSilent('DateHHMMShortFormat'), ServerToLocalDateTime($thread->last_create_time, $this->getSession())); ?>"><a href="<?php echo $last_url; ?>"><?php echo $layoutbits->ago($thread->last_create_time); ?></a></span>
                </div>
            </div>
        </td>
        <td class="middle p-2">
            <a href="<?php echo $last_url; ?>"><i class="fa fa-chevron-right" alt="<?php echo $words->getBuffered('to_last'); ?>" title="<?php echo $words->getBuffered('to_last'); ?>"></i></a><?php echo $words->flushBuffer(); ?>
        </td>
    </tr>
            <!-- end of loop -->
    <? } ?>
    </tbody>
</table>
<!-- end of new table structure -->

<?php
if ($User && empty($noForumNewTopicButton)) {
?>
<a class="btn btn-primary float-right" href="<?php echo $uri; ?>new"><?php echo $words->getBuffered('ForumNewTopic'); ?></a><?php echo $words->flushBuffer(); ?></div>
<?php
}
?>

<?php
require 'pages.php';
if (empty($noForumLegendBox)) {
?>

<div class="clearfix small float_left" style="width: 80%">
    <?php echo '<img src="styles/css/minimal/images/iconsfam/tag_blue.png" alt="'. $words->getBuffered('tags') .'" title="'. $words->getBuffered('tags') .'" class="forum_icon" />' . $words->flushBuffer();
    ?>
     = <?php echo $words->get('ForumLegendTagged');?>
</div>
<div class="clearfix small float_left" style="width: 80%">
    <?php echo '<img src="styles/css/minimal/images/iconsfam/world.png" alt="'. $words->getBuffered('geo') .'" title="'. $words->getBuffered('geo') .'" class="forum_icon" />' . $words->flushBuffer();
    ?>
     = <?php echo $words->get('ForumLegendTaggedGeo');?>
</div>
<div class="clearfix small float_left" style="width: 80%">
    <?php echo '<img src="styles/css/minimal/images/iconsfam/help.png" alt="'. $words->getBuffered('help') .'" title="'. $words->getBuffered('help') .'" class="forum_icon" />' . $words->flushBuffer();
    ?>
     = <?php echo $words->get('ForumLegendTaggedHelp');?>
</div>
<?php
}
?>