<?php
if ($count == 0) : ?>
    <tr><th><?php echo $words->get('Suggestion'); ?></th><th style="text-align: center"></th><th style="text-align: center"></th></tr>
<?php endif;
echo '<tr class="' . (($count % 2) ? 'highlight' : 'blank') . '">';
echo '<td style="padding-bottom: 20px; width: 80%;">';
echo '<h3><a href="suggestions/' . $suggestion->id . '">' . htmlspecialchars($suggestion->summary) . '</a></h3></td>';
if ($suggestion->state == SuggestionsModel::SUGGESTIONS_REJECTED) {
    echo '<td>Rejected</td>';
} else {
    echo '<td>Duplicate</td>';
}
echo '</tr>';
?>