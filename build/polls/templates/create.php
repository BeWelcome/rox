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
    <?php
}
$words = new MOD_words();
$Data = $this->_data;
if (isset($Data->rPoll->id)) { // Form for update
    $rr = $Data->rPoll;


    ?>
    <div class="alert alert-info">
        This is the page to update a poll.<br>
        You need to fill the following fields<br>
        Use English language for now only
    </div>

    <form class="form" name="contribute" action="polls/doupdatepoll" id="idupdatepoll" method="post">
        <!-- The following will disable the nasty PPostHandler -->
        <input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>

        <input type="hidden" name="<?= $callbackId ?>" value="1"/>
        <input type="hidden" name="IdPoll" value="<?= $Data->rPoll->id ?>"/>
        <div class="o-form-group">
            <label for="Status">Polls Status:</label>
            <?php
            $sChoice = array("Project", "Open", "Closed");
            if (empty($rr->Status)) $rr->Status = "Project"; // By default a poll will be at Project Status
            echo "<select class='o-input select2' data-minimum-results-for-search=\"Infinity\" id=\"Status\" name=\"Status\">\n";
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
        <div class="o-form-group">
            <label for="TypeOfChoice">Type of choice:</label>
            <?php
            $sChoice = array('Exclusive', 'Inclusive', 'Ordered');
            if (empty($rr->TypeOfChoice)) $rr->TypeOfChoice = "Exclusive"; // By default a poll will be Exclusive
            echo "<select class='o-input select2' data-minimum-results-for-search=\"Infinity\" id=\"TypeOfChoice\" name=\"TypeOfChoice\">\n";
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
        <div class="o-form-group">
            <label for="Ended">Poll will end beginning of (server time/CET):</label>
            <div class="input-group date" id="poll-end-datetimepicker" data-target-input="nearest">
                <div class="input-group-prepend" data-target="#Ended" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="far fa-calendar"></i></div>
                </div>
                <input type="text" id="Ended" name="Ended" class="o-input datetimepicker-input"
                       data-toggle="datetimepicker" data-target="#Ended" autocomplete="off" value="<?= $rr->Ended ?>" >
            </div>
        </div>
        <div class="o-form-group">
            <label for="CreatorUsername">Owner:</label>
            <?php
            $ii = 0;
            if (empty($rr->CreatorUsername)) {
                $rr->CreatorUsername = $this->session->get('Username'); // By default a poll is owned by the current member
            }
            echo "<input class='o-input' id=\"CreatorUsername\" name=\"CreatorUsername\" Value=\"" . $rr->CreatorUsername . "\" type=\"text\" />";
            ?>
        </div>
        <div class="o-form-group">
            <label for="Allow Comment">Allow Comments</label>
            <?php
            if (empty($rr->AllowComment)) $rr->AllowComment = "No"; // By default a poll is not aimed to collect comments
            $sChoice = array("Yes", "No");
            echo "<select class='o-input select2' data-minimum-results-for-search=\"Infinity\" id=\"AllowComment\" name=\"AllowComment\">";
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
        <div class="o-form-group">
            <label for="Anonym">Anonymous Poll</label>
            <?php
            if (empty($rr->Anonym)) $rr->Anonym = "No"; // By default a poll is not aimed to collect comments
            $sChoice = array("Yes", "No");
            echo "<select class='o-input select2' data-minimum-results-for-search=\"Infinity\" id=\"Anonym\"name=\"Anonym\">";
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
        <div class="o-form-group">
            <label for="ResultVisibility">Result visibility:</label>
            <?php
            if ($rr->ResultsVisibility == "") $rr->ResultsVisibility = "No"; // By default a poll is not aimed to collect comments
            $sChoice = array("Not Visible", "Visible", "VisibleAfterVisit");
            echo "<select class='o-input select2' data-minimum-results-for-search=\"Infinity\" id=\"ResultVisibiliyt\" name=\"ResultsVisibility\">";
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
        <div class="o-form-group">
            <label for="Title">Poll Title:</label>
            <input class="o-input" type="text" ID="Titile" name="Title" size="60"
                   value="<?= $words->fTrad($Data->rPoll->Title) ?>"/></div>
        <div class="o-form-group">
            <label for="Description">Poll Description:</label>
            <textarea class="o-input editor" id="Description" name="Description" cols="60"
                      rows="5"><?= $words->fTrad($Data->rPoll->Description) ?></textarea>
        </div>
        <div class="o-form-group">
            <label for="GroupIdLimit">Limited to Group:</label>
            <select class="o-input select2" readonly="readonly" id="GroupIdLimit" name="GroupIdLimit">
                <option value="-1" <?= (null === $Data->rPoll->IdGroupRestricted)?'selected="selected"':''; ?>></option>
                <?php
                $groups = $this->member->getGroups();
                foreach($groups as $group) {
                    echo '<option value="' . $group->id . '" ';
                    if ($Data->rPoll->IdGroupRestricted == $group->id) {
                        echo 'selected="selected"';
                    }
                    echo '>' . $group->Name . '</option>';
                }
                ?>
            </select>
            <small class="form-text text-muted ">Group selected (can't be changed after creation).</small>
        </div>
        <input type="submit" class="btn btn-primary" value="go update">
    </form>

    <hr>

    <?php
    for ($ii = 0; $ii < count($Data->Choices); $ii++) {
        $cc = $Data->Choices[$ii];
        ?>
        <form name="updatechoice_<?= $ii ?>" action="polls/updatechoice" id="idupdatechoice_<?= $ii ?>" method="post">
            <!-- The following will disable the nasty PPostHandler -->
            <input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>

            <input type="hidden" name="<?= $callbackId ?>" value="1"/>
            <input name="IdLanguage" value="<?php echo $this->session->get("IdLanguage"); ?>" type="hidden"/>
            <input type="hidden" name="IdPoll" value="<?= $Data->rPoll->id ?>"/>
            <input type="hidden" name="IdPollChoice" value="<?= $cc->id ?>"/>
            <input type="hidden" name="IdChoiceText" value="
    <?= $cc->IdChoiceText ?>
    "/>
            <div class="o-form-group"><label for="Option">Option:</label>
                <textarea class="o-input editor" id="Option" name="ChoiceText" cols="60"
                          rows="2"><?= $words->fTrad($cc->IdChoiceText) ?></textarea>
            </div>
                <input type="submit" class="btn btn-primary" value="update choice"/>

        </form>
        <?php
    }
    ?>


    <form name="addchoice" action="polls/addchoice" id="idaddchoice" method="post">
        <!-- The following will disable the nasty PPostHandler -->
        <input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>

        <input type="hidden" name="<?= $callbackId ?>" value="1"/>
        <input type="hidden" name="IdLanguage" value="<?php
        $this->session->get("IdLanguage") ?>"/>
        <input type="hidden" name="IdPoll" value="<?= $Data->rPoll->id ?>"/>
        <div class="o-form-group">
            <label for="NewOption">New option:</label>
            <textarea class="o-input editor" id="NewOption" name="ChoiceText" cols="60" rows="2"></textarea>
        </div>
        <input type="submit" class="btn btn-primary" value="Add"/>

    </form>
    <?php
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
        <input name="IdLanguage" value="<?php echo $this->session->get("IdLanguage"); ?>" type="hidden"/>
        <div class="o-form-group">
            <label for="Title">Poll Title:</label>
            <input type="text" id="Title" name="Title" size="60" class="o-input">
        </div>
        <div class="o-form-group">
            <label for="Description">Poll Description:</label>
            <textarea id="Description" name="Description" rows="5" cols="60" class="o-input editor"></textarea>
        </div>
        <div class="o-form-group">
            <label for="GroupIdLimit">Limited to Group</label>
            <select class="o-input select2" id="GroupIdLimit" name="GroupIdLimit" required="required">
                <option value="-1"></option>
                <?php
                    $groups = $this->member->getGroups();
                    foreach($groups as $group) {
                        echo '<option value="' . $group->id . '">' . $group->Name . '</option>';
                    }
                    ?>
            </select>
            <small class="form-text text-muted">Select one.</small>
        </div>
        <input type="submit" class="btn btn-primary" name="go create">

    </form>
    <?php
}
?>
</div>
<script type="text/javascript">
$(function () {
    var date = moment($('#Ended').val(), 'YYYY-MM-DD').toDate();
    let pollEnd = $('#Ended');
    pollEnd.datetimepicker({
        date: date,
        format: 'YYYY-MM-DD',
        keepInvalid: true,
    });
});
</script>
