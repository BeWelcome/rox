<?php
if ($count == 0) : ?>
    <tr><th class="description"><?php echo $words->get('Suggestion'); ?></th>
    <th class="details"><?php echo $words->get('SuggestionVoteEndDate'); ?></th>
    <th class="details"><?php echo $words->get('SuggestionNbVotes'); ?></th></tr>
<?php endif;
echo '<tr class="' . $background = (($count % 2) ? 'highlighttop' : 'blanktop') . '">';
echo '<td class="description">';
echo '<h3><a href="suggestions/' . $suggestion->id . '">' . htmlspecialchars($suggestion->summary);
if (count($suggestion->votes)) {
    echo ' (' . $words->get('SuggestionVoted') . ')';
}
echo '</a></h3></td>';
echo '<td class="details">' . date('Y-m-d', strtotime($suggestion->laststatechanged) + SuggestionsModel::DURATION_VOTING) . '</td>';
echo '<td class="details">' . $suggestion->voteCount . '</td>';
echo '</tr>';
echo '<tr class="' . $background = (($count % 2) ? 'highlightbottom' : 'blankbottom') . '">';
echo '<td colspan="3">' . $this->purifier->purify($layoutbits->truncate_words($suggestion->description, 100)) . '</td>';
echo '</tr>';
?>