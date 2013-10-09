<?php
if ($count == 0) : ?>
    <tr><th><?php echo $words->get('Suggestion'); ?></th>
    <th></th></tr>
<?php endif;
echo '<tr class="' . (($count % 2) ? 'highlight' : 'blank') . '">';
echo '<td class="description">';
echo '<h3><a href="suggestions/' . $suggestion->id . '">' . htmlspecialchars($suggestion->summary) . '</a></h3>';
echo '<p>' . $this->purifier->purify($layoutbits->truncate_words($suggestion->description, 50)) . '</p></td>';
if ($suggestion->state == SuggestionsModel::SUGGESTIONS_REJECTED) {
    echo '<td class="details">' . $words->get('SuggestionsRejected') . '</td>';
} else {
    echo '<td class="details">' . $words->get('SuggestionsDuplicate') . '</td>';
}
echo '</tr>';
?>