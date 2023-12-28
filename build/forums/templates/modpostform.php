<?php
/*

This is the form which manage the MODERATOR FULL EDIT POST

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
<h2><?= $DataPost->Thread->title; ?></h2>
<a href="forums/s<?= $DataPost->Thread->id; ?>" class="btn btn-sm btn-primary float-right">go to thread</a>
<h4>Post #m
    <?php
    echo $DataPost->IdPost;
    echo " in thread #s";
    echo $DataPost->Thread->id;
    ?> by <a href="/members/<?= $DataPost->Post->UserNamePoster ?>"><?= $DataPost->Post->UserNamePoster ?></a>
    [<?= $DataPost->Post->memberstatus ?>]
</h4>

<?php
if (!empty($DataPost->Error)) {
    echo "<h2 style=\"color:#ff0033;\" >", $DataPost->Error, "</h2>";
}

$request = PRequest::get()->request;
$uri = implode('/', $request);
?>

<?php
if (isset($DataPost->Thread->title))
?>
    <legend>Thread Properties</legend>
<hr>
<form method="post" action="forums/modeditpost/<?= $DataPost->Post->id; ?>" id="modpostforum">
    <input type="hidden" name="<?= $callbackId; ?>" value="1"/>
    <div class="o-form-group form-row">
        <label class="col-3 col-form-label" for="stickyvalue">stickyvalue</label>
        <input type="text" class="col-9 o-input" name="stickyvalue" id="stickyvalue" size="1"
               value="<?= $DataPost->Thread->stickyvalue; ?>"/>
        <small class="col-9 offset-3 text-muted">(default 0, the most negative will be the first visible)</small>
    </div>
    <div class="o-form-group form-row">
        <label class="col-3 col-form-label" for="expiredate">expiration date</label>
        <input type="text" class="col-9 o-input" id="expiredate" name="expiredate"
               value="<?= $DataPost->Thread->expiredate; ?>"/>
        <small class="col-9 offset-3 text-muted">(close the thread)</small>
    </div>
    <div class="o-form-group form-row">
        <label class="col-3 col-form-label" for="ThreadVisibility">Thread Visibility</label>
        <select name="ThreadVisibility" id="ThreadVisibility" class="col-9 o-input">
            <option value="MembersOnly"
                <?php
                if ($DataPost->Thread->ThreadVisibility == "MembersOnly") {
                    echo " selected";
                }
                ?>
            >BeWelcome Members only
            </option>
            <option value="GroupOnly"
                <?php
                if ($DataPost->Thread->ThreadVisibility == "GroupOnly") {
                    echo " selected";
                }
                ?>
            >Members of group
            </option>
            <option value="ModeratorOnly"
                <?php
                if ($DataPost->Thread->ThreadVisibility == "ModeratorOnly") {
                    echo " selected";
                }
                ?>
            >Moderators only
            </option>
        </select>
    </div>
    <div class="o-form-group form-row">
        <label class="col-3 col-form-label" for="IdGroup">Group</label>
        <select id="IdGroup" name="IdGroup" class="col-9 o-input">
            <option value="0"> no group</option>
            <?php
            foreach ($DataPost->PossibleGroups as $Group) {
                echo "<option value=\"" . $Group->IdGroup . "\"";
                if ($Group->IdGroup == $DataPost->Thread->IdGroup) {
                    echo " selected";
                }
                echo ">", $Group->Name, "</option>\n";
            }; ?>
        </select>
    </div>
    <div class="o-form-group form-row">
        <label class="col-3 col-form-label" for="WhoCanReply">Who can reply</label>
        <select name="WhoCanReply" id="WhoCanReply" class="col-9 o-input">
            <option value="MembersOnly"
                <?php
                if ($DataPost->Thread->WhoCanReply == "MembersOnly") {
                    echo " selected";
                }
                ?>
            >All members
            </option>
            <option value="GroupMembersOnly"
                <?php
                if ($DataPost->Thread->WhoCanReply == "GroupMembersOnly") {
                    echo " selected";
                }
                ?>
            >Group Members only
            </option>
            <option value="ModeratorOnly"
                <?php
                if ($DataPost->Thread->WhoCanReply == "ModeratorOnly") {
                    echo " selected";
                }
                ?>
            >Moderators only
            </option>
        </select>
    </div>
    <div class="o-form-group form-row">
        <label class="col-3 col-form-label" for="ThreadDeleted">Thread deleted</label>
        <select id="ThreadDeleted" name="ThreadDeleted" class="col-9 o-input">
            <option value="Deleted"
                <?php
                if ($DataPost->Thread->ThreadDeleted == "Deleted") {
                    echo " selected";
                }
                ?>
            >Deleted
            </option>
            <option value="NotDeleted"
                <?php
                if ($DataPost->Thread->ThreadDeleted == "NotDeleted") {
                    echo " selected";
                }
                ?>
            >Not Deleted
            </option>
        </select>
    </div>
    <input type="hidden" name="IdThread" value="<?= $DataPost->Thread->id; ?>"/>
    <input type="hidden" name="IdPost" value="<?= $DataPost->Post->id; ?>"/>
    <input type="submit" name="submit" class="btn btn-sm btn-primary float-right" value="update thread">
</form>
<?php $max = count($DataPost->Thread->Title); ?>
<legend>Thread Title
    <small class='text-muted'>(<?= $max ?> translations)</small>
</legend>
<hr>
<?php
foreach ($DataPost->Thread->Title as $Title) { ?>
    <form method="post" action="forums/modeditpost/<?= $DataPost->Post->id ?>" id="modpostforum">
        <input type="hidden" name="<?= $callbackId ?>" value="1">
        <input type="hidden" name="IdPost" value="<?= $DataPost->Post->id ?>">
        <input type="hidden" name="IdThread" value="<?= $DataPost->Thread->id ?>">
        <?php $ArrayLanguage = $this->_model->LanguageChoices($Title->IdLanguage); ?>
        <div class="o-form-group form-row">
            <label class="col-3 col-form-label" for="IdLanguage">Language</label>
            <select class="col-9 o-input select2" id="IdLanguage" name="IdLanguage">
                <?php foreach ($ArrayLanguage as $Choices) {
                    if (is_object($Choices)) {
                        echo "<option value=\"", $Choices->IdLanguage, "\"";
                        if ($Choices->IdLanguage == $Title->IdLanguage) echo " selected ";
                        echo "\">", $Choices->Name, "</option>";
                    }
                } ?>
            </select></div>
        <div class="o-form-group form-row">
            <label class="col-3 col-form-label" for="Sentence">Title</label>
            <textarea class="col-9 o-input" id="Sentence" name="Sentence"
                      rows="1"><?= $Title->Sentence ?></textarea>
            <input type="hidden" name="IdForumTrads" value="<?= $Title->IdForumTrads ?>">
        </div>
        <input type="submit" class="btn btn-sm btn-primary float-right" value="update title">
    </form>
    <?php
}
?>
<legend>Add new translated thread title</legend>
<hr>
<form method="post" action="forums/modeditpost/<?= $DataPost->Post->id ?>" id="modpostforum">
    <input type="hidden" name="<?= $callbackId ?>" value="1">
    <input type="hidden" name="IdPost" value="<?= $DataPost->Post->id ?>">
    <input type="hidden" name="IdThread" value="<?= $DataPost->Thread->id ?>">
    <input type="hidden" name="IdTrad" value="<?= $DataPost->Thread->IdTitle ?>">
    <?php
    $ArrayLanguage = $this->_model->LanguageChoices(0);
    ?>
    <div class="o-form-group form-row">
        <label class="col-3 col-form-label" for="IdLanguage">Language</label>
        <select class="o-input col-9 select2" id="IdLanguage" name="IdLanguage">
            <?php
            foreach ($ArrayLanguage as $Choices) {
                if (is_object($Choices)) {
                    echo "<option value=\"", $Choices->IdLanguage, "\"";
                    echo "\">" . $Choices->Name . "</option>";
                }
            }
            ?>
        </select>
    </div>
    <div class="o-form-group form-row">
        <label class="col-3 col-form-label" for="NewTranslatedTitle">Title</label>
        <textarea class="o-input col-9" id="NewTranslatedTitle" name="NewTranslatedTitle" rows="5"></textarea>
    </div>
    <input type="submit" class="btn btn-sm btn-primary float-right" name="submit" value="add translated title">
</form>
<legend>Post Properties</legend>
<hr>
<?php $max = count($DataPost->Post->Content); ?>
<form method="post" action="forums/modeditpost/<?= $DataPost->Post->id ?>" id="modpostforum">
    <input type="hidden" name="<?= $callbackId ?>" value="1">
    <input type="hidden" name="IdPost" value="<?= $DataPost->Post->id ?>">
    <input type="hidden" name="IdThread" value="<?= $DataPost->Thread->id ?>">
    <div class="o-form-group form-row">
        <label class="col-3 col-form-label" for="OwnerCanStillEdit">Can Owner edit:</label>
        <select class="o-input col-9" id="OwnerCanStillEdit" name="OwnerCanStillEdit">
            <option value="Yes" <?php
            if ($DataPost->Post->OwnerCanStillEdit == "Yes") echo " selected"; ?>
            >Yes
            </option>
            <option value="No" <?php
            if ($DataPost->Post->OwnerCanStillEdit == "No") echo " selected"; ?>
            >No
            </option>
        </select>
    </div>
    <div class="o-form-group form-row">
        <label class="col-3 col-form-label" for="PostVisibility">Post Visibility</label>
        <select class="col-9 o-input" id="PostVisibility" name="PostVisibility">
            <option value="MembersOnly"
                <?php
                if ($DataPost->Post->PostVisibility == "MembersOnly") {
                    echo " selected";
                }
                ?>
            >BeWelcome Members only
            </option>
            <option value="GroupOnly"
                <?php
                if ($DataPost->Post->PostVisibility == "GroupOnly") {
                    echo " selected";
                }
                ?>
            >Members of group
            </option>
            <option value="ModeratorOnly"
                <?php
                if ($DataPost->Post->PostVisibility == "ModeratorOnly") {
                    echo " selected";
                }
                ?>
            >Moderators only
            </option>
        </select>
    </div>
    <div class="o-form-group form-row">
        <label class="col-3 col-form-label" for="PostDeleted">Post deleted</label>
        <select class="o-input col-9" id="PostDeleted" name="PostDeleted">
            <option value="Deleted"
                <?php
                if ($DataPost->Post->PostDeleted == "Deleted") {
                    echo " selected";
                }
                ?>
            >Deleted
            </option>
            <option value="NotDeleted"
                <?php
                if ($DataPost->Post->PostDeleted == "NotDeleted") {
                    echo " selected";
                }
                ?>
            >Not Deleted
            </option>
        </select>
    </div>
    <input type="hidden" name="IdThread" value="<?= $DataPost->Thread->id ?>">
    <input class="btn btn-sm btn-primary float-right" name="submit" type="submit" value="update post">
</form>
<legend>Post Content</legend>
<hr>
<?php
foreach ($DataPost->Post->Content as $Content) { ?>
    <form method="post" action="forums/modeditpost/<?= $DataPost->Post->id ?>" id="modpostforum">
        <input type="hidden" name="<?= $callbackId ?>" value="1">
        <input type="hidden" name="IdPost" value="<?= $DataPost->Post->id ?>">
        <input type="hidden" name="IdThread" value="<?= $DataPost->Thread->id ?>">
        <?php $ArrayLanguage = $this->_model->LanguageChoices($Content->IdLanguage); ?>
        <div class="o-form-group form-row">
            <label class="col-3 col-form-label" for="IdLanguage">Language</label>
            <select class="col-9 o-input select2" name="IdLanguage" id="IdLanguage">
                <?php foreach ($ArrayLanguage as $Choices) {
                    if (is_object($Choices)) {
                        echo "<option value=\"", $Choices->IdLanguage, "\"";
                        if ($Choices->IdLanguage == $Content->IdLanguage) {
                            echo " selected ";
                        }
                        echo "\">" . $Choices->Name . "</option>";
                    }
                } ?>
            </select>
        </div>
        <div class="o-form-group form-row">
            <label class="col-3 col-form-label" for="Sentence">Post</label>
            <textarea class="col-9 o-input" id="Sentence" name="Sentence"
                      rows="5"><?= $Content->Sentence ?></textarea>
        </div>
        <input id="IdForumTrads" type="hidden" name="IdForumTrads" value="<?= $Content->IdForumTrads ?>">
        <input type="submit" class="btn btn-sm btn-primary float-right" value="update">
    </form>
<?php } ?>

<legend>Add new translated post</legend>
<hr>
<form class="mb-2" method="post" action="forums/modeditpost/<?= $DataPost->Post->id ?>" id="modpostforum">
    <input type="hidden" name="<?= $callbackId ?>" value="1">
    <input type="hidden" name="IdPost" value="<?= $DataPost->Post->id ?>">
    <input type="hidden" name="IdThread" value="<?= $DataPost->Thread->id ?>">
    <input type="hidden" name="IdTrad" value="<?= $DataPost->Post->IdContent ?>">
    <?php $ArrayLanguage = $this->_model->LanguageChoices(); ?>
    <div class="o-form-group form-row">
        <label class="col-3 col-form-label" id="IdLanguage">Language</label>
        <select class="col-9 o-input select2" id="IdLanguage" name="IdLanguage">
            <?php foreach ($ArrayLanguage as $Choices) {
                if (is_object($Choices)) {
                    echo "<option value=\"", $Choices->IdLanguage, "\"";
                    echo "\">", $Choices->Name, "</option>";
                }
            } ?>
        </select>
    </div>
    <div class="o-form-group form-row">
        <label class="col-3 col-form-label" for="NewTranslatedPost">Translation</label>
        <textarea class="col-9 o-input" id="NewTranslatedPost" name="NewTranslatedPost" rows="5"></textarea>
    </div>
    <input type="submit" class="btn btn-sm btn-primary float-right" value="add translated post" name="submit">
    <div class="clearfix"></div>
</form>

