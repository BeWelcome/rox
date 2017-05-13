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


JeanYves notes : every display of a forum post content  goes trhu this template

*/

$words = new MOD_words();
$styles = array('highlight', 'blank');

$hideGroupOnlyPost = false;

if (($post->IdGroup > 0) && ($post->PostVisibility == "GroupOnly")) {
    $hideGroupOnlyPost = ($this->_model->checkGroupMembership($post->IdGroup) == false);
}
?>

<!-- entire row -->
<div class="d-flex flex-row <?php echo $styles[$cnt % 2]; ?> mb-2 postbox w-100">

    <!-- left column -->
    <div class="d-flex flex-column col-4 col-md-3 mr-2 p-0 postleftcolumn">

        <!-- member info block -->
        <div class="d-flex flex-row align-self-start pull-left p-2 w-100 credentials">

            <!-- member avatar -->
            <div class="p-1">
                <img class="media-object" src="/members/avatar/<?php echo($post->OwnerUsername); ?>?size=50">
            </div>

            <!-- member name/location -->
            <div>
                <small class="username"><a
                            href="members/<?php echo $post->OwnerUsername; ?>"><?php echo $post->OwnerUsername; ?></a>
                <br>
                <?php
                if ($this->_session->has("IdMember")) {
                    if (isset($post->city) && isset($post->country)) {

                        echo $post->city . '<br>' .$post->country;

                    }
                }
                ?>
                </small>
            </div>
        </div>

        <!-- permanlink, bans, reports -->
        <div class="text-nowrap m-2">
            <?php

            if ($this->_session->has("IdMember")) {
                if ($this->BW_Right->HasRight("ForumModerator")) {
                    $TheReports = $this->_model->GetReports($post->IdPost);
                    $max = count($TheReports);
                    foreach ($TheReports as $report) {
                        echo "<br>report from ", $report->Username, " [" . $report->Status . "] ";
                        echo "<a href='forums/reporttomod/", $report->IdPost, "/" . $report->IdReporter . "'>view report</a>";
                    }
                }

                echo '<span class="gray">';
                if (isset($TheReports[0]->IdReporter)) {
                    echo "<a href='forums/reporttomod/", $post->IdPost, "'>", $words->getBuffered('ForumViewMyReportToMod'), "</a>";
                } else {
                    echo "<small><a href='forums/reporttomod/", $post->IdPost, "'><i class=\"fa fa-flag\"></i> ", $words->getBuffered('ForumMyReportToMod'), "</a></small>";
                }
                echo '</span><br>';

                echo '<span class="gray"><small><a href="forums/s' . $post->threadid . '/#post' . $post->IdPost . '"><i class="fa fa-link"></i> ' . $words->get('ForumPermalink') . '</a></small></span>';
                $TheReports = $this->_model->GetReports($post->IdPost, $this->_session->get("IdMember")); // Check if there is a pending report for this member

            }
            ?>
        </div>
        <!-- end permalink -->
    </div>
    <!-- message -->
    <div class="col-8 col-md-9 p-2">
        <div class="float-left w-100">
        <a name="post<?php echo $post->postid; ?>"></a>
        <p class="small gray">
            <?php
            //echo "[",$post->posttime,"]",$words->getFormatted('DateHHMMShortFormat') ;
            echo '<span>' . $words->getFormatted('posted'); ?><?php echo date($words->getBuffered('DateHHMMShortFormat'), ServerToLocalDateTime($post->posttime, $this->getSession())) . "</span>";
            echo $words->flushBuffer() . "<span class='ml-2'>" . $words->getFormatted("forum_label_visibility") . ": " . $words->getFormatted("forum_edit_vis_" . $post->PostVisibility) . "</span>";
            $max = 0;
            if (!empty($post->Trad)) {
                $max = count($post->Trad);
            }
            for ($jj = 0; (($jj < $max) and ($topic->WithDetail)); $jj++) { // Not optimized, it is a bit stupid to look in all the trads here
                if (($post->Trad[$jj]->trad_created != $post->Trad[$jj]->trad_updated)) { // If one of the trads have been updated
                    if ($post->Trad[$jj]->IdLanguage == $this->_session->get("IdLanguage")) {
                        echo "<br /><em>last edited on ", date($words->getFormatted('DateHHMMShortFormat'), ServerToLocalDateTime($post->Trad[$jj]->trad_updated, $this->getSession())), " by ", $post->Trad[$jj]->TranslatorUsername, "</em>";
                    }
                }
            }
            ?>
        </p>
        </div>

        <div class="forumsedit float-right">

            <?php

            if ($can_edit_own && $post->OwnerCanStillEdit == "Yes" && $User && $post->IdWriter == $this->_session->get("IdMember")) {
                echo '<a href="forums/edit/m' . $post->postid . '" class="btn btn-primary"><i class="fa fa-pencil-square-o" alt="edit" /></i> ' . $words->getFormatted('forum_EditUser') . '</a><a href="forums/translate/m' . $post->postid . '" class="btn btn-primary ml-1"><i class="fa fa-globe" alt="translate"></i> ' . $words->getFormatted('forum_TranslateUser') . '</a>';
            }
            if (($this->BW_Right->HasRight("ForumModerator", "Edit")) || ($this->BW_Right->HasRight("ForumModerator", "All"))) {
//                 echo ' [<a href="forums/modedit/m'.$post->postid.'">Mod Edit</a>]';
                echo '<a href="forums/modfulleditpost/' . $post->postid . '" class="btn btn-primary ml-1"><i class="fa fa-drivers-license-o" alt="adminedit"></i> Admin Edit</a>';
            }

            if ($can_del) {
                if ($post->postid == $topic->topicinfo->first_postid) {
                    $title = $words->getFormatted('del_topic_href');
                    $warning = $words->getFormatted('del_topic_warning');
                } else {
                    $title = $words->getFormatted('del_post_href');
                    $warning = $words->getFormatted('del_post_warning');
                }
                echo ' [<a href="forums/delete/m' . $post->postid . '" mouseover="return confirm(\'' . $warning . '\');">' . $title . '</a>]';
            }

            if (isset($post->title) && $post->title) { // This is set if it's a SEARCH
                echo $words->getFormatted('search_topic_text');
//                echo ' <b>'.$post->title.'</b> &mdash; <a href="'.ForumsView::postURL($post).'">'.$words->getFormatted('search_topic_href').'</a>';
                echo ' <strong><a href="' . ForumsView::postURL($post) . '">' . $words->fTrad($post->IdTitle) . '</a></strong>';
            }
            ?>
        </div>


            <?php
            // Todo : find a way to land here with a $topic variable well initialized
            if ($topic->WithDetail) { // If the details of trads are available, we will display them
                if ($post->PostDeleted == "Deleted") {
                    echo "[Deleted]";
                }
                // If current user has a moderator right, he can see the post
                if (($post->PostDeleted != "Deleted") or ($this->BW_Right->HasRight("ForumModerator"))) {
                    $PostMaxTrad = 0;
                    if (!empty($post->Trad)) {
                        $PostMaxTrad = count($post->Trad);
                    }
                    if ($PostMaxTrad > 1) { // we will display the list of trads only if there is more than one trad
                        echo "<p class=\"small\">", $words->getFormatted("forum_available_trads"), ":";
//                  print_r($post); echo"<br>" ;
                        for ($jj = 0; $jj < $PostMaxTrad; $jj++) {
                            $Trad = $post->Trad[$jj];


// Todo : the title for translations pops up when the mouse goes on the link but the html inside it is strips, the todo is to popup something which also displays the html result

                            $ssSentence = str_replace("\"", "&quot;", addslashes(strip_tags($Trad->Sentence, "<p><br /><br><strong><ul><li><a><img>")));
                            $ssSentence = str_replace("\n", "", $ssSentence); // If we dont remove teh extraline breaks, javascript with on mosover for translation doesn't work
//                      $ssTitle=addslashes(strip_tags(str_replace("<p>"," ",$Trad->Sentence))) ;
                            if ($jj == 0) {
                                echo "[Original <a  title=\" [" . $words->getFormatted("ForumTranslatedBy", $Trad->TranslatorUsername) . "]\"  href=\"rox/in/" . $Trad->ShortCode . "/forums/s" . $post->threadid . "\" onmouseover=\"singlepost_display" . $post->IdContent . "('" . $ssSentence . "','d" . $post->IdContent . "')\">" . $Trad->ShortCode . "</a>] ";
                            } else {
                                echo "\n[<a title=\" [" . $words->getFormatted("ForumTranslatedBy", $Trad->TranslatorUsername) . "]\"  href=\"rox/in/" . $Trad->ShortCode . "/forums/s" . $post->threadid . "\" onmouseover=\"singlepost_display" . $post->IdContent . "('" . $ssSentence . "','d" . $post->IdContent . "')\">" . $Trad->ShortCode . "</a>] \n";
                            }
                        }
                        echo "</p>";
                    }
                } // end if not deleted
            } // end If the details of trads are available, we will display them
            // If current user has a moderator right, he can see the post

// Todo: something in the following lines is causing a markup error, adding <b></b> and </div>

            ?>

            <div id="d<?= $post->IdContent ?>" class="float-left">

                <?php
                $Sentence = $words->fTrad($post->IdContent);

                if (($post->PostDeleted == "Deleted")&&($this->BW_Right->HasRight("ForumModerator"))) {
                echo "<s>", $Sentence, "</s>";
                }

                if ($post->PostDeleted != "Deleted") {

                // hide the post if the current member is not a member of this post and
                // not a forum moderator
                        if ($hideGroupOnlyPost && !($this->BW_Right->HasRight("ForumModerator"))) {
                            echo $this->words->get('GroupOnlyPostHidden');
                        } else {
                            echo $Sentence;
                        }
                    }
                    ?>
                </div>

                <?php

                // Here add additional data from votes if any

                if (isset($post->Vote)) {
                    $Vote = $post->Vote;

                    if ($Vote->PossibleAction == "ShowResult") { // If membe can see result, show them
                        echo "<div>";
                        echo $words->getFormatted("ForumPostCurrentResults");
                        echo "<ul>";
                        foreach ($Vote->PossibleChoice as $cc) {
                            $ss = $cc;
                            $count = $Vote->$ss;
                            $countpercent = "0%";
                            if ($Vote->Total > 0) {
                                $countpercent = sprintf("%0.0f", ($count / $Vote->Total) * 100);
                            }
                            echo "<li>", $words->getFormatted('ForumResultForChoice', '<b>' . $words->getFormatted('ForumVoteChoice_' . $cc) . '</b>', $count, $countpercent . '%'), "</li>";
                        }
                        echo "</ul></div>";
                    }


                    if (!empty($Vote->Choice)) { // If The current user has voted
                        echo "<div>";
                        echo "<a href=\"forums/deletevotepost/", $post->IdPost, "\">", $words->getFormatted('ForumDeleteVotePost'), "</a>";
                        echo "</div>";
                    }

                    if ($Vote->PossibleAction == "ProposeVote") { // If member can vote propose vote
                        echo $words->getFormatted("ForumPostMakeYourChoice"), ":<br />";
                        foreach ($Vote->PossibleChoice as $cc) {
                            echo "<a href=\"forums/votepost/", $post->IdPost, "/", $cc, "\">", $words->getBuffered('ForumVoteChoice_' . $cc), "</a>&nbsp; &nbsp; &nbsp;";
                        }
                    }
                }  // End of add additional data from local volunteers messages if any

            ?>

    </div>
    <!-- end message -->
</div>

<?php

if ((isset($PostMaxTrad)) and ($PostMaxTrad > 1)) { // No need to at javascript catcher function is there is no more than one translations
    ?>
    <script type="text/javascript">
        <!--
        function singlepost_display <?php echo $post->IdContent; ?>(strCode, div_area) {
            if (document.layers) {
                document.getElementById(div_area).open();
                document.getElementById(div_area).write(strCode.replace(/\\/g, ''));
                document.getElementById(div_area).close();
            }
            else {
                document.getElementById(div_area).innerHTML = strCode;
//      document.all(div_area).innerHTML = strCode;
            }
        }
        // -->
    </script>
    <?php
}

?>
