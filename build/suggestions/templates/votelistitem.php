<?php
if ($count == 0) : ?>
    <tr><th class="description"><?php echo $words->get('Suggestion'); ?></th>
    <th></th>
    <th class="details"><?php echo $words->get('SuggestionVoteEndDate'); ?></th>
    <th class="details"><?php echo $words->get('SuggestionNbVotes'); ?></th></tr>
<?php endif;
echo '<tr class="' . $background = (($count % 2) ? 'highlighttop' : 'blanktop') . '">';
echo '<td class="description">';
echo '<h3><a href="suggestions/' . $suggestion->id . '">' . htmlspecialchars($suggestion->summary);
if (count($suggestion->memberVotes)) {
    echo ' (' . $words->get('SuggestionVoted') . ')';
}
echo '</a></h3></td>';
if ($this->hasSuggestionRight) {
    if ($suggestion->exclusionsSet) {
        echo '<td class="details"><a href="suggestions/' . $suggestion->id . '/exclude">'
            . '<img src="images/icons/cancel.png" alt="' . $words->getBuffered('SuggestionsReviewExclusions') . '" /></a><br /><a href="suggestions/'
            . $suggestion->id . '/exclude">' . $words->getBuffered('SuggestionsReviewExclusions') . '</a></td>';
    } else {
        echo '<td class="details"><a href="suggestions/' . $suggestion->id . '/exclude">'
            . '<img src="images/icons/cancel.png" alt="' . $words->getBuffered('SuggestionsSetExclusions') . '" /></a><br /><a href="suggestions/'
            . $suggestion->id . '/exclude">' . $words->getBuffered('SuggestionsSetExclusions') . '</a></td>';
    }
} else {
    echo '<td></td>';
}
echo '<td class="details">' . date('Y-m-d', strtotime($suggestion->laststatechanged) + SuggestionsModel::DURATION_VOTING) . '</td>';
echo '<td class="details">' . $suggestion->voteCount . '</td>';
echo '</tr>';
echo '<tr class="' . $background = (($count % 2) ? 'highlightbottom' : 'blankbottom') . '">';
echo '<td colspan="4">' . $this->purifier->purify($layoutbits->truncate_words($suggestion->description, 100)) . '</td>';
echo '</tr>';
?>