<div class="col-12">
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

$words = $this->getWords();

if (!empty($errormessage)) {
    ?>
    <p class="alert alert-error"><?= $errormessage; ?></p>
    <?
}
$words = new MOD_words();
$Data = $this->_data;
if (isset($Data->rPoll->id)) { // Form for update
    $rr = $Data->rPoll;


    ?>
    <p class="note">
        This is the page to update a poll.<br>
        You need to fill the following fields<br>
        Use English language for now only
    </p>

    <form class="form" name="contribute" action="polls/doupdatepoll" id="idupdatepoll" method="post">
        <!-- The following will disable the nasty PPostHandler -->
        <input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>

        <input type="hidden" name="<?= $callbackId ?>" value="1"/>
        <input type="hidden" name="IdPoll" value="<?= $Data->rPoll->id ?>"/>
        <div class="form-group">
            <label for="Status">Polls Status:</label>
            <?
            $sChoice = array("Project", "Open", "Close");
            if (empty($rr->Status)) $rr->Status = "Project"; // By default a poll will be at Project Status
            echo "<select class='form-control select2' id=\"Status\" name=\"Status\">\n";
            for ($ii = 0; $ii < count($sChoice); $ii++) {
                echo "<option value=\"" . $sChoice[$ii] . "\"";
                if ($sChoice[$ii] == $rr->Status) {
                    echo " selected ";
                }
                echo ">", $sChoice[$ii], "</option>\n";
            }
            echo "</select>\n";
            ?>
        </div>
        <div class="form-group">
            <label for="TypeOfChoice">Type of choice:</label>
            <?
            $sChoice = array('Exclusive', 'Inclusive', 'Ordered');
            if (empty($rr->TypeOfChoice)) $rr->TypeOfChoice = "Exclusive"; // By default a poll will be Exclusive
            echo "<select class='form-control select2' id=\"TypeOfChoice\" name=\"TypeOfChoice\">\n";
            for ($ii = 0; $ii < count($sChoice); $ii++) {
                echo "<option value=\"" . $sChoice[$ii] . "\"";
                if ($sChoice[$ii] == $rr->TypeOfChoice) {
                    echo " selected ";
                }
                echo ">", $sChoice[$ii], "</option>\n";
            }
            echo "</select>\n";
            ?>
        </div>
        <div class="form-group">
            <label for="Ended">Poll will end on:</label>
            <?
            $ii = 0;
            if (empty($rr->Ended)) {
                $rr->Ended = "0000-00-00 00:00:00"; // By default a poll will not have a end
            }
            echo "<input class='form-control' id=\"Ended\" name=\"Ended\" Value=\"" . $rr->Ended . "\" type=\"text\" />";
            ?>
        </div>
        <div class="form-group">
            <label for="CreatorUsername">Owner:</label>
            <?
            $ii = 0;
            if (empty($rr->CreatorUsername)) {
                $rr->CreatorUsername = $this->_session->get('Username'); // By default a poll is owned by the current member
            }
            echo "<input class='form-control' id=\"CreatorUsername\" name=\"CreatorUsername\" Value=\"" . $rr->CreatorUsername . "\" type=\"text\" />";
            ?>
        </div>
        <div class="form-group">
            <label for="Allow Comment">Allow Comments</label>
            <?
            if (empty($rr->AllowComment)) $rr->AllowComment = "No"; // By default a poll is not aimed to collect comments
            $sChoice = array("Yes", "No");
            echo "<select class='form-control select2' id=\"AllowComment\" name=\"AllowComment\">";
            for ($ii = 0; $ii < count($sChoice); $ii++) {
                echo "<option value=\"" . $sChoice[$ii] . "\"";
                if ($sChoice[$ii] == $rr->AllowComment) {
                    echo " selected ";
                }
                echo ">", $sChoice[$ii], "</option>";
            }
            echo "</select>";
            ?>
        </div>
        <div class="form-group">
            <label for="Anonym">Anonymous Poll</label>
            <?
            if (empty($rr->Anonym)) $rr->Anonym = "No"; // By default a poll is not aimed to collect comments
            $sChoice = array("Yes", "No");
            echo "<select class='form-control select2' id=\"Anonym\"name=\"Anonym\">";
            for ($ii = 0; $ii < count($sChoice); $ii++) {
                echo "<option value=\"" . $sChoice[$ii] . "\"";
                if ($sChoice[$ii] == $rr->Anonym) {
                    echo " selected ";
                }
                echo ">", $sChoice[$ii], "</option>";
            }
            echo "</select>";
            ?>
        </div>
        <div class="form-group">
            <label for="ResultVisibility">Result visibility:</label>
            <?
            if ($rr->ResultsVisibility == "") $rr->ResultsVisibility = "No"; // By default a poll is not aimed to collect comments
            $sChoice = array("Not Visible", "Visible", "VisibleAfterVisit");
            echo "<select class='form-control select2' id=\"ResultVisibiliyt\" name=\"ResultsVisibility\">";
            for ($ii = 0; $ii < count($sChoice); $ii++) {
                echo "<option value=\"" . $sChoice[$ii] . "\"";
                if ($sChoice[$ii] == $rr->ResultsVisibility) {
                    echo " selected ";
                }
                echo ">", $sChoice[$ii], "</option>";
            }
            echo "</select>";
            ?>
        </div>
        <div class="form-group">
            <label for="Title">Poll Title:</label>
            <input class="form-control" type="text" ID="Titile" name="Title" size="60"
                   value="<?= $words->fTrad($Data->rPoll->Title) ?>"/></div>
        <div class="form-group">
            <label for="Description">Poll Description:</label>
            <textarea class="form-control" id="Description" name="Description" cols="60"
                      rows="5"><?= $words->fTrad($Data->rPoll->Description) ?></textarea>
        </div>
        <div class="form-group">
            <label for="GroupIdLimit">Limited to groups Id : </label>
            <input class="form-control" type="text" id="GroupIdLimit" name="GroupIdLimit" size="8" class="long" value="
<?php for ($ii = 0; $ii < count($Data->IdGroupRestricted); $ii++) {
                if ($ii > 0) echo ",";
                echo $Data->IdGroupRestricted[$ii]->IdGroup;
            }
            ?>
">
            <small class="text-muted ">(experimental comma separated)</small>
        </div>
        <?php if (!empty($rr->WhereToRestrictMember)) {
            echo "<p>Special restriction (ask admin) " . $rr->WhereToRestrictMember . "</p>";
        }
        ?>
        <input type="submit" class="btn btn-primary" value="go update">
    </form>

    <hr>

    <?
    for ($ii = 0; $ii < count($Data->Choices); $ii++) {
        $cc = $Data->Choices[$ii];
        ?>
        <form name="updatechoice_<?= $ii ?>" action="polls/updatechoice" id="idupdatechoice_<?= $ii ?>" method="post">
            <!-- The following will disable the nasty PPostHandler -->
            <input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>

            <input type="hidden" name="<?= $callbackId ?>" value="1"/>
            <input name="IdLanguage" value="<?php echo $this->_session->get("IdLanguage"); ?>" type="hidden"/>
            <input type="hidden" name="IdPoll" value="<?= $Data->rPoll->id ?>"/>
            <input type="hidden" name="IdPollChoice" value="<?= $cc->id ?>"/>
            <input type="hidden" name="IdChoiceText" value="
    <?= $cc->IdChoiceText ?>
    "/>
            <div class="form-group"><label for="Option">Option:</label>
                <textarea class="form-control" id="Option" name="ChoiceText" cols="60"
                          rows="2"><?= $words->fTrad($cc->IdChoiceText) ?></textarea>
            </div>
                <input type="submit" class="btn btn-primary" value="update choice"/>
            
        </form>
        <?
    }
    ?>


    <form name="addchoice" action="polls/addchoice" id="idaddchoice" method="post">
        <!-- The following will disable the nasty PPostHandler -->
        <input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>

        <input type="hidden" name="<?= $callbackId ?>" value="1"/>
        <input type="hidden" name="IdLanguage" value="<?
        $this->_session->get("IdLanguage") ?>"/>
        <input type="hidden" name="IdPoll" value="<?= $Data->rPoll->id ?>"/>
        <div class="form-group">
            <label for="NewOption">New option:</label>
            <textarea class="form-control" id="NewOption" name="ChoiceText" cols="60" rows="2"></textarea>
        </div>
        <input type="submit" class="btn btn-primary" value="Add"/>
        
    </form>
    <?
} else { // form for create
    ?>
    <p class="alert alert-notice">
        This is the page to create a new poll<br>
        You need to fill the following fields<br>
        Use English language for now only
    </p>

    <form name="contribute" action="polls/createpoll" id="idcreatepoll" method="post">
        <!-- The following will disable the nasty PPostHandler -->
        <input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>

        <input type="hidden" name="<?= $callbackId ?>" value="1"/>
        <input name="IdLanguage" value="<?php echo $this->_session->get("IdLanguage"); ?>" type="hidden"/>
        <div class="form-group">
            <label for="Title">Poll Title:</label>
            <input type="text" id="Title" name="Title" size="60" class="form-control">
        </div>
        <div class="form-group">
            <label for="Description">Poll Description:</label>
            <textarea id="Description" name="Description" rows="5" cols="60" class="form-control"></textarea>
        </div>
        <div class="form-group">
            <label for="GroupIdLimit">Limited to groups Id : </label>
            <input type="text" id="GroupIdLimit" name="GroupIdLimit" class="form-control">
            <small class="text-muted">experimental comma separated</small>
        </div>
        <input type="submit" class="btn btn-primary" name="go create">
        
    </form>
    <?
}
?>
</div>
