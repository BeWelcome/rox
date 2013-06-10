<?php $this->pager->render(); ?>
<table style="width: 99%" class='decisionslist'>
<tr><th>Suggestion</th><th style="text-align: center">End date</th><th style="text-align: center">Votes</th></tr>
<?php
$count= 0;
foreach($this->suggestions as $suggestion) {
    $voteEndDate = strtotime($suggestion->votingended);
     
    echo '<tr class="' . $background = (($count % 2) ? 'highlight' : 'blank') . '">';
    echo '<td style="padding-bottom: 30px; width: 80%;">';
    echo '<h3><a href="suggestions/' . $suggestion->id . '">' . htmlspecialchars($suggestion->summary) . '</a></h3></td>';
    echo '<td style="text-align: center;">' . date('d.m.Y', $voteEndDate) . '</td>';
    echo '<td style="text-align: center;">' . $suggestion->votes . '</td>';
    echo '</tr>';
    $count++;
}
?>
</table>
<?php $this->pager->render(); ?>
