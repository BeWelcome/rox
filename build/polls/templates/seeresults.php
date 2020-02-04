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
<h2><?=$words->fTrad($Data->rPoll->Title);?></h2>
<p><?=$words->fTrad($Data->rPoll->Description);?></p>
<p><?=$words->getFormatted("polls_peoplehavecontributed",$Data->rPoll->Started,$Data->rPoll->Ended,$Data->TotContrib);?></p>
<?php
$styles = array( 'highlight', 'blank' ); // alternating background for table rows
$iiMax = count($list) ; // This retrieve the number of polls
$IdPoll=$Data->rPoll->id ;
?>
<div class="table-responsive">
<table class="table table-striped">

<?php if ($list != false) { ?>
    <tr>
        <th><?=$words->getFormatted("polls_choice")." (".$words->getFormatted("polls_typechoice_".$Data->rPoll->TypeOfChoice).")" ?></th>
        <th><?=$words->getFormatted("poll_results") ?></th>
    </tr>
<?php } ?>

<?php
$NonBlankCount=0 ;
for ($ii = 0; $ii < $iiMax; $ii++) {
    $p = $list[$ii];
        $NonBlankCount=$NonBlankCount+$p->Counter ;
    ?>
    <tr class="<?=$styles[$ii%2] ?>">
        <td ><?php echo $words->fTrad($p->IdChoiceText); ?></td>
        <td>
            <?php echo sprintf("%4d (%3.1f%%)",$p->Counter,$p->Percent) ; ?>
        </td>
    </tr>
<?php
}
?>
<?php
    if ($Data->rPoll->TypeOfChoice=="Exclusive") { // If exclusive, display the number of blank vote

    ?>
    <tr class="<?=$styles[$ii%2] ?>">
        <td><strong>blank votes</strong></td>
        <td >
        <?php 
        echo "<strong>",sprintf("%4d (%3.1f%%)",($Data->TotContrib-$NonBlankCount),(($Data->TotContrib-$NonBlankCount)/$Data->TotContrib)*100),"</strong>" ;
        ?>
        </td>
    </tr>
    <?php
    }
    ?>
    <tr class="<?=$styles[$ii%2] ?>">
        <td><strong>Total</strong></td>
        <td>
            <?php 
            echo "<b>",sprintf("%4d (%3.1f%%)",$Data->TotContrib,100),"</b>" ;
            ?>
        </td>
    </tr>
</table>
</div>
<?php
if ($Data->rPoll->AllowComment=="Yes") {
?>
    <h3><?=$words->getFormatted("polls_commentspeoplehavemade",count($Data->Contributions))?></h3>
<?php  $jj=0 ;
    $ii=0 ;
    for ($jj=0;$jj<count($Data->Contributions);$jj++) {
        $Contrib=$Data->Contributions[$jj] ;
        if (empty($Contrib->comment)) continue ;
        $ii++ ;
        ?>
    <div class="<?=$styles[$ii%2] ?>">
        <p><?=$Contrib->Username.': <em>'.$Contrib->comment."</em>"?></p>
    </div>
        <?php
    }
}

