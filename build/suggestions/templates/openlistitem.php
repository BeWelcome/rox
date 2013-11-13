<?php
if ($count == 0) : ?>
    <tr><th class="description"><?php echo $words->get('Suggestion'); ?></th>
    <th class="details"></th>
    <th class="details"><?php echo $words->get('SuggestionNumberOfPosts') . ' /<br />' .
            $words->get('SuggestionNbOptions'). ' /<br />' .
            $words->get('SuggestionNbVotes'); ?></th>
    <th class="details"><?php echo $words->get('SuggestionsNextPhaseStarts'); ?></th></tr>
<?php endif;
echo '<tr class="' . (($count % 2) ? 'highlighttop' : 'blanktop') . '">';
echo '<td class="description">';
echo '<h3><a href="suggestions/' . $suggestion->id . '/discuss">' . htmlspecialchars($suggestion->summary) . '</a></h3>';
echo "</td>";
switch($suggestion->state) {
	case SuggestionsModel::SUGGESTIONS_DISCUSSION:
	    echo '<td></td>';
	    break;
	case SuggestionsModel::SUGGESTIONS_ADD_OPTIONS:
        echo '<td class="details"><a href="suggestions/' . $suggestion->id . '/addoptions">'
            . '<img src="images/icons/add.png" alt="' . $words->getBuffered('SuggestionsAddOptions') . '" /></a><br /><a href="suggestions/'
            . $suggestion->id . '/addoptions">' . $words->getBuffered('SuggestionsAddOptions') . '</a></td>';
        break;
	case SuggestionsModel::SUGGESTIONS_VOTING:
	    if (count($suggestion->memberVotes)) {
            echo '<td class="details"><a href="suggestions/' . $suggestion->id . '/vote">'
                . '<img src="images/icons/tick.png" alt="' . $words->getBuffered('SuggestionsReviewVote') . '" /></a><br /><a href="suggestions/'
                . $suggestion->id . '/vote">' . $words->getBuffered('SuggestionsReviewVote') . '</a></td>';
	    } else {
            echo '<td class="details"><a href="suggestions/' . $suggestion->id . '/vote">'
                . '<img src="images/icons/tick.png" alt="' . $words->getBuffered('SuggestionsVote') . '" /></a><br /><a href="suggestions/'
                . $suggestion->id . '/vote">' . $words->getBuffered('SuggestionsVote') . '</a></td>';
	    }
        break;
}
echo '<td class="details">' . $suggestion->posts;
if ($suggestion->state != SuggestionsModel::SUGGESTIONS_DISCUSSION) {
    echo ' / ' . $suggestion->optionsVisibleCount;
}
if ($suggestion->state == SuggestionsModel::SUGGESTIONS_VOTING) {
    echo ' / ' . $suggestion->voteCount;
}
echo '</td>';
echo '<td class="details">' . date('Y-m-d', strtotime($suggestion->nextstatechange)) . '</td>';
echo '</tr>';
echo '<tr class="' . (($count % 2) ? 'highlightbottom' : 'blankbottom') . '"><td colspan="4">';
echo $this->purifier->purify($layoutbits->truncate_words($suggestion->description, 100));
echo '</td></tr>';
?>