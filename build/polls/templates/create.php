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
    <p class="error"><?=$errormessage; ?></p>
    <?
}
$words = new MOD_words();
$Data=$this->_data  ;
if (isset($Data->rPoll->id)) { // Form for update
    $rr=$Data->rPoll ;


?>
<p class="note">
This is the page to update a poll

You need to fill the following fields

Use English language for now only

</p>

<form name="contribute" action="polls/doupdatepoll"  id="idupdatepoll" method="post">
<!-- The following will disable the nasty PPostHandler -->
<input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>

<input type="hidden" name="<?=$callbackId ?>"  value="1"/>
<input type="hidden" name="IdPoll"  value="<?=$Data->rPoll->id?>"/>

<table class="full">
    <tr>
        <td><label for="Status">Polls Status:</label></td>
        <td>
            <?
            $sChoice=array("Project","Open","Close") ;
            if (empty($rr->Status)) $rr->Status="Project" ; // By default a poll will be at Project Status
                echo "<select id=\"Status\" name=\"Status\">\n" ;
            for ($ii=0;$ii<count($sChoice);$ii++) {
                echo "<option value=\"".$sChoice[$ii]."\"" ;
                if ($sChoice[$ii]==$rr->Status) {
                    echo " selected " ;
                }
                echo ">",$sChoice[$ii],"</option>\n" ;
            }
            echo "</select>\n"  ;
            ?>
        </td>
    </tr>
    <tr>
        <td><label for="TypeOfChoice" >Type of choice:</label></td>
        <td>
            <?
            $sChoice=array('Exclusive', 'Inclusive', 'Ordered') ;
            if (empty($rr->TypeOfChoice)) $rr->TypeOfChoice="Exclusive" ; // By default a poll will be Exclusive
                echo "<select id=\"TypeOfChoice\" name=\"TypeOfChoice\">\n" ;
            for ($ii=0;$ii<count($sChoice);$ii++) {
                echo "<option value=\"".$sChoice[$ii]."\"" ;
                if ($sChoice[$ii]==$rr->TypeOfChoice) {
                    echo " selected " ;
                }
                echo ">",$sChoice[$ii],"</option>\n" ;
            }
            echo "</select>\n"  ;
            ?>
        </td>
    </tr>
    <tr>
        <td><label for="Ended" >Poll will end on:</label></td>
        <td>
            <?
            $ii=0 ;
            if (empty($rr->Ended)) {
                $rr->Ended="0000-00-00 00:00:00" ; // By default a poll will not have a end
            }
            echo "<input id=\"Ended\" name=\"Ended\" Value=\"" .$rr->Ended."\" type=\"text\" />" ;
            ?>
        </td>
    </tr>
    <tr>
        <td><label for="CreatorUsername" >Owner:</label></td>
        <td>
            <?
            $ii=0 ;
            if (empty($rr->CreatorUsername)) {
                $rr->CreatorUsername=$_SESSION['Username'] ; // By default a poll is owned by the current member
            }
            echo "<input id=\"CreatorUsername\" name=\"CreatorUsername\" Value=\"" .$rr->CreatorUsername."\" type=\"text\" />" ;
            ?>
        </td>
    </tr>
    <tr>
        <td><label for="Allow Comment" >Allow Comments</label></td>
        <td>
            <?
            if (empty($rr->AllowComment)) $rr->AllowComment="No" ; // By default a poll is not aimed to collect comments
            $sChoice=array("Yes","No") ;
                echo "<select id=\"AllowComment\"name=\"AllowComment\">\n" ;
            for ($ii=0;$ii<count($sChoice);$ii++) {
                echo "<option value=\"".$sChoice[$ii]."\"" ;
                if ($sChoice[$ii]==$rr->AllowComment) {
                    echo " selected " ;
                }
                echo ">",$sChoice[$ii],"</option>\n" ;
            }
            echo "</select>\n"  ;
            ?>
        </td>
    </tr>
    <tr>
        <td><label for="Anonym" >Anonymous Poll</label></td>
        <td>
            <?
            if (empty($rr->Anonym)) $rr->Anonym="No" ; // By default a poll is not aimed to collect comments
            $sChoice=array("Yes","No") ;
                echo "<select id=\"Anonym\"name=\"Anonym\">\n" ;
            for ($ii=0;$ii<count($sChoice);$ii++) {
                echo "<option value=\"".$sChoice[$ii]."\"" ;
                if ($sChoice[$ii]==$rr->Anonym) {
                    echo " selected " ;
                }
                echo ">",$sChoice[$ii],"</option>\n" ;
            }
            echo "</select>\n"  ;
            ?>
        </td>
    </tr>
    <tr>
        <td><label for="ResultVisibility" >Result visibility:</label></td>
        <td>
            <?
            if ($rr->ResultsVisibility=="") $rr->ResultsVisibility="No" ; // By default a poll is not aimed to collect comments
            $sChoice=array("Not Visible","Visible","VisibleAfterVisit") ;
                echo "<select id=\"ResultVisibiliyt\" name=\"ResultsVisibility\">\n" ;
            for ($ii=0;$ii<count($sChoice);$ii++) {
                echo "<option value=\"".$sChoice[$ii]."\"" ;
                if ($sChoice[$ii]==$rr->ResultsVisibility) {
                    echo " selected " ;
                }
                echo ">",$sChoice[$ii],"</option>\n" ;
            }
            echo "</select>\n"  ;
            ?>
        </td>
    </tr>
    <tr>
        <td><label for="Title" >Poll Title:</label></td>
        <td><input type="text" ID="Titile" name="Title" size="60" value="<?=$words->fTrad($Data->rPoll->Title)?>"/></td>
    </tr>
    <tr>
        <td><label for="Description" >Poll Description:</label></td>
        <td><textarea id="Description" name="Description" cols="60" rows="5"><?=$words->fTrad($Data->rPoll->Description) ?></textarea></td>
    </tr>
    <tr>
        <td><label for="GroupIdLimit">Limited to groups Id : </label></td>
        <td><input type="text" id="GroupIdLimit" name="GroupIdLimit" size="8" class="long" value="
<?php for ($ii=0;$ii<count($Data->IdGroupRestricted);$ii++) {
	if ($ii>0) echo "," ;
	echo $Data->IdGroupRestricted[$ii]->IdGroup ;
}
?>
"> (experimental comma separated)</td>
    </tr>
	<?php if (!empty($rr->WhereToRestrictMember)) {
			echo "<tr bgcolor='Yellow'><td>Special restriction<br />(ask admin)</td><td>".$rr->WhereToRestrictMember."</td></tr>" ;
		}
	?>
</table>
<p class="center"><input type="submit" class="button" value="go update"></p>
</form>

<hr>

<table class="full">
<?
for ($ii=0;$ii<count($Data->Choices);$ii++) {
    $cc=$Data->Choices[$ii] ;
    ?>
<form name="updatechoice_<?=$ii?>" action="polls/updatechoice"  id="idupdatechoice_<?=$ii?>" method="post">
    <!-- The following will disable the nasty PPostHandler -->
    <input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>

    <input type="hidden" name="<?=$callbackId ?>"  value="1"/>
	<input name="IdLanguage"  value="<?php echo $_SESSION["IdLanguage"] ; ?>" type="hidden"/>
    <input type="hidden" name="IdPoll"  value="<?=$Data->rPoll->id?>"/>
    <input type="hidden" name="IdPollChoice" value="<?=$cc->id?>"/>
    <input type="hidden" name="IdChoiceText" value="
    <?=$cc->IdChoiceText?>
    "/>
<tr>
    <td><label for="Option">Option:</label></td>
    <td>
        <textarea id="Option" name="ChoiceText" cols="60" rows="2"><?=$words->fTrad($cc->IdChoiceText)?></textarea>
        <input type="submit" class="button" value="update choice" />
    <td>
</tr>
</form>
    <?
}
?>


<form name="addchoice" action="polls/addchoice"  id="idaddchoice" method="post">
    <!-- The following will disable the nasty PPostHandler -->
    <input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>

    <input type="hidden" name="<?=$callbackId ?>"  value="1"/>
    <input type="hidden" name="IdLanguage"  value="<?$_SESSION["IdLanguage"]?>"/>
    <input type="hidden" name="IdPoll"  value="<?=$Data->rPoll->id?>"/>
        <tr>
            <td><label for="NewOption">New option:</label></td>
            <td><textarea id="NewOption" name="ChoiceText" cols="60" rows="2"></textarea>
                <input type="submit" class="button" value="Add" />
            </td>
        </tr>
    
</form>
</table>
<?
}
else { // form for create
?>
<p class="note">
This is the page to create a new poll

You need to fill the following fields

Use English language for now only

</p>

<form name="contribute" action="polls/createpoll"  id="idcreatepoll" method="post">
<!-- The following will disable the nasty PPostHandler -->
<input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>

<input type="hidden" name="<?=$callbackId ?>"  value="1"/>
<input name="IdLanguage"  value="<?php echo $_SESSION["IdLanguage"] ; ?>" type="hidden"/>

<table>
    <tr>
        <td><label for="Title">Poll Title:</label></td>
        <td><input type="text" id="Title" name="Title" size="60" class="long"></td>
    </tr>
    <tr>
        <td><label for="Description">Poll Description:</label></td>
        <td><textarea id="Description" name="Description" rows="5" cols="60" class="long" ></textarea></td>
    </tr>
    <tr>
        <td><label for="GroupIdLimit">Limited to groups Id : </label></td>
        <td><input type="text"  id="GroupIdLimit" name="GroupIdLimit" size="8" class="long"> (experimental comma separated)</td>
    </tr>
</table>
<p class="center"><input type="submit" class="button" name="go create"></p>

</form>

<?
}
?>
