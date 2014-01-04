<?php
if ($count == 0) : ?>
    <tr><th><?php echo $words->get('Suggestion'); ?></th>
    <th><?php echo $words->get('SuggestionCreated'); ?></th>
    <th><?php echo $words->get('SuggestionState'); ?></th></tr>
<?php endif;
echo '<tr class="' . (($count % 2) ? 'highlighttop' : 'blanktop') . '">';
echo '<td class="description">';
echo '<h3><a href="suggestions/' . $suggestion->id . '/results">' . htmlspecialchars($suggestion->summary) . '</a></h3></td>';
echo '<td class="details">' . $suggestion->created . '</td>';
$stateText = "";
switch($suggestion->state) {
    case SuggestionsModel::SUGGESTIONS_DUPLICATE:
        $stateText = $words->get('SuggestionsDuplicate');
        break;
    case SuggestionsModel::SUGGESTIONS_REJECTED:
        $stateText = $words->get('SuggestionsRejected');
        break;
    case SuggestionsModel::SUGGESTIONS_RANKING:
        $stateText = $words->get('SuggestionsRanking');
        break;
    case SuggestionsModel::SUGGESTIONS_IMPLEMENTING:
        $stateText = $words->get('SuggestionsInDevelopment');
        break;
    case SuggestionsModel::SUGGESTIONS_IMPLEMENTED:
        $stateText = $words->get('SuggestionsImplemented');
        break;
    case SuggestionsModel::SUGGESTIONS_DEV:
        $stateText = $words->get('SuggestionsDevBoth');
        break;
}
echo '<td class="details">' . $stateText . '</td>';
echo '</tr>';
?>