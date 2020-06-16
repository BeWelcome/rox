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

<div class="row no-gutters border border-white <?php echo $styles[$cnt % 2]; ?> mb-2">
    <!-- left column -->
    <div class="col-4 col-md-3 postleftcolumn">
        <div class="d-flex flex-column align-self-start">
            <div class="media credentials p-1">
                <img class="avatar-50 mr-1" src="/members/avatar/<?php echo($post->OwnerUsername); ?>/50">
                <div class="media-body">
                    <p class="small">
                    <a href="members/<?php echo $post->OwnerUsername; ?>"><?php echo $post->OwnerUsername; ?></a>
                    <br>
                    <?php
                        if ($this->session->has("IdMember")) {
                            if (isset($post->city) && isset($post->country)) {
                                echo $post->city . '<br>' .$post->country;
                            }
                        }
                    ?>
                    </p>
                </div>
            </div>

            <!-- permalink, bans, reports -->
            <div class="p-1">
                <?php

                if ($this->session->has("IdMember")) {
                    if ($this->BW_Right->HasRight("ForumModerator")) {
                        $TheReports = $this->_model->GetReports($post->postid);
                        $max = count($TheReports);
                        foreach ($TheReports as $report) {
                            echo "<small class='text-muted'>{$report->Status} report from ", $report->Username, "</small><br>";
                            echo "<small class='text-muted'><a href='forums/reporttomod/", $report->IdPost, "/" . $report->IdReporter . "'>view report</a></small><br>";
                        }
                    }

                    echo '<small class="text-muted">';
                    if (isset($TheReports[0]->IdReporter) && ($TheReports[0]->IdReporter == $this->session->get("IdMember"))) {
                        echo "<a href='forums/reporttomod/", $post->postid, "'>", $words->getBuffered('ForumViewMyReportToMod'), "</a>";
                    } else {
                        echo "<a href='forums/reporttomod/", $post->postid, "'><i class=\"fa fa-flag\"></i> ", $words->getBuffered('ForumMyReportToMod'), "</a>";
                    }
                    echo '</small><br>';

                    echo '<small class="text-muted"><a href="forums/s' . $post->threadid . '/#post' . $post->postid . '"><i class="fa fa-link"></i> ' . $words->get('ForumPermalink') . '</a></small><br>';
                    $TheReports = $this->_model->GetReports($post->postid, $this->session->get("IdMember")); // Check if there is a pending report for this member

                }
                ?>
            </div>
            <!-- end permalink -->
        </div>
    </div>

    <!-- message -->
    <div class="col-8 col-md-9 p-1">
        <div class="d-flex flex-column align-content-stretch">
            <div class="d-flex flex-row justify-content-between mb-1">
                <div>
                    <a id="post<?php echo $post->postid; ?>" class="text-truncate" style="position: relative;top: -50px;"></a>
                    <small class="gray">
                        <?php
                        echo '<span><i class="fa fa-comment mr-1" title="' . $words->getFormatted('posted'); ?>"></i><?php echo date($words->getBuffered('DateHHMMShortFormat'), ServerToLocalDateTime($post->posttime, $this->getSession())) . '</span>';
                        echo $words->flushBuffer() . '<i class="fa fa-eye mx-1" title="' . $words->getFormatted("forum_label_visibility") . '"></i>' . $words->getFormatted("forum_edit_vis_" . $post->PostVisibility) . '';
                        $max = 0;
                        if (!empty($post->Trad)) {
                            $max = count($post->Trad);
                        }
                        for ($jj = 0; (($jj < $max) and ($topic->WithDetail)); $jj++) { // Not optimized, it is a bit stupid to look in all the trads here
                            if (($post->Trad[$jj]->trad_created != $post->Trad[$jj]->trad_updated)) { // If one of the trads have been updated
                                if ($post->Trad[$jj]->IdLanguage == $this->session->get("IdLanguage")) {
                                    echo '<br><em><i class="fa fa-edit mr-1" title="edited"></i>' . date($words->getFormatted('DateHHMMShortFormat'), ServerToLocalDateTime($post->Trad[$jj]->trad_updated, $this->getSession())), ' by ', $post->Trad[$jj]->TranslatorUsername . '</em>';
                                }
                            }
                        }
                        ?>
                    </small>
                </div>
                <div>
                    <?php
                    if ($can_edit_own && $post->OwnerCanStillEdit == "Yes" && $User && $post->IdWriter == $this->session->get("IdMember")) {
                        echo '<a href="forums/edit/m' . $post->postid . '" class="btn btn-sm btn-outline-primary ml-1"><i class="fa fa-edit" title="edit" /></i> ' . $words->getFormatted('forum_EditUser') . '</a>';
                    }
                    if (($this->BW_Right->HasRight("ForumModerator", "Edit")) || ($this->BW_Right->HasRight("ForumModerator", "All"))) {
        //                 echo ' [<a href="forums/modedit/m'.$post->postid.'">Mod Edit</a>]';
                        echo '<a href="forums/modfulleditpost/' . $post->postid . '" class="btn btn-sm btn-outline-primary ml-1"><i class="fa fa-edit" title="adminedit"></i> Admin Edit</a>';
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

                    ?>
                </div>
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
            ?>

            <div id="d<?= $post->IdContent ?>">

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

                if (isset($post->title) && $post->title) { // This is set if it's a SEARCH
                    echo '<div class="d-flex justify-content-end"><small>';
                    if (isset($post->GroupName) && $post->GroupName) {
                        echo '<span class="gray">'.$words->getFormatted('group') . ":</span> ";
                        echo '<a href="' . ForumsView::groupURL($post) . '">' . $post->GroupName . '</a> ';
                    }
                    echo '<span class="gray">'.$words->getFormatted('search_topic_text').'</span>';
                    echo ' <a href="' . ForumsView::postURL($post) . '">' . $words->fTrad($post->IdTitle) . '</a>';
                    echo '</small></div>';
                }

            ?>
        </div>
    </div>
    <!-- end message -->
</div>
