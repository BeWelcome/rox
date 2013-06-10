<?php $this->pager->render(); ?>
<table style="width: 99%" class='decisionslist'>
<?php
$count= 0;
foreach($this->suggestions as $suggestion) {
    echo '<tr class="' . $background = (($count % 2) ? 'highlight' : 'blank') . '">';
    echo '<td style="padding-bottom: 30px; width: 90%;">';
    echo '<h3><a href="suggestions/' . $suggestion->id . '">' . htmlspecialchars($suggestion->summary) . '</a></h3></td>';
    echo '<td style="text-align: center;">' . $suggestion->votes . '</td>';
    echo '</tr>';
    $count++;
}
?>
</table>
<?php $this->pager->render(); ?>
