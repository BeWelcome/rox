<?php
if ($count == 0) : ?>
    <tr><th><?php echo $words->get('Suggestion'); ?></th>
    <th></th></tr>
<?php endif;
echo '<tr class="' . (($count % 2) ? 'highlighttop' : 'blanktop') . '">';
echo '<td class="description">';
echo '<h3><a href="suggestions/' . $suggestion->id . '">' . htmlspecialchars($suggestion->summary) . '</a></h3></td>';
if ($suggestion->state == SuggestionsModel::SUGGESTIONS_REJECTED) {
    echo '<td class="details">' . $words->get('SuggestionsRejected') . '</td>';
} else {
    echo '<td class="details">' . $words->get('SuggestionsDuplicate') . '</td>';
}
echo '</tr>';
echo '<tr class="' . (($count % 2) ? 'highlightbottom' : 'blankbottom') . '">';
echo '<td class="description" colspan="2">';
echo '<p>' . $this->purifier->purify($layoutbits->truncate_words($suggestion->description, 50)) . '</p></td>';
echo '</tr>'
?>