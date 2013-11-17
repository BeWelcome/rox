<?php
if ($count == 0) : ?>
    <tr><th><?php echo $words->get('Suggestion'); ?></th><th class="details"></th></tr>
<?php endif;
echo '<tr class="' . (($count % 2) ? 'highlighttop' : 'blanktop') . '">';
echo '<td class="description">';
echo '<h3><a href="suggestions/' . $suggestion->id . '">' . htmlspecialchars($suggestion->summary) . '</a></h3></td>';
if ($suggestion->state == SuggestionsModel::SUGGESTIONS_IMPLEMENTED) {
    echo '<td class="details">' . $words->get('SuggestionsImplemented') . '</td>';
} else {
    echo '<td class="details">' . $words->get('SuggestionsInDevelopment') . '</td>';
}
echo '</tr>';
echo '<tr class="' . (($count % 2) ? 'highlightbottom' : 'blankbottom') . '">';
echo '<td colspan="2">';
echo '<p>' . $this->purifier->purify($layoutbits->truncate_words($suggestion->description, 50)) . '</p></td>';
echo '</tr>';
?>