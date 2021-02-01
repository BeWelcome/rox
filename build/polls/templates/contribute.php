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
// get current request
$request = PRequest::get()->request;

$words = new MOD_words();

if (!isset($vars['errors']) || !is_array($vars['errors'])) {
    $vars['errors'] = array();
}

$Data=$this->_data ; // Retrieve the data to display (set by the controller)
$list=$Data->Choices ; // Retrieve the possible choices

?>
<h2><?= $words->fTrad($Data->rPoll->Title); ?></h2>
<p><?= $words->fTrad($Data->rPoll->Description); ?></p>
<?php
        if ($Data->rPoll->Anonym=="Yes") {
            ?>
            <p class="note"><?= $words->getFormatted("pols_IsAnonymExplanation"); ?></p>
            <?php
        }
        else {
            ?>
            <p class="note"><?= $words->getFormatted("pols_IsNotAnonymExplanation"); ?></p>
            <?php
        }

$styles = array( 'highlight', 'blank' ); // alternating background for table rows
$iiMax = count($list) ; // This retrieve the number of polls
$IdPoll=$Data->rPoll->id ;
$rPoll=$Data->rPoll ;
?>

<form name="contribute" action="polls/vote"  id="idcontribute" method="post">
    <input type="hidden" name="IdPoll" value="<?=$IdPoll ?>">
    <!-- The following will disable the nasty PPostHandler -->
    <input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>

    <input type="hidden" name="<?=$callbackId ?>"  value="1"/>


<?php if ($list != false) { ?>
        <h3><?=$words->getFormatted("polls_choice")." (".$words->getFormatted("polls_typechoice_".$Data->rPoll->TypeOfChoice).")" ?></h3>
<?php }
//if ($rPoll->Ended!='0000-00-00 00:00:00') {
		echo "<p>",$words->getFormatted("polls_willend_on",$rPoll->Ended)."</p>" ;
//	}
?>

<?php
for ($ii = 0; $ii < $iiMax; $ii++) {
    $p = $list[$ii];
    ?>
        <div class="form-check">
            <?php
            if ($Data->rPoll->TypeOfChoice=="Exclusive") {
                ?>
                <input class="form-check-input" type="radio" id="choice<?=$ii;?>" name="ExclusiveChoice" value="<?php echo $p->id; ?>" />
                <label class="form-check-label" for="choice<?=$ii;?>"><?php echo $words->fTrad($p->IdChoiceText); ?></label>
                <?php
            }
            if ($Data->rPoll->TypeOfChoice=="Inclusive") {
                ?>
                <input class="form-check-input" type="checkbox" id="choice<?=$ii;?>" name="choice_<?=$p->id;?>" />
                <label class="form-check-label" for="choice<?=$ii;?>"><?php echo $words->fTrad($p->IdChoiceText); ?></label>
                <?php
            }
            ?>
        </div>
<?php
}
?>
<?php
if ($Data->rPoll->AllowComment=="Yes") {
    ?>
    <div class="form-group"><label for="comment"><?=$words->getFormatted("polls_comment");?></label>
    <textarea class="o-input" id="comment" name="Comment" cols="60" rows="4"></textarea>
    </div>
    <?php
}
else {
    ?>
    <input type="hidden" name="Comment" value="" />
    <?php
}
?>
<input type="submit" class="btn btn-primary" value="<?=$words->getFormatted("polls_vote");?>" />
</form>
