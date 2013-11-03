<?php
if ($count == 0) : ?>
    <tr><th class="description"><?php echo $words->get('Suggestion'); ?></th>
    <th class="details"><?php echo $words->get('SuggestionVoteEndDate'); ?></th>
    <th class="details"><?php echo $words->get('SuggestionNbVotes'); ?></th></tr>
<?php endif;
    echo '<tr class="' . $background = (($count % 2) ? 'highlight' : 'blank') . '">';
    echo '<td class="description">';
    echo '<h3><a href="suggestions/' . $suggestion->id . '">' . htmlspecialchars($suggestion->summary) . '</a></h3>';
    echo '<p>' . $this->purifier->purify($layoutbits->truncate_words($suggestion->description, 25)) . '</p></td>';
    echo '<td class="details">' . date('Y-m-d', strtotime($suggestion->laststatechanged) + SuggestionsModel::DURATION_VOTING) . '</td>';
    echo '<td class="details">' . $suggestion->voteCount . '</td>';
    echo '</tr>';
?>