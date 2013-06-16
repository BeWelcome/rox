<?php $this->pager->render(); ?>
<table style="width: 99%" class='decisionslist'>
<tr><th><?php echo $words->get('Suggestion'); ?></th><th style="text-align: center"><?php echo $words->get('SuggestionVoteEndDate'); ?></th><th style="text-align: center"><?php echo $words->get('SuggestionNbVotes'); ?></th></tr>
<?php
$count= 0;
foreach($this->suggestions as $suggestion) {
    echo '<tr class="' . $background = (($count % 2) ? 'highlight' : 'blank') . '">';
    echo '<td style="padding-bottom: 30px; width: 80%;">';
    echo '<h3><a href="suggestions/' . $suggestion->id . '">' . htmlspecialchars($suggestion->summary) . '</a></h3></td>';
    echo '<td style="text-align: center;">' . date('d.m.Y', $suggestion->votingendts) . '</td>';
    echo '<td style="text-align: center;">' . $suggestion->votes . '</td>';
//    echo '<td style="text-align: center;"><a href="suggestions/' . $suggestion->id . '/calculate"><img src="images/icons/calculator.png" /></a></td>';
    echo '</tr>';
    $count++;
}
?>
</table>
<?php $this->pager->render(); ?>
